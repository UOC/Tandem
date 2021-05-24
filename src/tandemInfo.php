<?php

require_once dirname(__FILE__) . '/classes/lang.php';
$select_room = isset($_GET['select_room']) && $_GET['select_room'] == 1;
$goto = defined('BBB_SECRET') ? 'autoAssignTandemRoom' : 'preWaitingRoom';
if ($select_room) {
    $goto = 'selectUserAndRoom';
}

$enabledWebRTC = false;

require_once __DIR__ . '/classes/constants.php';
require_once __DIR__ . '/classes/gestorBD.php';
require_once __DIR__ . '/IMSBasicLTI/uoc-blti/lti_utils.php';
include_once __DIR__ . '/classes/pdf.php';
require_once __DIR__ . '/classes/IntegrationTandemBLTI.php';

$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;

$gestorBD = new GestorBD();
if ((isset($_GET['lang']) || (defined('USE_WAITING_ROOM_NO_TEAMS') && !empty($_SESSION[USE_WAITING_ROOM_NO_TEAMS]))) && !empty($_GET['force'])) {
    $_SESSION[LANG] = $_GET['lang'];
    // update user lang
	$gestorBD->update_user_course_language( $user_obj->id, $course_id, $_GET['lang'] );

    header('Location: ' . $goto . '.php');
    die();
}

if (empty($user_obj) || $user_obj->instructor != 1) {
    header('Location: index.php');
    die();
}

$tandemFailedSuccessByDateStart = !empty($_REQUEST['tandemFailedSuccessByDateStart']) ? $_REQUEST['tandemFailedSuccessByDateStart'] : date('Y-m-d');
$tandemFailedSuccessByDateEnd = !empty($_REQUEST['tandemFailedSuccessByDateEnd']) ? $_REQUEST['tandemFailedSuccessByDateEnd'] : date('Y-m-d');
$currentActiveTandems = $gestorBD->currentActiveTandems($course_id);
$getUsersWaiting = 0;
$getUsersWaitingEs = 0;
$getUsersWaitingEn = 0;

if ($_SESSION[USE_WAITING_ROOM_NO_TEAMS]) {
    $getUsersWaiting = $gestorBD->getUsersWaitingByLanguage($course_id, '', true);
} else {
    $getUsersWaitingEs = $gestorBD->getUsersWaitingByLanguage($course_id, 'es_ES');
    $getUsersWaitingEn = $gestorBD->getUsersWaitingByLanguage($course_id, 'en_US');
}

$tandemByDate = $gestorBD->getNumtandemsByDate(date('Y-m-d'), $course_id);
$getNumOfSuccessFailedTandems = $gestorBD->getNumOfSuccessFailedTandems($course_id, $tandemFailedSuccessByDateStart, $tandemFailedSuccessByDateEnd);
$stats = $gestorBD->get_stats_tandem_by_date($course_id, $tandemFailedSuccessByDateStart, $tandemFailedSuccessByDateEnd);
$getCountAllTandemsByDate = $gestorBD->getCountAllTandemsByDate($course_id);
$getCountAllUnFinishedTandemsByDate = $gestorBD->getCountAllUnFinishedTandemsByDate($course_id);
$getFeedbackStats = $gestorBD->getFeedbackStats($course_id, $tandemFailedSuccessByDateStart, $tandemFailedSuccessByDateEnd);

$peopleWaitedWithoutTandem = $gestorBD->peopleWaitedWithoutTandem($course_id, $tandemFailedSuccessByDateStart, $tandemFailedSuccessByDateEnd);

if (isset($_POST['startDateCurrentRanking'], $_POST['endDateCurrentRanking'])) {

    $gestorBD->set_course_start_end_date_ranking($course_id, $_POST['startDateCurrentRanking'], $_POST['endDateCurrentRanking'],
        intval($_POST['startHourCurrentRanking']), intval($_POST['endHourCurrentRanking']));
	$usersRanking = $gestorBD->getUsersRanking($course_id, $_SESSION[USE_WAITING_ROOM_NO_TEAMS]);
	$winners = $gestorBD->getWinnersPreviousWeeksUsersRanking( $course_id );
	foreach ($usersRanking as $lang => $items) {
	    switch ($lang) {
            case 'en':
                $lang = 'en_US';
		break;
            case 'es':
                $lang = 'es_ES';
		break;
            default:
                $lang = '';
        }
	    foreach ($items as $key => $item) {
		    $winners[$key] = $lang;
		    break;
        }
    }
    $gestorBD->delete_previous_ranking($course_id);
    $gestorBD->setWinnersBadge( $course_id, $winners);
    $gestorBD->updateAllUsersRankingPoints($course_id);
}

$course = $gestorBD->get_course_by_id($course_id);
$startDateCurrentRanking = '';
$startHourCurrentRanking = 0;
if (!empty($course['startDateRanking'])) {
    $startDateCurrentRanking = $course['startDateRanking'];
}
if (!empty($course['startHourRanking'])) {
    $startHourCurrentRanking = intval($course['startHourRanking']);
}
$endDateCurrentRanking = '';
$endHourCurrentRanking = 0;
if (!empty($course['endDateRanking'])) {
    $endDateCurrentRanking = $course['endDateRanking'];
}
if (!empty($course['endHourRanking'])) {
    $endHourCurrentRanking = intval($course['endHourRanking']);
}
$show_quiz = true;//isset($_SESSION[USE_WAITING_ROOM]) ? $_SESSION[USE_WAITING_ROOM] : false;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" media="all" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" media="all" href="css/tandem-waiting-room.css">
    <link rel="stylesheet" type="text/css" media="all" href="css/slider.css"/>
    <link rel="stylesheet" type="text/css" media="all" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" type="text/css" media="all" href="css/tandemInfo.css"/>
</head>
<body>
<div class='container'>
    <div class='row'>
        <div class='col-md-12 text-right'>
            <p><a href="#" title="<?php echo $LanguageInstance->get('tandem_logo') ?>"><img src="css/images/logo_Tandem.png" alt="<?php echo $LanguageInstance->get('tandem_logo') ?>"/></a>
            </p>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-12'>
            <p>
                <a class='btn btn-success'
                   href='manage_exercises_tandem.php'><?php echo $LanguageInstance->get('mange_exercises_tandem'); ?></a>
                <a href='statistics_tandem.php'
                   class='btn btn-success'><?php echo $LanguageInstance->get('Tandem Statistics'); ?></a>
                <a href="mailingTools.php"
                   class='btn btn-success'><?php echo $LanguageInstance->get('Mailing tools'); ?></a>
                <?php if (!$_SESSION[USE_WAITING_ROOM_NO_TEAMS]) { ?><a
                    href='tandemInfo.php?force=1&lang=en_US<?php echo $select_room ? '&select_room=1' : '' ?>'
                    class='btn btn-success' ><?php echo $LanguageInstance->get('Go to tandem to practise English'); ?></a>
                    <a href='tandemInfo.php?force=1&lang=es_ES<?php echo $select_room ? '&select_room=1' : '' ?>'
                       class='btn btn-success'><?php echo $LanguageInstance->get('Ir al tandem para practicar Español'); ?></a>
                <?php } else { ?>
                    <a href='tandemInfo.php?force=1<?php echo $select_room ? '&select_room=1' : '' ?>'
                       class='btn btn-success'><?php echo $LanguageInstance->get('Go to tandem'); ?></a>
                <?php } ?>
                <a href="api/generateTandemUseReport.php" target="_blank"
                   class='btn btn-success'><?php echo $LanguageInstance->get('Generate Tandem Use Report'); ?></a>
                <?php if ($show_quiz) { ?>
                <a href="multiple-choice-quiz/manage.php" target="_blank"
                   class='btn btn-success'><?php echo $LanguageInstance->get('Manage questions waiting room'); ?></a>
                <?php } ?>
            </p>
        </div>
    </div>
    <div class='well'>
        <div class='row'>
            <div class='col-md-12'><h3><?php echo $LanguageInstance->get('Tandem Configuration'); ?></h3></div>
            <div class='col-md-12'>
                <div class="list_group">
                    <div class="list-group-item">
                        <form role='form' class="form-inline" method="post">
                            <div class="form-group">
                                <label for="startDateCurrentRanking"> <?php echo $LanguageInstance->get('Start date current ranking'); ?></label>
                                <input type='text' name='startDateCurrentRanking'
                                       id='startDateCurrentRanking' value='<?php echo $startDateCurrentRanking ?>'>
                            </div>
                            <div class="form-group">
                                <label for="startHourCurrentRanking"> <?php echo $LanguageInstance->get('Hour'); ?></label>
                                <select name='startHourCurrentRanking' id='startHourCurrentRanking'>
                                    <?php
                                    for ($h=0; $h<24; $h++) {?>
                                        <option value="<?php echo $h?>" <?php echo $startHourCurrentRanking==$h?'selected':'' ?>><?php echo $h?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="endDateCurrentRanking"> <?php echo $LanguageInstance->get('End date current ranking'); ?></label>
                                <input type='text' name='endDateCurrentRanking'
                                       id='endDateCurrentRanking' value='<?php echo $endDateCurrentRanking ?>'>
                            </div>
                            <div class="form-group">
                                <label for="endHourCurrentRanking"> <?php echo $LanguageInstance->get('Hour'); ?></label>
                                <select name='endHourCurrentRanking' id='endHourCurrentRanking'>
                                    <?php
                                    for ($h=0; $h<24; $h++) {?>
                                        <option value="<?php echo $h?>" <?php echo $endHourCurrentRanking==$h?'selected':'' ?>><?php echo $h?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <button type="submit"
                                    class="btn btn-default"><?php echo $LanguageInstance->get('Save'); ?></button>
                        </form>
                    </div>
                    <div class="list-group-item info">
                        <small><?php echo $LanguageInstance->get('The configuration work like this:'); ?>
                        <ul>
                            <li><?php echo $LanguageInstance->get('All the tandems after or equal start date and hour and 0 minutes and 0 seconds, for example 12:00:00'); ?></li>
                            <li><?php echo $LanguageInstance->get('All the tandems before or equal end date and hour and 59 minuteds 59 seconds, for example 12:59:59'); ?></li>
                            <li><?php echo $LanguageInstance->get('If start date is not set we will get all the tandems before or equal end date and hour'); ?></li>
                            <li><?php echo $LanguageInstance->get('If end date is not set we will get all the tandems after or equal start date and hour'); ?></li>
                            <li><?php echo $LanguageInstance->get('If start and end date is not set we will get all the tandems'); ?></li>
                        </ul>
                        </small>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class='well'>
        <div class='row'>
            <div class='col-md-12'><h3><?php echo $LanguageInstance->get('Statistics'); ?></h3></div>
            <div class="col-md-6">
                <div class="list_group">
                    <div class="list-group-item">
                        <?php
                        echo $LanguageInstance->get('Current active tandems');
                        echo ': <strong><span id="totalActiveTandems">' . $currentActiveTandems . '</span></strong>';
                        ?>
                    </div>
                    <?php if (!$_SESSION[USE_WAITING_ROOM_NO_TEAMS]) { ?>
                        <div class="list-group-item">
                            <?php
                            echo $LanguageInstance->get('Users waiting to practice English');
                            echo ': <strong><span id="UsersWaitingEn">' . $getUsersWaitingEn . '</span></strong> <button id="view_details_en_US" class="btn btn-success">' . $LanguageInstance->get('View users') . '</button>';
                            ?>
                        </div>
                        <div class="list-group-item">
                            <?php
                            echo $LanguageInstance->get('Usuarios esperando para practicar Español');
                            echo ': <strong><span id="UsersWaitingEs">' . $getUsersWaitingEs . '</span></strong> <button id="view_details_es_ES" class="btn btn-success">' . $LanguageInstance->get('Ver usuarios') . '</button>';
                            ?>
                        </div>
                    <?php } else { ?>
                        <div class="list-group-item">
                            <?php
                            echo $LanguageInstance->get('users_waiting');
                            echo ': <strong><span id="UsersWaiting">' . $getUsersWaiting . '</span></strong> <button id="view_details_all" class="btn btn-success">' . $LanguageInstance->get('View users') . '</button>';
                            ?>
                        </div>
                    <?php } ?>
                    <div class="list-group-item">
                        <?php
                        echo $LanguageInstance->get('Total feedbacks');
                        echo ': <strong>' . $getFeedbackStats['feedback_tandem'] . '</strong>';
                        ?>
                    </div>
                    <div class="list-group-item">
                        <?php
                        echo $LanguageInstance->get('Total feedbacks submitted');
                        echo ': <strong>' . $getFeedbackStats['feedback_tandem_forms_sent'] . '</strong>';
                        ?>
                    </div>
                    <?php if (!$_SESSION[USE_WAITING_ROOM_NO_TEAMS]) { ?>
                        <div class="list-group-item">
                            <?php
                            echo $LanguageInstance->get('Total feedbacks submitted for ES');
                            echo ': <strong>' . $getFeedbackStats['feedback_tandem_form_es'] . '</strong>';
                            ?>
                        </div>
                        <div class="list-group-item">
                            <?php
                            echo $LanguageInstance->get('Total feedbacks submitted for EN');
                            echo ': <strong>' . $getFeedbackStats['feedback_tandem_form_en'] . '</strong>';
                            ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="list_group">
                    <div class="list-group-item">
                        <?php
                        echo "<form role='form'><div class='input-group'><div class='input-group-addon'>Select date</div><input class='form-control' type='text'  id='tandemByDate' value='" . date('d-m-Y') . "'></div></form><br />";
                        echo $LanguageInstance->get('Number of people that have done a tandem on specific date');
                        echo ": <strong id='nTandemsDate'>" . $tandemByDate . '</strong>';
                        ?>
                    </div>
                    <div class="list-group-item">
                        <?php
                        echo $LanguageInstance->get('Number of failed tandems');
                        echo ": <strong id='nTandemsDate'>" . $getNumOfSuccessFailedTandems['failed'] . '</strong>';
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-12'>
            <div class='well'>
                <div id='chart1'></div>
            </div>
            <br/>
            <div class='well'>
                <div class='selectDatesForTandems'>
                    <form role='form' class="form-inline" method="post">
                        <div class="form-group">
                            <label class="sr-only"> <?php echo $LanguageInstance->get('Start Date'); ?></label>
                            <p class="form-control-static"> <?php echo $LanguageInstance->get('Start Date'); ?></p>
                        </div>
                        <div class="form-group">
                            <input type='text' class="form-control" name='tandemFailedSuccessByDateStart'
                                   id='tandemFailedSuccessByDateStart' value='<?php echo $tandemFailedSuccessByDateStart ?>'>
                        </div>
                        <div class="form-group">
                            <label class="sr-only"> <?php echo $LanguageInstance->get('Start End'); ?></label>
                            <p class="form-control-static"> <?php echo $LanguageInstance->get('End Date'); ?></p>
                        </div>
                        <div class="form-group">
                            <input type='text' class="form-control" name='tandemFailedSuccessByDateEnd'
                                   id='tandemFailedSuccessByDateEnd' value='<?php echo $tandemFailedSuccessByDateEnd ?>'>
                        </div>
                        <button type="submit" class="btn btn-default"><?php echo $LanguageInstance->get('View'); ?></button>
                    </form>
                </div>
                <div id='chart2'></div>
            </div>
            <br/>
            <?php if (!$_SESSION[USE_WAITING_ROOM_NO_TEAMS]) { ?>
            <div id='chart3' class='well' style='width:380px;float:left;display:inline'></div>
            <div id='chart4' class='well' style='width:380px;float:left;display:inline'></div>
            <?php } ?>
            <?php if ($enabledWebRTC) { ?>
            <div id='chart5' class='well' style='width:380px;float:left;display:inline'></div>
            <div id='chart6' class='well' style='width:380px;float:left;display:inline'></div>
            <?php } ?>
            <?php if (!$_SESSION[USE_WAITING_ROOM_NO_TEAMS]) { ?>
            <div id='chart7' class='well' style='width:380px;float:left;display:inline'></div>
            <?php } ?>
        </div>
    </div>
    <?php if (!empty($stats['per_day_of_week_finalized'])) { ?>
    <div class='row'>
        <div class='col-md-12'>
            <div id='chart_per_day_of_week_finalized'></div>
        </div>
    </div>
    <?php } ?>
    <?php if (!empty($stats['per_day_of_week'])) { ?>
    <div class='row'>
        <div class='col-md-12'>
            <div id='chart_per_day_of_week'></div>
        </div>
    </div>
    <?php } ?>
    <?php if (!empty($stats['per_hour_finalized'])) { ?>
    <div class='row'>y
        <div class='col-md-12'>
            <div id='chart_per_hour_finalized'></div>
        </div>
    </div>
    <?php } ?>
    <?php if (!empty($stats['per_hour'])) { ?>
    <div class='row'>
        <div class='col-md-12'>
            <div id='chart_per_hour'></div>
        </div>
    </div>
    <?php } ?>
    <?php if (!empty($stats['per_user_status'])) { ?>
    <div class='row'>
        <div class='col-md-12'>
            <div id='chart_per_user_status'></div>
        </div>
    </div>
    <?php } ?>
<p></p>
</div>
<pre id="tsv" style="display:none">
Success <?php echo $getNumOfSuccessFailedTandems['success']; ?>%
Failed <?php echo $getNumOfSuccessFailedTandems['failed']; ?>%
</pre>
<!-- Modal -->
<div class="modal fade" id="modalUser" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="modalTitle"></h4>
            </div>
            <div class="modal-body">
                <div class="te" id="contentModalDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-dismiss="modal"><?php echo $LanguageInstance->get('Close'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<script src="js/bootstrap-slider2.js"></script>
<script src="js/highcharts/js/highcharts.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/highcharts/4.0.4/modules/data.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/highcharts/4.0.4/modules/drilldown.js"></script>
<script src="js/tandemInfo.js?version=20191026"></script>
<?php if (!$_SESSION[USE_WAITING_ROOM_NO_TEAMS]) { ?>
    <script>
        // Variable exports for tandemInfo_teams.js
        var a = parseInt("<?php echo round(($getFeedbackStats['feedback_tandem_form_es'] / ($getFeedbackStats['feedback_tandem_forms_sent'] == 0 ? 1 : $getFeedbackStats['feedback_tandem_forms_sent'])) * 100) ?>");
        var b = parseInt("<?php echo round(($getFeedbackStats['feedback_tandem_form_en'] / ($getFeedbackStats['feedback_tandem_forms_sent'] == 0 ? 1 : $getFeedbackStats['feedback_tandem_forms_sent'])) * 100) ?>");
        var c = parseInt("<?php echo $getFeedbackStats['feedback_tandem_form_es_not_sent'] ?>");
        var d = parseInt("<?php echo $getFeedbackStats['feedback_tandem_form_en_not_sent'] ?>");
        var e = parseInt("<?php echo $peopleWaitedWithoutTandem['es'] ?>");
        var f = parseInt("<?php echo $peopleWaitedWithoutTandem['en'] ?>");
    </script>
    <script src="js/tandemInfo_teams.js"></script>
<?php } ?>
<?php if ($stats['per_day_of_week']) { ?>
    <script>
        // Variable exports for tandemInfo_perDayOfWeek.js
        var aa = "<?php echo $tandemFailedSuccessByDateStart?>";
        var bb = "<?php echo $tandemFailedSuccessByDateEnd?>";
        var cc = [
            <?php
            $firstIteration = true;
            foreach ($stats['per_day_of_week'] as $key => $tandem_day) {
                if (!$firstIteration) {
                    echo ',';
                } else {
                    $firstIteration = false;
                }
                $data = implode(',', $tandem_day);
                echo '{ name: "' . $key . '", data: [' . $data . '] }';
            }
            ?>
        ];
    </script>
    <script src="js/tandemInfo_perDayOfWeeks.js"></script>
<?php } ?>
<?php if ($stats['per_hour']) { ?>
    <script>
        // Variable exports for tandemInfo_perDayOfWeek.js
        var aaa = "<?php echo $tandemFailedSuccessByDateStart?>";
        var bbb = "<?php echo $tandemFailedSuccessByDateEnd?>";
        var ccc = [<?php echo implode(',', $stats['per_hour']) ?>];
    </script>
    <script src="js/tandemInfo_perHour.js"></script>
<?php } ?>
<?php if (!empty($stats['per_user_status'])) { ?>
    <script>
        // Variable export for tandemInfo_perUserStatus.js
        var aaaa = parseInt("<?php echo round(($stats['per_user_status']['smilies'] / $stats['per_user_status']['total']) * 100) ?>");
        var bbbb = parseInt("<?php echo round(($stats['per_user_status']['neutral'] / $stats['per_user_status']['total']) * 100) ?>");
        var cccc = parseInt("<?php echo round(($stats['per_user_status']['sad'] / $stats['per_user_status']['total']) * 100) ?>");
    </script>
    <script src="js/tandemInfo_perUserStatus.js"></script>
<?php } ?>
<?php if ($enabledWebRTC) {
	$tandemStatsByVideoType = $gestorBD->tandemStatsByVideoType($course_id, $tandemFailedSuccessByDateStart, $tandemFailedSuccessByDateEnd);
	?>
    <script>
        // Variable export for tandemInfo_enabledWebRTC.js
        var aaaaa = parseInt("<?php echo $tandemStatsByVideoType['tandem_ok']['webrtc'] ?>");
        var bbbbb = parseInt("<?php echo $tandemStatsByVideoType['tandem_ok']['videochat'] ?>");
        var ccccc = parseInt("<?php echo $tandemStatsByVideoType['tandem_ko']['webrtc'] ?>");
        var ddddd = parseInt("<?php echo $tandemStatsByVideoType['tandem_ko']['videochat'] ?>");
    </script>
    <script src="js/tandemInfo_enabledWebRTC.js"></script>
<?php } ?>
<script>
    // Variable exports for tandemInfo2.js
    var numberOfFinishedTandemsByDate = [
        <?php
        if (!empty($getCountAllTandemsByDate)) {
            $tmp = array();
            foreach ($getCountAllTandemsByDate as $key => $value) {
                $exp = explode('-', $value['created']);
                $tmp[] = '[Date.UTC(' . $exp[0] . ',' . ($exp[1] - 1) . ',' . $exp[2] . ',10),' . $value['total'] . ']';
            }
            echo implode(',', $tmp);
        }
        ?>
    ];
    var numberOfUnfinishedTandemsByDate = [
        <?php
        if (!empty($getCountAllUnFinishedTandemsByDate)) {
            $tmp = array();
            foreach ($getCountAllUnFinishedTandemsByDate as $key => $value) {
                $exp = explode('-', $value['created']);
                $tmp[] = '[Date.UTC(' . $exp[0] . ',' . ($exp[1] - 1) . ',' . $exp[2] . ',10),' . $value['total'] . ']';
            }
            echo implode(',', $tmp);
        }
        ?>
    ];
</script>
<script src="js/tandemInfo2.js"></script>
<?php include_once __DIR__ .'/js/google_analytics.php';?>
</body>
</html>
