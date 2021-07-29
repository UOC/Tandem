<?php
require_once dirname(__FILE__).'/../classes/lang.php';
require_once dirname(__FILE__).'/../classes/constants.php';
require_once dirname(__FILE__).'/../classes/gestorBD.php';
require_once dirname(__FILE__).'/../classes/utils.php';

$user_obj = $_SESSION[CURRENT_USER];
$course_id = $_SESSION[COURSE_ID];
$course_folder = $_SESSION[TANDEM_COURSE_FOLDER];
$message = false;
$message_cls = 'alert-error';
if (!isset($user_obj) || !isset($course_id) || !isset($course_folder) || !$user_obj->instructor) {
	//Tornem a l'index
	header ('Location: ../index.php');
} else {
	$exercise_name = '';
	$exercise_week = '';
	$exercise_type = 'all';
	$task_id = -1;
	$gestorBD	= new GestorBD();


    if (isset($_GET['delete'])) {
        $delete = $_GET['delete'];

        // TODO delete it
        if ($gestorBD->delete_task($course_id, $delete)) {

            $message = $LanguageInstance->get('Task successfully deleted');
        }
    }
    // TODO get tasks
	$array_tasks = array();// $gestorBD->get_tandem_exercises($course_id, 0);


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Tandem</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="all" href="../css/tandem.css" />
<link rel="stylesheet" type="text/css" media="all" href="../css/jquery-ui.css" />
<script src="../js/jquery-1.7.2.min.js"></script>
<script src="../js/jquery.ui.core.js"></script>
<script src="../js/jquery.ui.widget.js"></script>
<script src="../js/jquery.ui.button.js"></script>
<script src="../js/jquery.ui.position.js"></script>
<script src="../js/jquery.ui.autocomplete.js"></script>
<script src="../js/jquery.ui.datepicker.js"></script>
<script src="../js/jquery.colorbox-min.js"></script>
<script src="../js/common.js"></script>
<?php include_once dirname(__FILE__).'/../js/google_analytics.php'; ?>
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
					<a href="../manage_exercises_tandem.php" class="tandem-btn-secundary btn-back"><span>&larr;</span>&nbsp;<?php echo $LanguageInstance->get('back')?></a>
					<div id="logo">
						<a href="#" title="<?php echo $LanguageInstance->get('tandem_logo')?>"><img src="css/images/logo_Tandem.png" alt="<?php echo $LanguageInstance->get('tandem_logo')?>" /></a>
					</div>

					<div class="clear">

						<h1 class="main-title"><?php echo $LanguageInstance->get('Manage Tasks')?></h1>

                        <a href="edit_task.php" class="tandem-btn btn-exercise"><i class="icon"></i><span><?php echo $LanguageInstance->get('New Task')?></span></a>
                        

						<div class="manage-area">
							<h3 class="secundary-title"><?php echo $LanguageInstance->get('Tasks List')?></h3>
							<?php if ($array_tasks && count($array_tasks)>0) {?>
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
									foreach ($array_tasks as $exercise) {?>
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