<?php

/**
 * Created by PhpStorm.
 * User: MrCraftCod
 * Date: 22/08/2016
 * Time: 11:38
 */
class SiteHelper
{
    public function millisecondsToTimeString($milliseconds)
    {
        return $this->secondsToTimeString($milliseconds / 1000);
    }

    public function secondsToTimeString($seconds)
    {
        $hours = floor($seconds / 3600);
        $mins = floor($seconds / 60 % 60);
        $secs = floor($seconds % 60);
        if ($hours > 0)
            return sprintf("%02d", $hours) . 'h' . sprintf("%02d", $mins) . 'm' . sprintf("%02d", $secs) . 's';
        elseif ($mins > 0)
            return $mins . 'm' . $secs . 's';
        return $secs . 's';
    }
}