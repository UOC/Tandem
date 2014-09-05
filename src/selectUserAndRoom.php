<?php 
require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/constants.php';
require_once dirname(__FILE__).'/classes/gestorBD.php';
require_once dirname(__FILE__).'/classes/utils.php';

require_once 'IMSBasicLTI/uoc-blti/lti_utils.php';

$user_obj = isset($_SESSION[CURRENT_USER])?$_SESSION[CURRENT_USER]:false;

$course_id = isset($_SESSION[COURSE_ID])?$_SESSION[COURSE_ID]:false;

require_once dirname(__FILE__).'/classes/IntegrationTandemBLTI.php';


if (!$user_obj || !$course_id) {
	//Tornem a l'index
	header ('Location: index.php');
} else {
	require_once(dirname(__FILE__).'/classes/constants.php');
	$path = '';
	if (isset($_SESSION[TANDEM_COURSE_FOLDER])) $path = $_SESSION[TANDEM_COURSE_FOLDER].'/';
	
	$id_resource_lti = $_SESSION[ID_RESOURCE];
	
	$lti_context = unserialize($_SESSION[LTI_CONTEXT]);
	
	$gestorBD	= new GestorBD();
	$users_course = $gestorBD->obte_llistat_usuaris($course_id, $user_obj->id);
	$is_reload = isset($_POST['reload'])?$_POST['reload']!=null:false;
	
	if ($is_reload || !$users_course || count($users_course)==0) {
		if ($lti_context->hasMembershipsService()) { //Carreguem per LTI
			//Convertir el llistat d'usuaris a un array de 
			//person_name_given
			//person_name_family
			//person_name_full
			//person_contact_email_primary
			//roles: separats per comes
			//lis_result_sourcedid
                    	$users_course_lti = $lti_context->doMembershipsService(array()); //$users_course no ho passem per evitar problemes ja que el continguts son array i no un obj LTI
			$users_course = array();
			foreach ($users_course_lti as $user_lti) {
				$id_user_lti = $user_lti->getId(  );
                                $id_user_lti = str_replace(":", "_", $id_user_lti);
                                
				$firstname = convertToUtf8($user_lti->firstname);
				$lastname = convertToUtf8($user_lti->lastname);
				$fullname = convertToUtf8($user_lti->fullname);
				$email = convertToUtf8($user_lti->email);
									
				$gestorBD->afegeixUsuari($course_id, $id_user_lti, $firstname, $lastname, $fullname, $email, '');
				//$users_course[$id_user_lti] = $gestorBD->get_user_by_username($id_user_lti);
			}
			//Reorder
			$users_course = $gestorBD->obte_llistat_usuaris($course_id, $user_obj->id);
		} else { //Mirem de carregar per OKI
			$okibusPHP_components = $_SESSION[OKIBUSPHP_COMPONENTS];
			$okibusPHP_okibusClient = $_SESSION[OKIBUSPHP_OKIBUSCLIENT];
			putenv(OKIBUSPHP_COMPONENTS.'='.$okibusPHP_components);
			putenv(OKIBUSPHP_OKIBUSCLIENT.'='.$okibusPHP_okibusClient);
			//Pel require d'autehtnication ja carrega les propietats
			require_once dirname(__FILE__).'/classes/gestorOKI.php';
			$gestorOKI	= new GestorOKI();
			$users_course = $gestorOKI->obte_llistat_usuaris($gestorBD, $course_id);
			
		}
	}
	$is_showTandem = isset($_POST['showTandem'])?$_POST['showTandem']!=null:false;
	$user_tandems = null;
	$user_selected = isset($_POST['user_selected'])?intval($_POST['user_selected']):0;
	if ($is_showTandem && $user_selected) {
		$exercise  = isset($_POST['room'])?intval($_POST['room'],10):false;
		$user_tandems = $gestorBD->obte_llistat_tandems($course_id, $user_selected, $exercise);
	}
	
	
	
	
	//Permetem que seleccini l'exercici 20111110
	$is_host = $user_obj->is_host;
	
	$array_exercises = $gestorBD->get_tandem_exercises($course_id);
	$tandemBLTI = new IntegrationTandemBLTI();
	$selected_exercise = $tandemBLTI->checkXML2GetExercise($user_obj);
	$selected_exercise_select = isset($_POST['room'])?$_POST['room']:'';

	$pending_invitations = $gestorBD->get_invited_to_join($user_obj->id, $id_resource_lti, $course_id, true);
	$last_id = $gestorBD->get_lastid_invited_to_join($user_obj->id, $id_resource_lti, $course_id);

	//Agafem les dades de l'usuari
	$name = mb_convert_encoding($user_obj->name, 'UTF-8', 'UTF-8');
	
?>
<!DOCTYPE html>
<html>
<head>
<title>Tandem</title>
<meta charset="UTF-8" />
<link rel="stylesheet" type="text/css" media="all" href="css/tandem.css" />
<link rel="stylesheet" type="text/css" media="all" href="css/defaultInit.css" />
<link rel="stylesheet" type="text/css" media="all" href="css/jquery-ui.css" />
<!-- 10082012: nfinney> ADDED COLORBOX CSS LINK -->
<link rel="stylesheet" type="text/css" media="all" href="css/colorbox.css" />
<!-- END -->
<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/jquery.ui.core.js"></script>
<script src="js/jquery.ui.widget.js"></script>
<script src="js/jquery.ui.button.js"></script>
<script src="js/jquery.ui.position.js"></script>
<script src="js/jquery.ui.autocomplete.js"></script>
<script src="js/jquery.colorbox-min.js"></script>

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
				.addClass( "ui-widget ui-widget-content" );

			 // MODIFICATION - bind to the input's focus
	        input.focus(function(event) {
	        	event.preventDefault();
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
				.addClass( " ui-button-icon" )
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

		$('#showNewsS').slideUp();
		
//IE DETECTION - ERROR MSG TO USER
		var isIE11 = !!navigator.userAgent.match(/Trident\/7\./); //check compatibility with iE11 (user agent has changed within this version)
		var isie8PlusF = (function(){var undef,v = 3,div = document.createElement('div'),all = div.getElementsByTagName('i');while(div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->',all[0]);return v > 4 ? v : undef;}());if(isie8PlusF>=8) isie8Plus=true;else isie8Plus=false;
		if(isIE11 || isie8Plus) isIEOk=true; else isIEOk=false;


		if (isie8PlusF<8) $.colorbox({href:"warningIE.html",escKey:true,overlayClose:false, width:400, height:350,onLoad:function(){$('#cboxClose').hide();}});
		// END

//xml request for iexploiter11+/others 		
		if (isIEOk || window.ActiveXObject) xmlReq = new ActiveXObject("Microsoft.XMLHTTP");
		else xmlReq = new XMLHttpRequest();
//global vars - will be extracted from dataROOM.xml
		var classOf="";
		var numBtns="";
		var numUsers="";
		var nextSample="";
		var node="";
		var room="";
		var data="";
		var TimerSUAR = 2000;
		var txtNews="";

		notifyTimerDown = function(id){
			if($.trim(txtNews)!=$.trim(id)){
				$('#showNewsS').html(id);
				$('#showNewsS').css("top",-20).fadeIn(1000).slideDown("fast");
				$('#showNewsS').css("top",-20).fadeIn(1000).slideDown("fast");
				$("#showNewsS").css("top",-50).delay(3000).fadeOut(1000).slideUp("fast");
				txtNews=id;
			}
		}

		enable_exercise= function(value){
			value=parseInt(value, 10);
			if (value!='' && value>0) {
				$('#room').removeAttr('disabled');
				$( "#room" ).combobox();
			}
			else { 
				$( "#room" ).destroy();
				$('#room').attr('disabled', 'disabled');
			}
		}
		enable_button= function(value){
			if (value!='')
				$('#start').removeAttr('disabled');
			else 
				//$('#start').addClass('disabled');
				$('#start').hide();
		}
		
		putLink = function(){
			getXML();
			return false;
		}
//opens next exercise	
		openLink = function(value,nextExer){
			top.document.location.href=classOf+".php?room="+value+"&user=a&nextSample="+nextSample+'&node='+node+'&data='+data;
		}
		getXMLRoom = function(room){
			var room_2 = room.split("-");
			room = room.split("_");
			data = room[0].split("speakApps");
			data = data[0].split("-");

			data = data[0];//.toUpperCase();
			var extra_path = '';
			if (data.indexOf("/")>=0) {
				data = data.split("/");
				data.indexOf("/");
				extra_path = data[0]+"/";
				data = data[1];
			}

			room = room_2[1];
			var url= "<?php echo $path; ?>"+extra_path+"data"+data+".xml";
			xmlReq.onreadystatechange = processXml;
			if(!isIEOk){
					xmlReq.timeout = 100000;
					xmlReq.overrideMimeType("text/xml");
			}
			xmlReq.open("GET", url, true);
			xmlReq.send(null);
		}
		printError  = function(error){
			$('#roomStatus').val(error);
		}
		
//get dataROOM.xml params
		getXML = function(){
			user_selected = $('#user_selected').val();
			room_temp = $('#room').val();
			if (user_selected=="" || user_selected=="-1") {
				alert("<?php echo $LanguageInstance->get('select_user')?>");
			} else {
				if (room_temp=="" || room_temp=="-1") {
					alert("<?php echo $LanguageInstance->get('select_exercise')?>");
				} else {
					enable_button('');
					getXMLRoom(room_temp);
				}
			}
		}
		processXml = function(){
			if((xmlReq.readyState	==	4) && (xmlReq.status == 200)){
				//extract data
				if (xmlReq.responseXML!=null) {
					var cad=xmlReq.responseXML.getElementsByTagName('nextType');
					classOf=cad[0].getAttribute("classOf");
					numBtns=cad[0].getAttribute("numBtns");
					numUsers=cad[0].getAttribute("numUsers");
					nextSample=cad[0].getAttribute("currSample");
					node=parseInt(cad[0].getAttribute("node"))+1;
					user_selected=$('#user_selected').val();
					document.getElementById('roomStatus').innerHTML="";
	$('#idfrm').attr('src','checkRoomUserTandem.php?id_user_guest='+user_selected+'&nextSample='+nextSample+'&node='+node+'&classOf='+classOf+'&data='+data);
				}else{
					$('#roomStatus').html('Error loading exercise '+data+' contact with the administrators');
				}
			} else if ((xmlReq.readyState	==	4) && (xmlReq.status == 404)) {
				$('#roomStatus').html('Exercise '+data+' does not exist');
			}
		}
		createRoom = function(room, data, nextSample, node, classOf){
				//extract data
				var cad=xmlReq.responseXML.getElementsByTagName('nextType');
				classOf=cad[0].getAttribute("classOf");
				numBtns=cad[0].getAttribute("numBtns");
				numUsers=cad[0].getAttribute("numUsers");
				nextSample=cad[0].getAttribute("currSample");
				node=parseInt(cad[0].getAttribute("node"))+1;
				user_selected=$('#user_selected').val();
				document.getElementById('roomStatus').innerHTML="";
				$('#idfrm').attr('src','checkRoomUserTandem.php?id_user_guest='+user_selected+'&create_room=1&nextSample='+nextSample+'&node='+node+'&classOf='+classOf+'&data='+data);
				//TODO in Ajax
		}
		printError = function (error) {
			alert(error);
		}
		$( "#user_selected" ).combobox();
		$( "#room" ).combobox();
		enable_button();
		<?php /*if (!$is_host) {?>
		$.colorbox.close = function(){}; 
		$.colorbox({href:"waiting4user.php",escKey:false,overlayClose:false,width:300,height:180});
		var interval = null;
		checkExercise = function() {
			if((xmlReq.readyState	==	4) && (xmlReq.status == 200)){
				//extract data
				var cad=xmlReq.responseXML.getElementsByTagName('usuarios');
				var exercise=cad[0].getAttribute("exercise")+"-";
				if (exercise.length>0) {
					$.colorbox.close();
					if (interval!=null)
						clearInterval(interval);
					getXMLRoom(exercise);
			}
		 } 
		}

		<?php if (isset($user_obj->custom_room)) {?>
		interval = setInterval(function(){
				var url="check.php?room=<?php echo $user_obj->custom_room?>";
				xmlReq.onreadystatechange = checkExercise;
				if(!isIEOk){
					xmlReq.timeout = 100000;
					xmlReq.overrideMimeType("text/xml");
				}
				xmlReq.open("GET", url, true);
				xmlReq.send(null);
			 },2000);
		 <?php } ?>
		//interval2 = setInterval("fes",1500);
		//checkExercise_interval();
		//interval = setInterval("checkExercise_interval",3000);
		<?php } */?>



		intervalCheck = setInterval(function(){
			$.ajax({
				  type: 'GET',
				  url: "new_tandems.php",
				  data: {
					  id: <?php echo $last_id; ?>
				  },
				  dataType: "xml",
				  success: function(xml){
				  	var id_txt = $(xml).find('id').text();
				  	if (id_txt &&  id_txt.length>0) {
						var created_txt = $(xml).find('created').text();
						var nameuser_txt = $(xml).find('nameuser').text();
						var exercise_txt = $(xml).find('exercise').text();
						$("#info-block").show();
						$("#info-block").append("<div class='alert-inside'><i class='icon'></i><h3><?php echo $LanguageInstance->get('just_been_invited');?> <em>"+nameuser_txt+"</em> <?php echo $LanguageInstance->get('exercise');?>: <em>"+exercise_txt+"</em> </h3><a id='startNowBtn' href=\"accessTandem.php?id="+id_txt+"\" class='tandem-btn'><?php echo $LanguageInstance->get('accept');?></a></div>");
						setExpiredNow(60);
						clearInterval(intervalCheck);
				  	}
				  },
				  error: function(){
				  		clearInterval(intervalCheck);
						TimerSUAR+=500;
						intervalCheck = setInterval(intervalCheck,TimerSUAR);
						notifyTimerDown('<?php echo $LanguageInstance->get('SlowConn')?>');
				  }
			});
		},TimerSUAR);			
		<?php if ($selected_exercise && strlen($selected_exercise)>0){ echo 'getXML();'; }?>


		canviaAction = function(show) {
			$('#main_form').attr('action', '');
			$('#main_form').attr('target', '');
			if (show=='show') {
				$('#main_form').attr('action', 'statistics_tandem.php');
				//$('#main_form').attr('target', 'statistics<?php echo rand();?>');
			} else {
				if (show=='exercises') {
					$('#main_form').attr('action', 'manage_exercises_tandem.php');
					//cmoyas
					//$('#main_form').attr('target', 'exercises<?php echo rand();?>');
				}
			}
		}
			
	});

	var intTimerNow;
	var isNowOn=0;
	function setExpiredNow(itNow){
		isNowOn=1;
		intTimerNow = setTimeout("getTimeNow("+itNow+");", 1000);
	}
	function getTimeNow(itNow){
		var tNow;
		itNow--;
		if(itNow<10) tNow ="0"+itNow;
		else tNow = itNow;
			$("#startNowBtn").html("<?php echo $LanguageInstance->get('accept')?> 00:"+tNow);
			if(itNow<=1){ 
				$("#startNowBtn").removeClass("tandem-btn").addClass("tandem-btnout");
				$("#startNowBtn").html("<?php echo $LanguageInstance->get('caducado')?>");
				$("#startNowBtn").attr("href", "#");
				window.location.href=window.location.href;
				clearInterval(intTimerNow);
			}
			else setExpiredNow(itNow);
	}
</script>
</head>
<body>

<!-- accessibility -->
	<div id="accessibility">
		<a href="#content" accesskey="s" title="Acceso directo al contenido"><?php echo $LanguageInstance->get('direct_access_to_content')?></a> | 
		<!--
		<a href="#" accesskey="n" title="Acceso directo al men de navegacin">Acceso directo al men de navegacin</a> | 
		<a href="#" accesskey="m" title="Mapa del sitio">Mapa del sitio</a> 
		-->
	</div>
	<!-- /accessibility -->
	
	<!-- /wrapper -->
	<div id="wrapper">

		<div id="head-containerS">
			<div id="headerS">
            	<div id="logoS">
            		<div id="showNewsS"></div>
            	</div>
       		</div>
        </div>
		<!-- main-container -->
  		<div id="main-container">
  			<!-- main -->
			<div id="main">
				<!-- content -->
				<div id="content">
					<span class="welcome"><?php echo $LanguageInstance->get('welcome')?> <?php echo $name ?>!</span><br/>
					<form action="#" method="post" id="main_form" class="login_form">
						<?php if ($array_exercises!==false && is_array($array_exercises) && count($array_exercises)>0) {?>
								<fieldset>
								<?php 
									if ($users_course && count($users_course)>0) {
									?>
									<label for="select_user" title="1. <?php echo $LanguageInstance->get('select_users')?>"><img class="point" src="css/images/p1.png" alt="1. <?php echo $LanguageInstance->get('select_users')?>" /></label>
									<br/><select name="user_selected" id="user_selected" tabindex="1"  onchange="enable_exercise(this.value);">
										<option value="-1"><?php echo $LanguageInstance->get('select_users')?></option>
										<?php foreach ($users_course as $user) {
											if ($user['id']!=$user_obj->id) {?>
											<option value="<?php echo $user['id']?>" <?php echo ($user_selected==$user['id']?'selected':'')?>><?php echo $user['surname'].', '.$user['firstname']?></option>
										<?php }
											}?>
									</select>
								<?php 
								} else {
									$msg = $gestorOKI->getLastError()==null?$LanguageInstance->get('no_users_in_course'):$gestorOKI->getLastError();
							?> 
							<label for="not_users" title="<?php echo $msg?>"><?php echo $msg?></label>
							<?php } ?>
								</fieldset>
								<fieldset>
									<?php if ($array_exercises!==false &&
									 is_array($array_exercises) &&
									 count($array_exercises)>0) {?>
										<label for="select_exercise" title="2. <?php echo $LanguageInstance->get('select_exercise')?>"><img class="point" src="css/images/p2.png" alt="2. <?php echo $LanguageInstance->get('select_exercise')?>" /></label>
										<br/><select id="room" name="room"  tabindex="2" onchange="enable_button(this.value);">
												<option value="-1"><?php echo $LanguageInstance->get('select_exercise')?></option>
											<?php foreach ($array_exercises as $exercise) {
												$extra_exercise = isset($exercise['relative_path']) && strlen($exercise['relative_path'])>0?str_replace("/", "", $exercise['relative_path']).'/':'';
												?>
												<option value="<?php echo $extra_exercise.$exercise['name_xml_file']?>" <?php echo ($selected_exercise_select==$exercise['name_xml_file']||$selected_exercise==$exercise['name_xml_file'])?'selected="selected"':''?>><?php echo $exercise['name']?></option>
											<?php }?>
										</select>	
									<?php } else {?>
										<input type="text" id="room" value="" size="10" onchange="putLink();"/>	
									<?php }?>
								</fieldset>
								<fieldset>
									<label for="start" title="3. <?php echo $LanguageInstance->get('start')?>"><img class="point" src="css/images/p3.png" alt="3. <?php echo $LanguageInstance->get('start')?>" /></label>
									<input type="button" onclick="Javascript:putLink();" id="start" name="start" disabled="disabled" value="<?php echo $LanguageInstance->get('start')?>" class="tandem-btn" tabindex="3" />
								</fieldset>
						<?php } else {
							echo '<div id="alert-top" class="alert alert-warning"><div class="alert-inside"><i class="icon"></i><h3>'.Language::get('no_exercises_found').'</h3></div></div>';
						} ?>
						<div class="manage-area">
						<div class="clear">
							<div id="info-block" class="alert alert-info" style="display:none"></div>
							<!--<div class="info" style="display:none"></div>--> <!-- 10092012 nfinney> type error: changed to 'none' from 'hidden' -->
							<?php if (!$pending_invitations){?>
								<div class="title">
									<h2><?php echo $LanguageInstance->get('pending_tandems')?></h2>
								</div>
								<div class="message">
									<p><strong><?php echo $LanguageInstance->get('no_pending_tandems')?></strong></p>
								</div>
							<?php } else { ?>
								<div class="title">
									<h2><?php echo $LanguageInstance->get('pending_tandems')?></h2>
									<a href="selectUserAndRoom.php" class="tandem-btn-secundary btn-reload"><i class="icon"></i><?php echo $LanguageInstance->get('reload_pending')?></a>
								</div>
								
								<table id="statistics1" class="table">
								<thead>
									<tr>	
										<th style="width:30%"><?php echo $LanguageInstance->get('user_guest')?></th>
										<th style="width:25%"><?php echo $LanguageInstance->get('exercise')?></th>
										<th style="width:25%"><?php echo $LanguageInstance->get('date')?></th>
										<th style="width:20%"><?php echo $LanguageInstance->get('state')?></th>
									</tr>
								</thead>
								<tbody>
								<?php 
								$ai=0;
								foreach ($pending_invitations as $tandem) {
								$ai++;
								?>
									<tr>
										
										<td><?php echo $tandem['surname'].', '.$tandem['firstname']?></td>
										<td><?php echo $tandem['name']?></td>
										<td><?php echo $tandem['created']?></td>
										
										<?php
										$time2Expire=60;
										if( (time() - strtotime($tandem['created']))>=$time2Expire){
										?> 
										<td><a href="#" title="<?php echo $LanguageInstance->get('go')?>" class="tandem-btnout"><?php echo $LanguageInstance->get('caducado')?></a></td>
										<?php }else{ ?>
											<script>
												setExpired(<?php echo $time2Expire;?>);
												var intTimer;
												function setExpired(i){
													intTimer = setTimeout("getTime("+i+");", 1000);
												}
												function getTime(i){
													//var t;
													//i--;
													//if(i<10) t ="0"+i;
													//else t = i;
													for(var iT=0;iT<=<?php echo $ai;?>;iT++){
														//$("#timer2expired"+iT).html("<?php echo $LanguageInstance->get('accept')?> 00:"+t);
														
														//if(i<=1 || isNowOn==1){ 
															$("#timer2expired"+iT).removeClass("tandem-btn").addClass("tandem-btnout");
															$("#timer2expired"+iT).html("<?php echo $LanguageInstance->get('caducado')?>");
															$("#timer2expired"+iT).attr("href", "#")
															clearInterval(intTimer);
														//}
														//else setExpired(i);
													}
												}
											</script>
										<td><a id="timer2expired<?php echo $ai;?>" href="accessTandem.php?id=<?php echo $tandem['id']?>" title="<?php echo $LanguageInstance->get('go')?>" class="tandem-btn"><?php echo $LanguageInstance->get('accept')?></a></td>
										
										<?php
											}
										?>
										
									</tr>
								<?php }?>
								</tbody>
								</table>
						 	<?php } ?>	
						</div>
						<?php /*
						<div class="clear">
						<p><a href="selectUserAndRoom.php"><?php echo $LanguageInstance->get('reload_pending')?></a></p>
						</div> 
						*/ ?>
						<?php if ($user_obj->instructor) { ?>
						<?php /*
						<div class="clear">
						
							if ($is_showTandem) {
								if ($user_selected==0) {?>
									<p class="error"><?php echo $LanguageInstance->get('select_user')?></p>
								<?php 
								} else {
									if ($user_tandems==null || count($user_tandems)==0) {
									?>
										<?php echo $LanguageInstance->get('no_tandems')?>
									<?php 	
									} else { 
										?>
											<div class="title"><?php echo $LanguageInstance->get('tandems')?></div>
											<table>
												<tr>
												<th><?php echo $LanguageInstance->get('date')?></th>
												<th><?php echo $LanguageInstance->get('total_time')?></th>
												<th><?php echo $LanguageInstance->get('user_guest')?></th>
												<th><?php echo $LanguageInstance->get('date_guest_user_logged')?></th>
												<th><?php echo $LanguageInstance->get('finalized')?></th>
												</tr>
											<?php 
											foreach ($user_tandems as $tandem) {
											?>
												<tr>
												<td><a href="statistics_tandem.php?id=<?php echo $tandem['id']?>" title="<?php echo $LanguageInstance->get('go')?>"><?php echo $tandem['created']?></a></td>
													<td><?php echo isset($tandem['total_time'])?$tandem['total_time']:0?></td>
													<td><?php echo $tandem['other_user']?></td>
													<td><?php echo $tandem['date_guest_user_logged']?></td>
													<td><?php echo $tandem['finalized']?></td>
												</tr>
											<?php }?>
											</table>
											<div class="clear" >&nbsp;</div>
							<?php  		}	
									}
							}
						</div>
						*/ ?>
                                                    
						<div class="clear">
							<input type="submit" name="reload" onclick="Javascript:canviaAction('');" value="<?php echo $LanguageInstance->get('refresh')?>" />
							<input type="submit" name="showTandem" onclick="Javascript:canviaAction('show');" value="<?php echo $LanguageInstance->get('activity_log')?>" />
							<input type="submit" name="showTandem" onclick="Javascript:canviaAction('exercises');" value="<?php echo $LanguageInstance->get('mange_exercises_tandem')?>" />
						</div>	
                                                    
						<?php } //is instructor ?>  
						
						<div class="clear">
							<?php /* <p>echo Language::getTag('tandem_description_1','<strong>'.$name.'</strong>') <!--10082012: nfinney> finney> replaced with popup on IE detection--><br/>
							<?php echo $LanguageInstance->get('tandem_description_2');</p> */ ?>
							<p id="roomStatus"></p>
						</div>
						
						</div> <!-- /manage-area -->
					</form>
					<div id="logo">
						<a href="#" title="<?php echo $LanguageInstance->get('tandem_logo')?>"><img src="css/images/logo_Tandem.png" alt="<?php echo $LanguageInstance->get('tandem_logo')?>" /></a>
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
	<div id="footer-container">
		<div id="footer">
			<div class="footer-tandem" title="<?php echo $LanguageInstance->get('tandem')?>"></div>
			<div class="footer-logos">
				<img src="css/images/logo_LLP.png" alt="Lifelong Learning Programme" />
				<img src="css/images/logo_EAC.png" alt="Education, Audiovisual &amp; Culture" />
				<img src="css/images/logo_speakapps.png" alt="Speakapps" />
			</div>
		</div>
	</div>
	<!-- /footer -->
	<iframe src="" width="0" frameborder="0" height="0" id="idfrm" name="idfrm"></iframe>
</body>
</html>
<?php } ?>