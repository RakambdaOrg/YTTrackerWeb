<?php

declare(strict_types=1);

namespace YTT;

use PDO;

final class Database
{
	private static ?PDO $pdo = null;

	public static function get(): PDO
	{
		return self::$pdo ??= self::connect();
	}

	private static function connect(): PDO
	{
		// The config lives outside the web root (/www/configs); YTT_DB_CONFIG points elsewhere for local checks.
		$config = require getenv('YTT_DB_CONFIG') ?: __DIR__ . '/../../../configs/database.config.php';
		return new PDO(
			"mysql:host={$config['host']};dbname={$config['database']};charset=utf8",
			$config['username'],
			$config['password'],
			[
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				// Same as the old code, so the JSON value types don't change (SUM() decimals stay strings).
				PDO::ATTR_EMULATE_PREPARES => true,
				PDO::ATTR_STRINGIFY_FETCHES => false,
			],
		);
	}
}
