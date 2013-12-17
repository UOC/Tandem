<?php session_start();
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
foreach(getDirectoryList("./") as $value){
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
            	 if (filemtime($value) < time()-(24*60*60)) unlink($value);
            }
        } 
	/*$extension = explode(".", $value);
	$isSys = explode("data", $value);
	if(count($extension)>1 && $extension[1]=="xml" && $isSys[1]=="") 
		if (filemtime($value) < time()-(24*60*60)) unlink($value);*/
}


$user_obj = $_SESSION['current_user'];
$room = $_REQUEST['room'];
$nextSample = $_REQUEST['nextSample'];
$node = $_REQUEST['node'];
$classOf = $_REQUEST['classOf'];
$data = $_REQUEST['data'];


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
			$id_resource = $user_obj->id_resource;
			//Ara cridem al createUser
			$DELIMITER = ':';
			if (strpos($id_resource, $DELIMITER)!==FALSE) {
				$id_resource = str_replace($DELIMITER, '_',$id_resource);
			//		$cArray = explode($DELIMITER, $id_resource);
			//		foreach ($cArray as $v){
			//			if ((int)$v>0){
			//				$id_resource = $v;
			//				break;
			//			}
			//		}
			}
			//TODO elimino el valor d'id_resource perq sigui igual q quan entres normal
			//$id_resource = '';
			$exercise = $data.$id_resource.$room;
			$redirect_to_room = false;
			$user_obj->type_user = 'b';
			if(!is_file($exercise.".xml")) { 
				$create_room = isset($_REQUEST['create_room'])?$_REQUEST['create_room']:false;
				if ($create_room=='1') {
					$user_obj->type_user = 'a'; $tandemBLTI->makeXMLUser($user_obj,$exercise,$data);
					$redirect_to_room = true;
				} else {?>
					top.document.getElementById('roomStatus').innerHTML="<input id='createRoom' type='submit' value=' create room ' onclick='createRoom(\"<?php echo $room;?>\",\"<?php echo $data;?>\",\"<?php echo $nextSample;?>\",\"<?php echo $node;?>\",\"<?php echo $classOf;?>\");'/>";
				<?php }
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
				top.document.location.href="<?php echo $classOf;?>.php?room=<?php echo $exercise;?>&user=<?php echo $user_obj->type_user?>&nextSample=<?php echo $nextSample;?>&node=<?php echo $node;?>&data=<?php echo $data;?>";
			}

			function openLinkWaiting(){
				top.document.getElementById('roomStatus').innerHTML="Connecting...";
				setTimeout(openLink,3000);
				//Fem que creii el fitxer per dir que ja esta seleccionada
				var room = "<?php echo $exercise?>";
			}
	<?php }
	}
//	header ('Location: '.$data_exercise->classOf.'.php?room='.$room.'&user='.$user_obj->type_user.'&nextSample='.$data_exercise->nextSample.'&node='.$data_exercise->node.'&data='.$exercise);

}?>
</script>