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
$message_cls = 'alert-error';
if (!isset($user_obj) || !isset($course_id) || !isset($course_folder) || !$user_obj->instructor) {
	//Tornem a l'index
	header ('Location: index.php');
} else {
	$exercise_name = '';
	$exercise_week = '';
	$exercise_type = 'all';
	$exercise_id = -1;
	$gestorBD	= new GestorBD();
	if(isset($_FILES["zip_file"]) && $_FILES["zip_file"]["name"]) {
		$filename = $_FILES["zip_file"]["name"];
		$source = $_FILES["zip_file"]["tmp_name"];
		$type = $_FILES["zip_file"]["type"];
		$exercise_week = isset($_POST["week"])?$_POST["week"]:0;
		$exercise_lang = isset($_POST["lang"])?$_POST["lang"]:'all';

		$name = explode(".", $filename);
		$accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
		foreach($accepted_types as $mime_type) {
			if($mime_type == $type) {
				$okay = true;
				break;
			}
		}

		$continue = strtolower($name[(count($name)-1)]) === 'zip' ? true : false;
		if(!$continue) {
			$message = $LanguageInstance->get('error_no_zip_format');
		} else {
			$target_path = dirname(__FILE__).DIRECTORY_SEPARATOR.$course_folder;

			$target_path_temp = $target_path.'/temp'.rand();
			if (!file_exists($target_path)) {
                mkdir($target_path, 0777, true);
            }
			if (!file_exists($target_path_temp)) {
                mkdir($target_path_temp, 0777, true);
            }
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
					$message = $LanguageInstance->getTag('file_exercise_xml_aready_exists','<strong>'.$name_xml_file.'</strong>');
					rrmdir($target_path_temp);
				} elseif (strlen($name_xml_file)==0) {
					$message = $LanguageInstance->get('main_xml_file_not_found');
					rrmdir($target_path_temp);
				}
				else
				{
					$delete = array();
					$enabled = 1;
					$id = $gestorBD->register_tandem_exercise($course_id, -1, $user_obj->id, $name_form, $name_xml_file, $enabled, $exercise_week, $exercise_lang);
					$target_path = dirname(__FILE__).DIRECTORY_SEPARATOR.$course_folder.DIRECTORY_SEPARATOR.$id;

					$delete = moveFromTempToCourseFolder($target_path_temp, $target_path, $delete);
					rrmdir($target_path_temp);

					$message = $LanguageInstance->getTag('zip_upload_ok',$filename);
					$message_cls = 'alert-info';
					$exercise_name = '';
					$exercise_id = -1;
				}
			} else {
				$message = $LanguageInstance->get('error_uploading_file');
			}
		}
	} else {

		if (isset($_POST['submit']) && strlen($_POST['submit'])>0) {
			//Here is a POST and dind't get the $_FILE for that reason show error;
			$message = $LanguageInstance->get('error_choose_file');
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
					$message = $LanguageInstance->get('exercise_deleted_ok');
					$message_cls = 'alert-info';
				} else {
					$message = $LanguageInstance->get('error_delete_exercise');
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
				$message = $LanguageInstance->get('error_getting_information_of_exercise');
			}
		}
	}
	$array_exercises = $gestorBD->get_tandem_exercises($course_id, 0);
	$max_upload = (int)(ini_get('upload_max_filesize'));
	$max_post = (int)(ini_get('post_max_size'));
	$memory_limit = (int)(ini_get('memory_limit'));
	$upload_mb = min($max_upload, $max_post, $memory_limit);
	$force_select_room = isset($_SESSION[FORCE_SELECT_ROOM]) && $_SESSION[FORCE_SELECT_ROOM];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Tandem</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
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
<script src="js/common.js"></script>
<?php include_once dirname(__FILE__).'/js/google_analytics.php'; ?>
<script>
$(document).ready(function(){
// victor - Lets change the go back link depending on if the custom parameter USE_WAITING_ROOM exists.
$("#GoBack").attr("href","<?php echo isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1 && !$force_select_room ? 'tandemInfo.php' : 'selectUserAndRoom.php' ?>");
});
</script>
</head>
<body>

<!-- accessibility -->
	<div id="accessibility">
		<a href="#content" accesskey="s" title="Acceso directo al contenido"><?php echo $LanguageInstance->get('direct_access_to_content')?></a> |
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
  			<?php if($message) echo '<div class="alert '.$message_cls.'" style="margin-bottom:0"><button type="button" class="close" aria-hidden="true">&#215;</button>'.$message.'</div>'; ?>
  			<!-- main -->
			<div id="main">
				<!-- content -->

				<?php /* if($message) echo '<div class="info">'.$message.'</div>'; */ ?>
				<div id="content">
					<a href="autoAssignTandemRoom.php" id='GoBack' class="tandem-btn-secundary btn-back"><span>&larr;</span>&nbsp;<?php echo $LanguageInstance->get('back')?></a>
					<div id="logo">
						<a href="#" title="<?php echo $LanguageInstance->get('tandem_logo')?>"><img src="css/images/logo_Tandem.png" alt="<?php echo $LanguageInstance->get('tandem_logo')?>" /></a>
					</div>

					<div class="clear">

						<h1 class="main-title"><?php echo $LanguageInstance->get('mange_exercises_tandem')?></h1>

                        <a href="editor/edit_exercise.php" class="tandem-btn btn-exercise"><i class="icon"></i><span><?php echo $LanguageInstance->get('new_exercise')?></span></a>
                        <a href="#" class="tandem-btn btn-exercise" id="btn-new-exercise"><i class="icon"></i><span><?php echo $LanguageInstance->get('import_exercise')?></span></a>
						<?php  if ($exercise_id != -1 ) { ?>
                            <a href="editor/edit_exercise.php?id=<?php echo $exercise_id?>" class="tandem-btn btn-exercise"><i class="icon"></i><span><?php echo $LanguageInstance->get('edit_exercise')?></span></a>
                            <a href="#" class="tandem-btn btn-exercise open" id="btn-edit-exercise"><i class="icon"></i><span><?php echo $LanguageInstance->get('reimport_exercise')?>: <em><?php echo $exercise_name ?></em></span></a>
						<?php } ?>
                        <a href="editor/tasks.php" class="tandem-btn btn-exercise"><span><?php echo $LanguageInstance->get('Manage Tasks')?></span></a>

						<form id="frm-new-exercise" enctype="multipart/form-data" method="post" action="" style="display:none">
							<div class="frm-group">
								<label class="frm-label"><?php echo $LanguageInstance->get('exercise_name')?>:</label>
								<input type="text" name="name" value="" />
							</div>
							<div class="frm-group">
								<label  class="frm-label" data-title-file="<?php echo $LanguageInstance->get('choose_zip_file')?>:" data-title-none="<?php echo $LanguageInstance->get('file_name')?>:"><?php echo $LanguageInstance->get('choose_zip_file')?>:</label>
								<span class="attach-input">
									<input type="text" value="" class="attach-input-text" placeholder="<?php echo $LanguageInstance->get('none_file_selected')?>" />
									<span class="attach-input-btn">
										<i class="icon"></i>
					                    <span aria-hidden="true"><?php echo $LanguageInstance->get('browse')?></span>
					                    <input type="file" name="zip_file" class="attach-input-file" />
					                </span>
					                <span class="attach-input-help">Max. <?php echo $upload_mb; ?> MB</span>
					            </span>
							</div>
							<?php if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1) {?>
							<div class="frm-group">
								<label class="frm-label"><?php echo $LanguageInstance->get('select week')?>:</label>
								<select name="week" >
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
									<option value="6">6</option>
									<option value="0"><?php echo $LanguageInstance->get('not apply')?></option>
								</select>
							</div>

							<?php } ?>
							<?php if (isset($_SESSION[USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE]) && $_SESSION[USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE]==1) {?>
							<div class="frm-group">
								<label class="frm-label"><?php echo $LanguageInstance->get('Language')?>:</label>
								<select name="lang" >
									<option value="all"><?php echo $LanguageInstance->get('All')?></option>
									<option value="en_US"><?php echo $LanguageInstance->get('English')?></option>
									<option value="es_ES"><?php echo $LanguageInstance->get('Spanish')?></option>
								</select>
							</div>

							<?php } ?>
							<div class="frm-foot">
								<input type="hidden" name="id" value="-1" />
								<input type="hidden" name="overrides_xml_file" value="1" />
								<input type="submit" name="submit" value="<?php echo $LanguageInstance->get('upload')?>" />
							</div>
						</form>

						<?php if ($exercise_id != -1 ) { ?>
						<form id="frm-edit-exercise" enctype="multipart/form-data" method="post" action="" style="display:block">
							<div class="frm-group">
								<label  class="frm-label"><?php echo $LanguageInstance->get('exercise_name')?>:</label>
								<input type="text" name="name" value="<?php echo $exercise_name ?>" />
							</div>
							<div class="frm-group">
								<label  class="frm-label" data-title-file="<?php echo $LanguageInstance->get('choose_zip_file')?>:" data-title-none="<?php echo $LanguageInstance->get('file_name')?>:"><?php echo $LanguageInstance->get('choose_zip_file')?>:</label>
								<span class="attach-input">
									<input type="text" value="" class="attach-input-text" placeholder="<?php echo $LanguageInstance->get('none_file_selected')?>" />
									<span class="attach-input-btn">
										<i class="icon"></i>
					                    <span aria-hidden="true"><?php echo $LanguageInstance->get('browse')?></span>
					                    <input type="file" name="zip_file" class="attach-input-file" />
					                </span>
					                <span class="attach-input-help">Max. <?php echo $upload_mb; ?> MB</span>
					            </span>
							</div>
							<div class="frm-foot">
								<input type="hidden" name="id" value="<?php echo $exercise_id ?>" />
								<input type="hidden" name="overrides_xml_file" value="1" />
								<input type="submit" name="submit" value="<?php echo $LanguageInstance->get('upload')?>" />
							</div>
						</form>
						<?php } ?>

						<div class="manage-area">
							<h3 class="secundary-title"><?php echo $LanguageInstance->get('exercises_list')?></h3>
							<?php if ($array_exercises && count($array_exercises)>0) {?>
							<!--<div id="tableContainer" class="tableContainer">-->
							<table  class="table">
								<thead>
									<tr>
										<th><?php echo $LanguageInstance->get('exercise_name')?></th>
										<th><?php echo $LanguageInstance->get('name_xml_file')?></th>
										<?php if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1) {?>
                                            <th><?php echo $LanguageInstance->get('Week')?></th>
										<?php } ?>
										<?php if (isset($_SESSION[USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE]) && $_SESSION[USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE]==1) {?>
                                            <th><?php echo $LanguageInstance->get('Language')?></th>
										<?php } ?>
										<th class="center"><?php echo $LanguageInstance->get('enabled')?></th>
										<th class="center"><?php echo $LanguageInstance->get('update')?></th>
										<th class="center"><?php echo $LanguageInstance->get('delete')?></th>
									</tr>
								</thead>
								<tbody>
									<?php $i=0;
									foreach ($array_exercises as $exercise) {?>
									<tr class="<?php echo $i%2==0?'normalRow':'alternateRow'?>">
										<td><?php echo $exercise['name']?></td>
										<td><?php echo $exercise['name_xml_file']?></td>
										<?php if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1) {?>
										<td><?php echo $exercise['week']?></td>
										<?php } ?>
										<?php if (isset($_SESSION[USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE]) && $_SESSION[USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE]==1) {?>
										<td><?php echo $exercise['lang']?></td>
										<?php } ?>
										<td class="center"><a href="manage_exercises_tandem.php?enabled=<?php echo $exercise['id']?>&action=<?php echo $exercise['enabled']?>"><?php echo $LanguageInstance->get($exercise['enabled']=='1'?'yes':'no')?></a></td>
										<td class="center"><a href="manage_exercises_tandem.php?update_exercise_form_id=<?php echo $exercise['id']?>" class="lnk-btn-edit" title="<?php echo $LanguageInstance->get('update')?>"><span class="visually-hidden"><?php echo $LanguageInstance->get('update')?></span></a></td>
										<td class="center"><a href="manage_exercises_tandem.php?delete=<?php echo $exercise['id']?>" class="lnk-btn-trash" title="<?php echo $LanguageInstance->get('delete')?>"><span class="visually-hidden"><?php echo $LanguageInstance->get('delete')?></span></a></td>
									</tr>
									<?php
									$i++;
									}?>
								 </tbody>
							</table>

							<!--</div>-->
							<?php } else {?>
								<div class="message">
									<p><strong><?php echo $LanguageInstance->get('no_results_found')?></strong></p>
								</div>
							<?php }?>
						</div>
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
				<!--img src="img/logo_LLP.png" alt="Lifelong Learning Programme" />
				<img src="img/logo_EAC.png" alt="Education, Audiovisual &amp; Culture" /-->
				<div style="float: left; margin-top: 0pt; text-align: justify; width: 600px;"><span style="font-size:9px;">This project has been funded with support from the Lifelong Learning Programme of the European Commission.  <br />
This site reflects only the views of the authors, and the European Commission cannot be held responsible for any use which may be made of the information contained therein.</span>
</div>
		 &nbsp;	<img src="css/images/EU_flag.jpg" alt="" />
				<img src="img/logo_speakapps.png" alt="Speakapps" />
			</div>
		</div>
	</div>

	<!-- /footer -->
</body>
</html>
<?php } ?>