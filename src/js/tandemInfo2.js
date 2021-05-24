$(function () {
    //finished vs unfinished tandems
    Highcharts.setOptions({
        global: {
            useUTC: false
        }
    });
    var chart = new Highcharts.Chart({
        title: {
            text: 'Tandems per day'
        },
        credits: {
            enabled: false
        },
        subtitle: {
            text: 'Finished are all tandems that were finished by the user and Unfinished tandems are all the tandems that didn\'t got to be finished by the user '
        },
        chart: {
            renderTo: 'chart1',
            zoomType: 'xy'
        },
        xAxis: {
            type: 'datetime'
        },
        series: [{
            name: 'Number of finished Tandems by date',
            data: numberOfFinishedTandemsByDate
        }, {
            name: 'Number of unfinished Tandems by date ',
            data: numberOfUnfinishedTandemsByDate
        }]
    });

    //sucessfull vs failed tandems
    try {
        Highcharts.data({
            csv: document.getElementById('tsv').innerHTML,
            itemDelimiter: '\t',
            parsed: function (columns) {
                var brands = {},
                    brandsData = [],
                    versions = {},
                    drilldownSeries = [];

                try {
                    // Parse percentage strings
                    columns[1] = $.map(columns[1], function (value) {
                        if (value.indexOf('%') === value.length - 1) {
                            value = parseFloat(value);
                        }
                        return value;
                    });

                    $.each(columns[0], function (i, name) {
                        var brand,
                            version;

                        if (i > 0) {

                            // Remove special edition notes
                            name = name.split(' -')[0];

                            // Split into brand and version
                            version = name.match(/([0-9]+[\.0-9x]*)/);
                            if (version) {
                                version = version[0];
                            }
                            brand = name.replace(version, '');

                            // Create the main data
                            if (!brands[brand]) {
                                brands[brand] = columns[1][i];
                            } else {
                                brands[brand] += columns[1][i];
                            }

                            // Create the version data
                            if (version !== null) {
                                if (!versions[brand]) {
                                    versions[brand] = [];
                                }
                                versions[brand].push(['v' + version, columns[1][i]]);
                            }
                        }

                    });

                    $.each(brands, function (name, y) {
                        brandsData.push({
                            name: name,
                            y: y,
                            drilldown: versions[name] ? name : null
                        });
                    });
                    $.each(versions, function (key, value) {
                        drilldownSeries.push({
                            name: key,
                            id: key,
                            data: value
                        });
                    });

                    // Create the chart
                    $('#chart2').highcharts({
                        chart: {
                            type: 'column'
                        },
                        credits: {
                            enabled: false
                        },
                        title: {
                            text: 'Successful vs Failed tandems by date range'
                        },
                        subtitle: {
                            text: 'The success are all the tandems that the total_time is 60 seconds or more, and the Failed tandems are all the tandems counting the finished and unfinished and that the total_time is less than 60 seconds '
                        },
                        xAxis: {
                            type: 'category'
                        },
                        yAxis: {
                            title: {
                                text: 'Total percent per success - failure'
                            }
                        },
                        legend: {
                            enabled: false
                        },
                        plotOptions: {
                            series: {
                                borderWidth: 0,
                                dataLabels: {
                                    enabled: true,
                                    format: '{point.y:1f}'
                                }
                            }
                        },

                        tooltip: {
                            headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                            pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:1f}</b><br/>'
                        },

                        series: [{
                            name: 'tandems',
                            colorByPoint: true,
                            data: brandsData
                        }],
                        drilldown: {
                            series: drilldownSeries
                        }
                    });

                } catch (e) {
                    console.error(e);
                }
            }
        });
    } catch (e) {
        console.error("Error loading highchart", e);
    }
});
