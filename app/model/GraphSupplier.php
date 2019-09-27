<?php

	namespace YTT
	{
		/**
		 * Created by PhpStorm.
		 * User: mrcraftcod
		 * Date: 07/05/2017
		 * Time: 16:34
		 */
		abstract class GraphSupplier
		{
			function plot()
			{ ?>
                <script type='text/javascript'>
					$(function () {
							const chartDiv = document.getElementById('chartDiv' + '<?php echo $this->getID(); ?>');

							function getUsers(usersCallback) {
								$.ajax({
									url: '<?php echo $this->getUsersURL(); ?>',
                                    data: {
										range: <?php echo $this->getDataRange(); ?>
                                    },
									context: document.body,
									method: 'GET'
								}).done(function (data) {
									if (data && data['users'] && data['users'] instanceof Array) {
										usersCallback(data['users']);
									}
								});
							}

							const getUserDataURL = function(uuid){<?php echo $this->getUserDataURLFunction(); ?>};

							if (chartDiv) {
								getUsers(function (users) {
									let chart = am4core.create(chartDiv, am4charts.XYChart);
									chart.dateFormat = 'yyyy-MM-dd';
									chart.numberFormatter.numberFormat = "#.###";
									chart.durationFormatter.durationFormat = "hh':'mm':'ss";

									chart.exporting.menu = new am4core.ExportMenu();
									let title = chart.titles.create();
									title.text = "<?php echo $this->getTitle(); ?>";
									title.fontSize = 15;
									title.marginBottom = 15;

									let xAxis = chart.xAxes.push(new am4charts.DateAxis());
									xAxis.title.text = 'Date';
									xAxis.skipEmptyPeriods = true;
									xAxis.dateFormats.setKey("year", "yyyy");
									xAxis.dateFormats.setKey("month", "MMM yyyy");
									xAxis.dateFormats.setKey("week", "dd MMM yyyy");
									xAxis.dateFormats.setKey("day", "dd MMM");
									xAxis.dateFormats.setKey("hour", "HH:00");
									xAxis.dateFormats.setKey("minute", "HH:mm");
									xAxis.dateFormats.setKey("second", "HH:mm:ss");
									xAxis.baseInterval = {
										"timeUnit": "day",
										"count": 1
									};
									let yAxis = null;
									if (<?php echo $this->isDurationGraph() ? "true" : "false"; ?>) {
										yAxis = chart.yAxes.push(new am4charts.DurationAxis());
										yAxis.baseUnit = "millisecond";
									} else {
										yAxis = chart.yAxes.push(new am4charts.ValueAxis());
									}

									chart.legend = new am4charts.Legend();
									chart.legend.useDefaultMarker = true;

									const legendContainer = am4core.create("legendDiv" + '<?php echo $this->getID(); ?>', am4core.Container);
									legendContainer.width = am4core.percent(100);
									legendContainer.height = am4core.percent(100);
									chart.legend.parent = legendContainer;

									chart.events.on("datavalidated", resizeLegend);
									chart.events.on("maxsizechanged", resizeLegend);

									function resizeLegend(ev) {
										document.getElementById("legendDiv" + '<?php echo $this->getID(); ?>').style.height = chart.legend.contentHeight + "px";
									}

									let marker = chart.legend.markers.template.children.getIndex(0);
									marker.cornerRadius(12, 12, 12, 12);
									marker.strokeWidth = 2;
									marker.strokeOpacity = 1;
									marker.stroke = am4core.color("#cccccc");

									chart.cursor = new am4charts.XYCursor();
									chart.cursor.xAxis = xAxis;

									<?php echo $this->getGuides(); ?>

									for (const userIndex in users) {
										if (users.hasOwnProperty(userIndex)) {
											const userObject = users[userIndex];
											let series = chart.series.push(new am4charts.LineSeries());
											series.dataFields.valueY = "value";
											series.dataFields.dateX = "date";
											series.tooltipText = "[bold]" + userObject['username'] + " - {date.formatDate(\"yyyy-MM-dd\")}[/]\n<?php echo $this->getBalloonTooltip(); ?>";
											series.dataSource.url = getUserDataURL(userObject['uuid']);
											series.dataSource.requestOptions.requestHeaders = [{
												"key": "range",
												"value": "<?php echo $this->getDataRange() ?>"
											}];
											series.dataSource.parser.options.dateFields = ['date'];
											series.dataSource.parser.options.dateFormat = 'yyyy-MM-dd';
											series.name = userObject['username'];
											series.strokeWidth = 2;
											//series.legendSettings.valueText = "<?php //echo $this->getLegendText(); ?>//";
											// series.fillOpacity = 0.3;

											let bullet = series.bullets.push(new am4charts.CircleBullet());
											bullet.width = 10;
											bullet.height = 10;
										}
									}

									// Create scrollbars
									chart.scrollbarX = new am4core.Scrollbar();
									chart.scrollbarY = new am4core.Scrollbar();
								});
							}
						}
					);
                </script>
				<?php
			}

			/**
			 * @return string
			 */
			abstract function getID();

			/**
			 * @return string
			 */
			abstract function getTitle();

			/**
			 * @return bool
			 */
			function shouldPlot()
			{
				return true;
			}

			/**
			 * @return string
			 */
			abstract function getUsersURL();

			/**
			 * @return string
			 */
			abstract function getUserDataURLFunction();

			/**
			 * @return string
			 */
			protected function getBalloonTooltip()
			{
				return "[bold]{value}";
			}

			/**
			 * @return bool
			 */
			protected function isDurationGraph()
			{
				return false;
			}

			/**
			 * @return string
			 */
			protected function getLegendText()
			{
				return "{value}";
			}

			/**
			 * @return string
			 */
			protected function getGuides()
			{
				return ';';
			}

			/**
			 * @return mixed
			 */
			protected function getDataRange()
			{
				return isset($_GET['all']) ? ($_GET['all'] ? 2147483647 : 31) : 31;
			}
		}
	}