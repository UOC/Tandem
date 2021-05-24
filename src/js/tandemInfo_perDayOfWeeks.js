$(function () {
    $('#chart_per_day_of_week').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: 'Tandems per day of week from ' + aa + ' to ' + bb
        },
        subtitle: {
            text: 'Shows the finalized and not'
        },
        xAxis: {
            categories: ['00:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00', '08:00',
                '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00',
                '19:00', '20:00', '21:00', '22:00', '23:00'],
            crosshair: true,
            title: {
                text: 'Hours'
            }
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Number of tandems'
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.0f} tandems</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: cc
    });
});
