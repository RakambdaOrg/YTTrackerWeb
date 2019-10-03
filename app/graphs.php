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

	use YTT\GraphSupplier;
	use YTT\OpenedCountGraph;
	use YTT\OpenedGraph;
	use YTT\WatchedGraph;

	require_once(__DIR__ . '/model/GraphSupplier.php');
	foreach(glob(__DIR__ . "/graphs/*.php") as $filename)
		/** @noinspection PhpIncludeInspection */
		require_once $filename;

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
