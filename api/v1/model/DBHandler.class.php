<?php

/**
 * Created by PhpStorm.
 * User: MrCraftCod
 * Date: 21/08/2016
 * Time: 09:57
 */

	namespace YTT
	{
		class DBHandler
		{
			private $conn;

			/**
			 * DBHandler constructor.
			 *
			 * @param \mysqli $conn
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
				$this->conn->query('INSERT IGNORE INTO `YTTUsers`(`UUID`, `Username`) VALUES("' . $uuid . '", "Annonymous");');
				if(!$this->conn->query('INSERT INTO `YTTRecords`(`UUID`, `Type`, `VideoID`, `Stat`, `Time`, `Browser`) VALUES("' . $uuid . '", ' . $type . ',"' . $videoID . '",' . $stat . ', ' . $this->getTimestamp($date) . ',"' . ($browser == null ? 'Unknown' : $browser) . '");'))
					return array('code' => 400, 'result' => 'err', 'error' => 'E2');
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
					return 'CURRENT_TIMESTAMP()';
				}
				return 'STR_TO_DATE("' . $date . '", "%Y-%m-%d %H:%i:%s")';
			}

			/**
			 * @param string $uuid
			 * @return array
			 */
			public function getStats($uuid)
			{
				$query = $this->conn->query('SELECT * FROM  `YTTRecords` WHERE `UUID`="' . $uuid . '" ORDER BY `ID` ASC;');
				if(!$query)
					return array('code' => 500, 'result' => 'err', 'error' => 'E3');
				$stats = array();
				if($query->num_rows > 0)
					while($row = $query->fetch_assoc())
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
				$query = $this->conn->query('INSERT INTO `YTTUsers`(`UUID`, `Username`) VALUES("' . $uuid . '","' . $this->conn->real_escape_string($username) . '") ON DUPLICATE KEY UPDATE `Username`="' . $this->conn->real_escape_string($username) . '";');
				if(!$query)
					return array('code' => 500, 'result' => 'err', 'error' => 'E4');
				return array('code' => 200, 'result' => 'OK');
			}
		}
	}