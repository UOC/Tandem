//ok tandems by webrtc and videochat
$(function () {
    $('#chart5').highcharts({
        chart: {
            type: 'bar'
        },
        title: {
            text: 'Successful tandems by WEBRTC and VIDEOCHAT in Total'
        },
        subtitle: {
            text: 'Not counting the one\'s that were not finished'
        },
        xAxis: {
            categories: ['WebRTC', 'Videochat'],
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
            name: 'ok tandems by webrtc and videochat',
            data: [aaaaa, bbbbb]
        }]
    });

    //ko tandems by webrtc and videochat
    $('#chart6').highcharts({
        chart: {
            type: 'bar'
        },
        title: {
            text: 'Failed tandems by WEBRTC and VIDEOCHAT in Total'
        },
        subtitle: {
            text: 'Not counting the one\'s that were not finished'
        },
        xAxis: {
            categories: ['WebRTC', 'Videochat'],
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
            name: 'Failed tandems by WEBRTC and VIDEOCHAT',
            data: [ccccc, ddddd]
        }]
    });
});
