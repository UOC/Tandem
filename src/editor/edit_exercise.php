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
    $language = isset($_POST['language']) ? $_POST['language'] : '';
    $level = isset($_POST['level']) ? $_POST['level'] : '';
    $active = isset($_POST['active']) ? $_POST['active'] : '';
    $exercise_id = $gestorBD->saveExercise($exercise_id, $title, $language, $level, $active);

    if ($exercise_id > 0) {
        $message = $LanguageInstance->get('Saved successfully');
        $message_cls = 'alert-info';
    } else {

    }

}
$exercise = array();
$exercise['id'] = -1;
$exercise['title'] = '';
$exercise['language'] = 'en_US';
$exercise['active'] = 1;
$exercise['level'] = '';
if ($exercise_id > 0) {
    $exercise = $gestorBD->get_questions_quiz_manage($exercise_id);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Tandem</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" media="all" href="../css/tandem.css"/>
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

                    <h1 class="main-title"><?php echo $LanguageInstance->get($exercise_id > 0 ? 'edit_exercise' : 'new_exercise') ?></h1>


                    <div class='row'>
                        <div class="col-md-12">
                            <form class="form" method="post">
                                <div class="frm-group">
                                    <label for="title" class="frm-label"><?php echo $LanguageInstance->get('Title') ?>:</label>
                                    <input type="text" id="title" name="title"
                                           value="<?php echo $exercise['title'] ?>">
                                </div>
                                <div class="frm-group">
                                    <label for="language" class="frm-label"><?php echo $LanguageInstance->get('Language') ?>:</label>
                                    <select id="language" name="language">
                                        <option value="ca_ES" <?php echo 'ca_ES' == $exercise['language'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('Catalan') ?></option>
                                        <option value="en_US" <?php echo 'en_US' == $exercise['language'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('English') ?></option>
                                        <option value="es_ES" <?php echo 'es_ES' == $exercise['language'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('Spanish') ?></option>
                                        <option value="es_ES" <?php echo 'fr_FR' == $exercise['language'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('French') ?></option>
                                    </select>
                                </div>
                                <div class="frm-group">
                                    <label for="level" class="frm-label"><?php echo $LanguageInstance->get('Level') ?>:</label>
                                    <select id="level" name="level">
                                        <option value="A1" <?php echo 'A1' == $exercise['level'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('A1') ?></option>
                                        <option value="A2" <?php echo 'A2' == $exercise['level'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('A2') ?></option>
                                        <option value="B1" <?php echo 'B1' == $exercise['level'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('B1') ?></option>
                                        <option value="B2" <?php echo 'B2' == $exercise['level'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('B2') ?></option>
                                        <option value="B1" <?php echo 'C1' == $exercise['level'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('C1') ?></option>
                                        <option value="B2" <?php echo 'C2' == $exercise['level'] ? 'selected' :
                                                '' ?>><?php echo $LanguageInstance->get('C2') ?></option>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>

<?php include_once __DIR__ . '/../js/google_analytics.php'; ?>
</body>
</html>