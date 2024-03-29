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
$exercise_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
if (isset($_POST['save'])) {
    $title = isset($_POST['title']) ? $_POST['title'] : '';
    $lang = isset($_POST['lang']) ? $_POST['lang'] : '';
    $week = isset($_POST['week']) ? $_POST['week'] : '';
    $level = isset($_POST['level']) ? $_POST['level'] : '';
    $active = isset($_POST['active']) ? $_POST['active'] : '';
    $exercise_id = $gestorBD->saveExercise($exercise_id, $title, $lang, $level, $active, $course_id, $week, $user_obj->id);

    if ($exercise_id > 0) {
        $message = $LanguageInstance->get('Saved successfully');
        $course_folder = $_SESSION[TANDEM_COURSE_FOLDER];
        generate_image_from_repository($exercise_id, $gestorBD, $course_id, $course_folder);
        $message_cls = 'alert-info';
    } else {
        $message = $LanguageInstance->get('Error storing data');
    }

}
$levels = array('A1', 'A2', 'B1', 'B2', 'C1', 'C2');
$weeks = array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '0' => $LanguageInstance->get('not apply'));
$langs = array('all' => $LanguageInstance->get('All'), 'en_US' => $LanguageInstance->get('English'), 'es_ES' => $LanguageInstance->get('Spanish'));
$exercise = array();
$exercise['id'] = -1;
$exercise['title'] = '';
$exercise['lang'] = 'all';
$exercise['week'] = '';
$exercise['active'] = 1;
$exercise['level'] = '';
$availableTasks = array();
if ($exercise_id > 0) {
    $exercise_array = $gestorBD->get_exercise($exercise_id);
    $exercise_id = $exercise_array[0]['id'];
    if ($exercise_array[0]['imported'] == 1) {
        header ('Location: ../manage_exercises_tandem.php?update_exercise_form_id='.$exercise_id);
        exit();
    } else {

        $data = $gestorBD->getExerciseCourseData($exercise_id, $course_id);
        $exercise['id'] = $exercise_id;
        $exercise['title'] = $exercise_array[0]['name'];
        $exercise['lang'] = $data ? $data['lang'] : 'all';
        $exercise['week'] = $data ? $data['week'] : '';
        $exercise['active'] = $exercise_array[0]['enabled'];
        $exercise['level'] = $exercise_array[0]['level'];

    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Tandem</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" media="all" href="../css/tandem.css"/>
    <link rel="stylesheet" type="text/css" media="all" href="../css/spinner.css"/>
    <link rel="stylesheet" type="text/css" media="all" href="../css/jquery-ui.css"/>
    <script src="../js/jquery-1.7.2.min.js"></script>
    <script src="../js/jquery.ui.core.js"></script>
    <script src="../js/jquery.ui.widget.js"></script>
    <script src="../js/jquery.ui.button.js"></script>
    <script src="../js/jquery.ui.position.js"></script>
    <script src="../js/jquery.ui.autocomplete.js"></script>
    <script src="../js/jquery.ui.datepicker.js"></script>
    <script src="../js/jquery.colorbox-min.js"></script>
    <script src="../js/common.js"></script>
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
                <a href="../manage_exercises_tandem.php"
                   class="tandem-btn-secundary btn-back"><span>&larr;</span>&nbsp;<?php echo $LanguageInstance->get('back') ?></a>
                <div id="logo">
                    <a href="#" title="<?php echo $LanguageInstance->get('tandem_logo') ?>"><img src="../css/images/logo_Tandem.png"
                                                                                                 alt="<?php echo $LanguageInstance->get('tandem_logo') ?>"/></a>
                </div>

                <div class="clear">

                    <h1 class="main-title"><?php echo $LanguageInstance->get($exercise_id > 0 ? 'Edit exercise' : 'New exercise') ?></h1>


                    <div class='row'>
                        <div class="col-md-12">
                            <form class="form" method="post">
                                <div class="frm-group">
                                    <label for="title" class="frm-label"><?php echo $LanguageInstance->get('Title') ?>:</label>
                                    <input type="text" id="title" name="title"
                                           value="<?php echo $exercise['title'] ?>">
                                </div>
                                <?php if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1) {?>
                                    <div class="frm-group">
                                        <label class="frm-label"><?php echo $LanguageInstance->get('select week')?>:</label>
                                        <select name="week" >
                                            <?php
                                            foreach ($weeks as $key => $week) { ?>
                                                <option value="<?php echo $key?>" <?php echo $key == $exercise['week'] ? 'selected' :
                                                        '' ?>><?php echo $week ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                <?php } ?>
                                <?php if (isset($_SESSION[USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE]) && $_SESSION[USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE]==1) {?>
                                <div class="frm-group">
                                    <label for="lang" class="frm-label"><?php echo $LanguageInstance->get('Language') ?>:</label>
                                    <select id="lang" name="lang">
                                        <?php
                                        foreach ($langs as $key => $lang) { ?>
                                            <option value="<?php echo $key?>" <?php echo $key == $exercise['lang'] ? 'selected' :
                                                    '' ?>><?php echo $lang ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <?php } ?>
                                <div class="frm-group">
                                    <label for="level" class="frm-label"><?php echo $LanguageInstance->get('Level') ?>:</label>
                                    <select id="level" name="level">
                                        <?php
                                        foreach ($levels as $level) { ?>
                                            <option value="<?php echo $level?>" <?php echo $level == $exercise['level'] ? 'selected' :
                                                    '' ?>><?php echo $LanguageInstance->get($level) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="frm-group">
                                    <label for="active" class="frm-label"><?php echo $LanguageInstance->get('Enabled') ?>:</label>
                                    <select id="active" name="active">
                                        <option value="1" <?php echo '1' == $exercise['active'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('Yes') ?></option>
                                        <option value="0" <?php echo '0' == $exercise['active'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('No') ?></option>
                                    </select>
                                </div>
                                <?php if ($exercise_id > 0) { ?>
                                    <div class="clear"></div>
                                    <div class="frm-group">
                                        <h3><?php echo $LanguageInstance->get('Linked Tasks') ?></h3>
                                    </div>
                                    <div id="linked_tasks">
                                    </div>
                                    <div class="clear"></div>
                                    <div class="frm-group">
                                        <h3><?php echo $LanguageInstance->get('Available Tasks') ?></h3>
                                    </div>
                                    <div id="available_tasks">
                                    </div>
                                <?php } ?>
                                <div class="clear"></div>

                                <input type="submit" class="tandem-btn"
                                        name="save" value="<?php echo $LanguageInstance->get('Submit') ?>" />
                                <input type="hidden" name="id" value="<?php echo $exercise_id ?>">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>

<?php include_once __DIR__ . '/../js/google_analytics.php'; ?>
<?php if ($exercise_id > 0) { ?>
    <script language="JavaScript">
        const exerciseId = <?php echo $exercise_id?>;
        var spinner = "<table class=\"table\"><tr><th colspan=\"100%\"><strong><?php echo $LanguageInstance->get('Processing') ?></strong><div class=\"spinner-grow spinner-grow-sm\" role=\"status\" aria-hidden=\"true\"></div></table></tr></th>";
        var sortData;
        let target = $('#linked_tasks');
        $(document).ready(function() {
            target.sortable({
                items: "tr",
                cursor: 'move',
                opacity: 0.6,
                update: function () {
                    sortData = target.sortable('toArray', {attribute: 'data-id'})
                    sortLinkedTasks();
                }
            });
            getLinkedTasks();
            getAvailableTasks();


        });


        function getLinkedTasks(){
            $.ajax({
                url: "linkedTasks.php",
                type:"POST",
                data:{
                    exerciseId: exerciseId,
                },
                beforeSend: function() {
                    $("#linked_tasks").html(spinner);
                },
                success:function(response){
                    $("#linked_tasks").html(response);
                    sortData = target.sortable('toArray',{ attribute: 'data-id'});
                },
            });
        }
        function getAvailableTasks(){

            $.ajax({
                url: "availableTasks.php",
                type:"POST",
                data:{
                    exerciseId: exerciseId
                },
                beforeSend: function() {
                    $("#available_tasks").html(spinner);
                },
                success:function(response){
                    $("#available_tasks").html(response);
                    sortData = target.sortable('toArray',{ attribute: 'data-id'});
                },
            });
        }

        function sortLinkedTasks(){

            let idString = sortData.join(',');

            $.ajax({
                url:'sortTasks.php',
                method:'POST',
                data:{
                    exerciseId: exerciseId,
                    ids: idString
                },
                beforeSend: function() {
                    $("#linked_tasks").html(spinner);
                },
                success:function(){
                    getLinkedTasks();
                }
            })
        }

        function linkTask(taskId){

            $.ajax({
                url:'linkTask.php',
                method:'POST',
                data:{
                    exerciseId: exerciseId,
                    taskId: taskId,
                    unlinkTask: 0
                },
                beforeSend: function() {
                    $("#available_tasks").html(spinner);
                },
                success:function(){
                    getLinkedTasks();
                    getAvailableTasks();

                }
            })
        }
        function unlinkTask(taskId){

            const index = sortData.indexOf(taskId.toString());
            if (index > -1){
                sortData.splice(index, 1);
            }
            $.ajax({
                url:'linkTask.php',
                method:'POST',

                data:{
                    exerciseId: exerciseId,
                    taskId: taskId,
                    unlink: 1
                },
                beforeSend: function() {
                    $("#linked_tasks").html(spinner);
                },
                success:function(){
                    sortLinkedTasks();
                    getAvailableTasks();
                },
            })
        }

    </script>
<?php } ?>
</body>
</html>