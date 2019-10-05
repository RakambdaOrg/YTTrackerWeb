<?php
	namespace YTT
	{

		use PDO;

		final class DBConnection
		{
			/**
			 * @var PDO
			 */
			private static $conn;

			/**
			 * @return PDO
			 */
			public static function getConnection()
			{
				if(!DBConnection::$conn || !is_resource(DBConnection::$conn))
				{
					/** @noinspection PhpIncludeInspection */
					$infos = include __DIR__ . '/../../../../../configs/database.config.php';
					DBConnection::$conn = $pdo = new PDO("mysql:host=" . $infos['host'] . ";dbname=" . $infos['database'] . ";charset=utf8", $infos['username'], $infos['password']);
					DBConnection::$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
					DBConnection::$conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
					DBConnection::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
					DBConnection::$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
				}
				return DBConnection::$conn;
			}

			public static function close()
			{
				DBConnection::$conn = null;
			}
		}
	}
