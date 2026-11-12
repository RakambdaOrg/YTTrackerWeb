<?php

declare(strict_types=1);

namespace YTT;

use PDOException;

final class UserRepository
{
	private const int MAX_RANGE = 3650;
	private const string DEFAULT_USERNAME = 'Anonymous';

	/**
	 * @param ?int $range only users with records in the last $range days; null for every user
	 * @return list<array{uuid: string, username: string}>
	 */
	public function list(?int $range): array
	{
		if ($range === null) {
			$stmt = Database::get()->prepare('SELECT UUID, Username FROM YTT_Users');
			$stmt->execute();
		} else {
			$stmt = Database::get()->prepare('SELECT DISTINCT(YTT_Users.UUID), Username FROM YTT_Users LEFT JOIN YTT_Records YR ON YTT_Users.ID = YR.UserId WHERE StatDay >= DATE_SUB(NOW(), INTERVAL :range DAY)');
			$stmt->execute([':range' => min($range, self::MAX_RANGE)]);
		}
		return array_map(
			static fn(array $row) => ['uuid' => $row['UUID'], 'username' => self::display($row['Username'])],
			$stmt->fetchAll(),
		);
	}

	/** @return ?string the display name, or null for an unknown UUID */
	public function username(string $uuid): ?string
	{
		$stmt = Database::get()->prepare('SELECT UUID, Username FROM YTT_Users WHERE UUID=:uuid');
		$stmt->execute([':uuid' => $uuid]);
		$row = $stmt->fetch();
		return $row ? self::display($row['Username']) : null;
	}

	/**
	 * Creates the user if needed. Empty or "Anonymous" stores null.
	 *
	 * @return bool false if the query failed
	 */
	public function setUsername(string $uuid, mixed $username): bool
	{
		if ($username === self::DEFAULT_USERNAME || !$username) {
			$username = null;
		}
		try {
			Database::get()
				->prepare('INSERT INTO `YTT_Users`(`UUID`, `Username`) VALUES(:uuid, :username) ON DUPLICATE KEY UPDATE `Username`=:username')
				->execute([':uuid' => $uuid, ':username' => $username]);
		} catch (PDOException $e) {
			error_log((string) $e);
			return false;
		}
		return true;
	}

	/**
	 * Write check, trust on first use: a user without a key accepts any write (old extensions send none),
	 * the first key sent locks the user, then only that key is accepted.
	 * Recovery for a user locked by someone else: set KeyHash to NULL by hand.
	 */
	public function authorize(string $uuid, mixed $key): bool
	{
		$key = is_string($key) && $key !== '' ? $key : null;
		$stmt = Database::get()->prepare('SELECT KeyHash FROM YTT_Users WHERE UUID=:uuid');
		$stmt->execute([':uuid' => $uuid]);
		$stored = $stmt->fetchColumn() ?: null;
		if ($stored !== null) {
			return $key !== null && hash_equals($stored, hash('sha256', $key));
		}
		if ($key === null) {
			return true;
		}
		// IFNULL: a concurrent first key wins; the re-check then rejects this one.
		Database::get()
			->prepare('INSERT INTO YTT_Users(`UUID`, `KeyHash`) VALUES(:uuid, :hash) ON DUPLICATE KEY UPDATE `KeyHash`=IFNULL(`KeyHash`, :hash)')
			->execute([':uuid' => $uuid, ':hash' => hash('sha256', $key)]);
		return $this->authorize($uuid, $key);
	}

	public function id(string $uuid): ?int
	{
		$stmt = Database::get()->prepare('SELECT ID FROM YTT_Users WHERE UUID=:uuid');
		$stmt->execute([':uuid' => $uuid]);
		$id = $stmt->fetchColumn();
		return $id === false ? null : (int) $id;
	}

	public function idOrCreate(string $uuid): int
	{
		if (($id = $this->id($uuid)) !== null) {
			return $id;
		}
		Database::get()->prepare('INSERT INTO YTT_Users(`UUID`) VALUES(:uuid)')->execute([':uuid' => $uuid]);
		return (int) Database::get()->lastInsertId();
	}

	private static function display(?string $username): string
	{
		return $username ?: self::DEFAULT_USERNAME;
	}
}
