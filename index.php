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
	$conn = YTT\DBConnection::getConnection();
	$handler = new YTT\DBHandlerSite($conn);
	$siteHelper = new YTT\SiteHelper();
	$customPeriodDisplayed = isset($_GET['startPeriod']) && isset($_GET['endPeriod']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css"
          integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">

    <script src="https://code.jquery.com/jquery-3.2.1.min.js"
            crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"
            integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh"
            crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js"
            integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ"
            crossorigin="anonymous"></script>

    <script type="text/javascript" src="js/libs/ElementQueries/ResizeSensor.js"></script>
    <script type="text/javascript" src="js/libs/ElementQueries/ElementQueries.js"></script>
    <script type="text/javascript" src="js/libs/tablesorter/jquery.tablesorter.js"></script>

    <script type="text/javascript" src="js/libs/amcharts/amcharts.js"></script>
    <script type="text/javascript" src="js/libs/amcharts/serial.js"></script>
    <script type="text/javascript" src="js/libs/amcharts/themes/light.js"></script>
    <script type="text/javascript" src="js/libs/amcharts/plugins/responsive/responsive.min.js"></script>

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1">
    <title>Rainbow6 stats</title>

    <link rel="stylesheet" href="css/main.css"/>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script type="text/javascript" src="js/script.js"></script>

    <meta charset="UTF-8">
    <title>YTTracker</title>
</head>
<body>
<?php
    include __DIR__ . "/header.php";
?>
<div class="container-fluid" style="margin-top:40px">
    <table id="dataTable" class="table table-striped table-bordered table-hover">
        <thead class="thead-dark">
        <tr>
            <th class="userCell" scope="col" rowspan="2">User<br/>(oldest record)</th>
            <th class="" scope="col" colspan="3">Total</th>
            <th class="" scope="col" colspan="3">Last Week</th>
            <th class="" scope="col" colspan="3">Last 24h</th>
			<?php
				if($customPeriodDisplayed)
				{
					?>
                    <th class="" colspan="3">Period</th>
					<?php
				}
			?>
        </tr>
        <tr>
            <th class="totalOpenedCell">Opened</th>
            <th class="totalWatchedCell">Watched</th>
            <th class="totalCountCell">Count</th>
            <th class="weekOpenedCell">Opened</th>
            <th class="weekWatchedCell">Watched</th>
            <th class="weekCountCell">Count</th>
            <th class="todayOpenedCell">Opened</th>
            <th class="todayWatchedCell">Watched</th>
            <th class="todayCountCell">Count</th>
			<?php
				if($customPeriodDisplayed)
				{
					?>
                    <th class="periodOpenedCell">Opened</th>
                    <th class="periodWatchedCell">Watched</th>
                    <th class="periodCountCell">Count</th>
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
		        foreach($uuids['uuids'] as $UUIDIndex => $UUID)
		        {
			        ?>
                    <tr id="user<?php
				        echo $UUID['ID'];
			        ?>">
                        <td class="userCell">
                            <div class="username">
						        <?php
							        $username = $handler->getUsername($UUID['UUID']);
							        echo $username ? $username : $UUID['ID'];
						        ?>
                            </div>
					        <?php
						        echo '(';
						        echo $handler->getOldestRecord($UUID['UUID']);
						        echo ')';
					        ?>
                        </td>
                        <td class="totalOpenedCell">
					        <?php
						        echo $siteHelper->millisecondsToTimeString($handler->getTotalOpened($UUID['UUID']));
					        ?>
                        </td>
                        <td class="totalWatchedCell">
					        <?php
						        echo $siteHelper->millisecondsToTimeString($handler->getTotalWatched($UUID['UUID']));
					        ?>
                        </td>
                        <td class="totalCountCell">
					        <?php
						        echo $handler->getTotalOpenedCount($UUID['UUID']);
					        ?>
                        </td>
                        <td class="weekOpenedCell">
					        <?php
						        echo $siteHelper->millisecondsToTimeString($handler->getWeekOpened($UUID['UUID']));
					        ?>
                        </td>
                        <td class="weekWatchedCell">
					        <?php
						        echo $siteHelper->millisecondsToTimeString($handler->getWeekWatched($UUID['UUID']));
					        ?>
                        </td>
                        <td class="weekCountCell">
					        <?php
						        echo $handler->getWeekOpenedCount($UUID['UUID']);
					        ?>
                        </td>
                        <td class="todayOpenedCell">
					        <?php
						        echo $siteHelper->millisecondsToTimeString($handler->getLast24hOpened($UUID['UUID']));
					        ?>
                        </td>
                        <td class="todayWatchedCell">
					        <?php
						        echo $siteHelper->millisecondsToTimeString($handler->getLast24hWatched($UUID['UUID']));
					        ?>
                        </td>
                        <td class="todayCountCell">
					        <?php
						        echo $handler->getLast24hOpenedCount($UUID['UUID']);
					        ?>
                        </td>
				        <?php
					        if($customPeriodDisplayed)
					        {
						        $start = 'STR_TO_DATE("' . $_GET['startPeriod'] . '", "%Y-%m-%dT%H:%i")';
						        $end = 'STR_TO_DATE("' . $_GET['endPeriod'] . ':59", "%Y-%m-%dT%H:%i:%s")';
						        ?>
                                <td class="periodOpenedCell">
							        <?php
								        echo $siteHelper->millisecondsToTimeString($handler->getPeriodOpened($UUID['UUID'], $start, $end));
							        ?>
                                </td>
                                <td class="periodWatchedCell">
							        <?php
								        echo $siteHelper->millisecondsToTimeString($handler->getPeriodWatched($UUID['UUID'], $start, $end));
							        ?>
                                </td>
                                <td class="periodCountCell">
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
    <div class="chartDiv" id="chartDivWatched"></div>
</div>
<hr/>
<div class="chartHolder" id="chartHolderOpened">
    <div class="chartDiv" id="chartDivOpened"></div>
</div>
<hr/>
<div class="chartHolder" id="chartHolderOpenedCount">
    <div class="chartDiv" id="chartDivOpenedCount"></div>
</div>
<?php include 'chartWatched.php'; ?>
<?php include 'chartOpened.php'; ?>
<?php include 'chartOpenedCount.php'; ?>
</body>
</html>