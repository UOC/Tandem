<?php 
require_once 'classes/lang.php';
require_once 'classes/gestorBD.php';
//Creem la room
$gestorBD = new GestorBD();

$user_obj = $_SESSION['current_user'];

$nextSample = $_REQUEST['nextSample'];
$node = $_REQUEST['node'];
$classOf = $_REQUEST['classOf'];
$data = $_REQUEST['data'];


$id_resource_lti = $_SESSION[ID_RESOURCE];
$id_user_guest = $_REQUEST['id_user_guest'];
$id_user_host = $user_obj->id;
//TODO passar missatge
$message = '';
$id_course = $_SESSION[COURSE_ID];
$id_exercise = $gestorBD->getExerciseByXmlName($data, $id_course);

$room = $gestorBD->has_invited_to_tandem($id_exercise, $id_course, $id_resource_lti, $id_user_host, $id_user_guest);
$user_agent = $_SERVER['HTTP_USER_AGENT'];
if ($room <= 0) {
	$room = $gestorBD->register_tandem($id_exercise, $id_course, $id_resource_lti, $id_user_host, $id_user_guest, $message, $user_agent);
}
$_SESSION[CURRENT_TANDEM] = $room;
?>
<script src="js/jquery-1.7.2.min.js"></script>
<script type="text/javascript">
<?php 
	function getDirectoryList ($directory){
	    $results = array();
	    $handler = opendir($directory);
	    while ($file = readdir($handler)) {
	    	if ($file != "." && $file != ".." && $file != ".DS_STORE") $results[] = $file;
		}
	    closedir($handler);
	    return $results;
	}
	
	//Netejem els xmls anteriors
	foreach(getDirectoryList(PROTECTED_FOLDER) as $value){
	    	preg_match('/[^?]*/', $value, $matches); 
	        $string = $matches[0]; 
	      
	        $pattern = preg_split('/\./', $string, -1, PREG_SPLIT_OFFSET_CAPTURE); 
	
	        # check if there is any extension 
	        if(count($pattern) > 1) 
	        { 
	            $filenamepart = $pattern[count($pattern)-1][0]; 
	            preg_match('/[^?]*/', $filenamepart, $matches); 
	            $pos=strpos($pattern[0][0],'data');
	            if ($matches[0]=='xml' && $pos===FALSE) {
	            	 if (filemtime(PROTECTED_FOLDER.'/'.$value) < time()-(24*60*60)) unlink(PROTECTED_FOLDER.'/'.$value);
	            }
	        } 
		/*$extension = explode(".", $value);
		$isSys = explode("data", $value);
		if(count($extension)>1 && $extension[1]=="xml" && $isSys[1]=="") 
			if (filemtime($value) < time()-(24*60*60)) unlink($value);*/
	}
	
	
	
	
	if (!isset($user_obj) && isset($exercise) && strlen($exercise)>0) {
		//Tornem a l'index
		?>
		printError('Error in session');
		<?php
	} else {
	
		require_once dirname(__FILE__).'/classes/IntegrationTandemBLTI.php';
		$tandemBLTI = new IntegrationTandemBLTI();
		
		//Now we try to get data course
		$data_exercise = $tandemBLTI->getDataExercise($data, false);
		if ($data_exercise==null) {
		?>
		printError('Error exercise <?php echo $data; ?> does not exist');
		<?php
		} else {
				$exercise = $data.$id_resource_lti.'_'.$room;
				$redirect_to_room = false;
				$user_obj->type_user = 'b';
				if(!is_file(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$exercise.".xml")) { 
					//$create_room = isset($_REQUEST['create_room'])?$_REQUEST['create_room']:false;
					//if ($create_room=='1') {
						$user_obj->type_user = 'a'; $tandemBLTI->makeXMLUserLTI($user_obj,$exercise,$data);
						$redirect_to_room = true;
					//} else {?>
						//top.document.getElementById('roomStatus').innerHTML="<input id='createRoom' type='submit' value=' create room ' onclick='createRoom(\"<?php echo $room;?>\",\"<?php echo $data;?>\",\"<?php echo $nextSample;?>\",\"<?php echo $node;?>\",\"<?php echo $classOf;?>\");'/>";
					<?php //}
				} else  {
					if (!$tandemBLTI->canUserLoginInTandem($user_obj,$exercise)) { ?>
						jQuery(document).ready(function(){
							top.document.getElementById('roomStatus').innerHTML='Please choose another room, this one is currently in use.';
						});
					<?php } else {
						$tandemBLTI->editXMLUser($user_obj,$exercise); 
						$redirect_to_room = true;
					}?>
		<?php }  
			if ($redirect_to_room) { ?>
	
				jQuery(document).ready(function(){
					top.document.getElementById('roomStatus').innerHTML="";
					openLinkWaiting(); });
	
				function openLink(){
					top.document.location.href="<?php echo $classOf;?>.php?room=<?php echo $exercise;?>&user=<?php echo $user_obj->type_user?>&nextSample=<?php echo $nextSample;?>&node=<?php echo $node;?>&data=<?php echo $data;?>&userb=<?php echo $id_user_guest;?>";
				}
	
				function openLinkWaiting(){
					top.document.getElementById('roomStatus').innerHTML="Connecting...";
					setTimeout(openLink,3000);
					//Fem que creii el fitxer per dir que ja esta seleccionada
					var room = "<?php echo $exercise?>";
				}
		<?php }
		}
	
	}?>
</script>