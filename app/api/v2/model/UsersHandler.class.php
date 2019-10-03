<?php

	namespace YTT;

	require_once __DIR__ . "/RouteHandler.class.php";

	class UsersHandler extends RouteHandler
	{
		private $MAX_RANGE = 3650;

		public function __construct(){ }

		/** @noinspection PhpUnused */
		public function getUsers($groups, $params)
		{
			$users = array();
			if(isset($params['range']))
			{
				$stmt = $this->getConnection()->prepare("SELECT DISTINCT(YTT_Users.UUID), Username FROM YTT_Users LEFT JOIN YTT_Records YR ON YTT_Users.UUID = YR.UUID WHERE DATE(YR.Time) >= DATE_SUB(NOW(), INTERVAL :range DAY)");
				$stmt->execute(array('range' => min(intval($params['range']), $this->MAX_RANGE)));
			}
			else
			{
				$stmt = $this->getConnection()->prepare("SELECT UUID, Username FROM YTT_Users");
				$stmt->execute(array());
			}
			$rows = $stmt->fetchAll();
			foreach($rows as $key => $row)
			{
				$users[] = array('uuid' => $row['UUID'], 'username' => $row["Username"]);
			}
			return array('code' => 200, 'users' => $users, 'message' => "OK");
		}
	}