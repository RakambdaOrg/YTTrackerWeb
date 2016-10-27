<script type='text/javascript'>
    $(document).ready(function () {
        /**
         * @return {string}
         */
        function YTTGetDateString(time) {
            if (!time)
                return '';
            var date = new Date(time);
            var y = date.getFullYear();
            var m = ("0" + (date.getMonth() + 1)).slice(-2);
            var d = ("0" + date.getDate()).slice(-2);
            return y + "-" + m + "-" + d;
        }

        //Resize chart to fit height
        var chartHolderCount = document.getElementById('chartHolderOpenedCount');
        var chartdivCount = document.getElementById('chartDivOpenedCount');
        new ResizeSensor(chartHolderCount, function () {
            chartdivCount.style.height = '' + chartHolderCount.clientHeight + 'px';
        });

        AmCharts.ready(function () {
            var chartColors = {
                theme: 'dark',
                selectedBackgroundColor: '#444444',
                gridColor: '#999999',
                color: '#111111',
                scrollBarBackgroundColor: '#666666',
                labelColor: '#000000',
                backgroundColor: '#777777',
                ratioLineColor: '#196E1F',
                countLineColor: '#214DD1',
                handDrawn: false
            };

            //Get days from config
            //noinspection JSAnnotator
            var parsedConfigCount = {};
            parsedConfigCount = <?php echo $siteHelper->getChartData($handler->getLastWeekTotalsCountOpened(), 3600000); ?>;
            var countUIDS = [];
            //Reorder dates
            const datasCOunt = [];
            Object.keys(parsedConfigCount).sort(function (a, b) {
                return Date.parse(a) - Date.parse(b);
            }).forEach(function (key) {
                for(var UIDIndex in parsedConfigCount[key])
                {
                    if(parsedConfigCount[key].hasOwnProperty(UIDIndex))
                    {
                        if(countUIDS.indexOf(UIDIndex) < 0)
                        {
                            countUIDS.push(UIDIndex);
                        }
                    }
                }
                var conf = parsedConfigCount[key];
                conf['date'] = key;
                datasCOunt.push(conf);
            });
            var watchedGraphs = [];
            for(var key in countUIDS)
            {
                if(countUIDS.hasOwnProperty(key))
                {
                    const username = $('#user' + countUIDS[key] + '>.userCell').text().trim();
                    watchedGraphs.push({
                        bullet: 'circle',
                        bulletBorderAlpha: 1,
                        bulletBorderThickness: 1,
                        dashLengthField: 'dashLength',
                        legendValueText: '[[value]]',
                        title: username,
                        fillAlphas: 0.2,
                        valueField: countUIDS[key],
                        valueAxis: 'countAxis',
                        type: 'smoothedLine',
                        lineThickness: 2,
                        bulletSize: 8,
                        balloonFunction: function (graphDataItem) {
                            return username + '<br>' + YTTGetDateString(graphDataItem.category.getTime()) + '<br/><b><span style="font-size:14px;">' + graphDataItem.values.value + '</span></b>';
                        }
                    });
                }
            }

            //Build Chart
            var chartCount = AmCharts.makeChart(chartdivCount, {
                type: 'serial',
                theme: chartColors['theme'],
                backgroundAlpha: 1,
                backgroundColor: chartColors['backgroundColor'],
                fillColors: chartColors['backgroundColor'],
                startDuration: 0.6,
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
                        return graphDataItem && graphDataItem.graph && graphDataItem.graph.valueField && graphDataItem.values && (graphDataItem.values.value || graphDataItem.values.value === 0) ? graphDataItem.values.value : '';
                    }
                },
                dataProvider: datasCOunt,
                valueAxes: [{
                    id: 'countAxis',
                    minimum: 0,
                    axisAlpha: 0,
                    gridAlpha: 0,
                    labelsEnabled: false,
                    inside: false,
                    position: 'left',
                    title: '',
                    labelFrequency: 2,
                    labelFunction: function (value) {
                        return value;
                    }
                }],
                graphs: watchedGraphs,
                chartScrollbar: {
                    autoGridCount: true,
                    scrollbarHeight: 40,
                    selectedBackgroundColor: chartColors['selectedBackgroundColor'],
                    gridColor: chartColors['gridColor'],
                    color: chartColors['color'],
                    backgroundColor: chartColors['scrollBarBackgroundColor'],
                    listeners: [{
                        event: 'zoomed',
                        method: onChartZoomed
                    }]
                },
                chartCursor: {
                    categoryBalloonDateFormat: 'YYYY-MM-DD',
                    cursorAlpha: 0.1,
                    cursorColor: '#000000',
                    fullWidth: true,
                    valueBalloonsEnabled: true,
                    zoomable: true
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

            function onChartZoomed(event) {
                var datas = [];
                for (var i in event.chart.dataProvider) {
                    if (event.chart.dataProvider.hasOwnProperty(i)) {
                        var data = event.chart.dataProvider[i];
                        var parts = data['date'].split('-');
                        var date = new Date(parts[0], parts[1] - 1, parts[2]);
                        if (date.getTime() >= event.start && date.getTime() <= event.end) {
                            datas.push(data);
                        }
                    }
                }
            }

            function zoomChart(range) {
                if (!range) {
                    range = 7;
                }
                chartCount.zoomToIndexes(datasCOunt.length - range, datasCOunt.length - 1);
            }
        });
    });
</script>
