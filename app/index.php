<?php

	use YTT\GraphSupplier;
	use YTT\OpenedGraph;
	use YTT\OpenedCountGraph;
	use YTT\WatchedGraph;

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
    <!-- JQuery -->
    <script src="//code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/popper.js/1.15.0/umd/popper.js" integrity="sha256-7BfFV/dSvQT4pGBvRAIt6JDXsehb92DQqpGUndLCPQ4=" crossorigin="anonymous"></script>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="css/libs/fontawesome-free-5.11.1-web/css/fontawesome.min.css">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="//stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="//stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <!-- Material Design Bootstrap -->
    <link href="//cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.8.10/css/mdb.min.css" rel="stylesheet">
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.8.10/js/mdb.min.js"></script>

    <!-- AmCharts -->
    <script src="//www.amcharts.com/lib/4/core.js"></script>
    <script src="//www.amcharts.com/lib/4/charts.js"></script>
    <script src="//www.amcharts.com/lib/4/themes/animated.js"></script>
    <script src="//www.amcharts.com/lib/4/themes/dark.js"></script>
    <script src="//www.amcharts.com/lib/4/themes/material.js"></script>

    <!-- Custom -->
    <link rel="stylesheet" href="css/main.css"/>
    <!--    <link rel="stylesheet" href="css/bootstrap.min.css">-->
    <script type="text/javascript" src="js/script.js"></script>

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="UTF-8">
    <meta name="viewport" content="width = device-width, initial-scale = 1">
    <title>YTTracker stats</title>
</head>
<body>
<?php
	include __DIR__ . "/header.php";
?>
<div class="container-fluid" style="margin-top:40px">
	<?php
//		include __DIR__ . "/table.php"
	?>
</div>
<div class="chartHolder" id="chartHolderWatched">
    <div class="chartDiv" id="chartDivWatched"></div>
</div>
<div class="legendHolder" id='legendHolderWatched'>
    <div class="legendDiv" id='legendDivWatched'></div>
</div>
<hr/>
<div class="chartHolder" id="chartHolderOpened">
    <div class="chartDiv" id="chartDivOpened"></div>
</div>
<div class="legendHolder" id='legendHolderOpened'>
    <div class="legendDiv" id='legendDivOpened'></div>
</div>
<hr/>
<div class="chartHolder" id="chartHolderOpenedCount">
    <div class="chartDiv" id="chartDivOpenedCount"></div>
</div>
<div class="legendHolder" id='legendHolderOpenedCount'>
    <div class="legendDiv" id='legendDivOpenedCount'></div>
</div>
<?php
	foreach(glob("graphs/*.php") as $filename)
		/** @noinspection PhpIncludeInspection */
		require_once __DIR__ . '/' . $filename;

	$plots[] = new OpenedGraph();
	$plots[] = new OpenedCountGraph();
	$plots[] = new WatchedGraph();

	$plots = array_filter($plots, function($plot){
		/**
		 * @var $plot GraphSupplier
		 */
		return $plot->shouldPlot();
	});

	foreach($plots as $plotIndex => $plot)
	{
		/**
		 * @var $plot GraphSupplier
		 */
		$name = $plot->getID();
		echo "<!-- $name -->";
		$plot->plot();
	}
?>
</body>
</html>