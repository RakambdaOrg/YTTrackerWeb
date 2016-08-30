<?php

/**
 * Created by PhpStorm.
 * User: MrCraftCod
 * Date: 21/08/2016
 * Time: 09:57
 */
class DBHandler
{
    public function __construct()
    {
    }

    function addStat($conn, $uuid, $type, $stat, $videoID){
        if(!$this->createUserTable($conn, $uuid))
            return array('code'=>500, 'result'=>'err', 'error'=>'E1');
        if(!$conn->query('INSERT INTO `' . $uuid . '`(`Type`, `VideoID`, `Stat`) VALUES(' . $type . ',"' . $videoID . '",' . $stat . ');'))
            return array('code'=>400, 'result'=>'err', 'error'=>'E2');
        return array('code'=>200, 'result'=>'OK');
    }

    function createUserTable($conn, $uuid)
    {
        return $conn->query('CREATE TABLE IF NOT EXISTS `' . $uuid . '`(`UID` INT PRIMARY KEY NOT NULL AUTO_INCREMENT, `Type` INT NOT NULL, `VideoID` TINYTEXT NOT NULL, `Stat` BIGINT SIGNED DEFAULT 0 NOT NULL, `Time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP() NOT NULL);');
    }

    public function getStats($conn, $uuid)
    {
        $query = $conn->query('SELECT * FROM  `' . $uuid . '` ORDER BY `UID` ASC;');
        if(!$query)
            return array('code'=>500, 'result'=>'err', 'error'=>'E3');
        $stats = array();
        if($query->num_rows > 0)
            while($row = $query->fetch_assoc())
                $stats[$row['UID']] = array('uid'=>$row['UID'], 'type'=>$row['Type'], 'videoid'=>$row['VideoID'], 'Stat'=>$row['Stat'], 'time'=>$row['Time']);
        if(count($stats) > 0)
            return array('code'=>200, 'result'=>'OK', 'stats'=>$stats);
        return array('code'=>400, 'result'=>'No entry');
    }

    public function setUsername($conn, $uuid, $username)
    {
        $query = $conn->query('INSERT INTO `usernames`(`UUID`, `Username`) VALUES("' . $uuid . '","' . $conn->real_escape_string($username) . '") ON DUPLICATE KEY UPDATE `Username`="' . $conn->real_escape_string($username) . '";');
        if(!$query)
            return array('code'=>500, 'result'=>'err', 'error'=>'E4');
        return array('code'=>200, 'result'=>'OK');
    }
}