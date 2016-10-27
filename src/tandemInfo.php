<?php
require_once dirname(__FILE__) . '/classes/lang.php';
$select_room = isset($_GET['select_room']) && $_GET['select_room'] == 1;
$goto = 'preWaitingRoom';
if ($select_room) {
	$goto = 'selectUserAndRoom';
}
if (
    (isset($_GET['lang']) || $_SESSION[USE_WAITING_ROOM_NO_TEAMS])
    && isset($_GET['force']) && $_GET['force']) {
	$_SESSION[LANG] = $_GET['lang'];
	
	header('Location: '.$goto.'.php');
	die();
}

$enabledWebRTC= false;
require_once dirname(__FILE__) . '/classes/constants.php';
require_once dirname(__FILE__) . '/classes/gestorBD.php';
require_once 'IMSBasicLTI/uoc-blti/lti_utils.php';
include(dirname(__FILE__) . '/classes/pdf.php');
require_once dirname(__FILE__) . '/classes/IntegrationTandemBLTI.php';

$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;
	
$gestorBD = new GestorBD();
if (empty($user_obj) || $user_obj->instructor != 1  ) {
	header('Location: index.php');
	die();
} 
$tandemFailedSuccessByDateStart = !empty($_REQUEST['tandemFailedSuccessByDateStart']) ? $_REQUEST['tandemFailedSuccessByDateStart'] : date("Y-m-d");
$tandemFailedSuccessByDateEnd = !empty($_REQUEST['tandemFailedSuccessByDateEnd']) ? $_REQUEST['tandemFailedSuccessByDateEnd'] : date("Y-m-d");
$currentActiveTandems = $gestorBD->currentActiveTandems($course_id);
$getUsersWaitingEs = $gestorBD->getUsersWaitingByLanguage($course_id,"es_ES");
$getUsersWaitingEn = $gestorBD->getUsersWaitingByLanguage($course_id,"en_US");
$tandemByDate = $gestorBD->getNumtandemsByDate(date("Y-m-d"),$course_id);
$getNumOfSuccessFailedTandems = $gestorBD->getNumOfSuccessFailedTandems($course_id,$tandemFailedSuccessByDateStart,$tandemFailedSuccessByDateEnd);

$stats = $gestorBD->get_stats_tandem_by_date($course_id, $tandemFailedSuccessByDateStart,$tandemFailedSuccessByDateEnd);

$getCountAllTandemsByDate = $gestorBD->getCountAllTandemsByDate($course_id);
$getCountAllUnFinishedTandemsByDate = $gestorBD->getCountAllUnFinishedTandemsByDate($course_id);
$getFeedbackStats = $gestorBD->getFeedbackStats($course_id);
$tandemStatsByVideoType = $gestorBD->tandemStatsByVideoType($course_id);
$peopleWaitedWithoutTandem  = $gestorBD->peopleWaitedWithoutTandem($course_id);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
<link href="css/tandem-waiting-room.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" media="all" href="css/slider.css" />
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
 <script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<script src="js/bootstrap-slider2.js"></script>
<script src="js/highcharts/js/highcharts.js"></script>
<script src="http://code.highcharts.com/modules/data.js"></script>
<script src="http://code.highcharts.com/modules/drilldown.js"></script>
<style>
	.container{ margin-top:20px;}
</style>
<script>
		$(document).ready(function(){

	        var interval = setInterval(function(){
	        	$.ajax({
	        		type: 'POST',
	        		url: "getCurrentUserCount.php",
	        		data : {
	        		},
	        		dataType: "JSON",
	        		success: function(json){	        			
	        			if(json  &&  typeof json.users_en !== "undefined" &&  typeof json.users_es !== "undefined"){
	        				$('#UsersWaitingEn').html(json.users_en);
	        				$('#UsersWaitingEs').html(json.users_es);
	        			}
	        		}
	        	});
	        },2500);

    	   (function( $ ) {
                $("#tandemByDate").datepicker({dateFormat: 'yy-mm-dd',altFormat :'dd-mm-yy',firstDay: 1, 
                            onSelect : function(date){
                                $.ajax({
                                        type: 'POST',
                                        url: "getNumTandemsByDate.php",
                                        dataType: "JSON",
                                        data :{date : date},
                                        success: function(json){                        
                                            if(json  &&  typeof json.tandems !== "undefined"){
                                                $('#nTandemsDate').html(json.tandems);                                              
                                            }
                                        }
                                });
                            }
                });
            })( jQuery );	

            (function( $ ) {
        		$("#tandemFailedSuccessByDateStart").datepicker({dateFormat: 'yy-mm-dd',altFormat :'dd-mm-yy',firstDay: 1});
                $("#tandemFailedSuccessByDateEnd").datepicker({dateFormat: 'yy-mm-dd',altFormat :'dd-mm-yy',firstDay: 1});
       		})( jQuery );

//finished vs unfinished tandems
$(function () {
    Highcharts.setOptions({ 
                global : {
                    useUTC : false
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
        	name : 'Number of finished Tandems by date',
            data: [
            	<?php 
            	if(!empty($getCountAllTandemsByDate)){ 
            		$tmp = array();
            		foreach($getCountAllTandemsByDate as $key => $value){
            			$exp = explode("-",$value['created']);
            			$tmp[] = "[Date.UTC(".$exp[0].",".($exp[1]-1).",".$exp[2].",10),".$value['total']."]";
            		}
            		echo implode(",",$tmp);
            	}
            	?>
             ]
        },
        {
        	name : 'Number of unfinished Tandems by date ',
            data: [
            	<?php 
            	if(!empty($getCountAllUnFinishedTandemsByDate)){ 
            		$tmp = array();
            		foreach($getCountAllUnFinishedTandemsByDate as $key => $value){
            			$exp = explode("-",$value['created']);
            			$tmp[] = "[Date.UTC(".$exp[0].",".($exp[1]-1).",".$exp[2].",10),".$value['total']."]";
            		}
            		echo implode(",",$tmp);
            	}
            	?>
             ]
        }]
    
    });
});

<?php if (!$_SESSION[USE_WAITING_ROOM_NO_TEAMS]) { ?>
//feedback forms by language
$(function () {
	   Highcharts.getOptions().plotOptions.pie.colors = (function () {
        var colors = ["#4E2E77","#788B44"];
            
            
        return colors;
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
            data: [
                ['ES',   <?php echo round( ($getFeedbackStats['feedback_tandem_form_es'] / $getFeedbackStats['feedback_tandem_forms_sent']) * 100 )?>],
                ['EN',   <?php echo round( ($getFeedbackStats['feedback_tandem_form_en'] / $getFeedbackStats['feedback_tandem_forms_sent']) * 100 )?>]                                             
            ]
        }]
    });
});

//feedback forms not sent correctly by language
$(function () {
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
            { color:'#4E2E77', y : <?php echo $getFeedbackStats['feedback_tandem_form_es_not_sent'] ?>},
            { color:'#788B44', y : <?php echo $getFeedbackStats['feedback_tandem_form_en_not_sent'] ?>}
            ]
        }]
    });
});
            //feedback forms not sent correctly by language
            $(function () {
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
                            { color:'#4E2E77', y : <?php echo $peopleWaitedWithoutTandem['es'] ?>},
                            { color:'#788B44', y : <?php echo $peopleWaitedWithoutTandem['en'] ?>}
                        ]
                    }]
                });
            });

        <?php } ?>

			<?php
			if ($stats['per_day_of_week']) {?>
				$(function () {
					$('#chart_per_day_of_week').highcharts({
						chart: {
							type: 'column'
						},
						title: {
							text: 'Tandems per day of week from <?php echo $tandemFailedSuccessByDateStart?> to <?php echo $tandemFailedSuccessByDateEnd?>'
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
						series: [
							<?php foreach ($stats['per_day_of_week'] as $key => $tandem_day){?>
							{
								name: "<?php echo $key?>",
								data: [<?php echo implode(",",$tandem_day)?>]
							},
							<?php } ?>
						]
					});
				});

				<?php
        		}
				if ($stats['per_day_of_week']) {?>
				//<div id='chart_per_day_of_week' class='well' style='width:380px;float:left;display:inline'></div>
				<?php }
				if ($stats['per_hour_finalized']) {?>
				//<div id='chart_per_hour_finalized' class='well' style='width:380px;float:left;display:inline'></div>
				<?php }
				if ($stats['per_hour']) {?>
			$(function () {
				$('#chart_per_hour').highcharts({
					chart: {
						type: 'column'
					},
					title: {
						text: 'Tandems per hour from <?php echo $tandemFailedSuccessByDateStart?> to <?php echo $tandemFailedSuccessByDateEnd?>'
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
					series:[{
						name: 'Tandems',
						data: [<?php echo implode(",",$stats['per_hour'])?>]
					}]
				});
			});
			<?php }
			if ($stats['per_user_status']) {
?>

			$(function () {
				$('#chart_per_user_status').highcharts({
					chart: {
						plotBackgroundColor: null,
						plotBorderWidth: 1,//null,
						plotShadow: false
					},
					title: {
						text: 'User Status per tandem'
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
						name: 'User status',
						data: [
							['Smilie',   <?php echo round(($stats['per_user_status']['smilies'] / $stats['per_user_status']['total']) * 100)?>],
							['Neutral',   <?php echo round(($stats['per_user_status']['neutral'] / $stats['per_user_status']['total']) * 100)?>],
							['Sad',   <?php echo round(($stats['per_user_status']['sad'] / $stats['per_user_status']['total']) * 100)?>]
						]
					}]
				});
			});

			<?php
			}
			if ($enabledWebRTC) {?>
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
            data: [<?php echo $tandemStatsByVideoType['tandem_ok']['webrtc'] ?>,<?php echo $tandemStatsByVideoType['tandem_ok']['videochat'] ?>]
        }]
    });
});

//ko tandems by webrtc and videochat
$(function () {
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
            data: [<?php echo $tandemStatsByVideoType['tandem_ko']['webrtc'] ?>,<?php echo $tandemStatsByVideoType['tandem_ko']['videochat'] ?>]
        }]
    });
});
<?php } ?>


//sucessfull vs failed tandems
			$(function () {
				//alert (document.getElementById('tsv').innerHTML);
				highcharts.data({
					csv: document.getElementById('tsv').innerHTML,
					itemDelimiter: '\t',
					parsed: function (columns) {

						var brands = {},
							brandsData = [],
							versions = {},
							drilldownSeries = [];

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
					}
				});
			});


});	
</script>
</head>
<body>
<div class='container'>
	<div class='row'>		
		<div class='col-md-12 text-right'>
				<p><a href="#" title="<?php echo $LanguageInstance->get('tandem_logo')?>"><img src="css/images/logo_Tandem.png" alt="<?php echo $LanguageInstance->get('tandem_logo')?>" /></a></p>
		</div>		
	</div>
		<div class='row'>
		<div class='col-md-12'>
		<p>
			 <a class='btn btn-success' href='manage_exercises_tandem.php'><?php echo $LanguageInstance->get("mange_exercises_tandem");?></a>
             <a href='statistics_tandem.php' class='btn btn-success' ><?php echo $LanguageInstance->get("Tandem Statistics");?></a> 
            <?php if (!$_SESSION[USE_WAITING_ROOM_NO_TEAMS]) {?><a href='tandemInfo.php?force=1&lang=en_US<?php echo $select_room?'&select_room=1':''?>' class='btn btn-success' ><?php echo $LanguageInstance->get("Go to tandem to practise English");?></a>
             <a href='tandemInfo.php?force=1&lang=es_ES<?php echo $select_room?'&select_room=1':''?>' class='btn btn-success' ><?php echo $LanguageInstance->get("Ir al tandem para practicar Español");?></a>
            <?php } else { ?>
                <a href='tandemInfo.php?force=1<?php echo $select_room?'&select_room=1':''?>' class='btn btn-success' ><?php echo $LanguageInstance->get("Go to tandem");?></a>
            <?php } ?>
		</p>
		</div>
	</div>
    <div class='well'>
    	<div class='row'>
    	<div class='col-md-12'>	<h3><?php echo $LanguageInstance->get('Statistics'); ?></h3></div>
    	   	<div class="col-md-6">
       			<div class="list_group">
    	   			<div class="list-group-item">
    	   			<?php
    	   			 	echo $LanguageInstance->get('Current active tandems');
    	   			 	echo ": <strong>".$currentActiveTandems."</strong>";
    	   			?>
    	   			</div> 
    	   			<div class="list-group-item">
    	   			<?php
    	   			 	echo $LanguageInstance->get('Users waiting to practice English');
    	   			 	echo ": <strong><span id=\"UsersWaitingEn\">".$getUsersWaitingEn."</span></strong>";
    	   			?>
    	   			</div> 
    	   			<div class="list-group-item">
    	   			<?php
    	   			 	echo $LanguageInstance->get('Usuarios esperando para practicar Español');
    	   			 	echo ": <strong><span id=\"UsersWaitingEn\">".$getUsersWaitingEs."</span></strong>";
    	   			?>	   				   				
    	   			</div> 
    	   			<div class="list-group-item">
    	   			<?php
    	   			 	echo $LanguageInstance->get('Total feedbacks');
    	   			 	echo ": <strong>".$getFeedbackStats['feedback_tandem']."</strong>";
    	   			?>	   				   				
    	   			</div> 
    	   			<div class="list-group-item">
    	   			<?php
    	   			 	echo $LanguageInstance->get('Total feedbacks submitted');
    	   			 	echo ": <strong>".$getFeedbackStats['feedback_tandem_forms_sent']."</strong>";
    	   			?>	   				   				
    	   			</div>
                    <?php if (!$_SESSION[USE_WAITING_ROOM_NO_TEAMS]) { ?>
    	   			<div class="list-group-item">
    	   			<?php
    	   			 	echo $LanguageInstance->get('Total feedbacks submitted for ES');
    	   			 	echo ": <strong>".$getFeedbackStats['feedback_tandem_form_es']."</strong>";
    	   			?>	   				   				
    	   			</div> 	   			
    	   			<div class="list-group-item">
    	   			<?php
    	   			 	echo $LanguageInstance->get('Total feedbacks submitted for EN');
    	   			 	echo ": <strong>".$getFeedbackStats['feedback_tandem_form_en']."</strong>";
    	   			?>	   				   				
    	   			</div>
                    <?php } ?>
      			</div>
      		</div>	   	
      		<div class="col-md-6">
       			<div class="list_group">
    	   			<div class="list-group-item">
    	   			<?php	   			 	
    	   			 	echo "<form role='form'><div class='input-group'><div class='input-group-addon'>Select date</div><input class='form-control' type='text'  id='tandemByDate' value='".date("d-m-Y")."'></div></form><br />";
    	   			 	echo $LanguageInstance->get('Number of people that have done a tandem on specific date');
    	   			 	echo ": <strong id='nTandemsDate'>".$tandemByDate."</strong>";
    	   			?>
    	   			</div> 	 	   			
    	   			<div class="list-group-item">
    	   			<?php	   			 	
    					echo $LanguageInstance->get('Number of failed tandems');
    	   			 	echo ": <strong id='nTandemsDate'>".$getNumOfSuccessFailedTandems['failed']."</strong>";
    	   			?>
    	   			</div> 	   		
      			</div> 
      		</div>
      	</div>
    </div>
  	<div class='row'>
	  	<div class='col-md-12'>	
	  		<div class='well'><div id='chart1'></div></div>
	  		<br />
            <div class='well'>
            <p>
                <div class='selectDatesForTandems'>
                    <form role='form' class="form-inline" method="post">
                        <div class="form-group">                        
                            <label  class="sr-only"> <?php echo $LanguageInstance->get('Start Date');?></label>
                            <p class="form-control-static"> <?php echo $LanguageInstance->get('Start Date');?></p>                           
                        </div>       
                        <div class="form-group">
                            <input type='text' class="form-control"  name='tandemFailedSuccessByDateStart' id='tandemFailedSuccessByDateStart' value='<?php echo $tandemFailedSuccessByDateStart?>'>                         
                        </div>
                       <div class="form-group">                        
                            <label  class="sr-only"> <?php echo $LanguageInstance->get('Start End');?></label>
                            <p class="form-control-static"> <?php echo $LanguageInstance->get('End Date');?></p>                           
                        </div>       
                        <div class="form-group">
                            <input type='text' class="form-control"  name='tandemFailedSuccessByDateEnd' id='tandemFailedSuccessByDateEnd' value='<?php echo $tandemFailedSuccessByDateEnd?>'>
                        </div>
                        <button type="submit" class="btn btn-default"><?php echo $LanguageInstance->get('View');?></button>
                    </form>
                </div>
            </p>
    	  		<div id='chart2' ></div>
            </div>	
	  		<br />
    <?php if (!$_SESSION[USE_WAITING_ROOM_NO_TEAMS]) { ?>
        <div id='chart3' class='well' style='width:380px;float:left;display:inline'></div>
            <div id='chart4' class='well' style='width:380px;float:left;display:inline'></div>
    <?php }?>
	<?php if ($enabledWebRTC) {?>
	  		<div id='chart5' class='well' style='width:380px;float:left;display:inline'></div>
	  		<div id='chart6' class='well' style='width:380px;float:left;display:inline'></div>
	<?php }?>
    <?php if (!$_SESSION[USE_WAITING_ROOM_NO_TEAMS]) { ?>
	  		<div id='chart7' class='well' style='width:380px;float:left;display:inline'></div>
    <?php }?>
	  	</div>
  	</div>
<?php
if ($stats['per_day_of_week_finalized']) {?>
	<div class='row'>
		<div class='col-md-12'>
			<div id='chart_per_day_of_week_finalized'></div>
		</div>
	</div>
<?php }
if ($stats['per_day_of_week']) {?>
	<div class='row'>
		<div class='col-md-12'>
			<div id='chart_per_day_of_week'></div>
		</div>
	</div>
<?php }
if ($stats['per_hour_finalized']) {?>
		<div class='row'>y
			<div class='col-md-12'>
				<div id='chart_per_hour_finalized' ></div>
			</div>
		</div>
<?php }
if ($stats['per_hour']) {?>
	<div class='row'>
		<div class='col-md-12'>
			<div id='chart_per_hour'></div>
		</div>
	</div>
<?php }
if ($stats['per_user_status']) {?>
	<div class='row'>
		<div class='col-md-12'>
			<div id='chart_per_user_status'></div>
		</div>
	</div>
<?php } ?>

  	<p></p>
</div>

<pre id="tsv" style="display:none">

Success 	<?php echo $getNumOfSuccessFailedTandems['success'];?>%

Failed 	<?php echo $getNumOfSuccessFailedTandems['failed'];?>%</pre>


</body>
</html>











