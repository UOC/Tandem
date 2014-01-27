<?php 
require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/constants.php';
require_once dirname(__FILE__).'/classes/gestorBD.php';
require_once dirname(__FILE__).'/classes/utils.php';

$user_obj = $_SESSION[CURRENT_USER];
$course_id = $_SESSION[COURSE_ID];
if (!isset($user_obj) || !isset($course_id) || !$user_obj->instructor) {
	//Tornem a l'index
	header ('Location: index.php');
} else {
	$id_resource_lti = $_SESSION[ID_RESOURCE];
	$gestorBD	= new GestorBD();
	$users_course = $gestorBD->obte_llistat_usuaris($course_id);
	$is_showTandem =(isset($_POST['showTandem']) || (isset($_POST['showTandemReload']) && $_POST['showTandemReload']=="1"));
	$user_tandems = null;
	$task_tandems = null;
	$question_task_tandems = null;
	$id_tandem = isset($_POST['id_tandem'])?intval($_POST['id_tandem']):0;
	$id_task = isset($_POST['id_task'])?intval($_POST['id_task']):0;
	$id_question = isset($_POST['id_question'])?intval($_POST['id_question']):0;
	$user_selected = isset($_POST['user_selected'])?intval($_POST['user_selected']):0;
	
	$order_by_tandems = isset($_POST['order_by_tandems'])?intval($_POST['order_by_tandems']):0;
	$order_by_tandems_direction = isset($_POST['order_by_tandems_direction'])?intval($_POST['order_by_tandems_direction']):0;
	$order_by_tasks = isset($_POST['order_by_tasks'])?intval($_POST['order_by_tasks']):0;
	$order_by_tasks_direction = isset($_POST['order_by_tasks_direction'])?intval($_POST['order_by_tasks_direction']):0;
	$order_by_questions = isset($_POST['order_by_questions'])?intval($_POST['order_by_questions']):0;
	$order_by_questions_direction = isset($_POST['order_by_questions_direction'])?intval($_POST['order_by_questions_direction']):0;
	
	$start_date = isset($_POST['start_date'])?$_POST['start_date']:'';
	$finish_date = isset($_POST['finish_date'])?$_POST['finish_date']:'';
	$finished = isset($_POST['finished'])?intval($_POST['finished']):-1; //-1 all values, 0 not finished, 1 only finished, 2 error
	
	$exercise_form  = isset($_POST['room'])?intval($_POST['room'],10):false;
	if ($is_showTandem){
		$user_tandems = $gestorBD->obte_llistat_tandems($course_id, $user_selected, $exercise_form, $id_tandem, $order_by_tandems, $order_by_tandems_direction, $start_date, $finish_date, $finished);
		//Busquem per tandem
		if ($id_tandem>0) {
			$task_tandems = $gestorBD->obte_task_tandems($course_id, $user_selected, $exercise_form, $id_tandem, $id_task, $order_by_tasks, $order_by_tasks_direction, $start_date, $finish_date, $finished);
			if ($id_task>0) {
				$question_task_tandems = $gestorBD->obte_questions_task_tandems($course_id, $user_selected, $exercise_form, $id_tandem, $id_task, $id_question, $order_by_questions, $order_by_questions_direction, $start_date, $finish_date, $finished);
			}
		}
	}
	$array_exercises = $gestorBD->get_tandem_exercises($course_id);
	//$selected_exercise = isset($_POST['select_exercise']) ? $_POST['select_exercise'] : isset($_POST['room']) ? $_POST['room'] : '';
	$selected_exercise = isset($_POST['select_exercise']) ? $_POST['select_exercise'] : '';

//var_dump($_POST);
	//Agafem les dades de l'usuari
	$name = mb_convert_encoding($user_obj->name, 'ISO-8859-1', 'UTF-8');

	function get_tandem_status ($status){
		global $LanguageInstance;
		if($status==2){
			return '<span class="lbl-success">'.$LanguageInstance->get('complete').'</span>';
		}else if($status==1){
			return '<span class="lbl-warning">'.$LanguageInstance->get('incomplete').'</span>';
		}else{
			return '<span class="lbl-error">'.$LanguageInstance->get('error').'</span>';
		}
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Tandem</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link rel="stylesheet" type="text/css" media="all" href="css/tandem.css" />
<link rel="stylesheet" type="text/css" media="all" href="css/jquery-ui.css" />
<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/jquery.ui.core.js"></script>
<script src="js/jquery.ui.widget.js"></script>
<script src="js/jquery.ui.button.js"></script>
<script src="js/jquery.ui.position.js"></script>
<script src="js/jquery.ui.autocomplete.js"></script>
<script src="js/jquery.ui.datepicker.js"></script>
<script src="js/jquery.colorbox-min.js"></script>
<script src="js/jquery.simplemodal.1.4.2.min.js"></script>
<script src="js/common.js"></script>
<?php include_once dirname(__FILE__).'/js/google_analytics.php'?>
<script type="text/javascript">

(function( $ ) {
	$.widget( "ui.combobox", {
		_create: function() {
			var input,
				self = this,
				select = this.element.hide(),
				selected = select.children( ":selected" ),
				value = selected.val() ? selected.text() : "",
				wrapper = $( "<span>" )
					.addClass( "ui-combobox" )
					.insertAfter( select );

			input = $( "<input>" )
				.appendTo( wrapper )
				.val( value )
				.addClass( "ui-state-default" )
				.autocomplete({
					delay: 0,
					minLength: 0,
					source: function( request, response ) {
						var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
						response( select.children( "option" ).map(function() {
							var text = $( this ).text();
							if ( this.value && ( !request.term || matcher.test(text) ) )
								return {
									label: text.replace(
										new RegExp(
											"(?![^&;]+;)(?!<[^<>]*)(" +
											$.ui.autocomplete.escapeRegex(request.term) +
											")(?![^<>]*>)(?![^&;]+;)", "gi"
										), "<strong>$1</strong>" ),
									value: text,
									option: this
								};
						}) );
					},
					select: function( event, ui ) {
						ui.item.option.selected = true;
						self._trigger( "selected", event, {
							item: ui.item.option
						});
					},
					change: function( event, ui ) {
						if ( !ui.item ) {
							input.val( $(select).find("option:selected").text());
							var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
								valid = false;
							select.children( "option" ).each(function() {
								if ( $( this ).text().match( matcher ) ) {
									this.selected = valid = true;
									return false;
								}
							});
							if ( !valid ) {
								// remove invalid value, as it didn't match anything
								$( this ).val( "" );
								select.val( "" );
								input.data( "autocomplete" ).term = "";
								return false;
							}
						}
					}
				})
				.addClass( "ui-widget ui-widget-content ui-corner-left" );

			input.focus(function(event) {
	            $(this).val('');
	            $(input).autocomplete('search', '');
	        });
			//Aquesta linia es pq seleccioni
			input.val( $(select).find("option:selected").text());

			input.data( "autocomplete" )._renderItem = function( ul, item ) {
				return $( "<li></li>" )
					.data( "item.autocomplete", item )
					.append( "<a>" + item.label + "</a>" )
					.appendTo( ul );
			};

			$( "<a>" )
				.attr( "tabIndex", -1 )
				.attr( "title", "Show All Items" )
				.appendTo( wrapper )
				.button({
					icons: {
						primary: "ui-icon-triangle-1-s"
					},
					text: false
				})
				.removeClass( "ui-corner-all" )
				.addClass( "ui-corner-right ui-button-icon" )
				.click(function() {
					// close if already visible
					if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
						input.autocomplete( "close" );
						return;
					}

					// work around a bug (likely same cause as #5265)
					$( this ).blur();

					// pass empty string as value to search for, displaying all results
					input.autocomplete( "search", "" );
					input.focus();
				});
		},
		

		destroy: function() {
			this.wrapper.remove();
			this.element.show();
			$.Widget.prototype.destroy.call( this );
		}
	});
})( jQuery );

	$(document).ready(function(){
		printError = function (error) {
			alert(error);
		}

		cleanForm = function () {
			putValues(-1, -1, -1, 0, 0 , 0, 0);
		}
		putValues = function (form_id_tandem, form_id_task, form_id_question, showTandemReload, form_order_by_tandems, form_order_by_tandems_direction, form_order_by_tasks, form_order_by_tasks_direction, form_order_by_questions, form_order_by_questions_direction) {
			$("#form_id_tandem").val(form_id_tandem);
			$("#form_id_task").val(form_id_task);
			$("#form_id_question").val(form_id_question);
			$("#showTandemReload").val(showTandemReload);
			$("#form_order_by_tandems").val(form_order_by_tandems);
			$("#form_order_by_tandems_direction").val(form_order_by_tandems_direction);
			$("#form_order_by_tasks").val(form_order_by_tasks);
			$("#form_order_by_tasks_direction").val(form_order_by_tasks_direction);
			$("#form_order_by_questions").val(form_order_by_questions);
			$("#form_order_by_questions_direction").val(form_order_by_questions_direction);
			$("#form_start_date").val($("#start_date").val());
			$("#form_finish_date").val($("#finish_date").val());
			$("#form_finished").val($("#finished").val());
			
		}
		search = function (form_id_tandem, form_id_task, form_id_question) {
			putValues(form_id_tandem, form_id_task, form_id_question, 1, <?php echo $order_by_tandems;?>, <?php echo $order_by_tandems_direction;?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);
			$("#form_action").submit();
		}
		order = function (form_order_by_tandems, form_order_by_tandems_direction, form_order_by_tasks, form_order_by_tasks_direction, form_order_by_questions, form_order_by_questions_direction) {
			putValues(<?php echo $id_tandem; ?>, <?php echo $id_task; ?>, <?php echo $id_question ?>, 1, form_order_by_tandems, form_order_by_tandems_direction, form_order_by_tasks, form_order_by_tasks_direction, form_order_by_questions, form_order_by_questions_direction);
			$("#form_action").submit();
		}

		$( "#start_date" ).datepicker({
			dateFormat: 'yy-mm-dd', 
			showOn: "both", 
			buttonImage: "img/calendar.gif", 
			buttonImageOnly: true,
			numberOfMonths: 2, 
      		onClose: function( selectedDate ) {
        		$( "#finish_date" ).datepicker( "option", "minDate", selectedDate );
      		}
		});

		$( "#finish_date" ).datepicker({
			dateFormat: 'yy-mm-dd', 
			showOn: "both", 
			buttonImage: "img/calendar.gif", 
			buttonImageOnly: true,
			numberOfMonths: 2, 
      		onClose: function( selectedDate ) {
        		$( "#start_date" ).datepicker( "option", "maxDate", selectedDate );
      		}
		});
		
		$( "#user_selected" ).combobox();
		$( "#select_exercise" ).combobox();

		play_tandem = function (id, is_user_host) {
			window.open('play_tandem.php?id='+id+'&is_user_host='+is_user_host);
		}
			 
	});
	
</script>
</head>
<body>

<!-- accessibility -->
	<div id="accessibility">
		<a href="#content" accesskey="s" title="Acceso directo al contenido"><?php echo $LanguageInstance->get('direct_access_to_content')?></a> | 
	</div>
	<!-- /accessibility -->
	
	<!-- /wrapper -->
	<div id="wrapper">
		<!-- main-container -->
  		<div id="main-container">
  			<!-- main -->
			<div id="main">
				<!-- content -->
				<div id="content">
					<a href="selectUserAndRoom.php" class="tandem-btn-secundary btn-back"><span>&larr;</span>&nbsp;<?php echo $LanguageInstance->get('back')?></a>

					<div id="logo">
						<a href="#" title="<?php echo $LanguageInstance->get('tandem_logo')?>"><img src="css/images/logo_Tandem.png" alt="<?php echo $LanguageInstance->get('tandem_logo')?>" /></a>
					</div>

					<div class="clear">
						<h1 class="main-title"><?php echo $LanguageInstance->get('activity_log')?></h1>
						
						<div id="criteria-wrapper">
							<a href="#" class="tandem-btn btn-search-criteria" id="btn-search-criteria"><i class="icon"></i><span><?php echo $LanguageInstance->get('search_criteria')?></span></a>
							<div id="applied-criteria"><ul></ul></div>
						</div>
						
						<form action="#" method="post" class="" id="form_action">
						
						<div id="search-criteria">

							<fieldset>
								<label for="select_user" class="search-lbl"><?php echo $LanguageInstance->get('user')?>:</label>
							<?php 
								if ($users_course && count($users_course)>0) {
								?>
								<select name="user_selected" id="user_selected" tabindex="1" data-title="<?php echo $LanguageInstance->get('user')?>">
									<option value="0"><?php echo $LanguageInstance->get('select_users')?></option>
									<?php foreach ($users_course as $user) {?>
									<option value="<?php echo $user['id']?>" <?php echo ($user_selected==$user['id']?'selected':'')?>><?php echo $user['surname'].', '.$user['firstname']?></option>
									<?php }?>
								</select>
							<?php 
								} else {
							?>
								<input type="text" name="not_users" value="<?php echo $LanguageInstance->get('no_users_in_course'); ?>" tabindex="1" disabled="disabled" />
							<?php } ?>
							</fieldset>

							<?php if (count($array_exercises)>0) {?>
							<fieldset>
								<label for="select_exercise" class="search-lbl"><?php echo $LanguageInstance->get('exercise')?></label>
								<select id="select_exercise" name="select_exercise" tabindex="2" data-title="<?php echo $LanguageInstance->get('exercise')?>">
									<option value="-1"><?php echo $LanguageInstance->get('select_exercise')?></option>
									<?php foreach ($array_exercises as $exercise) {?>
										<option value="<?php echo $exercise['name_xml_file']?>" <?php echo $selected_exercise==$exercise['name_xml_file']?'selected="selected"':''?>><?php echo $exercise['name']?></option>
									<?php }?>
								</select>	
							</fieldset>
							<?php }?>

							<fieldset class="date-range-fs">
									<label for="date-range" class="search-lbl"><?php echo $LanguageInstance->get('date_range')?>:</label>
									<div class="date-range-fields">
										<div class="calendar-field">
											<input type="text" id="start_date" name="start_date" value="<?php echo $start_date;?>" class="input_date ui-state-default ui-widget ui-widget-content ui-corner-left ui-corner-right" placeholder="<?php echo $LanguageInstance->get('from')?>" data-title="<?php echo $LanguageInstance->get('from')?>"/>
										</div>
										<div class="calendar-field">
											<input type="text" id="finish_date" name="finish_date" value="<?php echo $finish_date;?>"  class="input_date ui-state-default ui-widget ui-widget-content ui-corner-left ui-corner-right" placeholder="<?php echo $LanguageInstance->get('to')?>" data-title="<?php echo $LanguageInstance->get('to')?>" />
										</div>
									</div>
							</fieldset>
							<fieldset class="status-fs">
									<label for="finished" class="search-lbl"><?php echo $LanguageInstance->get('status')?>:</label>
									<select id="finished" name="finished" tabindex="1" class="input_date" data-title="<?php echo $LanguageInstance->get('status')?>">
										<option value="-1"><?php echo $LanguageInstance->get('all')?></option>
										<option value="0" <?php echo $finished==0?'selected="selected"':''?>><?php echo $LanguageInstance->get('incomplete')?></option>
										<option value="1" <?php echo $finished==1?'selected="selected"':''?>><?php echo $LanguageInstance->get('complete')?></option>
										<option value="2" <?php echo $finished==2?'selected="selected"':''?>><?php echo $LanguageInstance->get('error')?></option>
									</select>
							</fieldset>

							<fieldset class="search-fs">
								<input type="submit" name="showTandem" value="<?php echo $LanguageInstance->get('search')?>" onclick="Javascript:cleanForm()" />
							</fieldset>

						</div>
						<div class="manage-area">
						<?php 
							if ($is_showTandem) { ?>
								<h3 class="secundary-title"><?php echo $LanguageInstance->get('Tandems')?></h3>
								<?
								if ($user_selected==0 && ($user_tandems==null || count($user_tandems)==0)) {?>
									<div class="message">
										<p><strong><?php echo $LanguageInstance->get('no_results_found')?></strong></p>
									</div>
								<?php 
								} else {
									if ($user_tandems==null || count($user_tandems)==0) {
									?>
										<div class="message">
											<p><strong><?php echo $LanguageInstance->get('no_tandems')?></strong></p>
										</div>
									<?php 	
									} else { 

										?>
										<table id="statistics1" class="table">
										<thead>
											<tr>
												<th style="width:15%"><a href="Javascript:order(0, <?php echo ($order_by_tandems==0)?($order_by_tandems_direction==0?1:0):0 ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('date')?></a><?php if ($order_by_tandems==0){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tandems_direction==0?'n':'s'?> right"></span><?php } ?></th>
												<th><a href="Javascript:order(1, <?php echo ($order_by_tandems==1)?($order_by_tandems_direction==0?1:0):0 ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('exercise')?></a><?php if ($order_by_tandems==1){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tandems_direction==0?'n':'s'?> right"></span><?php } ?></th>
												<?php /*
												<th><a href="Javascript:order(3, <?php echo ($order_by_tandems==3)?($order_by_tandems_direction==0?1:0):0 ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('user')?></a><?php if ($order_by_tandems==3){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tandems_direction==0?'n':'s'?> right"></span><?php } ?></th>
												*/ ?>
												<th><a href="Javascript:order(4, <?php echo ($order_by_tandems==4)?($order_by_tandems_direction==0?1:0):0 ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('user_host')?></a><?php if ($order_by_tandems==4){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tandems_direction==0?'n':'s'?> right"></span><?php } ?></th>
												<th><a href="Javascript:order(5, <?php echo ($order_by_tandems==5)?($order_by_tandems_direction==0?1:0):0 ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('user_guest')?></a><?php if ($order_by_tandems==5){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tandems_direction==0?'n':'s'?> right"></span><?php } ?></th>
												<?php /*<th><a href="Javascript:order(6, <?php echo ($order_by_tandems==6)?($order_by_tandems_direction==0?1:0):0 ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('date_guest_user_logged')?></a><?php if ($order_by_tandems==6){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tandems_direction==0?'n':'s'?> right"></span><?php } ?></th>
												<th><a href="Javascript:order(8, <?php echo ($order_by_tandems==8)?($order_by_tandems_direction==0?1:0):0 ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('user_agent_host')?></a><?php if ($order_by_tandems==6){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tandems_direction==0?'n':'s'?> right"></span><?php } ?></th>
												<th><a href="Javascript:order(9, <?php echo ($order_by_tandems==9)?($order_by_tandems_direction==0?1:0):0 ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('user_agent_guest')?></a><?php if ($order_by_tandems==7){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tandems_direction==0?'n':'s'?> right"></span><?php } ?></th>
												*/ ?>
												<th style="width:10%;"><a href="Javascript:order(7, <?php echo ($order_by_tandems==7)?($order_by_tandems_direction==0?1:0):0 ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('status')?></a><?php if ($order_by_tandems==7){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tandems_direction==0?'n':'s'?> right"></span><?php } ?></th>
												<th  style="width:22%;"><a href="Javascript:order(2, <?php echo ($order_by_tandems==2)?($order_by_tandems_direction==0?1:0):0 ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('duration')?></a><?php if ($order_by_tandems==2){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tandems_direction==0?'n':'s'?> right"></span><?php } ?></th>
												<th class="center" style="width:12%;"><?php echo $LanguageInstance->get('browser_info')?></th>
											</tr>
										</thead>
										<tbody>
										<?php 
										foreach ($user_tandems as $tandem) {
											$seconds = isset($tandem['total_time']) ? $tandem['total_time']:0;
											$minutes = minutes($seconds);
											$total_time = time_format($seconds);
										?>
											<tr>
												<td><a href="Javascript:search(<?php echo $tandem['id']?>,-1,-1)" title="<?php echo $LanguageInstance->get('go')?>" target="_blank"><?php echo $tandem['created']?></a></td>
												<td><?php echo $tandem['exercise']?></td>
												<!--
												<td title="<?php echo $LanguageInstance->getTagDouble('total_time_seconds', $minutes,$seconds)?>"><?php echo $total_time?></td>
												<td><?php echo $tandem['fullname']?></td>
												-->
												<td><?php echo $tandem['user_host']?> <?php /*if ($tandem['has_xml_description']==1) {?><input type="button" onclick="Javascript:play_tandem(<?php echo $tandem['id'] ?>, 1)" value="<?php echo $LanguageInstance->get('play');?>" title="<?php echo $LanguageInstance->get('play_as_user_host');?>" ><?php }*/ ?></td>
												<td><?php echo $tandem['user_guest']?> <?php /*if ($tandem['has_xml_description']==1) {?><input type="button" onclick="Javascript:play_tandem(<?php echo $tandem['id'] ?>, 0)" value="<?php echo $LanguageInstance->get('play');?>" title="<?php echo $LanguageInstance->get('play_as_user_guest');?>" ><?php }*/ ?></td>
												<!--<td><?php echo $tandem['date_guest_user_logged']?></td>-->
												<td><?php echo get_tandem_status($tandem['status']);?></td>
												<!--<td><?php echo $tandem['user_agent_host']?></td>
												<td><?php echo $tandem['user_agent_guest']?></td>-->
												<td title="<?php echo $LanguageInstance->getTagDouble('total_time_seconds', $minutes,$seconds)?>">
													<span class="duration">
														<span class="lbl"><?php echo $LanguageInstance->get('total'); ?></span>
														<span class="val"><?php echo $total_time?> <a href="#duration_<?php echo $tandem['id']?>" class="expand"  title="<?php echo $LanguageInstance->get('expand')?>"></a></span>
													</span>
													<table id="duration_<?php echo $tandem['id']?>" class="subtable duration-details">
														<thead>
															<tr>
																<th>T1</th>
																<th>T2</th>
																<th>T3</th>
																<th>T4</th>
																<th>T5</th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td>00:00</td>
																<td>00:00</td>
																<td>00:00</td>
																<td>00:00</td>
																<td>00:00</td>
															</tr>
														</tbody>
													</table>
												</td>
												<td class="center">
													<a href="#bi_<?php echo $tandem['id']?>" class="modal_bi">Ver</a>
													<div id="bi_<?php echo $tandem['id']?>" style="display:none">
														<h3><?php echo $LanguageInstance->get('user_agent_host')?></h3>
														<p><?php echo $tandem['user_agent_host']?></p>
														<h3><?php echo $LanguageInstance->get('user_agent_guest')?></h3>
														<p><?php echo $tandem['user_agent_guest']?></p>
													</div>
												</td>
											</tr>
										<?php } ?>
										</tbody>
										</table>
							<?php   }	
								}
									
									
								if ($task_tandems != null) {
									?>
									<h3 class="secundary-title"><?php echo $LanguageInstance->get('tasks_tandems')?></h3>
									<table id="statistics2" class="table">
									<thead>
										<tr>
											<th style="width:15%"><a href="Javascript:order(<?php echo $order_by_tandems; ?>, <?php echo $order_by_tandems_direction; ?>, 0, <?php echo ($order_by_tasks==0)?($order_by_tasks_direction==0?1:0):0 ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('date')?></a><?php if ($order_by_tasks==0){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tasks_direction==0?'n':'s'?> right"></span><?php } ?></th>
											<th><a href="Javascript:order(<?php echo $order_by_tandems; ?>, <?php echo $order_by_tandems_direction; ?>, 1, <?php echo ($order_by_tasks==1)?($order_by_tasks_direction==0?1:0):0 ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('exercise')?></a><?php if ($order_by_tasks==1){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tasks_direction==0?'n':'s'?> right"></span><?php } ?></th>
											<th><a href="Javascript:order(<?php echo $order_by_tandems; ?>, <?php echo $order_by_tandems_direction; ?>, 2, <?php echo ($order_by_tasks==2)?($order_by_tasks_direction==0?1:0):0 ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('task')?></a><?php if ($order_by_tasks==2){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tasks_direction==0?'n':'s'?> right"></span><?php } ?></th>
											<th><a href="Javascript:order(<?php echo $order_by_tandems; ?>, <?php echo $order_by_tandems_direction; ?>, 3, <?php echo ($order_by_tasks==3)?($order_by_tasks_direction==0?1:0):0 ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('total_time')?></a><?php if ($order_by_tasks==3){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tasks_direction==0?'n':'s'?> right"></span><?php } ?></th>
											<th><a href="Javascript:order(<?php echo $order_by_tandems; ?>, <?php echo $order_by_tandems_direction; ?>, 4, <?php echo ($order_by_tasks==4)?($order_by_tasks_direction==0?1:0):0 ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('user')?></a><?php if ($order_by_tasks==4){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tasks_direction==0?'n':'s'?> right"></span><?php } ?></th>
											<?php /*
											<th><a href="Javascript:order(<?php echo $order_by_tandems; ?>, <?php echo $order_by_tandems_direction; ?>, 5, <?php echo ($order_by_tasks==5)?($order_by_tasks_direction==0?1:0):0 ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('date_guest_user_logged')?></a><?php if ($order_by_tasks==5){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tasks_direction==0?'n':'s'?> right"></span><?php } ?></th>
											*/ ?>
											<th><a href="Javascript:order(<?php echo $order_by_tandems; ?>, <?php echo $order_by_tandems_direction; ?>, 6, <?php echo ($order_by_tasks==6)?($order_by_tasks_direction==0?1:0):0 ?>, <?php echo $order_by_questions;?>, <?php echo $order_by_questions_direction;?>);"><?php echo $LanguageInstance->get('finalized')?></a><?php if ($order_by_tasks==6){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_tasks_direction==0?'n':'s'?> right"></span><?php } ?></th>
										</tr>
									</thead>
									<tbody>
									<?php 
									foreach ($task_tandems as $task) {
										$seconds = isset($task['total_time'])?$task['total_time']:0;
										$minutes = minutes($seconds);
										$total_time = time_format($seconds);
										
									?>
										<tr>
											<td><a href="Javascript:search(<?php echo $task['id_tandem']?>,<?php echo $task['task_number']?>,-1)" title="<?php echo $LanguageInstance->get('go')?>" target="_blank"><?php echo $task['created']?></a></td>
											<td><?php echo $task['exercise']?></td>
											<td><?php echo $task['task_number']?></td>
											<td title="<?php echo $LanguageInstance->getTagDouble('total_time_seconds', $minutes, $seconds)?>"><?php echo $total_time?></td>
											<td ><?php echo $task['user']?></td>
											<?php /*<td><?php echo $task['date_guest_user_logged']?></td>*/ ?>
											<td><?php echo $task['finalized']?></td>
										</tr>
									<?php }?>
									</tbody>
									</table>
									<?php 
								}
								
								
								if ($question_task_tandems != null) {
									?>
									<h3 class="secundary-title"><?php echo $LanguageInstance->get('questions_tasks_tandems')?></h3>
									<table id="statistics3" class="table">
									<thead>
										<tr>
											<th style="width:15%"><a href="Javascript:order(<?php echo $order_by_tandems; ?>, <?php echo $order_by_tandems_direction; ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>, 0, <?php echo ($order_by_questions==0)?($order_by_questions_direction==0?1:0):0 ?>);"><?php echo $LanguageInstance->get('date')?></a><?php if ($order_by_questions==0){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_questions_direction==0?'n':'s'?> right"></span><?php } ?></th>
											<th><a href="Javascript:order(<?php echo $order_by_tandems; ?>, <?php echo $order_by_tandems_direction; ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>,1, <?php echo ($order_by_questions==1)?($order_by_questions_direction==0?1:0):0 ?>);"><?php echo $LanguageInstance->get('exercise')?></a><?php if ($order_by_questions==1){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_questions_direction==0?'n':'s'?> right"></span><?php } ?></th>
											<th><a href="Javascript:order(<?php echo $order_by_tandems; ?>, <?php echo $order_by_tandems_direction; ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>,2, <?php echo ($order_by_questions==2)?($order_by_questions_direction==0?1:0):0 ?>);"><?php echo $LanguageInstance->get('task')?></a><?php if ($order_by_questions==2){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_questions_direction==0?'n':'s'?> right"></span><?php } ?></th>
											<th><a href="Javascript:order(<?php echo $order_by_tandems; ?>, <?php echo $order_by_tandems_direction; ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>,3, <?php echo ($order_by_questions==3)?($order_by_questions_direction==0?1:0):0 ?>);"><?php echo $LanguageInstance->get('question')?></a><?php if ($order_by_questions==3){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_questions_direction==0?'n':'s'?> right"></span><?php } ?></th>
											<th><a href="Javascript:order(<?php echo $order_by_tandems; ?>, <?php echo $order_by_tandems_direction; ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>,4, <?php echo ($order_by_questions==4)?($order_by_questions_direction==0?1:0):0 ?>);"><?php echo $LanguageInstance->get('total_time')?></a><?php if ($order_by_questions==4){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_questions_direction==0?'n':'s'?> right"></span><?php } ?></th>
											<th><a href="Javascript:order(<?php echo $order_by_tandems; ?>, <?php echo $order_by_tandems_direction; ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>,5, <?php echo ($order_by_questions==5)?($order_by_questions_direction==0?1:0):0 ?>);"><?php echo $LanguageInstance->get('user')?></a><?php if ($order_by_questions==5){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_questions_direction==0?'n':'s'?> right"></span><?php } ?></th>
											<!-- th><a href="Javascript:order(<?php echo $order_by_tandems; ?>, <?php echo $order_by_tandems_direction; ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>,6, <?php echo ($order_by_questions==6)?($order_by_questions_direction==0?1:0):0 ?>);"><?php echo $LanguageInstance->get('date_guest_user_logged')?></a><?php if ($order_by_questions==6){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_questions_direction==0?'n':'s'?> right"></span><?php } ?></th> -->
											<th><a href="Javascript:order(<?php echo $order_by_tandems; ?>, <?php echo $order_by_tandems_direction; ?>, <?php echo $order_by_tasks; ?>, <?php echo $order_by_tasks_direction; ?>,7, <?php echo ($order_by_questions==7)?($order_by_questions_direction==0?1:0):0 ?>);"><?php echo $LanguageInstance->get('finalized')?></a><?php if ($order_by_questions==7){?><span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-<?php echo $order_by_questions_direction==0?'n':'s'?> right"></span><?php } ?></th>
										</tr>
									</thead>
									<tbody>
									<?php 
									foreach ($question_task_tandems as $question) {
										$seconds = isset($question['total_time'])?$question['total_time']:0;
										$minutes = minutes($seconds);
										$total_time = time_format($seconds);
									?>
										<tr>
											<td><a href="Javascript:search(<?php echo $question['id_tandem']?>,<?php echo $question['task_number']?>,<?php echo $question['question_number']?>)" title="<?php echo $LanguageInstance->get('go')?>" target="_blank"><?php echo $question['created']?></a></td>
											<td><?php echo $question['exercise']?></td>
											<td><?php echo $question['task_number']?></td>
											<td><?php echo $question['question_number']?></td>
											<td title="<?php echo $LanguageInstance->getTagDouble('total_time_seconds', $minutes, $seconds)?>"><?php echo $total_time?></td>
											<td><?php echo $question['user']?></td>
											<!-- td><?php echo $question['date_guest_user_logged']?></td> -->
											<td><?php echo $question['finalized']?></td>
										</tr>
									<?php }?>
									</tbody>
									</table>
									<?php 
								}
	
							}?>
						
							<input type="hidden" name="id_tandem" id="form_id_tandem" value="<?php echo $id_tandem?>" />
							<input type="hidden" name="id_task" id="form_id_task" value="<?php echo $id_task?>" />
							<input type="hidden" name="id_question" id="form_id_question" value="<?php echo $id_task?>" />
							<input type="hidden" name="showTandemReload" id="showTandemReload" value="0" />
							<input type="hidden" name="order_by_tandems" id="form_order_by_tandems" value="<?php echo $order_by_tandems?>" />
							<input type="hidden" name="order_by_tandems_direction" id="form_order_by_tandems_direction" value="<?php echo $order_by_tandems_direction?>" />
							<input type="hidden" name="order_by_tasks" id="form_order_by_tasks" value="<?php echo $order_by_tasks?>" />
							<input type="hidden" name="order_by_tasks_direction" id="form_order_by_tasks_direction" value="<?php echo $order_by_tasks_direction?>" />
							<input type="hidden" name="order_by_questions" id="form_order_by_questions" value="<?php echo $order_by_questions?>" />
							<input type="hidden" name="order_by_questions_direction" id="form_order_by_questions_direction" value="<?php echo $order_by_questions_direction?>" />
							<input type="hidden" name="start_date" id="form_start_date" value="<?php echo $start_date?>" />
							<input type="hidden" name="finish_date" id="form_finish_date" value="<?php echo $finish_date?>" />
							<input type="hidden" name="finished" id="form_finished" value="<?php echo $finished?>" />
						</form>
					</div><!-- /manage-area -->
					</div>
				</div>
				<!-- /content -->
			</div>
			<!-- /main -->
		</div>
		<!-- /main-container -->
	</div>
	<!-- /wrapper -->
	<!-- footer -->
	<!-- div id="footer-container">
		<div id="footer">
			<div class="footer-tandem" title="<?php echo $LanguageInstance->get('tandem')?>"></div>
			<div class="footer-logos">
				<img src="css/images/logo_LLP.png" alt="Lifelong Learning Programme" />
				<img src="css/images/logo_EAC.png" alt="Education, Audiovisual &amp; Culture" />
				<img src="css/images/logo_speakapps.png" alt="Speakapps" />
			</div>
		</div>
	</div -->>
	<!-- /footer -->
</body>
</html>
<?php } ?>
