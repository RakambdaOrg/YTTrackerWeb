<?php

	/**Anonymous*/

	namespace YTT
	{
        require_once __DIR__ . "/../../v2/model/UsersHandler.class.php";
        require_once __DIR__ . "/../../v2/model/StatsHandler.class.php";

		use PDO;

		class DBHandler
		{
			private $conn;

			/**
			 * DBHandler constructor.
			 *
			 * @param PDO $conn
			 */
			public function __construct($conn)
			{
				$this->conn = $conn;
			}

			/**
			 * @param string $uuid
			 * @param int $type
			 * @param int $stat
			 * @param int $date
			 * @return array
			 */
			function addStat($uuid, $type, $stat, $date)
			{
			    $handler = new StatsHandler();
			    return $handler->addUserStat(['', $uuid], array('type' => $type, 'stat' => $stat, 'date' => $date));
			}

			/**
			 * @param string $uuid
			 * @param string $username
			 * @return array
			 */
			public function setUsername($uuid, $username)
			{
			    $handler = new UsersHandler();
			    return $handler->setUserUsername(['', $uuid], array('username' => $username));
			}
		}
	}