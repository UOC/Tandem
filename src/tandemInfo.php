<?php
require_once dirname(__FILE__) . '/classes/lang.php';
$select_room = isset($_GET['select_room']) && $_GET['select_room'] == 1;
$goto = 'autoAssignTandemRoom';
if ($select_room) {
	$goto = 'selectUserAndRoom';
}
if (isset($_GET['lang']) && isset($_GET['force']) && $_GET['force']) {
	$_SESSION[LANG] = $_GET['lang'];
	
	header('Location: '.$goto.'.php');
	die();
}

require_once dirname(__FILE__) . '/classes/constants.php';
require_once dirname(__FILE__) . '/classes/gestorBD.php';
require_once 'IMSBasicLTI/uoc-blti/lti_utils.php';
include(dirname(__FILE__) . '/classes/pdf.php');
require_once dirname(__FILE__) . '/classes/IntegrationTandemBLTI.php';

$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$course_id = 255;//isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;
	
$gestorBD = new GestorBD();
if (empty($user_obj) || $user_obj->instructor != 1  ) {
	header('Location: index.php');
	die();
} 

$currentActiveTandems = $gestorBD->currentActiveTandems($course_id);
$getUsersWaitingEs = $gestorBD->getUsersWaitingByLanguage($course_id,"es_ES");
$getUsersWaitingEn = $gestorBD->getUsersWaitingByLanguage($course_id,"en_US");
$tandemByDate = $gestorBD->getNumtandemsByDate(date("Y-m-d"),$course_id);
$getNumOfSuccessFailedTandems = $gestorBD->getNumOfSuccessFailedTandems($course_id);
$getCountAllTandemsByDate = $gestorBD->getCountAllTandemsByDate($course_id);
$getCountAllUnFinishedTandemsByDate = $gestorBD->getCountAllUnFinishedTandemsByDate($course_id);
$getFeedbackStats = $gestorBD->getFeedbackStats($course_id);
$tandemStatsByVideoType = $gestorBD->tandemStatsByVideoType($course_id);
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
            text: 'Finishes vs unfinished tandems'
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


//sucessfull vs failed tandems
$(function () {

    Highcharts.data({
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
                    text: 'Successful vs Failed tandems in Total'
                },
                subtitle: {
                    text: 'View the amounts of successful executed tandems vs the ones that failed( counting total_time less than 5s and the one\'s that were not finished )'
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
             <a href='tandemInfo.php?force=1&lang=en_US<?php echo $select_room?'&select_room=1':''?>' class='btn btn-success' ><?php echo $LanguageInstance->get("Go to tandem to practise English");?></a> 
             <a href='tandemInfo.php?force=1&lang=es_ES<?php echo $select_room?'&select_room=1':''?>' class='btn btn-success' ><?php echo $LanguageInstance->get("Ir al tandem para practicar Español");?></a> 
		</p>
		</div>
	</div>
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
  			</div>
  		</div>	   	
  		<div class="col-md-6">
   			<div class="list_group">
	   			<div class="list-group-item">
	   			<?php	   			 	
	   			 	echo "<input type='text' style ='width: 65px;font-size: 11px;' id='tandemByDate' value='".date("d-m-Y")."'><br />";
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
  	<div class='row'>
	  	<div class='col-md-12'>	  	
	  		<div id='chart1'></div>
	  		<br />
	  		<div id='chart2'></div>	
	  		<br />
	  		<div id='chart3' style='width:350px;float:left;display:inline'></div>
	  		<div id='chart4' style='width:350px;display:inline'></div>
	  		<div id='chart5' style='width:350px;float:left;display:inline'></div>
	  		<div id='chart6' style='width:350px;float:left;display:inline'></div>
	  	</div>
  	</div>
  	<p></p>
</div>

<pre id="tsv" style="display:none">
Microsoft Internet Explorer 8.0	26.61%

Success 	<?php echo $getNumOfSuccessFailedTandems['success'];?>%

Failed 	<?php echo $getNumOfSuccessFailedTandems['failed'];?>%</pre>


</body>
</html>











