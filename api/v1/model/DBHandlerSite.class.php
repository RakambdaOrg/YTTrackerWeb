<?php

/**
 * Created by PhpStorm.
 * User: MrCraftCod
 * Date: 22/08/2016
 * Time: 11:23
 */
class DBHandlerSite
{
    public function __construct($conn)
    {
        $this->sqlConnection = $conn;
    }

    public function getUUIDS()
    {
        $query = $this->sqlConnection->query('SELECT `ID`, `UUID` FROM `YTTUsers`;');
        if(!$query)
            return array('code'=>500, 'result'=>'err', 'error'=>'E0');
        $uuids = array();
        $i = 0;
        if($query->num_rows > 0)
            while($row = $query->fetch_assoc())
                $uuids[$i++] = array('ID'=> $row['ID'], 'UUID' => $row['UUID']);
        if (count($uuids) > 0)
            return array('code' => 200, 'result' => 'OK', 'uuids' => $uuids);
        return array('code' => 400, 'result' => 'No entry');
    }

    public function getUsername($UUID)
    {
        $query = $this->sqlConnection->query('SELECT `Username` FROM  `YTTUsers` WHERE `UUID`="' . $UUID . '";');
        if($query)
        {
            if($query->num_rows > 0)
                while($row = $query->fetch_assoc())
                    return $row['Username'];
        }
        return null;
    }

    public function getTodayStats($uuid)
    {
        $query = $this->sqlConnection->query('SELECT * FROM `YTTRecords` WHERE `UUID`="' . $uuid . '" AND `Time` >= CURDATE();');
        if (!$query)
            return array('code' => 500, 'result' => 'err', 'error' => 'E2');
        $stats = array();
        if ($query->num_rows > 0)
            while ($row = $query->fetch_assoc())
                $stats[$row['UID']] = array('uid' => $row['UID'], 'type' => $row['Type'], 'videoid' => $row['VideoID'], 'Stat' => $row['Stat'], 'time' => $row['Time']);
        $username = $this->getUsername($uuid);
        if($username){
            $stats['username'] = $username;
        }
        if (count($stats) > 0)
            return array('code' => 200, 'result' => 'OK', 'stats' => $stats);
        return array('code' => 400, 'result' => 'No entry');
    }

    private function getSumRecordType($UUID, $type)
    {
        $result = 0;
        $query = $this->sqlConnection->query('SELECT SUM(`Stat`) AS Total FROM  `YTTRecords` WHERE `Type`=' . $type . ' AND `UUID`="' . $UUID . '";');
        if($query)
        {
            if($query->num_rows > 0)
                while($row = $query->fetch_assoc())
                    $result = $row['Total'];
        }
        return $result;
    }

    public function getTotalWatched($UUID)
    {
        return $this->getSumRecordType($UUID, 1);
    }

    public function getTotalOpened($UUID)
    {
        return $this->getSumRecordType($UUID, 2);
    }

    public function getTotalOpenedCount($UUID)
    {
        $result = 0;
        $query = $this->sqlConnection->query('SELECT COUNT(`Stat`) AS Total FROM  `YTTRecords` WHERE `Type`=2 AND `UUID`="' . $UUID . '";');
        if($query)
        {
            if($query->num_rows > 0)
                while($row = $query->fetch_assoc())
                    $result = $row['Total'];
        }
        return $result;
    }

    public function getSumRecordTypeToday($UUID, $type)
    {
        $result = 0;
        $query = $this->sqlConnection->query('SELECT SUM(`Stat`) AS Total FROM  `YTTRecords` WHERE `Type`=' . $type . ' AND `UUID`="' . $UUID . '" AND `Time` >= CURDATE();');
        if($query)
        {
            if($query->num_rows > 0)
                while($row = $query->fetch_assoc())
                    $result = $row['Total'];
        }
        return $result;
    }

    public function getTodayWatched($UUID)
    {
        return $this->getSumRecordTypeToday($UUID, 1);
    }

    public function getTodayOpened($UUID)
    {
        return $this->getSumRecordTypeToday($UUID, 2);
    }

    public function getTodayOpenedCount($UUID)
    {
        $result = 0;
        $query = $this->sqlConnection->query('SELECT COUNT(`Stat`) AS Total FROM  `YTTRecords` WHERE `Type`=2 AND `UUID`="' . $UUID . '" AND `Time` >= CURDATE();');
        if($query)
        {
            if($query->num_rows > 0)
                while($row = $query->fetch_assoc())
                    $result = $row['Total'];
        }
        return $result;
    }

    public function getSumRecordTypePeriod($UUID, $type, $start, $end)
    {
        $result = 0;
        $query = $this->sqlConnection->query('SELECT SUM(`Stat`) AS Total FROM  `YTTRecords` WHERE `Type`=' . $type . ' AND `UUID`="' . $UUID . '" AND `Time` >= ' . $start . ' AND `Time` <= ' . $end . ';');
        if($query)
        {
            if($query->num_rows > 0)
                while($row = $query->fetch_assoc())
                    $result = $row['Total'];
        }
        return $result;
    }

    public function getPeriodWatched($UUID, $start, $end)
    {
        return $this->getSumRecordTypePeriod($UUID, 1, $start, $end);
    }

    public function getPeriodOpened($UUID, $start, $end)
    {
        return $this->getSumRecordTypePeriod($UUID, 2, $start, $end);
    }

    public function getPeriodCount($UUID, $start, $end)
    {
        $result = 0;
        $query = $this->sqlConnection->query('SELECT COUNT(`Stat`) AS Total FROM  `YTTRecords` WHERE `Type`=2 AND `UUID`="' . $UUID . '" AND `Time` >= ' . $start . ' AND `Time` < ' . $end . ';');
        if($query)
        {
            if($query->num_rows > 0)
                while($row = $query->fetch_assoc())
                    $result = $row['Total'];
        }
        return $result;
    }

    public function getLastWeekTotals()
    {
        $result = array();
        $query = $this->sqlConnection->query('SELECT `YTTRecords`.`ID`, `YTTUsers`.`ID` AS `UID`, `YTTRecords`.`Type`, SUM(`YTTRecords`.`Stat`) AS `Stat`, DATE(`YTTRecords`.`Time`) AS `StatDay` FROM `YTTRecords` LEFT JOIN `YTTUsers` ON `YTTRecords`.`UUID` = `YTTUsers`.`UUID` WHERE `YTTRecords`.`Type` = 2 GROUP BY `YTTRecords`.`UUID`, `StatDay`, `YTTRecords`.`Type`;');
        if($query)
        {
            if($query->num_rows > 0)
                while($row = $query->fetch_assoc())
                    $result[$row['ID']] = array('UID' => $row['UID'], 'Stat' => $row['Stat'], 'Date' => $row['StatDay'], 'Type' => $row['Type']);
        }
        return $result;
    }
}