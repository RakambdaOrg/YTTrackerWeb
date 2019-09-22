<?php

	namespace YTT;

	require_once __DIR__ . "/DBConnection.class.php";
	require_once __DIR__ . "/RouteHandler.class.php";

	class StatsHandler extends RouteHandler
	{
		private $DEFAULT_RANGE = 31;

		public function __construct(){ }

		/** @noinspection PhpUnused */
		public function getUserStats($groups, $params)
		{
			$userUUID = $groups[1];
			$category = $groups[2];
			if(isset($params['range']))
			{
				try
				{
					$range = intval($params['range']);
				}
				catch(\Exception $e)
				{
					$range = $this->DEFAULT_RANGE;
				}
			}
			else
			{
				$range = $this->DEFAULT_RANGE;
			}

			switch($category){
				case "opened":
					$type = 2;
					break;
				default:
					$type = -1;
			}

			$data = array();
			$prepared = DBConnection::getConnection()->prepare("SELECT SUM(`YTT_Records`.`Stat`) AS `Stat`, DATE(`YTT_Records`.`Time`) AS `StatDay` FROM `YTT_Records` LEFT JOIN `YTT_Users` ON `YTT_Records`.`UUID` = `YTT_Users`.`UUID` WHERE YTT_Users.UUID = :uuid AND `YTT_Records`.`Type` = :type AND DATE(`YTT_Records`.`Time`) >= DATE_SUB(NOW(), INTERVAL :days DAY) GROUP BY `StatDay`");
			$prepared->execute(array(":uuid" => $userUUID, ':days' => $range, ':type' => $type));
			$result = $prepared->fetchAll();
			foreach($result as $key => $row)
			{
				$data[] = array('date' => $row['StatDay'], 'value' => $row['Stat'], 'duration' => $row['Stat']);
			}
			return $data;
		}
	}