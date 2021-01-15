<?php

    namespace YTT;

    require_once __DIR__ . "/RouteHandler.class.php";

    class UsersHandler extends RouteHandler
    {
        private $MAX_RANGE = 3650;
        private $DEFAULT_USERNAME = 'Anonymous';

        public function __construct(){ }

        /** @noinspection PhpUnused */
        public function getUsers($groups, $params)
        {
            $users = array();
            if(isset($params['range']))
            {
                $stmt = $this->getConnection()->prepare("SELECT DISTINCT(YTT_Users.UUID), Username FROM YTT_Users LEFT JOIN YTT_Records YR ON YTT_Users.ID = YR.UserId WHERE StatDay >= DATE_SUB(NOW(), INTERVAL :range DAY)");
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
                $users[] = array('uuid' => $row['UUID'], 'username' => $this->parseUsername($row["Username"]));
            }
            return array('code' => 200, 'users' => $users, 'message' => "OK");
        }

        /** @noinspection PhpUnused */
        /**
         * @param array $groups 1: UUID
         * @param array $params username?
         * @return array
         */
        public function setUserUsername($groups, $params)
        {
            $userUUID = $groups[1];

            $username = isset($params['username']) ? $params['username'] : null;
            if($username === $this->DEFAULT_USERNAME)
                $username = null;
            if(!$username)
                $username = null;

            $query = $this->getConnection()->prepare("INSERT INTO `YTT_Users`(`UUID`, `Username`) VALUES(:uuid, :username) ON DUPLICATE KEY UPDATE `Username`=:username");
            if(!$query->execute(array(':uuid' => $userUUID, ':username' => $username)))
                return array('code' => 500, 'result' => 'err', 'error' => 'E4');
            return array('code' => 200, 'result' => 'OK');
        }

        /** @noinspection PhpUnused */
        public function getUsername($groups, $params)
        {
            $userUUID = $groups[1];

            $stmt = $this->getConnection()->prepare("SELECT UUID, Username FROM YTT_Users WHERE UUID=:uuid");
            $stmt->execute(array(':uuid' => $userUUID));

            if($row = $stmt->fetch())
            {
                return array('code' => 200, 'username' => $this->parseUsername($row['Username']));
            }
            return array('code' => 204, 'error' => 'UserID not found');
        }

        private function parseUsername($username)
        {
            if(!$username)
                return $this->DEFAULT_USERNAME;
            return $username;
        }

        public function getUserId($userUUID)
        {
            $stmt = $this->getConnection()->prepare("SELECT ID FROM YTT_Users WHERE UUID=:uuid");
            $stmt->execute(array(':uuid' => $userUUID));

            if($row = $stmt->fetch())
            {
                return $row['ID'];
            }
            return null;
        }

        public function getUserIdOrCreate($userUUID)
        {
            $userId = $this->getUserId($userUUID);
            if($userId)
                return $userId;
            $stmt = $this->getConnection()->prepare('INSERT INTO YTT_Users(`UUID`) VALUES(:uuid)');
            $stmt->execute(array(':uuid' => $userUUID));
            return $this->getConnection()->lastInsertId();
        }
    }