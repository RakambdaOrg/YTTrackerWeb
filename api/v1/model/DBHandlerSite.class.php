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
        $query = $conn->query('SHOW TABLES LIKE "________-____-____-____-____________";');
        if(!$query)
            return array('code'=>500, 'result'=>'err', 'error'=>'E0');
        $i = 0;
        $uuids = array();
        if($query->num_rows > 0)
            while($row = $query->fetch_assoc())
                $uuids[$i++] = $row['Tables_in_mrcraftcggytt (________-____-____-____-____________)'];
        if(count($uuids) > 0)
            return array('code'=>200, 'result'=>'OK', 'uuids'=>$uuids);
        return array('code'=>400, 'result'=>'No entry');
    }

    public function getUserInfos($conn, $uuid)
    {
        $query = $conn->query('SELECT * FROM  `' . $uuid . '` ORDER BY `UID` ASC;');
        if(!$query)
            return array('code'=>500, 'result'=>'err', 'error'=>'E1');
        $stats = array();
        if($query->num_rows > 0)
            while($row = $query->fetch_assoc())
                $stats[$row['UID']] = array('uid'=>$row['UID'], 'type'=>$row['Type'], 'videoid'=>$row['VideoID'], 'Stat'=>$row['Stat'], 'time'=>$row['Time']);
        $query = $conn->query('SELECT `Username` FROM  `usernames` WHERE `UUID`="' . $uuid . '";');
        if($query)
        {
            if($query->num_rows > 0)
                while($row = $query->fetch_assoc())
                    $stats['username'] = $row['Username'];
        }
        if(count($stats) > 0)
            return array('code'=>200, 'result'=>'OK', 'stats'=>$stats);
        return array('code'=>400, 'result'=>'No entry');
    }

    public function getTodayStats($conn, $uuid)
    {
        $query = $conn->query('SELECT * FROM `' . $uuid . '` WHERE `Time` >= CURDATE();');
        if(!$query)
            return array('code'=>500, 'result'=>'err', 'error'=>'E2');
        $stats = array();
        if($query->num_rows > 0)
            while($row = $query->fetch_assoc())
                $stats[$row['UID']] = array('uid'=>$row['UID'], 'type'=>$row['Type'], 'videoid'=>$row['VideoID'], 'Stat'=>$row['Stat'], 'time'=>$row['Time']);
        $query = $conn->query('SELECT `Username` FROM  `usernames` WHERE `UUID`="' . $uuid . '";');
        if($query)
        {
            if($query->num_rows > 0)
                while($row = $query->fetch_assoc())
                    $stats['username'] = $row['Username'];
        }
        if(count($stats) > 0)
            return array('code'=>200, 'result'=>'OK', 'stats'=>$stats);
        return array('code'=>400, 'result'=>'No entry');
    }
}