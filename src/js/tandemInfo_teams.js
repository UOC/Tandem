// Feedback forms by language
$(function () {
    Highcharts.getOptions().plotOptions.pie.colors = (function () {
        return ["#4E2E77", "#788B44", '#ff6600'];
    }());

    $('#chart3').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: 1,//null,
            plotShadow: false
        },
        title: {
            text: 'Feedback forms submissions by Language in Total'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Feedback form by language',
            data: [['ES', a], ['EN', b]]
        }]
    });

    // Feedback forms not sent correctly by language
    $('#chart4').highcharts({
        chart: {
            type: 'bar'
        },
        title: {
            text: 'Form submission not sent by language in Total'
        },
        subtitle: {
            text: 'Tandem'
        },
        xAxis: {
            categories: ['Es', 'En'],
            title: {
                text: null
            }
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Not sent',
                align: 'high'
            },
            labels: {
                overflow: 'justify'
            }
        },
        tooltip: {
            valueSuffix: ' feedback forms'
        },
        plotOptions: {
            bar: {
                dataLabels: {
                    enabled: true
                }
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            x: -20,
            y: 190,
            floating: true,
            borderWidth: 1,
            backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
            shadow: true
        },
        credits: {
            enabled: false
        },
        series: [{
            name: 'Form submission not sent by language',
            data: [
                {color: '#4E2E77', y: c},
                {color: '#788B44', y: d}
            ]

        }]
    });

    //feedback forms not sent correctly by language
    $('#chart7').highcharts({
        chart: {
            type: 'bar'
        },
        title: {
            text: 'People that waited for a tandem but never got a partner in total'
        },
        subtitle: {
            text: 'Tandem'
        },
        xAxis: {
            categories: ['Es', 'En'],
            title: {
                text: null
            }
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Not sent',
                align: 'high'
            },
            labels: {
                overflow: 'justify'
            }
        },
        tooltip: {
            valueSuffix: ' feedback forms'
        },
        plotOptions: {
            bar: {
                dataLabels: {
                    enabled: true
                }
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            x: -20,
            y: 190,
            floating: true,
            borderWidth: 1,
            backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
            shadow: true
        },
        credits: {
            enabled: false
        },
        series: [{
            name: 'People waited for tanden by language',
            data: [
                {color: '#4E2E77', y: e},
                {color: '#788B44', y: f}
            ]
        }]
    });
});
