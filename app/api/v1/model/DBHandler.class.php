<?php

	/**Anonymous*/

	namespace YTT
	{

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
			 * @param string $videoID
			 * @param int $date
			 * @param string $browser
			 * @return array
			 */
			function addStat($uuid, $type, $stat, $videoID, $date, $browser)
			{
				$query = $this->conn->prepare("INSERT IGNORE INTO `YTT_Users`(`UUID`, `Username`) VALUES(:uuid, 'Anonymous');");
				if(!$query->execute(array(':uuid' => $uuid)))
					return array('code' => 400, 'result' => 'err', 'error' => 'E2.1');
				$query2 = $this->conn->prepare("INSERT INTO `YTT_Records`(`UUID`, `Type`, `VideoID`, `Stat`, `Time`, `Browser`) VALUES(:uuid, :type, :videoID, :stat, STR_TO_DATE(:timee, '%Y-%m-%d %H:%i:%s'), :browser);");
				if(!$query2->execute(array(':uuid' => $uuid, ':type' => $type, ':videoID' => $videoID, ':stat' => $stat, ':timee' => $this->getTimestamp($date), ':browser' => ($browser == null ? 'Unknown' : $browser))))
					return array('code' => 400, 'result' => 'err', 'error' => 'E2.2');
				return array('code' => 200, 'result' => 'OK');
			}

			/**
			 * @param int $time
			 * @return string
			 */
			private static function formatTime($time)
			{
				$time = $time / 1000;
				$sec = $time % 60;
				$min = $time / 60;
				$hour = $min / 60;
				$min = $min % 60;
				return ((int) $hour) . 'H' . $min . 'M' . $sec . 'S';
			}

			/**
			 * @param int $date
			 * @return string
			 */
			function getTimestamp($date)
			{
				if(!$date)
				{
					return date('%Y-%m-%d %H:%i:%s');
				}
				return $date;
			}

			/**
			 * @param string $uuid
			 * @return array
			 */
			public function getStats($uuid)
			{
				$query = $this->conn->prepare("SELECT * FROM  `YTT_Records` WHERE `UUID`=:uuid ORDER BY `ID` ASC;");
				if(!$query->execute(array(':uuid' => $uuid)))
					return array('code' => 500, 'result' => 'err', 'error' => 'E3');
				$stats = array();
				foreach($query->fetchAll() as $index => $row)
					$stats[$row['ID']] = array('id' => $row['ID'], 'type' => $row['Type'], 'typeStr' => $row['Type'] == 2 ? 'Opened' : 'Watched', 'videoid' => $row['VideoID'], 'Stat' => $row['Stat'], 'Fstats' => DBHandler::formatTime($row['Stat']), 'time' => $row['Time']);
				if(count($stats) > 0)
					return array('code' => 200, 'result' => 'OK', 'stats' => $stats);
				return array('code' => 400, 'result' => 'No entry');
			}

			/**
			 * @param string $uuid
			 * @param string $username
			 * @return array
			 */
			public function setUsername($uuid, $username)
			{
				$query = $this->conn->prepare("INSERT INTO `YTT_Users`(`UUID`, `Username`) VALUES(:uuid, :username) ON DUPLICATE KEY UPDATE `Username`=:username;");
				if(!$query->execute(array(':uuid' => $uuid, ':username' => $username)))
					return array('code' => 500, 'result' => 'err', 'error' => 'E4');
				return array('code' => 200, 'result' => 'OK');
			}
		}
	}