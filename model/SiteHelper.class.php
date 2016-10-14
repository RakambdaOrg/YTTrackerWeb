<?php

/**
 * Created by PhpStorm.
 * User: MrCraftCod
 * Date: 22/08/2016
 * Time: 11:38
 */
class SiteHelper
{
    public function decodeInfosFromDB($infos, $periodStart, $periodEnd)
    {
        $periodCalc = $periodStart != 'NULL' && $periodEnd != 'NULL';
        $newInfos = array('TotalCount'=>0, 'TotalWatched'=>0, 'TotalOpened'=>0, 'TodayCount'=>0, 'TodayWatched'=>0, 'TodayOpened'=>0, 'PeriodCount'=>0, 'PeriodWatched'=>0, 'PeriodOpened'=>0);
        foreach($infos as $UID=>$info) {
            if($info['type'] === '2'){
                $newInfos['TotalCount'] += 1;
                $newInfos['TotalOpened'] += $info['Stat'];
                if($this->isToday($info['time'])) {
                    $newInfos['TodayCount'] += 1;
                    $newInfos['TodayOpened'] += $info['Stat'];
                }
                if($periodCalc && $this->isBetween($info['time'], $periodStart, $periodEnd)) {
                    $newInfos['PeriodCount'] += 1;
                    $newInfos['PeriodOpened'] += $info['Stat'];
                }
            } else {
                $newInfos['TotalWatched'] += $info['Stat'];
                if($this->isToday($info['time'])) {
                    $newInfos['TodayWatched'] += $info['Stat'];
                }
                if($periodCalc && $this->isBetween($info['time'], $periodStart, $periodEnd)) {
                    $newInfos['PeriodWatched'] += $info['Stat'];
                }
            }
        }
        $newInfos['TotalOpened'] /= 1000;
        $newInfos['TotalWatched'] /= 1000;
        $newInfos['TodayOpened'] /= 1000;
        $newInfos['TodayWatched'] /= 1000;
        $newInfos['PeriodOpened'] /= 1000;
        $newInfos['PeriodWatched'] /= 1000;
        return $newInfos;
    }

    function isToday($date)
    {
        return date('Y-m-d') == date('Y-m-d', strtotime($date));
    }

    function isBetween($date, $low, $up)
    {
        $test = date('Y-m-d', strtotime($date));
        $start = date('Y-m-d', strtotime($low));
        $end = date('Y-m-d', strtotime($up));
        return $test >= $start && $test <= $end;
    }

    public function millisecondsToTimeString($milliseconds)
    {
        return $this->secondsToTimeString($milliseconds / 1000);
    }

    public function secondsToTimeString($seconds)
    {
        $hours = floor($seconds / 3600);
        $mins = floor($seconds / 60 % 60);
        $secs = floor($seconds % 60);
        if($hours > 0)
            return $hours . 'h' . $mins . 'm' . $secs . 's';
        elseif ($mins > 0)
            return $mins . 'm' . $secs . 's';
        return $secs . 's';
    }
}