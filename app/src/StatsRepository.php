<?php

declare(strict_types=1);

namespace YTT;

use PDOException;

final class StatsRepository
{
	private const int DEFAULT_RANGE = 31;
	private const int MAX_RANGE = 3650;

	public function __construct(private readonly UserRepository $users)
	{
	}

	/** The `range` param in days: an int (or numeric string), 31 by default, at most 3650. */
	public static function range(mixed $value): int
	{
		$range = filter_var($value, FILTER_VALIDATE_INT);
		return min($range === false ? self::DEFAULT_RANGE : $range, self::MAX_RANGE);
	}

	/** @return list<array{date: string, value: mixed, duration: mixed}> one row per day, durations in ms */
	public function durationSeries(string $uuid, StatType $type, int $days): array
	{
		$stmt = Database::get()->prepare('SELECT SUM(`YTT_Records`.`Stat`) AS `Stat`, `StatDay` AS `StatDay` FROM `YTT_Records` LEFT JOIN `YTT_Users` ON `YTT_Records`.`UserId` = `YTT_Users`.`ID` WHERE YTT_Users.UUID = :uuid AND `YTT_Records`.`Type` = :type AND `StatDay` >= DATE_SUB(NOW(), INTERVAL :days DAY) AND `StatDay` <= DATE_ADD(NOW(), INTERVAL 1 DAY) GROUP BY `StatDay`');
		$stmt->execute([':uuid' => $uuid, ':days' => $days, ':type' => $type->value]);
		return array_map(
			static fn(array $row) => ['date' => $row['StatDay'], 'value' => $row['Stat'], 'duration' => $row['Stat']],
			$stmt->fetchAll(),
		);
	}

	/** @return list<array{date: string, value: mixed}> opened videos per day */
	public function countSeries(string $uuid, int $days): array
	{
		$stmt = Database::get()->prepare('SELECT SUM(`Amount`) AS Total, StatDay AS `StatDay` FROM `YTT_Records` WHERE `Type`=:type AND `UserId`=:userId AND StatDay >= DATE_SUB(NOW(), INTERVAL :days DAY) AND StatDay <= DATE_ADD(NOW(), INTERVAL 1 DAY) GROUP BY `StatDay`');
		$stmt->execute([':userId' => $this->users->id($uuid), ':days' => $days, ':type' => StatType::Opened->value]);
		return array_map(
			static fn(array $row) => ['date' => $row['StatDay'], 'value' => $row['Total']],
			$stmt->fetchAll(),
		);
	}

	/** @return array<string, array{opened: float, watched: float, count: float}> durations in seconds */
	public function summary(string $uuid): array
	{
		$userId = $this->users->id($uuid);
		$period = fn(int $days) => [
			'opened' => $this->sum($userId, StatType::Opened, $days),
			'watched' => $this->sum($userId, StatType::Watched, $days),
			'count' => $this->count($userId, $days),
		];
		return ['total' => $period(self::MAX_RANGE), 'week' => $period(7), 'day' => $period(1)];
	}

	/**
	 * Adds to the user's row for that day, creating the user if needed.
	 *
	 * @param mixed $type a StatType param; other values are stored as-is, like the old API did
	 * @param string $date "Y-M-D H:m:s", the time part is ignored
	 * @return bool false if the insert failed
	 */
	public function add(string $uuid, mixed $type, mixed $stat, string $date): bool
	{
		$userId = $this->users->idOrCreate($uuid);
		try {
			Database::get()
				->prepare("INSERT INTO `YTT_Records`(`UserId`, `Type`, `Stat`, `StatDay`, `Amount`) VALUES(:userId, :type, :stat, STR_TO_DATE(:timee, '%Y-%m-%d'), 1) ON DUPLICATE KEY UPDATE `Stat`=`Stat`+VALUES(`Stat`), `Amount`=`Amount`+1;")
				->execute([':userId' => $userId, ':type' => StatType::fromParam($type)?->value ?? $type, ':stat' => $stat, ':timee' => $date ?: date('Y-m-d')]);
		} catch (PDOException $e) {
			error_log((string) $e);
			return false;
		}
		Database::get()->prepare('UPDATE `YTT_Users` SET `LastActivity`=CURRENT_TIMESTAMP() WHERE ID=:userId')->execute([':userId' => $userId]);
		return true;
	}

	private function sum(?int $userId, StatType $type, int $days): float
	{
		$stmt = Database::get()->prepare('SELECT SUM(`Stat`)/1000 AS Total FROM `YTT_Records` WHERE `Type`=:type AND `UserId`=:userId AND StatDay >= DATE_SUB(NOW(), INTERVAL :days DAY ) AND StatDay <= DATE_ADD(NOW(), INTERVAL 1 DAY)');
		$stmt->execute([':userId' => $userId, ':days' => $days, ':type' => $type->value]);
		return (float) $stmt->fetchColumn();
	}

	private function count(?int $userId, int $days): float
	{
		$stmt = Database::get()->prepare('SELECT SUM(`Amount`) AS Total FROM `YTT_Records` WHERE `Type`=:type AND `UserId`=:userId AND StatDay >= DATE_SUB(NOW(), INTERVAL :days DAY ) AND StatDay <= DATE_ADD(NOW(), INTERVAL 1 DAY) AND Stat > 0');
		$stmt->execute([':userId' => $userId, ':days' => $days, ':type' => StatType::Opened->value]);
		return (float) $stmt->fetchColumn();
	}
}
