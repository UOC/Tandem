<?php 
require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/constants.php';
require_once dirname(__FILE__).'/classes/gestorBD.php';
require_once dirname(__FILE__).'/classes/utils.php';

$user_obj = $_SESSION[CURRENT_USER];
$course_id = $_SESSION[COURSE_ID];
//20120830 abertranb register the course folder
$course_folder = $_SESSION[TANDEM_COURSE_FOLDER];
//FIIIII
$message = false;
if (!isset($user_obj) || !isset($course_id) || !isset($course_folder) || !$user_obj->instructor) {
	//Tornem a l'index
	header ('Location: index.php');
} else {
	$exercise_name = '';
	$exercise_id = -1;
	$gestorBD	= new GestorBD();
	if(isset($_FILES["zip_file"]) && $_FILES["zip_file"]["name"]) {
		$filename = $_FILES["zip_file"]["name"];
		$source = $_FILES["zip_file"]["tmp_name"];
		$type = $_FILES["zip_file"]["type"];
		
		$name = explode(".", $filename);
		$accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
		foreach($accepted_types as $mime_type) {
			if($mime_type == $type) {
				$okay = true;
				break;
			}
		}
	
		$continue = strtolower($name[1]) == 'zip' ? true : false;
		if(!$continue) {
			$message = Language::get('error_no_zip_format');
		} else {
			$target_path = dirname(__FILE__).DIRECTORY_SEPARATOR.$course_folder;
			
			$target_path_temp = $target_path.'/temp'.rand();
				
			if (!file_exists($target_path))
				mkdir($target_path, 0777, true);
			if (!file_exists($target_path_temp))
				mkdir($target_path_temp, 0777, true);
			$target_path_file = $target_path.DIRECTORY_SEPARATOR.$filename;
			if(move_uploaded_file($source, $target_path_file)) {
				$zip = new ZipArchive();
				$x = $zip->open($target_path_file);
				if ($x === true) {
					$zip->extractTo($target_path_temp); 
					$zip->close();
		
					unlink($target_path_file);
				}
				$name_form = isset($_REQUEST['name'])?$_REQUEST['name']:$name[0];
				if (empty($name_form)) {
					$name_form = $name[0];
				}
				$overrides_xml_file = 1==(isset($_REQUEST['overrides_xml_file'])?$_REQUEST['overrides_xml_file']:0);
				$name_xml_file = getNameXmlFileUnZipped($target_path_temp);
				
				if (file_exists($target_path.DIRECTORY_SEPARATOR.'data'.$name_xml_file.'.xml') && !$overrides_xml_file) {
					$message = Language::getTag('file_exercise_xml_aready_exists','<strong>'.$name_xml_file.'</strong>');
					rrmdir($target_path_temp);
				} elseif (strlen($name_xml_file)==0) {
					$message = Language::get('main_xml_file_not_found');
					rrmdir($target_path_temp);
				}
				else 
				{
					$delete = array();
					$delete = moveFromTempToCourseFolder($target_path_temp, $target_path, $delete);
					rrmdir($target_path_temp);
					
					$enabled = 1;
					$gestorBD->register_tandem_exercise($course_id, -1, $user_obj->id, $name_form, $name_xml_file, $enabled);
					$message = Language::getTag('zip_upload_ok',$filename);
					$exercise_name = '';
					$exercise_id = -1;
				}
			} else {
				$message = Language::get('error_uploading_file');
			}
		}
	} else {
	
		if (isset($_POST['submit']) && strlen($_POST['submit'])>0) {
			//Here is a POST and dind't get the $_FILE for that reason show error;
			$message = Language::get('error_choose_file');
			if (isset($_POST['name']) && strlen($_POST['name'])>0) {
				$exercise_name = $_POST['name'];
			}
		}
	
		if (isset($_GET['delete'])) {
			$delete = $_GET['delete'];
			
			$exercise = $gestorBD->delete_exercise($course_id, $delete);
			if ($exercise) {
				//Eliminem el fitxer xml
				$target_path_file = dirname(__FILE__).DIRECTORY_SEPARATOR.$course_folder.DIRECTORY_SEPARATOR.$exercise['name_xml_file'].'.xml';
				if (file_exists($target_path_file)) {
					unlink($target_path_file);
					$message = Language::get('exercise_deleted_ok');
				} else {
					$message = Language::get('error_delete_exercise');
				}
			}
		} elseif (isset($_GET['enabled'])) {
			$enabled = $_GET['enabled'];
			$gestorBD->enable_exercise($enabled);
		} elseif (isset($_GET['update_exercise_form_id'])) {
			$update_exercise_form_id = $_GET['update_exercise_form_id'];
			$exercise = $gestorBD->get_exercise($update_exercise_form_id);
			if ($exercise && count($exercise)>'') {
				if (strlen($exercise_name)==0) { //Because if it has some value is because user indicated it
					$exercise_name = $exercise[0]['name'];
				}
				$exercise_id = $exercise[0]['id'];
			} else {
				$message = Language::get('error_getting_information_of_exercise');
			}
		}
	} 
	$array_exercises = $gestorBD->get_tandem_exercises($course_id, 0);
	$max_upload = (int)(ini_get('upload_max_filesize'));
	$max_post = (int)(ini_get('post_max_size'));
	$memory_limit = (int)(ini_get('memory_limit'));
	$upload_mb = min($max_upload, $max_post, $memory_limit);
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
<?php include_once dirname(__FILE__).'/js/google_analytics.php'?>
</head>
<body>

<!-- accessibility -->
	<div id="accessibility">
		<a href="#content" accesskey="s" title="Acceso directo al contenido"><?php echo Language::get('direct_access_to_content')?></a> | 
		<!--
		<a href="#" accesskey="n" title="Acceso directo al men� de navegaci�n">Acceso directo al men� de navegaci�n</a> | 
		<a href="#" accesskey="m" title="Mapa del sitio">Mapa del sitio</a> 
		-->
	</div>
	<!-- /accessibility -->
	
	<!-- /wrapper -->
	<div id="wrapper">
		<!-- main-container -->
  		<div id="main-container">
  			<!-- main -->
			<div id="main">
				<!-- content -->
				<?php if($message) echo '<div class="info">'.$message.'</div>'; ?>
				<div id="content">
					<div id="logo">
						<a href="#" title="<?php echo Language::get('tandem_logo')?>"><img src="css/images/logo_Tandem.png" alt="<?php echo Language::get('tandem_logo')?>" /></a>
					</div>
				<h4><a href="selectUserAndRoom.php">&larr;&nbsp;<?php echo Language::get('back')?></a></h4>
				<h1><?php echo Language::get('mange_exercises_tandem')?></h1>
				<form enctype="multipart/form-data" method="post" action="">
					<p><label><?php echo Language::get('exercise_name')?>: <input type="text" name="name" value="<?php echo $exercise_name ?>"/></label></p>
					<p><label><?php echo Language::get('choose_zip_file')?>: <input type="file" name="zip_file" /> Max. <?php echo $upload_mb; ?> MB</label></p>
					<p><label><?php echo Language::get('overrides_xml_file')?>: <select name="overrides_xml_file" ><option value="0"><?php echo Language::get('no')?></option><option value="1" <?php echo $exercise_id>0?'selected="selected"':''?>><?php echo Language::get('yes')?></option></select> </label></p>
					<input type="submit" name="submit" value="<?php echo Language::get('upload')?>" />
					<input type="hidden" name="id" value="<?php echo $exercise_id ?>" />
					</form>
					<div class="clear">&nbsp;</div>
					<?php if ($array_exercises && count($array_exercises)>0) {?>
					<div id="tableContainer" class="tableContainer">
						<table  class="scrollTable">
						<thead class="fixedHeader">
							<tr>
								<th><?php echo Language::get('exercise_name')?></th>
								<th><?php echo Language::get('name_xml_file')?></th>
								<th><?php echo Language::get('enabled')?></th>
								<th>&nbsp;</th>
								<th>&nbsp;</th>
							</tr>
						</thead>
						<tbody class="scrollContent">
							<?php $i=0;
							foreach ($array_exercises as $exercise) {?>
							<tr class="<?php echo $i%2==0?'normalRow':'alternateRow'?>">
								<td><?php echo $exercise['name']?></td>
								<td><?php echo $exercise['name_xml_file']?></td>
								<td><a href="manage_exercises_tandem.php?enabled=<?php echo $exercise['id']?>&action=<?php echo $exercise['enabled']?>"><?php echo Language::get($exercise['enabled']=='1'?'yes':'no')?></a></td>
								<td><a href="manage_exercises_tandem.php?update_exercise_form_id=<?php echo $exercise['id']?>"><?php echo Language::get('update')?></a></td>
								<td><a href="manage_exercises_tandem.php?delete=<?php echo $exercise['id']?>"><?php echo Language::get('delete')?></a></td>
							</tr>
							<?php 
							$i++;
							}?>
						 </tbody> 
						</table>
					</div>
					<?php } else {?>
						<p class="error"><?php echo Language::get('no_results_found')?></p>
					<?php }?>
			
					
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
			<div class="footer-tandem" title="<?php echo Language::get('tandem')?>"></div>
			<div class="footer-logos">
				<img src="img/logo_LLP.png" alt="Lifelong Learning Programme" />
				<img src="img/logo_EAC.png" alt="Education, Audiovisual &amp; Culture" />
				<img src="img/logo_speakapps.png" alt="Speakapps" />
			</div>
		</div>
	</div>
	    
	<!-- /footer -->
</body>
</html>
<?php } ?>