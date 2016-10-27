<?php
require_once('api/v1/model/DBConnection.class.php');
require_once('api/v1/model/DBHandlerSite.class.php');
require_once('model/SiteHelper.class.php');
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
    <div>
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
                    <th class="userCell">User</th>
                    <th class="totalOpenedCell leftVerticalLine">Total Opened</th>
                    <th class="totalWatchedCell lightVerticalLine">Total Watched</th>
                    <th class="totalCountCell lightVerticalLine">Total Count</th>
                    <th class="todayOpenedCell leftVerticalLine">Opened Today</th>
                    <th class="todayWatchedCell lightVerticalLine">Watched Today</th>
                    <th class="todayCountCell lightVerticalLine">Count Today</th>
                    <?php
                    if($customPeriodDisplayed){
                        ?>
                        <th class="periodOpenedCell leftVerticalLine">Period Opened</th>
                        <th class="periodWatchedCell lightVerticalLine">Period Watched</th>
                        <th class="periodCountCell lightVerticalLine">Period Count</th>
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
                            echo $UUID['ID'];
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
                            <td class="todayOpenedCell leftVerticalLine">
                                <?php
                                echo $siteHelper->millisecondsToTimeString($handler->getTodayOpened($UUID['UUID']));
                                ?>
                            </td>
                            <td class="todayWatchedCell lightVerticalLine">
                                <?php
                                echo $siteHelper->millisecondsToTimeString($handler->getTodayWatched($UUID['UUID']));
                                ?>
                            </td>
                            <td class="todayCountCell lightVerticalLine">
                                <?php
                                echo $handler->getTodayOpenedCount($UUID['UUID']);
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
    <div id="chartHolder">
        <div id="chartDiv"><?php
            if($dev)
            {
                include "chart.php";
            }
            ?></div>
    </div>
</body>
</html>
<?php
$conn->close();
?>