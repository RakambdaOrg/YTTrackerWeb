<?php
require_once('api/v1/model/DBConnection.class.php');
require_once('api/v1/model/DBHandlerSite.class.php');
require_once('model/SiteHelper.class.php');
$conn = DBConnection::getConnection();
$handler = new DBHandlerSite();
$siteHelper = new SiteHelper();
$customPeriodDisplayed = false;
if(isset($_GET['startPeriod']) && isset($_GET['endPeriod'])){
    $customPeriodDisplayed = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/main.css"/>
    <script type="text/javascript" src="js/jquery/jquery.js"></script>
    <script type="text/javascript" src="js/tablesorter/jquery.tablesorter.js"></script>
    <script type="text/javascript" src="js/script.js"></script>
    <meta charset="UTF-8">
    <title>YTTracker</title>
</head>
<body>
    <div>
        <form method="get">
            <label>
                Start:
                <input type="date" name="startPeriod"<?php
                if(isset($_GET['startPeriod'])){
                    echo ' value="' . $_GET['startPeriod'] . '"';
                }
                ?>>
            </label>
            <label>
                End:
                <input type="date" name="endPeriod"<?php
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
                <tr>
                <?php
                $uuids = $handler->getUUIDS($conn);
                if($uuids['code'] === 200)
                {
                    foreach($uuids['uuids'] as $UIDIndex=>$UID) {
                        $rawInfos = $handler->getUserInfos($conn, $UID)['stats'];
                        $infos = $siteHelper->decodeInfosFromDB($rawInfos, $customPeriodDisplayed ? $_GET['startPeriod'] : 'NULL', $customPeriodDisplayed ? $_GET['endPeriod'] : 'NULL');
                        echo '<tr id="' . $UIDIndex . '">'
                        ?>
                            <td class="userCell">
                                <?php
                                echo $rawInfos['username'] ? $rawInfos['username'] : $UIDIndex;
                                ?>
                            </td>
                            <td class="totalOpenedCell leftVerticalLine">
                                <?php
                                echo $siteHelper->secondsToTimeString($infos['TotalOpened']);
                                ?>
                            </td>
                            <td class="totalWatchedCell lightVerticalLine">
                                <?php
                                echo $siteHelper->secondsToTimeString($infos['TotalWatched']);
                                ?>
                            </td>
                            <td class="totalCountCell lightVerticalLine">
                                <?php
                                echo $infos['TotalCount'];
                                ?>
                            </td>
                            <td class="todayOpenedCell leftVerticalLine">
                                <?php
                                echo $siteHelper->secondsToTimeString($infos['TodayOpened']);
                                ?>
                            </td>
                            <td class="todayWatchedCell lightVerticalLine">
                                <?php
                                echo $siteHelper->secondsToTimeString($infos['TodayWatched']);
                                ?>
                            </td>
                            <td class="todayCountCell lightVerticalLine">
                                <?php
                                echo $infos['TodayCount'];
                                ?>
                            </td>
                            <?php
                                if($customPeriodDisplayed){
                                    ?>
                                    <td class="periodOpenedCell leftVerticalLine">
                                        <?php
                                        echo $siteHelper->secondsToTimeString($infos['PeriodOpened']);
                                        ?>
                                    </td>
                                    <td class="periodWatchedCell lightVerticalLine">
                                        <?php
                                        echo $siteHelper->secondsToTimeString($infos['PeriodWatched']);
                                        ?>
                                    </td>
                                    <td class="periodCountCell lightVerticalLine">
                                        <?php
                                        echo $infos['PeriodCount'];
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
</body>
</html>
<?php
$conn->close();
?>