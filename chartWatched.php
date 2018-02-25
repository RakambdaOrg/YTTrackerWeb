<script type='text/javascript'>
	$(document).ready(function () {
		/**
		 * @return {number}
		 */
		function YTTGetDurationAsMillisec(d) {
			if (!d) return 0;
			return (((((d.days || 0) * 24 + (d.hours || 0)) * 60 + (d.minutes || 0)) * 60 + (d.seconds || 0)) * 1000 + (d.milliseconds || 0)) || 0;
		}

		function YTTGetValidDuration(d) {
			if (!d) return {};
			if (YTTGetDurationAsMillisec(d) < 0) return {};
			if (d.days) {
				//noinspection JSDuplicatedDeclaration
				var temp = d.days - Math.floor(d.days);
				d.days = Math.floor(d.days);
				d.hours = (d.hours || 0) + temp * 24;
			}
			if (d.hours) {
				//noinspection JSDuplicatedDeclaration
				var temp = d.hours - Math.floor(d.hours);
				d.hours = Math.floor(d.hours);
				d.minutes = (d.minutes || 0) + temp * 60;
			}
			if (d.minutes) {
				//noinspection JSDuplicatedDeclaration
				var temp = d.minutes - Math.floor(d.minutes);
				d.minutes = Math.floor(d.minutes);
				d.secondes = (d.secondes || 0) + temp * 60;
			}
			if (d.secondes) {
				//noinspection JSDuplicatedDeclaration
				var temp = d.secondes - Math.floor(d.secondes);
				d.secondes = Math.floor(d.secondes);
				d.milliseconds = (d.milliseconds || 0) + temp * 1000;
			}
			if (d.milliseconds) {
				d.milliseconds = Math.floor(d.milliseconds);
			}
			return d;
		}

		function YTTAddDurations(d1, d2) {
			d1 = YTTGetValidDuration(d1);
			d2 = YTTGetValidDuration(d2);
			var d = {
				milliseconds: 0,
				seconds: 0,
				minutes: 0,
				hours: 0,
				days: 0
			};
			d.milliseconds += (d1.milliseconds || 0) + (d2.milliseconds || 0);
			d.seconds += (d1.seconds || 0) + (d2.seconds || 0) + parseInt(d.milliseconds / 1000);
			d.milliseconds %= 1000;
			d.minutes = (d1.minutes || 0) + (d2.minutes || 0) + parseInt(d.seconds / 60);
			d.seconds %= 60;
			d.hours = (d1.hours || 0) + (d2.hours || 0) + parseInt(d.minutes / 60);
			d.minutes %= 60;
			d.days = (d1.days || 0) + (d2.days || 0) + parseInt(d.hours / 24);
			d.hours %= 24;
			return d;
		}

		/**
		 * @return {string}
		 */
		function YTTGetDurationString(duration) {
			if (!duration)
				return '0S';
			duration = YTTAddDurations(duration, {});
			var text = '';
			if (duration.days)
				text += duration.days + 'D ';
			if (duration.hours)
				text += duration.hours + 'H ';
			if (duration.minutes)
				text += duration.minutes + 'M ';
			if (duration.seconds)
				text += duration.seconds + 'S';
			if (text === '')
				return '0S';
			return text;
		}

		/**
		 * @return {string}
		 */
		function YTTGetDateString(time) {
			if (!time)
				return '';
			var date = new Date(time);
			var y = date.getFullYear();
			var m = ('0' + (date.getMonth() + 1)).slice(-2);
			var d = ('0' + date.getDate()).slice(-2);
			return y + '-' + m + '-' + d;
		}

		//Resize chart to fit height
		var chartHolderWatched = document.getElementById('chartHolderWatched');
		var chartdivWatched = document.getElementById('chartDivWatched');
		new ResizeSensor(chartHolderWatched, function () {
			chartdivWatched.style.height = '' + chartHolderWatched.clientHeight + 'px';
		});

		AmCharts.ready(function () {
			var chartColors = {
				theme: 'dark',
				selectedBackgroundColor: '#3c5077',
				gridColor: '#999999',
				color: '#111111',
				scrollBarBackgroundColor: '#3d5e77',
				labelColor: '#000000',
				backgroundColor: '#2b3e50',
				ratioLineColor: '#196E1F',
				countLineColor: '#214DD1',
				handDrawn: false
			};

			//Get days from config
			//noinspection JSAnnotator
			var parsedConfigWatched = {};
			parsedConfigWatched = <?php
			if(isset($_GET['all']))
				echo $siteHelper->getChartData($handler->getUsersTotalsWatchedForever(), 3600000);
			else
				echo $siteHelper->getChartData($handler->getUsersTotalsWatched(), 3600000);
			?>;
			var openedUIDS = [];
			//Reorder dates
			const datasWatchedTemp = [];
			var dates = [];
			var startDate = new Date();
			for (var key in parsedConfigWatched) {
				if (parsedConfigWatched.hasOwnProperty(key)) {
					for (var UIDIndex in parsedConfigWatched[key]) {
						if (parsedConfigWatched[key].hasOwnProperty(UIDIndex)) {
							if (openedUIDS.indexOf(UIDIndex) < 0) {
								openedUIDS.push(UIDIndex);
							}
						}
					}
					var conf = parsedConfigWatched[key];
					conf['date'] = key;
					dates.push(key);
					datasWatchedTemp.push(conf);
					if (startDate - Date.parse(key) > 0)
						startDate = new Date(key);
				}
			}
			//Fill missing records
			startDate.setHours(0, 0, 0, 0);
			var endDate = new Date();
			endDate.setHours(0, 0, 0, 0);
			var nullDay = {};
			for (var i = 0; i < openedUIDS.length; i++) {
				nullDay[openedUIDS[i]] = 0;
			}
			while (startDate.getTime() <= endDate.getTime()) {
				var current = startDate.getFullYear() + '-' + (startDate.getMonth() < 9 ? "0" : "") + (startDate.getMonth() + 1) + '-' + (startDate.getDate() < 10 ? "0" : "") + startDate.getDate();
				if (dates.indexOf(current) < 0) {
					var data = {};
					for (var k in nullDay)
						if (nullDay.hasOwnProperty(k))
							data[k] = nullDay[k];
					data['date'] = current;
					datasWatchedTemp.push(data)
				}
				else {
					for (var i = 0; i < datasWatchedTemp.length; i++) {
						if (datasWatchedTemp[i]['date'] === current) {
							for (var j = 0; j < openedUIDS.length; j++) {
								if (!datasWatchedTemp[i].hasOwnProperty(openedUIDS[j])) {
									datasWatchedTemp[i][openedUIDS[j]] = 0;
								}
							}
							break;
						}
					}
				}
				startDate.setDate(startDate.getDate() + 1);
			}
			const datasWatched = datasWatchedTemp.sort(function (a, b) {
				return Date.parse(a['date']) - Date.parse(b['date']);
			});

			var watchedGraphs = [];
			for (var key in openedUIDS) {
				if (openedUIDS.hasOwnProperty(key)) {
					const username = $('#user' + openedUIDS[key] + '>.userCell>.username').text().trim();
					watchedGraphs.push({
						bullet: 'circle',
						bulletBorderAlpha: 1,
						bulletBorderThickness: 1,
						connect: false,
						dashLengthField: 'dashLength',
						legendValueText: '[[value]]',
						title: username,
						fillAlphas: 0.2,
						valueField: openedUIDS[key],
						valueAxis: 'durationAxis',
						type: 'smoothedLine',
						lineThickness: 2,
						bulletSize: 8,
						balloonFunction: function (graphDataItem) {
							return username + '<br>' + YTTGetDateString(graphDataItem.category.getTime()) + '<br/><b><span style="font-size:14px;">' + YTTGetDurationString({hours: graphDataItem.values.value}) + '</span></b><br/>' + (Math.round((graphDataItem.values.value / 24.0) * 10000) / 100) + '% of day';
						}
					});
				}
			}

			//Build Chart
			var chartWatched = AmCharts.makeChart(chartdivWatched, {
				type: 'serial',
				theme: chartColors['theme'],
				backgroundAlpha: 1,
				backgroundColor: chartColors['backgroundColor'],
				fillColors: chartColors['backgroundColor'],
				handDrawn: chartColors['handDrawn'],
				legend: {
					equalWidths: false,
					useGraphSettings: true,
					valueAlign: 'left',
					valueWidth: 60,
					backgroundAlpha: 1,
					backgroundColor: chartColors['backgroundColor'],
					fillColors: chartColors['backgroundColor'],
					valueFunction: function (graphDataItem) {
						return graphDataItem && graphDataItem.graph && graphDataItem.graph.valueField && graphDataItem.values && (graphDataItem.values.value || graphDataItem.values.value === 0) ? YTTGetDurationString({hours: graphDataItem.values.value}) : '';
					}
				},
				dataProvider: datasWatched,
				valueAxes: [
					{
						id: 'durationAxis',
						duration: 'hh',
						durationUnits: {
							DD: 'd',
							hh: 'h ',
							mm: 'min',
							ss: 's'
						},
						axisAlpha: 0.5,
						gridAlpha: 0.2,
						inside: true,
						color: chartColors['labelColor'],
						position: 'left',
						title: 'Opened time',
						labelFrequency: 2,
						labelFunction: function (value) {
							return YTTGetDurationString({hours: value});
						}
					}
				],
				graphs: watchedGraphs,
				chartScrollbar: {
					autoGridCount: true,
					scrollbarHeight: 40,
					selectedBackgroundColor: chartColors['selectedBackgroundColor'],
					gridColor: chartColors['gridColor'],
					color: chartColors['color'],
					backgroundColor: chartColors['scrollBarBackgroundColor']
				},
				chartCursor: {
					categoryBalloonDateFormat: 'YYYY-MM-DD',
					cursorAlpha: 0.1,
					cursorColor: '#000000',
					fullWidth: true,
					valueBalloonsEnabled: true,
					zoomable: false
				},
				dataDateFormat: 'YYYY-MM-DD',
				categoryField: 'date',
				categoryAxis: {
					dateFormats: [{
						period: 'DD',
						format: 'DD'
					}, {
						period: 'WW',
						format: 'MMM DD'
					}, {
						period: 'MM',
						format: 'MMM'
					}, {
						period: 'YYYY',
						format: 'YYYY'
					}],
					minPeriod: 'DD',
					parseDates: true,
					autoGridCount: true,
					axisColor: '#555555',
					gridAlpha: 0.1,
					gridColor: '#FFFFFF'
				},
				responsive: {
					enabled: true
				}
			});

			zoomChart();

			function zoomChart(range) {
				if (!range) {
					range = 7;
				}
				try {
					chartWatched.zoomToIndexes(datasWatched.length - range, datasWatched.length - 1);
				}
				catch (TypeError) {
				}
			}
		});
	});
</script>
