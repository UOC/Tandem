$(function () {
    $('#chart_per_hour').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: 'Tandems per hour from ' + aaa + ' to ' + bbb
        },
        subtitle: {
            text: 'Shows the finalized and not'
        },
        xAxis: {
            type: 'category',
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
        series: [{
            name: 'Tandems',
            data: ccc
        }]
    });
});
