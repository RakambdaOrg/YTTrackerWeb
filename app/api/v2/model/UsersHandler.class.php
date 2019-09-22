<?php

	namespace YTT;

	require_once __DIR__ . "/RouteHandler.class.php";

	class UsersHandler extends RouteHandler
	{
		public function __construct(){ }

		/** @noinspection PhpUnused */
		public function getUsers($groups, $params)
		{
			$users = array();
			$stmt = $this->getConnection()->prepare("SELECT UUID, Username FROM YTT_Users");
			$stmt->execute(array());
			$rows = $stmt->fetchAll();
			foreach($rows as $key => $row)
			{
				$users[] = array('uuid' => $row['UUID'], 'username' => $row["Username"]);
			}
			return array('code' => 200, 'users' => $users, 'message' => "OK");
		}
	}