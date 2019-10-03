<?php

	namespace YTT;

	require_once __DIR__ . "/DBConnection.class.php";
	require_once __DIR__ . "/RouteHandler.class.php";

	class StatsHandler extends RouteHandler
	{
		private $DEFAULT_RANGE = 31;
		private $MAX_RANGE = 3650;

		public function __construct(){ }

		private function getDataType($name){
			switch($name){
				case "opened":
					return 2;
				case "watched":
					return 1;
			}
			return -1;
		}

		private function getUserDurationStats($userUUID, $category, $range = null)
		{
			if(!is_int($range)){
				$range = $this->DEFAULT_RANGE;
			}

			$range = min($range, $this->MAX_RANGE);

			$data = array();
			$prepared = DBConnection::getConnection()->prepare("SELECT SUM(`YTT_Records`.`Stat`) AS `Stat`, DATE(`YTT_Records`.`Time`) AS `StatDay` FROM `YTT_Records` LEFT JOIN `YTT_Users` ON `YTT_Records`.`UUID` = `YTT_Users`.`UUID` WHERE YTT_Users.UUID = :uuid AND `YTT_Records`.`Type` = :type AND DATE(`YTT_Records`.`Time`) >= DATE_SUB(NOW(), INTERVAL :days DAY) GROUP BY `StatDay`");
			$prepared->execute(array(":uuid" => $userUUID, ':days' => $range, ':type' => $this->getDataType($category)));
			$result = $prepared->fetchAll();
			foreach($result as $key => $row)
			{
				$data[] = array('date' => $row['StatDay'], 'value' => $row['Stat'], 'duration' => $row['Stat']);
			}
			return $data;
		}

		/** @noinspection PhpUnused */
		public function getUserWatched($groups, $params){
			return $this->getUserDurationStats($groups[1], "watched", isset($params["range"]) ? $params['range'] : null);
		}

		/** @noinspection PhpUnused */
		public function getUserOpened($groups, $params){
			return $this->getUserDurationStats($groups[1], "opened", isset($params["range"]) ? $params['range'] : null);
		}

		/** @noinspection PhpUnused */
		public function getUserOpenedCount($groups, $params){
			$userUUID = $groups[1];
			$range = min(3650, isset($params["range"]) ? $params['range'] : $this->DEFAULT_RANGE);

			$data = array();
			$prepared = DBConnection::getConnection()->prepare("SELECT COUNT(`Stat`) AS Total, DATE(`YTT_Records`.`Time`) AS `StatDay` FROM `YTT_Records` WHERE `Type`=:type AND `UUID`=:uuid AND DATE(`YTT_Records`.`Time`) >= DATE_SUB(NOW(), INTERVAL :days DAY) GROUP BY `StatDay`");
			$prepared->execute(array(":uuid" => $userUUID, ':days' => $range, ':type' => $this->getDataType("opened")));
			$result = $prepared->fetchAll();
			foreach($result as $key => $row)
			{
				$data[] = array('date' => $row['StatDay'], 'value' => $row['Total']);
			}
			return $data;
		}

		private function getUserSumStats($userUUID, $type, $hours){
			$prepared = DBConnection::getConnection()->prepare("SELECT SUM(`Stat`)/1000 AS Total FROM `YTT_Records` WHERE `Type`=:type AND `UUID`=:uuid AND DATE(`YTT_Records`.`Time`) >= DATE_SUB(NOW(), INTERVAL :hours HOUR)");
			$prepared->execute(array(":uuid" => $userUUID, ':hours' => $hours, ':type' => $this->getDataType($type)));
			if($row = $prepared->fetch())
			{
				return doubleval($row['Total']);
			}
			return 0;
		}

		private function getUserCountStats($userUUID, $hours){
			$prepared = DBConnection::getConnection()->prepare("SELECT COUNT(*) AS Total FROM `YTT_Records` WHERE `Type`=:type AND `UUID`=:uuid AND DATE(`YTT_Records`.`Time`) >= DATE_SUB(NOW(), INTERVAL :hours HOUR) AND Stat > 0");
			$prepared->execute(array(":uuid" => $userUUID, ':hours' => $hours, ':type' => $this->getDataType('opened')));
			if($row = $prepared->fetch())
			{
				return doubleval($row['Total']);
			}
			return 0;
		}

		/** @noinspection PhpUnused */
		public function getUserStats($groups, $params){
			$userUUID = $groups[1];

			$data = array(
				'total' => array(
					'opened' => $this->getUserSumStats($userUUID, 'opened', 24 * $this->MAX_RANGE),
					'watched' => $this->getUserSumStats($userUUID, 'watched', 24 * $this->MAX_RANGE),
					'count' => $this->getUserCountStats($userUUID, 24 * $this->MAX_RANGE)
				),
				'week' => array(
					'opened' => $this->getUserSumStats($userUUID, 'opened', 24 * 7),
					'watched' => $this->getUserSumStats($userUUID, 'watched', 24 * 7),
					'count' => $this->getUserCountStats($userUUID, 24 * 7)
				),
				'day' => array(
					'opened' => $this->getUserSumStats($userUUID, 'opened', 24),
					'watched' => $this->getUserSumStats($userUUID, 'watched', 24),
					'count' => $this->getUserCountStats($userUUID, 24)
				));
			return $data;
		}
	}