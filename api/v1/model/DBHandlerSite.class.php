<?php

/**
 * Created by PhpStorm.
 * User: MrCraftCod
 * Date: 22/08/2016
 * Time: 11:23
 */
class DBHandlerSite
{
    public function __construct()
    {
    }

    public function getUUIDS($conn)
    {
        $query = $conn->query('SELECT DISTINCT UUID FROM YTTRecords;');
        if(!$query)
            return array('code'=>500, 'result'=>'err', 'error'=>'E0');
        $i = 0;
        $uuids = array();
        if($query->num_rows > 0)
            while($row = $query->fetch_assoc())
                $uuids[$i++] = $row['UUID'];
        if (count($uuids) > 0)
            return array('code' => 200, 'result' => 'OK', 'uuids' => $uuids);
        return array('code' => 400, 'result' => 'No entry');
    }

    public function getUsername($conn, $UUID)
    {
        $query = $conn->query('SELECT `Username` FROM  `YTTUsers` WHERE `UUID`="' . $UUID . '";');
        if($query)
        {
            if($query->num_rows > 0)
                while($row = $query->fetch_assoc())
                    return $row['Username'];
        }
        return null;
    }

    public function getUserInfos($conn, $uuid)
    {
        $query = $conn->query('SELECT * FROM  `YTTRecords` WHERE `UUID`="' . $uuid . '" ORDER BY `ID` ASC;');
        if (!$query)
            return array('code' => 500, 'result' => 'err', 'error' => 'E1');
        $stats = array();
        if($query->num_rows > 0)
            while($row = $query->fetch_assoc())
                $stats[$row['ID']] = array('id'=>$row['UID'], 'type'=>$row['Type'], 'videoid'=>$row['VideoID'], 'Stat'=>$row['Stat'], 'time'=>$row['Time']);
        $username = $this->getUsername($uuid);
        if($username){
            $stats['username'] = $username;
        }
        if (count($stats) > 0)
            return array('code' => 200, 'result' => 'OK', 'stats' => $stats);
        return array('code' => 400, 'result' => 'No entry');
    }

    public function getTodayStats($conn, $uuid)
    {
        $query = $conn->query('SELECT * FROM `YTTRecords` WHERE `UUID`="' . $uuid . '" AND `Time` >= CURDATE();');
        if (!$query)
            return array('code' => 500, 'result' => 'err', 'error' => 'E2');
        $stats = array();
        if ($query->num_rows > 0)
            while ($row = $query->fetch_assoc())
                $stats[$row['UID']] = array('uid' => $row['UID'], 'type' => $row['Type'], 'videoid' => $row['VideoID'], 'Stat' => $row['Stat'], 'time' => $row['Time']);
        $query = $conn->query('SELECT `Username` FROM  `YTTUsers` WHERE `UUID`="' . $uuid . '";');
        if ($query) {
            if ($query->num_rows > 0)
                while ($row = $query->fetch_assoc())
                    $stats['username'] = $row['Username'];
        }
        if (count($stats) > 0)
            return array('code' => 200, 'result' => 'OK', 'stats' => $stats);
        return array('code' => 400, 'result' => 'No entry');
    }

    public function getTotalWatched($conn, $UUID)
    {
        $result = 0;
        $query = $conn->query('SELECT SUM(`Stat`) AS Total FROM  `YTTRecords` WHERE Type=1 AND `UUID`="' . $UUID . '";');
        if($query)
        {
            if($query->num_rows > 0)
                while($row = $query->fetch_assoc())
                    $result = $row['Total'];
        }
        return $result;
    }
}