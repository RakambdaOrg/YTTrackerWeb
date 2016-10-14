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

    function addStat($conn, $uuid, $type, $stat, $videoID, $date)
    {
        $red = 'INSERT INTO `YTTRecords`(`UUID`, `Type`, `VideoID`, `Stat`, `Time`) VALUES("' . $uuid . '", ' . $type . ',"' . $videoID . '",' . $stat . ', ' . $this->getTimestamp($date) . ');';
        if (!$conn->query($red))
            return array('code' => 400, 'result' => 'err', 'error' => 'E2', 'req' => $red);
        return array('code' => 200, 'result' => 'OK', 'req' => $red);
    }

    private static function formatTime($time)
    {
        $time = $time / 1000;
        $sec = $time % 60;
        $min = $time / 60;
        $hour = $min / 60;
        $min = $min % 60;
        return ((int)$hour) . 'H' . $min . 'M' . $sec . 'S';
    }

    function getTimestamp($date)
    {
        if (!$date) {
            return 'CURRENT_TIMESTAMP()';
        }
        return 'STR_TO_DATE("' . $date . '", "%Y-%m-%d %H:%i:%s")';
    }

    public function getStats($conn, $uuid)
    {
        $query = $conn->query('SELECT * FROM  `YTTRecords` WHERE `UUID` IS "' . $uuid . '" ORDER BY `ID` ASC;');
        if (!$query)
            return array('code' => 500, 'result' => 'err', 'error' => 'E3');
        $stats = array();
        if ($query->num_rows > 0)
            while ($row = $query->fetch_assoc())
                $stats[$row['ID']] = array('id' => $row['ID'], 'type' => $row['Type'], 'typeStr' => $row['Type'] == 2 ? 'Opened' : 'Watched', 'videoid' => $row['VideoID'], 'Stat' => $row['Stat'], 'Fstats' => DBHandler::formatTime($row['Stat']), 'time' => $row['Time']);
        if (count($stats) > 0)
            return array('code' => 200, 'result' => 'OK', 'stats' => $stats);
        return array('code' => 400, 'result' => 'No entry');
    }

    public function setUsername($conn, $uuid, $username)
    {
        $query = $conn->query('INSERT INTO `YTTUsers`(`UUID`, `Username`) VALUES("' . $uuid . '","' . $conn->real_escape_string($username) . '") ON DUPLICATE KEY UPDATE `Username`="' . $conn->real_escape_string($username) . '";');
        if (!$query)
            return array('code' => 500, 'result' => 'err', 'error' => 'E4');
        return array('code' => 200, 'result' => 'OK');
    }
}