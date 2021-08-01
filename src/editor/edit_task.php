<?php
/**
 * Created by PhpStorm.
 * User: antonibertranbellido
 * Date: 11/10/2018
 * Time: 23:55
 */
require_once dirname(__FILE__) . '/../classes/lang.php';

require_once __DIR__ . '/../classes/constants.php';
require_once __DIR__ . '/../classes/gestorBD.php';
require_once __DIR__ . '/../classes/utils.php';
require_once __DIR__ . '/../IMSBasicLTI/uoc-blti/lti_utils.php';

$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;

$gestorBD = new GestorBD();
if (empty($user_obj) || $user_obj->instructor != 1) {
    header('Location: ../index.php');
    die();
}
$message = false;

$message_cls = 'alert-error';
$task_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
if (isset($_POST['save'])) {
    $title = isset($_POST['title']) ? $_POST['title'] : '';
    $language = isset($_POST['language']) ? $_POST['language'] : '';
    $level = isset($_POST['level']) ? $_POST['level'] : '';
    $typology = isset($_POST['typology']) ? $_POST['typology'] : '';
    $active = isset($_POST['active']) ? $_POST['active'] : '';
    $timer_duration = isset($_POST['timer_duration']) ? $_POST['timer_duration'] : '';
    $descriptionA = isset($_POST['descriptionA']) ? $_POST['descriptionA'] : '';
    $imageA = isset($_POST['imageA']) ? $_POST['imageA'] : '';
    $imageA2 = isset($_POST['imageA2']) ? $_POST['imageA2'] : '';
    $descriptionB = isset($_POST['descriptionB']) ? $_POST['descriptionB'] : '';
    $imageB = isset($_POST['imageB']) ? $_POST['imageB'] : '';
    $imageB2 = isset($_POST['imageB2']) ? $_POST['imageB2'] : '';
    $solutionA = isset($_POST['solutionA']) ? $_POST['solutionA'] : '';
    $solutionImageA = isset($_POST['solutionImageA']) ? $_POST['solutionImageA'] : '';
    $solutionImageA2 = isset($_POST['solutionImageA2']) ? $_POST['solutionImageA2'] : '';
    $solutionB = isset($_POST['solutionB']) ? $_POST['solutionB'] : '';
    $solutionImageB = isset($_POST['solutionImageB']) ? $_POST['solutionImageB'] : '';
    $solutionImageB2 = isset($_POST['solutionImageB2']) ? $_POST['solutionImageB2'] : '';
    $task_id = $gestorBD->saveTask($task_id, $title, $language, $level, $typology, $active, $timer_duration, $descriptionA,
            $descriptionB, $solutionA, $solutionB, $user_obj->id, $course_id);

    if ($task_id > 0) {
        // Store files

        $message = $LanguageInstance->get('Saved successfully');
        $message_cls = 'alert-info';
        $files_uploaded = upload_files_task ($course_id, $task_id, array('imageA', 'imageA2', 'imageB', 'imageB2',
                'solutionImageA', 'solutionImageA2', 'solutionImageB', 'solutionImageB2'));

        $added_error_format = false;
        $added_error_uploading = false;
        foreach ($files_uploaded as $image => $result) {

            if (!$added_error_format && $result['result'] === FILE_FORMAT_ERROR) {
                $message .= '. ' . $LanguageInstance->get('Some files are not a supported images');
                $message_cls = 'alert-warning';
                $added_error_format = true;
            }
            if (!$added_error_uploading && $result['result'] === FILE_CAN_NOT_BE_UPLOADED) {
                $message .= '. ' . $LanguageInstance->get('error_uploading_file');
                $message_cls = 'alert-warning';
                $added_error_uploading = true;
            }
            if ($result['result'] === FILE_UPLOADED) {
                $file_path = $result['path'];
                $gestorBD->saveTaskImage($task_id, $image, $file_path);
            }
        }
        $message = $LanguageInstance->get('Saved successfully');
        $message_cls = 'alert-info';
    } else {
        $message = $LanguageInstance->get('Error storing data');
    }

}
$levels = array('A1', 'A2', 'B1', 'B2', 'C1', 'C2');
$typologies = array('Other', 'Decision Making', 'Description of different pictures',
        'Giving directions', 'Make a guess', 'Object description', 'Problem-solving', 'Ranking task',
        'Role-play', 'Spot the difference');

$max_upload = (int)(ini_get('upload_max_filesize'));
$max_post = (int)(ini_get('post_max_size'));
$memory_limit = (int)(ini_get('memory_limit'));
$upload_mb = min($max_upload, $max_post, $memory_limit);

if ($task_id > 0) {
    $task = $gestorBD->getTask($task_id);
    $task['imageA'] = get_file_name($task['imageA']);
    $task['imageA2'] = get_file_name($task['imageA2']);
    $task['imageB'] = get_file_name($task['imageB']);
    $task['imageB2'] = get_file_name($task['imageB2']);
    $task['solutionImageA'] = get_file_name($task['solutionImageA']);
    $task['solutionImageA2'] = get_file_name($task['solutionImageA2']);
    $task['solutionImageB'] = get_file_name($task['solutionImageB']);
    $task['solutionImageB2'] = get_file_name($task['solutionImageB2']);

} else {
    $task = array();
    $task['id'] = -1;
    $task['title'] = '';
    $task['language'] = 'en_US';
    $task['level'] = '';
    $task['typology'] = '';
    $task['timer_duration'] = '';
    $task['active'] = 1;
    $task['descriptionA'] = '';
    $task['imageA'] = '';
    $task['imageA2'] = '';
    $task['descriptionB'] = '';
    $task['imageB'] = '';
    $task['imageB2'] = '';
    $task['solutionA'] = '';
    $task['solutionImageA'] = '';
    $task['solutionImageA2'] = '';
    $task['solutionB'] = '';
    $task['solutionImageB'] = '';
    $task['solutionImageB2'] = '';

}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Tandem</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" media="all" href="../css/tandem.css"/>
    <!-- include libraries(jQuery, bootstrap) -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">

    <!-- include summernote css/js -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

</head>
<body>
<!-- accessibility -->
<div id="accessibility">
    <a href="#content" accesskey="s"
       title="Acceso directo al contenido"><?php echo $LanguageInstance->get('direct_access_to_content') ?></a> |
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
        <?php if ($message) {
            echo '<div class="alert ' . $message_cls .
                    '" style="margin-bottom:0"><button type="button" class="close" aria-hidden="true">&#215;</button>' . $message .
                    '</div>';
        } ?>
        <!-- main -->
        <div id="main">
            <div id="content">
                <a href="./tasks.php" id='GoBack'
                   class="tandem-btn-secundary btn-back"><span>&larr;</span>&nbsp;<?php echo $LanguageInstance->get('back') ?></a>
                <div id="logo">
                    <a href="#" title="<?php echo $LanguageInstance->get('tandem_logo') ?>"><img src="../css/images/logo_Tandem.png"
                                                                                                 alt="<?php echo $LanguageInstance->get('tandem_logo') ?>"/></a>
                </div>

                <div class="clear">

                    <h1 class="main-title"><?php echo $LanguageInstance->get($task_id > 0 ? 'Edit Task' : 'New Task') ?></h1>


                    <div class='row'>
                        <div class="col-md-12">
                            <form class="form" method="post" enctype="multipart/form-data" >
                                <div class="frm-group">
                                    <label for="title" class="frm-label"><?php echo $LanguageInstance->get('Title') ?>:</label>
                                    <input type="text" id="title" name="title"
                                           value="<?php echo $task['title'] ?>">
                                </div>
                                <div class="frm-group">
                                    <label for="language" class="frm-label"><?php echo $LanguageInstance->get('Language') ?>:</label>
                                    <select id="language" name="language">
                                        <option value="ca_ES" <?php echo 'ca_ES' == $task['language'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('Catalan') ?></option>
                                        <option value="en_US" <?php echo 'en_US' == $task['language'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('English') ?></option>
                                        <option value="es_ES" <?php echo 'es_ES' == $task['language'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('Spanish') ?></option>
                                    </select>
                                </div>
                                <div class="frm-group">
                                    <label for="level" class="frm-label"><?php echo $LanguageInstance->get('Level') ?>:</label>
                                    <select id="level" name="level">
                                        <?php
                                        foreach ($levels as $level) { ?>
                                            <option value="<?php echo $level?>" <?php echo $level == $task['level'] ? 'selected' :
                                                    '' ?>><?php echo $LanguageInstance->get($level) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="frm-group">
                                    <label for="typology" class="frm-label"><?php echo $LanguageInstance->get('Typology') ?>:</label>
                                    <select id="typology" name="typology">
                                        <?php
                                        foreach ($typologies as $typology) { ?>
                                        <option value="<?php echo $typology?>" <?php echo $typology == $task['typology'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get($typology) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="frm-group">
                                    <label for="timer_duration" class="frm-label"><?php echo $LanguageInstance->get('timer_duration') ?>:</label>
                                    <select id="timer_duration" name="timer_duration">
                                        <option value="0"><?php echo $LanguageInstance->get('not apply')?></option>
                                        <?php for ($i=1; $i<=30; $i++) { ?>
                                        <option value=" <?php echo $i ?>" <?php echo $i == $task['timer_duration'] ? 'selected' :
                                                '' ?>><?php echo $i . ' ' . $LanguageInstance->get('minutes'.($i>1?'s':'')) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="frm-group">
                                    <label for="active" class="frm-label"><?php echo $LanguageInstance->get('Enabled') ?>:</label>
                                    <select id="active" name="active">
                                        <option value="1" <?php echo '1' == $task['active'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('Yes') ?></option>
                                        <option value="0" <?php echo '0' == $task['active'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('No') ?></option>
                                    </select>
                                </div>
                                <div class="frm-group">
                                    <label for="descriptionA"><?php echo $LanguageInstance->get('Description A') ?>:</label>
                                    <textarea class="summernote" name="descriptionA" cols="50" rows="10" id="descriptionA"><?php echo $task['descriptionA']?></textarea>
                                </div>
                                <div class="frm-group">
                                    <label  class="frm-label" data-title-file="<?php echo $LanguageInstance->get('Choose Image File')?>:" data-title-none="<?php echo $LanguageInstance->get('Image A')?>:"><?php echo $LanguageInstance->get('Image A')?>:</label>
                                    <span class="attach-input">
									<input type="text" value="" class="attach-input-text" placeholder="<?php echo empty($task['imageA'])?$LanguageInstance->get('Choose Image File'):$task['imageA']?>" />
									<span class="attach-input-btn">
										<i class="icon"></i>
					                    <span aria-hidden="true"><?php echo $LanguageInstance->get('browse')?></span>
					                    <input type="file" name="imageA" class="attach-input-file" value="<?php echo $task['imageA']?>"/>
					                </span>
					                <span class="attach-input-help">Max. <?php echo $upload_mb; ?> MB</span>
					            </span>
                                </div>
                                <div class="frm-group">
                                    <label  class="frm-label" data-title-file="<?php echo $LanguageInstance->get('Choose Image File')?>:" data-title-none="<?php echo $LanguageInstance->get('Image A 2')?>:"><?php echo $LanguageInstance->get('Image A 2')?>:</label>
                                    <span class="attach-input">
									<input type="text" value="" class="attach-input-text" placeholder="<?php echo empty($task['imageA2'])?$LanguageInstance->get('Choose Image File'):$task['imageA2']?>" />
									<span class="attach-input-btn">
										<i class="icon"></i>
					                    <span aria-hidden="true"><?php echo $LanguageInstance->get('browse')?></span>
					                    <input type="file" name="imageA2" class="attach-input-file" />
					                </span>
					                <span class="attach-input-help">Max. <?php echo $upload_mb; ?> MB</span>
					            </span>
                                </div>
                                <div class="frm-group">
                                    <label for="descriptionB"><?php echo $LanguageInstance->get('Description B') ?> <small><?php echo $LanguageInstance->get('(Leave emtpy if it\'s the same as A)') ?></small>:</label>
                                    <textarea class="summernote" name="descriptionB" cols="50" rows="10" id="descriptionB"><?php echo $task['descriptionB']?></textarea>
                                </div>
                                <div class="frm-group">
                                    <label  class="frm-label" data-title-file="<?php echo $LanguageInstance->get('Choose Image File')?>:" data-title-none="<?php echo $LanguageInstance->get('Image B')?>:"><?php echo $LanguageInstance->get('Image B')?> <small><?php echo $LanguageInstance->get('(Leave emtpy if it\'s the same as A)') ?></small>:</label>
                                    <span class="attach-input">
									<input type="text" value="" class="attach-input-text" placeholder="<?php echo empty($task['imageB'])?$LanguageInstance->get('Choose Image File'):$task['imageB']?>" />
									<span class="attach-input-btn">
										<i class="icon"></i>
					                    <span aria-hidden="true"><?php echo $LanguageInstance->get('browse')?></span>
					                    <input type="file" name="imageB" class="attach-input-file" />
					                </span>
					                <span class="attach-input-help">Max. <?php echo $upload_mb; ?> MB</span>
					            </span>
                                </div>
                                <div class="frm-group">
                                    <label  class="frm-label" data-title-file="<?php echo $LanguageInstance->get('Choose Image File')?>:" data-title-none="<?php echo $LanguageInstance->get('Image B 2')?>:"><?php echo $LanguageInstance->get('Image B 2')?> <small><?php echo $LanguageInstance->get('(Leave emtpy if it\'s the same as A)') ?></small>:</label>
                                    <span class="attach-input">
									<input type="text" value="" class="attach-input-text" placeholder="<?php echo empty($task['imageB2'])?$LanguageInstance->get('Choose Image File'):$task['imageB2']?>" />
									<span class="attach-input-btn">
										<i class="icon"></i>
					                    <span aria-hidden="true"><?php echo $LanguageInstance->get('browse')?></span>
					                    <input type="file" name="imageB2" class="attach-input-file" />
					                </span>
					                <span class="attach-input-help">Max. <?php echo $upload_mb; ?> MB</span>
					            </span>
                                </div>


                                <div class="frm-group">
                                    <label for="solutionA"><?php echo $LanguageInstance->get('Solution A') ?>:</label>
                                    <textarea class="summernote" name="solutionA" cols="50" rows="10" id="solutionA"><?php echo $task['solutionA']?></textarea>
                                </div>
                                <div class="frm-group">
                                    <label  class="frm-label" data-title-file="<?php echo $LanguageInstance->get('Choose Image File')?>:" data-title-none="<?php echo $LanguageInstance->get('Image A')?>:"><?php echo $LanguageInstance->get('Solution Image A')?>:</label>
                                    <span class="attach-input">
									<input type="text" value="" class="attach-input-text" placeholder="<?php echo empty($task['solutionImageA'])?$LanguageInstance->get('Choose Image File'):$task['solutionImageA']?>" />
									<span class="attach-input-btn">
										<i class="icon"></i>
					                    <span aria-hidden="true"><?php echo $LanguageInstance->get('browse')?></span>
					                    <input type="file" name="solutionImageA" class="attach-input-file" />
					                </span>
					                <span class="attach-input-help">Max. <?php echo $upload_mb; ?> MB</span>
					            </span>
                                </div>
                                <div class="frm-group">
                                    <label  class="frm-label" data-title-file="<?php echo $LanguageInstance->get('Choose Image File')?>:" data-title-none="<?php echo $LanguageInstance->get('Image A 2')?>:"><?php echo $LanguageInstance->get('Solution Image A 2')?>:</label>
                                    <span class="attach-input">
									<input type="text" value="" class="attach-input-text" placeholder="<?php echo empty($task['solutionImageA2'])?$LanguageInstance->get('Choose Image File'):$task['solutionImageA2']?>" />
									<span class="attach-input-btn">
										<i class="icon"></i>
					                    <span aria-hidden="true"><?php echo $LanguageInstance->get('browse')?></span>
					                    <input type="file" name="solutionImageA2" class="attach-input-file" />
					                </span>
					                <span class="attach-input-help">Max. <?php echo $upload_mb; ?> MB</span>
					            </span>
                                </div>
                                <div class="frm-group">
                                    <label for="solutionB"><?php echo $LanguageInstance->get('Solution B') ?> <small><?php echo $LanguageInstance->get('(Leave emtpy if it\'s the same as A)') ?></small>:</label>
                                    <textarea class="summernote" name="solutionB" cols="50" rows="10" id="solutionB"><?php echo $task['solutionB']?></textarea>
                                </div>
                                <div class="frm-group">
                                    <label  class="frm-label" data-title-file="<?php echo $LanguageInstance->get('Choose Image File')?>:" data-title-none="<?php echo $LanguageInstance->get('Image B')?>:"><?php echo $LanguageInstance->get('Solution Image B')?> <small><?php echo $LanguageInstance->get('(Leave emtpy if it\'s the same as A)') ?></small>:</label>
                                    <span class="attach-input">
									<input type="text" value="" class="attach-input-text" placeholder="<?php echo empty($task['solutionImageB'])?$LanguageInstance->get('Choose Image File'):$task['solutionImageB']?>" />
									<span class="attach-input-btn">
										<i class="icon"></i>
					                    <span aria-hidden="true"><?php echo $LanguageInstance->get('browse')?></span>
					                    <input type="file" name="solutionImageB" class="attach-input-file" />
					                </span>
					                <span class="attach-input-help">Max. <?php echo $upload_mb; ?> MB</span>
					            </span>
                                </div>
                                <div class="frm-group">
                                    <label  class="frm-label" data-title-file="<?php echo $LanguageInstance->get('Choose Image File')?>:" data-title-none="<?php echo $LanguageInstance->get('Image B 2')?>:"><?php echo $LanguageInstance->get('Solution Image B 2')?> <small><?php echo $LanguageInstance->get('(Leave emtpy if it\'s the same as A)') ?></small>:</label>
                                    <span class="attach-input">
									<input type="text" value="" class="attach-input-text" placeholder="<?php echo empty($task['solutionImageB'])?$LanguageInstance->get('Choose Image File'):$task['solutionImageB2']?>" />
									<span class="attach-input-btn">
										<i class="icon"></i>
					                    <span aria-hidden="true"><?php echo $LanguageInstance->get('browse')?></span>
					                    <input type="file" name="solutionImageB2" class="attach-input-file" />
					                </span>
					                <span class="attach-input-help">Max. <?php echo $upload_mb; ?> MB</span>
					            </span>
                                </div>
                                

                                <div class="clear"></div>

                                <input type="submit" class="tandem-btn"
                                        name="save" value="<?php echo $LanguageInstance->get('Submit') ?>" />
                                <input type="hidden" name="id" value="<?php echo $task_id ?>">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
<!--script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script-->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script src="../js/common.js"></script>

<?php include_once __DIR__ . '/../js/google_analytics.php'; ?>
<script type="application/javascript">
    $(document).ready(function() {
        $('.summernote').summernote(
            {height: 150,                 // set editor height
            minHeight: null,             // set minimum height of editor
            maxHeight: null,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['view', ['fullscreen', 'codeview', 'help']],
            ]
            });
    });
</script>
</body>
</html>