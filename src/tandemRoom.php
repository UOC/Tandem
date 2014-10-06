<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
ini_set("display_errors",0);
require_once dirname(__FILE__) . '/classes/lang.php';
require_once dirname(__FILE__) . '/classes/constants.php';
require_once dirname(__FILE__) . '/classes/gestorBD.php';
require_once 'IMSBasicLTI/uoc-blti/lti_utils.php';


$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;
$use_waiting_room = isset($_SESSION[USE_WAITING_ROOM]) ? $_SESSION[USE_WAITING_ROOM] : false;
require_once dirname(__FILE__) . '/classes/IntegrationTandemBLTI.php';
//si no existeix objecte usuari o no existeix curs redireccionem cap a l'index....preguntar Antoni cap a on redirigir...
if (!$user_obj || !$course_id) {
//Tornem a l'index
    header('Location: index.php');
} else {
    require_once(dirname(__FILE__) . '/classes/constants.php');
    $path = '';
    if (isset($_SESSION[TANDEM_COURSE_FOLDER]))
        $path = $_SESSION[TANDEM_COURSE_FOLDER] . '/';
    
    $id_resource_lti = $_SESSION[ID_RESOURCE];
    $lti_context = unserialize($_SESSION[LTI_CONTEXT]);
    $gestorBD = new GestorBD();
    
    //retorna el llistat d'usuaris que te un curs (format array)
    $users_course = $gestorBD->obte_llistat_usuaris($course_id, $user_obj->id);
   
    //de moment ens retorna el teacher creat anteriorment curs/sala
    $is_reload = isset($_POST['reload']) ? $_POST['reload'] != null : false;
    $last_id = $gestorBD->get_lastid_invited_to_join($user_obj->id, $id_resource_lti, $course_id);
 
    //si hi ha recarrega o be no hi han usuaris en el curs o be els usuaris del curs son 0
    //DE MOMENT NO ENTRA !!!
    if ($is_reload || !$users_course || count($users_course) == 0) {
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
                $id_user_lti = $user_lti->getId();
                $firstname = mb_convert_encoding($user_lti->firstname, 'ISO-8859-1', 'UTF-8');
                $lastname = mb_convert_encoding($user_lti->lastname, 'ISO-8859-1', 'UTF-8');
                $fullname = mb_convert_encoding($user_lti->fullname, 'ISO-8859-1', 'UTF-8');
                $email = mb_convert_encoding($user_lti->email, 'ISO-8859-1', 'UTF-8');
                
                //Afegim usuari x LTI
                
                $gestorBD->afegeixUsuari($course_id, $id_user_lti, $firstname, $lastname, $fullname, $email, '');
//$users_course[$id_user_lti] = $gestorBD->get_user_by_username($id_user_lti);
            }
//Reorder
            $users_course = $gestorBD->obte_llistat_usuaris($course_id, $user_obj->id);
            
          
            
        } else { //Mirem de carregar per OKI
             // echo 'Carreguem per OKI'.'<br>';
            
            $okibusPHP_components = $_SESSION[OKIBUSPHP_COMPONENTS];
           // var_dump($okibusPHP_components);
            $okibusPHP_okibusClient = $_SESSION[OKIBUSPHP_OKIBUSCLIENT];
           //  var_dump($okibusPHP_okibusClient);
            putenv(OKIBUSPHP_COMPONENTS . '=' . $okibusPHP_components);
            putenv(OKIBUSPHP_OKIBUSCLIENT . '=' . $okibusPHP_okibusClient);
//Pel require d'autehtnication ja carrega les propietats
            require_once dirname(__FILE__) . '/classes/gestorOKI.php';
            $gestorOKI = new GestorOKI();
            $users_course = $gestorOKI->obte_llistat_usuaris($gestorBD, $course_id);
        }
    }
    
    $is_showTandem = isset($_POST['showTandem']) ? $_POST['showTandem'] != null : false;
    
   
    $user_tandems = null;
    $user_selected = isset($_POST['user_selected']) ? intval($_POST['user_selected']) : 0;
    
  
    
    if ($is_showTandem && $user_selected) {
       // echo "EN PRINCIPI NO ENTREM!!!!".'<br>';
        $exercise = isset($_POST['room']) ? intval($_POST['room'], 10) : false;
        $user_tandems = $gestorBD->obte_llistat_tandems($course_id, $user_selected, $exercise);
    }

?>
<?php      
/*
VARIABLES K SE TENDRAN DE MODIFICAR !!!
*/
//$languageURL = $_GET['localLanguage'];

//$_SESSION[LANG] = 'en_US';
if(!empty($_GET['locale'])){
	$_SESSION[LANG] = $_GET['locale'];
}else
    $_SESSION[LANG] = "es_ES";

?>
<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/jquery.ui.core.js"></script>
<script src="js/jquery.ui.widget.js"></script>
<script src="js/jquery.ui.button.js"></script>
<script src="js/jquery.ui.position.js"></script>
<script src="js/jquery.ui.autocomplete.js"></script>
<script src="js/jquery.colorbox-min.js"></script>
<script src="js/common.js"></script>

<!-- Timer Start!! -->
<script src="js/loadUserData.js"></script>
<script type="text/javascript" src="js/jquery.animate-colors.min.js"></script>
<script type="text/javascript" src="js/jquery.simplemodal.1.4.2.min.js"></script>
<script type="text/javascript" src="js/jquery.iframe-auto-height.plugin.1.7.1.min.js"></script>
<script type="text/javascript" src="js/jquery.infotip.min.js"></script>
<script type="text/javascript" src="js/jquery.timeline-clock.min.js"></script>
<script src="js/jquery.ui.progressbar.js"></script>                        
<script type="text/javascript">

jQuery(document).ready(function(){
		jQuery('#showNewsS').slideUp();
		
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
				$("#room").combobox();
			}
			else { 
				$("#room").destroy();
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
                        //alert(url);
			xmlReq.open("GET", url, true);
			xmlReq.send(null);
		}
		printError  = function(error){
			$('#roomStatus').val(error);
		}
		
//get dataROOM.xml params
		getXML = function(){
            alert('dins de getXML');
			user_selected = $('#user_selected').val();
			room_temp = $('#room').val();
			if (user_selected=="" || user_selected=="-1") {
				//alert("<?php echo $LanguageInstance->get('select_user')?>");
			} else {
				if (room_temp=="" || room_temp=="-1") {
					//alert("<?php echo $LanguageInstance->get('select_exercise')?>");
				} else {
					enable_button('');
					getXMLRoom(room_temp);
				}
			}
		}
		processXml = function(){
			if((xmlReq.readyState == 4) && (xmlReq.status == 200)){
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
	$('#idfrm').attr('src','checkRoomUserTandem.php?id_user_guest=-1&nextSample='+nextSample+'&node='+node+'&classOf='+classOf+'&data='+data);
				} else {
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
				$('#idfrm').attr('src','checkRoomUserTandem.php?id_user_guest=-1&create_room=1&nextSample='+nextSample+'&node='+node+'&classOf='+classOf+'&data='+data);
				//TODO in Ajax
		}
		printError = function (error) {
			alert(error);
		}
		//$("#user_selected").combobox();
		//$("#room").combobox();
		enable_button();



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
                                                
                                                window.location.replace("accessTandem.php?id="+id_txt);
                                                
                                                /*ja no afectaria*/
						//$("#info-block").append("<div class='alert-inside'><i class='icon'></i><h3><?php echo $LanguageInstance->get('just_been_invited');?> <em>"+nameuser_txt+"</em> <?php echo $LanguageInstance->get('exercise');?>: <em>"+exercise_txt+"</em> </h3><a id='startNowBtn' href=\"accessTandem.php?id="+id_txt+"\" class='tandem-btn'><?php echo $LanguageInstance->get('accept');?></a></div>");
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
<?php 
//Permetem que seleccini l'exercici 20111110
    $is_host = $user_obj->is_host;

  
      
    $array_exercises = $gestorBD->get_tandem_exercises($course_id);
    
   
    
    
    $tandemBLTI = new IntegrationTandemBLTI();
    
    $selected_exercise = $tandemBLTI->checkXML2GetExercise($user_obj);
    
  
    
    $selected_exercise_select = isset($_POST['room']) ? $_POST['room'] : '';

   
    
    //IMPORTANT ***************************************************************
    //
    //Gets the object of tandem if exists, if not this user is the host
    $pending_invitations = $gestorBD->get_invited_to_join($user_obj->id, $id_resource_lti, $course_id, true);
   
  
    $last_id = $gestorBD->get_lastid_invited_to_join($user_obj->id, $id_resource_lti, $course_id);
  
  
    $name = mb_convert_encoding($user_obj->name, 'UTF-8', 'UTF-8');
  
   
    ?>
    <!DOCTYPE html>
    <html>
        <head>
            <title>Tandem</title>
            <meta charset="UTF-8" />
            <link rel="stylesheet" type="text/css" media="all" href="css/tandem.css" />
            <link rel="stylesheet" type="text/css" media="all" href="css/tandem-waiting-room.css" />
            <link rel="stylesheet" type="text/css" media="all" href="css/defaultInit.css" />
            <link rel="stylesheet" type="text/css" media="all" href="css/jquery-ui.css" />
            <!-- 10082012: nfinney> ADDED COLORBOX CSS LINK -->
            <link rel="stylesheet" type="text/css" media="all" href="css/colorbox.css" />
            <!-- END -->
           
           
            <!-- Timer End -->
            
    <?php include_once dirname(__FILE__) . '/js/google_analytics.php' ?>
            
            
            
            <script>
                $(document).ready(function() {
                    StartTandemTimer();
                });
            </script>
            
            
            
    <?php if ($selected_exercise && strlen($selected_exercise) > 0) {
        echo 'getXML();';
    } ?>

            
            <script>
            //$language, $courseID, $idExercise, $idNumberUserWaiting, $idUser);
            // echo '<div>' . $lang = $_SESSION[LANG] . '</div>';
              //                          echo $course_id;
              //                          echo $user_obj->id;
              
              
              
            function restFromWaitingRoomAndTandem(paramString){
                    $.ajax({
                    data:{ 
                            'language': "<?php echo $_SESSION[LANG]; ?>", 
                            'courseID': "<?php echo $course_id; ?>",
                            'userID':  "<?php echo $user_obj->id; ?>",
                            'typeClose': paramString
                        },
                    url: "differentRequests.php",
                    type: "POST",
                    dataType: "html",
                }).done(function(data){
                    console.log(data);
                }); 
            }
              
            function getWaitingTandemRoom(exercise_id,only_exercise_id,is_tandem){
                //6%2FTandemXWikiadmin060220131529&userID=1
                //alert(exercise_id);
               
                $.ajax({
                    data:{ 
                            'language': "<?php echo $_SESSION[LANG]; ?>", 
                            'courseID': "<?php echo $course_id; ?>", 
                            //'exerciseID': encodeURIComponent(exercise_id),
                            'exerciseID': exercise_id,
                            'userID':  "<?php echo $user_obj->id; ?>",
                            'ltiID': "<?php echo $_SESSION[ID_RESOURCE]; ?>",
                            'onlyExID' : only_exercise_id,
                            'is_tandem' : is_tandem?1:0
                        },
                    url: "getRequestsWaitingTandemRoom.php",
                    type: "POST",
                    dataType: "json",
        
                }).done(function(data){
                	  if (is_tandem==true && data =="room_taken"){

                	  	      modalRoomTaken();                      
	                  
	                  }else if(is_tandem==true){
                                  
                            tandemStandBy();
                            //$("#simplemodal-container").show( "fast");
                            top.document.getElementById('roomStatus').innerHTML="<?php echo $LanguageInstance->get('Connecting...')?>";
                            setTimeout("alert",30000);
	                  }
                	
                }); 
                
            }
            
            
            
            var interval = null;
            $(document).on('ready',function(){
                interval = setInterval(updateDiv,2000);
            });
            
           
            function updateDiv(){
                console.time( "Peticion AJAX" );
                $.ajax({
                    data:{ 
                            language: "<?php echo $lang = $_SESSION[LANG]; ?>", 
                            localLanguageURI: "",
                            courseID: "<?php echo $course_id; ?>" 
                        },
                    url: "updateWiewportWaitingTandemRoom.php",
                    type: "POST",
                    dataType: "json",
                }).done(function(data){
                    //http://stackoverflow.com/questions/2265167/why-is-forvar-item-in-list-with-arrays-considered-bad-practice-in-javascript
                    //http://stackoverflow.com/questions/11922383/access-process-nested-objects-arrays-or-json
                  
                    var data = data;
                    
                    var localLanguage = ""; 

                   
                    sumatorioWaitingTable = '';
                   
                    
                    sumatorioTandemTable = '';
                    
                    
                    for (var i in data){
                        
                        var id_exercise = data[i]['id_exercise'];
                        var name = data[i]['name'];                       
                        var language = data[i]['language'];
                        var waitingRoomId = data[i]['waitingRoomId'];                       
                        var number_user_waiting = data[i]['number_user_waiting'];                        
                        var extra_exercise = data[i]['relative_path'] && data[i]['relative_path'].length > 0 ? data[i]['relative_path'].replace("/","").replace("\\","")+ '/' : '';                        
                        var name_xml_file = extra_exercise + data[i]['name_xml_file'];
                          
                         <?php $userLanguageFinal = $_SESSION[LANG]; ?>
                        
                        if((language == '<?php echo $userLanguageFinal; ?>' ) || number_user_waiting==0){
                            //waiting
                            //alert('primera opcio');
                            if (number_user_waiting !=0 ){
                             
                                sumatorioWaitingTable +='<tr><td><input class="exButtonWaiting" type="button" name="exercise-'+id_exercise+'" data-id-number="'+id_exercise+'" data-is-tandem="false" data-id-exercise="'+name_xml_file+'" id="exercise-'+id_exercise+'" value="'+name+'"></td><td><label style="color:black;" class="common-waiting-tandem_users waiting-users-more-one">'+number_user_waiting+ '</label></td></tr>';
                            
                            }
                            if (number_user_waiting == 0 || number_user_waiting == null){
                            
                                sumatorioWaitingTable +='<tr><td><input class="exButtonWaiting" type="button" name="exercise-'+id_exercise+'" data-id-number="'+id_exercise+'" data-is-tandem="false" data-id-exercise="'+name_xml_file+'" id="exercise-'+id_exercise+'" value="'+name+'"></td><td><label style="color:red;" class="common-waiting-tandem_users waiting-users-more-one">'+number_user_waiting+ '</label></td></tr>';
                            }
                            
                            //ajax 
                            //mostrarem dades en un jquery dialog
                        }
                        if(language != '<?php echo $userLanguageFinal; ?>' && number_user_waiting>0){
                        
                            sumatorioTandemTable += '<tr><td><input class="exButtonTandem" type="button" data-waiting-room-id='+waitingRoomId+' name="exercise-'+id_exercise+'" data-id-number="'+id_exercise+'" id="exercise-'+id_exercise+'" data-is-tandem="true" data-id-exercise="'+name_xml_file+'"  value="'+name+'"></td><td><label style="color:black;" class="common-waiting-tandem_users tandem-users-more-one">'+number_user_waiting+'</label></td></tr>';
                        }
                        //alert(id_exercise+'-'+name+'-'+language+'-'+number_user_waiting);
                        
                    }
                  
                    $('.leftTable').html(sumatorioWaitingTable);
                    $('.rightTable').html(sumatorioTandemTable);
                     
                    for (var i in data){
                        
                        var id_exercise = data[i]['id_exercise'];
                        
                        var id_only_number = data[i]['id_exercise'];
                        
                        //si aquest boto es clickat passem id,language,id_user,num_usuaris_en_espera
                        
                        $(document).ready(function(){
                            $('#exercise-'+id_exercise).on("click",function(){
                                
                                //desactivar botó
                                $(this).prop("disabled",true);
                                 
                                //alert('Hemos seleccionado el ejercicio: '+$(this).data("id-exercise"));
                                timeStop(); //Stopping the timebar
                                //startTask(); //We show the connexion div with the charging image

                                //Victor
                                //  if($(this).data("is-tandem")==true){
                                //    	checkIfRoomTaken($(this).data("waiting-room-id")); 
                            	// }
                                //Check if is waiting or tandem and execute his functions()
                               
                                getWaitingTandemRoom($(this).data("id-exercise"),$(this).data("id-number"),$(this).data("is-tandem")); //Passamos por ajax el id del ejercicio a la base de datos                                 

	                           
	                                                               
	                                //conexio tandem
	                                if ($(this).data("is-tandem")==false){
	                                
	                                  //alert("executing XML Room");
	                                   startModalQuickly();
	                                   modalTimer();
	                                   var content = getXMLRoom($(this).data("id-exercise"));
	                                }
	                                //alert (content);
                            
                                
                            });
                        }); 
                        
                    }
                    
                    sumatorioWaiting='';
                    sumatorioTandem='';
                    
                });
            }
            </script>
            <script>
            
		var intTimerNow;
		var limitTimer = 500;
		var limitTimerConn = 1000;
		function setExpiredNow(itNow){
			intTimerNow = setTimeout("getTimeNow("+itNow+");", 10000);
		}
		function getTimeNow(itNow){
			var tNow;
			itNow--;
			if(itNow<10) tNow ="0"+itNow;
			else tNow = itNow;
			$("#startNowBtn").html("00:"+tNow);
			if(itNow<=1){ 
				clearInterval(intTimerNow);
				//desconn();
			}
			else setExpiredNow(itNow);
		}
                
                StartTandemTimer = function(){
                        /*$("#lnk-start-task").addClass("btnOff");
                        $("#lnk-start-task").html("Waiting...");
                        $("#lnk-start-task").removeAttr("href");
                        $("#lnk-start-task").removeAttr("onclick");*/
                        $("#timeline").show("fast");
                        var minutos = 30;
                        var segundos = 0;
                        timerOn(minutos,segundos);
                        timeline.start();
                       
                      
                        //intervalTimerAction = setInterval(timerChecker,1000);
                        
                        
                        
                }
                        
                        
                var totalUser = 0;
		$(document).ready(function(){
//colorbox js actionexample3
			notifyTimerDown = function(id){
				if($.trim(txtNews)!=$.trim(id)){
					$('#showNews').html(id);
					$('#showNews').fadeIn(1000).slideDown("fast");
					$("#showNews").delay(3000).fadeOut(1000).slideUp("fast");
					txtNews=id;
				}
			}
//colorbox
			$("a[rel='example1']").click(function(event){
				event.preventDefault();
			    $('a[rel="example1"]').colorbox({
			        maxWidth: '90%',
			        initialWidth: '200px',
			        initialHeight: '200px',
			        speed: 300,            
			        overlayClose: false
			    });
			    $.colorbox({href: $(this).attr('href')});
			});
                    });
               // Ajuste timeline (anchura máxima)
	var lwidth = $('#timeline').outerWidth() - ($('#timeline .lbl').outerWidth() + $('#timeline .clock').outerWidth()) + 5;
	var lmargin = $('#timeline .lbl').outerWidth() - 5;
	$('#timeline .linewrap').css({'width': lwidth + 'px', 'margin-left' : lmargin + 'px'});
	var timeline;
	timerOn = function(minutos,segundos){
		// Configuración timeline
               
		timeline = $('#timeline').timeLineClock({
			time: {hh:0,mm:parseInt(minutos),ss:parseInt(segundos)},
			onEnd: theEnd
		}); 
	}    
        
        // fin espera partner tarea con timer
	function partnerTimerTaskReady(){
		$.modal.close();
		stepReady($('#steps li .waiting'));
		timeline.start();
	}
        
	// ventana modal tiempo agotado
	function theEnd(){
		if ($("#modal-end-task").length > 0){
			$.modal($('#modal-end-task'));
			//accionTimer();
		}
	}
        
        //conectando tandem
        function tandemStandBy(){
		if ($("#waitingUser").length > 0){
			$.modal($('#waitingUser'));
             modalTimer2();
		}
	}  
	 //modal with the message of room already in a tandem.
        function modalRoomTaken(){
		if($("#modalRoomTaken").length > 0){
			$.modal($('#modalRoomTaken'));            
		  }
		}
        
        var show_message_giveup = true;
        
        function showWaitingMessage(urlToRedirect, tandem_id){
            if ($("#modal-start-task").length > 0){
                show_message_giveup = true;
               // alert(urlToRedirect);
               // alert(tandem_id);
                //modalTimer();
                //clearInterval(intervalCheck);
                var TimerSUAR = 1000;
                intervalCheckHavePartner = setInterval(function(){
			$.ajax({
				  type: 'GET',
				  url: "have_partner.php",
				  data: {
					  id: tandem_id
				  },
				  dataType: "xml",
				  success: function(xml){
				  	var id_txt = $(xml).find('id').text();
				  	if (id_txt &&  id_txt.length>0) {

						document.location.href = urlToRedirect;						
                         /*ja no afectaria*/
						//$("#info-block").append("<div class='alert-inside'><i class='icon'></i><h3><?php echo $LanguageInstance->get('just_been_invited');?> <em>"+nameuser_txt+"</em> <?php echo $LanguageInstance->get('exercise');?>: <em>"+exercise_txt+"</em> </h3><a id='startNowBtn' href=\"accessTandem.php?id="+id_txt+"\" class='tandem-btn'><?php echo $LanguageInstance->get('accept');?></a></div>");
					//	setExpiredNow(60);
						clearInterval(intervalCheckHavePartner);
                                               
				  	}
				  },
				  error: function(){
				  		clearInterval(intervalCheckHavePartner);
						TimerSUAR+=500;
						intervalCheckHavePartner = setInterval(intervalCheckHavePartner,TimerSUAR);
						notifyTimerDown('<?php echo $LanguageInstance->get('SlowConn')?>');
				  }
			});
		},TimerSUAR);	
               
               // startModalQuickly();
            }
        }
        
        function startModalQuickly(){
        
             $('#modal-start-task').modal( {onClose: function () {
                    if (show_message_giveup) {
                      //alert('give_up');
                      closeModalAndCountAgain('give_up');
                  } else {

                      restFromWaitingRoomAndTandem('lapsed'); 
                      //closeModalAndCountAgain('lapsed');
                      $.modal.close(); // must call this!
                  }
                }})
        
        }
        
        
        function closeModalAndCountAgain(paramString) {
            if (counterW){
             clearInterval(counterW);   
            }
            beginningOneMoreTime();
            $.modal.close(); // must call this!
            if (show_message_giveup){
                restFromWaitingRoomAndTandem(paramString); //we rest from the waiting room and the tandem room
            }
        }
        
        function closeModalAndCountAgainTandem() {
            if (counterT){
             clearInterval(counterT);   
            }
            beginningOneMoreTime();
            $.modal.close(); // must call this!
           
        }
        
        
        
        var counterW = false;
        var countW = <?php echo WAITING_TIME; ?>;
        var countCurrentW = countW;
         
        function modalTimer(){
            countCurrentW = countW;
            counterW = setInterval(timerW, 1000); //1000 will  run it every 1 second
        }
        
        function timerW(){
           
            countCurrentW=countCurrentW-1;
            if (countCurrentW <= 0)
            {
                //alert('lapsed');
                show_message_giveup=false;
                closeModalAndCountAgain('lapsed');
                return;
            }

            document.getElementById("timerWaiting").innerHTML=countCurrentW + " secs"; // watch for spelling
         }
        
        /*************/
        
        var counterT = false;
        var countT = <?php echo TANDEM_TIME; ?>;
        var countCurrentT = countT;
         
         
        function modalTimer2(){
            countCurrentT = countT;
            counterT = setInterval(timerT, 1000); //1000 will  run it every 1 second
        }
        
        function timerT(){
           
            countCurrentT=countCurrentT-1;
            if (countCurrentT <= 0)
            {
                //clearInterval(counterT);
                //$.modal.close(); 
                closeModalAndCountAgainTandem();
                return;
            }
            document.getElementById("timerTandem").innerHTML=countCurrentT + " secs"; // watch for spelling
            
        }
        
        
        
        
        
        function timeStop(){
		 timeline.stop();
	}
        
        function beginningOneMoreTime(){
                timeline.start();
        }
        
        
      
         
        </script>
           
           
            
        </head>
        <body>

            <!-- accessibility -->
            <div id="accessibility">
                <a href="#content" accesskey="s" title="Acceso directo al contenido"><?php echo $LanguageInstance->get('direct_access_to_content') ?></a> |
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
                            <span class="welcome"><?php echo $LanguageInstance->get('welcome') ?> <?php echo $name ?>!</span>
                            <form action="#" method="post" id="main_form" class="login_form">
                             
                            <!-- *********************************** -->   
                            <!-- ****WAITING-TANDEM-ROOM-dynamic**** -->
                            <!-- *********************************** -->
                            
                             <div id="timeline" style="display:none;">
                                    <div class="lbl"><?php echo $LanguageInstance->get('waiting_remaining_time')?></div>
                                    <div class="clock" id="clock"><span class="mm">00</span>:<span class="ss">00</span></div>
                                    <div class="linewrap"><div class="line"></div></div>
                            </div>
                           
                            <!--
                            <p><a href='#' onclick="StartTandemTimer();return false;" id="lnk-start-task" class="btn">Empezamos con las pruebas del Timer</a></p>
                            -->
                            
                            <!-- WAITING MODAL -->
                            
                            <div id="modal-start-task" class="modal">
                              
                                <body id="home_" style="background-color:#FFFFFF;">
                                <div>
                                        <img id="home" src="images/final1.png" width="310" height="85" alt="" />
                                </div>
                                <div class="text">
                                        <!-- Falten introduir al PO -->
                                        <p><?php echo $LanguageInstance->get('Waiting for Tandem Connexion !!');?></p>
                                        <p><?php echo $LanguageInstance->get('Please Stand By !!');?></p>
                                </div>
                                <div class="waitingImagePosition">
                                  <img id="home" src="css/images/loading_1.gif" width="150" height="150" alt="" />
                                </div>
                                
                                                      
                                                  
                             <div id="timerWaiting" style="text-align:center;"></div>         
                            </body>
                            </div>
                            
                            
                            <!-- TANDEM MODAL -->
                            
                            <div id="waitingUser" class="modal">
                                <script>
                                         
                                </script>
                                <body id="home_" style="background-color:#FFFFFF;">
                                <div>
                                        <img id="home" src="images/final1.png" width="310" height="85" alt="" />
                                </div>
                                <div class="text">
                                        <!-- Falten introduir al PO -->
                                        <p><?php echo $LanguageInstance->get('Waiting for Waiting User Connexion !!');?></p>
                                        <p><?php echo $LanguageInstance->get('Please Stand By !!');?></p>
                                </div>
                                <div class="waitingImagePosition">
                                  <img id="home" src="css/images/loading_1.gif" width="150" height="150" alt="" />
                                </div>
                              
                                <div id="timerTandem" style="text-align:center;"></div>     
                            </body>
                            </div>        
                             <div id="modalRoomTaken" class="modal">
                                <body id="home_" style="background-color:#FFFFFF;">
                                <div>
                                        <img id="home" src="images/final1.png" width="310" height="85" alt="" />
                                </div>
                                <div class="text">
                                        <!-- Falten introduir al PO -->
                                        <p><?php echo $LanguageInstance->get('room_taken');?></p>
                                </div>                                    
                            </body>
                            </div>
                            
                            
                            
                            
                            <div id="modal-end-task" class="modal">
                                <script>
                                         
                                </script>
                                <body id="home_" style="background-color:#FFFFFF;">
                                <div>
                                        <img id="home" src="images/final1.png" width="310" height="85" alt="" />
                                </div>
                                <div class="text">
                                        <!-- Falten introduir al PO -->
                                        <p><?php echo $LanguageInstance->get('Waiting for Tandem Connexion !!');?></p>
                                        <p><?php echo $LanguageInstance->get('Please Stand By !!');?></p>
                                </div>
                                <div class="waitingImagePosition">
                                  <img id="home" src="css/images/loading_1.gif" width="150" height="150" alt="" />
                                </div>
                            </body>
                            </div>
                            
                            <div id="" class="modal">
                                <p class="msg">Time up!</p>
                                <p><a href='#' id="lnk-end-task" class="btn simplemodal-close">Close</a></p>
                            </div>
                            
                            
                            
                            <div class="tandem-room-content">
                                <div class="tandem-room-left">
                                    <ul></ul>
                                </div>
                                <div class="tandem-room-right">
                                    <ul></ul>
                                </div>
                                
                            </div>
                            <div class="cleaner"></div> 
                            
                    <table id="statisticsWaiting" class="table" style="float:left;">
                    <thead>
                            <tr>
                                    <th style="width:100px;">Waiting</th>
                                    <th style="width:100px;">Users Waiting</th>
                            </tr>
                    </thead>
                    <tbody class="leftTable">
                        
                    </tbody>
                    </table>
                            
                    
                            
                    <table id="statisticsTandem" class="table" style="float:left;margin-left:10px;">
                    <thead>
                            <tr>
                                    <th style="width:100px;">Tandem</th>
                                    <th style="width:100px;">Users Tandem</th>
                            </tr>
                    </thead>
                    <tbody class="rightTable">
                        
                    </tbody>
                    </table>    
                                <?php
                               // echo '<div><h2>Lenguaje: ' . $lang = $_SESSION[LANG] . '</h2></div>' . '<br>';
                               // echo 'ID del Curso: ' . $course_id . '<br>';
                               // echo 'ID Usuario: ' . $user_obj->id . '<br>';
                                //print_r($array_exercises);
                                echo '<br>';
                                ?>
                            
                            
                             <?php if ($user_obj->instructor) { ?>
                            
                                <div class="clear">
                                   
                                    <input type="submit" name="showTandem" onclick="Javascript:canviaAction('exercises');" value="<?php echo $LanguageInstance->get('mange_exercises_tandem')?>" />
                                </div>
                                
                             <?php } ?>
                            
                            
                                <div class="manage-area" style="display:none;">
                                    <div class="clear">
                                        <!--<div id="info-block" class="alert alert-info" style="display:none"></div>-->
                                        <!--<div class="info" style="display:none"></div>--> <!-- 10092012 nfinney> type error: changed to 'none' from 'hidden' -->
                                        <?php if (!$pending_invitations) { ?>
                                           
                                        <?php } else { ?>
                                            
                                          
                                                    <?php
                                                    $ai = 0;
                                                    foreach ($pending_invitations as $tandem) {
                                                        $ai++;
                                                        ?>
                                                       
                                                          
                                                            <?php
                                                            $time2Expire = 60;
                                                            if ((time() - strtotime($tandem['created'])) >= $time2Expire) {
                                                                ?>
                                                              
                                                      <?php }else { ?>
                                                            <script>
                                                                setExpired(<?php echo $time2Expire; ?>);
                                                                var intTimer;
                                                                
                                                                function setExpired(i){
                                                                    intTimer = setTimeout("getTime(" + i + ");", 1000);
                                                                }
                                                                
                                                                function getTime(i){
                                                       
                                                                    for (var iT = 0; iT <=<?php echo $ai; ?>; iT++){
                                                                        
                                                                        $("#timer2expired" + iT).removeClass("tandem-btn").addClass("tandem-btnout");
                                                                        $("#timer2expired" + iT).html("<?php echo $LanguageInstance->get('caducado') ?>");
                                                                        $("#timer2expired" + iT).attr("href", "#")
                                                                        clearInterval(intTimer);
                                                       
                                                                        }
                                                                }
                                                        </script>
                                                        <?php
                                                    }
                                                    ?>
                                                  
                                        <?php } ?>
                                               
                                    <?php } ?>	
                                    </div>

                                    <div class="clear">
                                    <p id="roomStatus"></p>
                                    </div>
                                </div> <!-- /manage-area -->
                            </form>
                            <div id="logo">
                                <a href="#" title="<?php echo $LanguageInstance->get('tandem_logo') ?>"><img src="css/images/logo_Tandem.png" alt="<?php echo $LanguageInstance->get('tandem_logo') ?>" /></a>
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
                    <div class="footer-tandem" title="<?php echo $LanguageInstance->get('tandem') ?>"></div>
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