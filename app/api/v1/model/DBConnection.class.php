<?php

	namespace YTT
	{
		use PDO;

		final class DBConnection
		{
			/**
			 * @var \PDO
			 */
			private static $conn;
			/**
			 * @return \PDO
			 */
			public static function getConnection()
			{
				if(!DBConnection::$conn || !is_resource(DBConnection::$conn))
				{
					$infos = include __DIR__ . '/../../../../../configs/database.config.php';
					DBConnection::$conn = $pdo = new PDO("mysql:host=" . $infos['host'] . ";dbname=" . $infos['database'] . ";charset=utf8", $infos['username'], $infos['password']);
				}
				return DBConnection::$conn;
			}
		}
	}