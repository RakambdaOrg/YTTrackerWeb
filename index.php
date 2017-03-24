<?php
require_once('api/v1/model/DBConnection.class.php');
require_once('api/v1/model/DBHandlerSite.class.php');
require_once('model/SiteHelper.class.php');
if(false)
{
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
$dev = isset($_GET['dev']);
$conn = DBConnection::getConnection();
$handler = new DBHandlerSite($conn);
$siteHelper = new SiteHelper();
$customPeriodDisplayed = isset($_GET['startPeriod']) && isset($_GET['endPeriod']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/main.css"/>
    <script type="text/javascript" src="js/libs/jquery/jquery.js"></script>
    <script type="text/javascript" src="js/libs/ElementQueries/ResizeSensor.js"></script>
    <script type="text/javascript" src="js/libs/ElementQueries/ElementQueries.js"></script>
    <script type="text/javascript" src="js/libs/amcharts/amcharts.js"></script>
    <script type="text/javascript" src="js/libs/amcharts/serial.js"></script>
    <script type="text/javascript" src="js/libs/amcharts/themes/light.js"></script>
    <script type="text/javascript" src="js/libs/amcharts/plugins/responsive/responsive.min.js"></script>
    <script type="text/javascript" src="js/libs/tablesorter/jquery.tablesorter.js"></script>
    <script type="text/javascript" src="js/script.js"></script>
    <meta charset="UTF-8">
    <title>YTTracker</title>
</head>
<body>
    <div style="margin-top: 5px;">
        <form method="get">
            <label>
                Start:
                <input type="datetime-local" name="startPeriod"<?php
                if(isset($_GET['startPeriod'])){
                    echo ' value="' . $_GET['startPeriod'] . '"';
                }
                ?>>
            </label>
            <label>
                End:
                <input type="datetime-local" name="endPeriod"<?php
                if(isset($_GET['startPeriod'])){
                    echo ' value="' . $_GET['endPeriod'] . '"';
                }
                ?>>
            </label>
            <label>
                <input type="submit" id="submitPeriod" value="Submit"/>
            </label>
        </form>
        <hr>
    </div>
    <div>
        <table id="dataTable">
            <thead>
                <tr>
                    <th class="userCell" rowspan="2">User</th>
                    <th class="leftVerticalLine" colspan="3">Total</th>
                    <th class="leftVerticalLine" colspan="3">Last Week</th>
                    <th class="leftVerticalLine" colspan="3">Last 24h</th>
                    <?php
                    if($customPeriodDisplayed){
                        ?>
                        <th class="leftVerticalLine" colspan="3">Period</th>
                        <?php
                    }
                    ?>
                </tr>
                <tr>
                    <th class="totalOpenedCell leftVerticalLine">Opened</th>
                    <th class="totalWatchedCell lightVerticalLine">Watched</th>
                    <th class="totalCountCell lightVerticalLine">Count</th>
                    <th class="weekOpenedCell leftVerticalLine">Opened</th>
                    <th class="weekWatchedCell lightVerticalLine">Watched</th>
                    <th class="weekCountCell lightVerticalLine">Count</th>
                    <th class="todayOpenedCell leftVerticalLine">Opened</th>
                    <th class="todayWatchedCell lightVerticalLine">Watched</th>
                    <th class="todayCountCell lightVerticalLine">Count</th>
                    <?php
                    if($customPeriodDisplayed){
                        ?>
                        <th class="periodOpenedCell leftVerticalLine">Opened</th>
                        <th class="periodWatchedCell lightVerticalLine">Watched</th>
                        <th class="periodCountCell lightVerticalLine">Count</th>
                        <?php
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $uuids = $handler->getUUIDS();
                if($uuids['code'] === 200)
                {
                    foreach($uuids['uuids'] as $UUIDIndex=>$UUID) {
                        ?>
                        <tr id="user<?php
                            echo $UUID['ID'] . "<br/>(" . $handler->getOldestRecord($UUID) . ")";
                        ?>">
                            <td class="userCell">
                                <?php
                                $username = $handler->getUsername($UUID['UUID']);
                                echo $username ? $username : $UUID['ID'];
                                ?>
                            </td>
                            <td class="totalOpenedCell leftVerticalLine">
                                <?php
                                echo $siteHelper->millisecondsToTimeString($handler->getTotalOpened($UUID['UUID']));
                                ?>
                            </td>
                            <td class="totalWatchedCell lightVerticalLine">
                                <?php
                                echo $siteHelper->millisecondsToTimeString($handler->getTotalWatched($UUID['UUID']));
                                ?>
                            </td>
                            <td class="totalCountCell lightVerticalLine">
                                <?php
                                echo $handler->getTotalOpenedCount($UUID['UUID']);
                                ?>
                            </td>
                            <td class="weekOpenedCell leftVerticalLine">
                                <?php
                                echo $siteHelper->millisecondsToTimeString($handler->getWeekOpened($UUID['UUID']));
                                ?>
                            </td>
                            <td class="weekWatchedCell lightVerticalLine">
                                <?php
                                echo $siteHelper->millisecondsToTimeString($handler->getWeekWatched($UUID['UUID']));
                                ?>
                            </td>
                            <td class="weekCountCell lightVerticalLine">
                                <?php
                                echo $handler->getWeekOpenedCount($UUID['UUID']);
                                ?>
                            </td>
                            <td class="todayOpenedCell leftVerticalLine">
                                <?php
                                echo $siteHelper->millisecondsToTimeString($handler->getLast24hOpened($UUID['UUID']));
                                ?>
                            </td>
                            <td class="todayWatchedCell lightVerticalLine">
                                <?php
                                echo $siteHelper->millisecondsToTimeString($handler->getLast24hWatched($UUID['UUID']));
                                ?>
                            </td>
                            <td class="todayCountCell lightVerticalLine">
                                <?php
                                echo $handler->getLast24hOpenedCount($UUID['UUID']);
                                ?>
                            </td>
                            <?php
                                if($customPeriodDisplayed){
                                    $start = 'STR_TO_DATE("' . $_GET['startPeriod'] . '", "%Y-%m-%dT%H:%i")';
                                    $end = 'STR_TO_DATE("' . $_GET['endPeriod'] . ':59", "%Y-%m-%dT%H:%i:%s")';
                                    ?>
                                    <td class="periodOpenedCell leftVerticalLine">
                                        <?php
                                        echo $siteHelper->millisecondsToTimeString($handler->getPeriodOpened($UUID['UUID'], $start, $end));
                                        ?>
                                    </td>
                                    <td class="periodWatchedCell lightVerticalLine">
                                        <?php
                                        echo $siteHelper->millisecondsToTimeString($handler->getPeriodWatched($UUID['UUID'], $start, $end));
                                        ?>
                                    </td>
                                    <td class="periodCountCell lightVerticalLine">
                                        <?php
                                        echo $handler->getPeriodCount($UUID['UUID'], $start, $end)
                                        ?>
                                    </td>
                                    <?php
                                }
                            ?>
                        </tr>
                        <?php
                    }
                }
                else
                {
                    ?>
                    <tr>
                        <td colspan="4">Error while getting users</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <hr/>
    <div class="chartHolder" id="chartHolderWatched">
        <span class="chartName">Watched time</span>
        <div class="chartDiv" id="chartDivWatched"></div>
    </div>
    <?php include "chartWatched.php"; ?>
    <hr/>
    <div class="chartHolder" id="chartHolderOpened">
        <span class="chartName">Opened time</span>
        <div class="chartDiv" id="chartDivOpened"></div>
    </div>
    <?php include "chartOpened.php"; ?>
    <hr/>
    <div class="chartHolder" id="chartHolderOpenedCount">
        <span class="chartName">Opened count</span>
        <div class="chartDiv" id="chartDivOpenedCount"></div>
    </div>
    <?php include "chartOpenedCount.php"; ?>
</body>
</html>
<?php
$conn->close();
?>