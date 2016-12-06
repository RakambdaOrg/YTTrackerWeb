<?php
require_once('../api/v1/model/DBConnection.class.php');
require_once('../api/v1/model/DBHandlerSite.class.php');
require_once('../model/SiteHelper.class.php');
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
    <link rel="stylesheet" href="../css/main.css"/>
    <script type="text/javascript" src="../js/libs/jquery/jquery.js"></script>
    <script type="text/javascript" src="../js/libs/ElementQueries/ResizeSensor.js"></script>
    <script type="text/javascript" src="../js/libs/ElementQueries/ElementQueries.js"></script>
    <script type="text/javascript" src="../js/libs/amcharts/amcharts.js"></script>
    <script type="text/javascript" src="../js/libs/amcharts/serial.js"></script>
    <script type="text/javascript" src="../js/libs/amcharts/themes/light.js"></script>
    <script type="text/javascript" src="../js/libs/amcharts/plugins/responsive/responsive.min.js"></script>
    <script type="text/javascript" src="../js/libs/tablesorter/jquery.tablesorter.js"></script>
    <script type="text/javascript" src="../js/script.js"></script>
    <meta charset="UTF-8">
    <title>YTTracker - All</title>
</head>
<body>
    <div>
        <table id="dataTable">
            <thead>
                <tr>
                    <th class="userCell" rowspan="2">User</th>
                    <th class="leftVerticalLine" colspan="3">Total</th>
                </tr>
                <tr>
                    <th class="totalOpenedCell leftVerticalLine">Opened</th>
                    <th class="totalWatchedCell lightVerticalLine">Watched</th>
                    <th class="totalCountCell lightVerticalLine">Count</th>
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