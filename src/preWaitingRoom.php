<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/classes/lang.php';
require_once dirname(__FILE__) . '/classes/constants.php';
require_once dirname(__FILE__) . '/classes/gestorBD.php';
require_once 'IMSBasicLTI/uoc-blti/lti_utils.php';

$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;

$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;
$use_waiting_room = isset($_SESSION[USE_WAITING_ROOM]) ? $_SESSION[USE_WAITING_ROOM] : false;

require_once dirname(__FILE__) . '/classes/IntegrationTandemBLTI.php';

$gestorBD = new gestorBD();
//si no existeix objecte usuari o no existeix curs redireccionem cap a l'index....preguntar Antoni cap a on redirigir...
if (!$user_obj || !$course_id) {
//Tornem a l'index
    header('Location: index.php');
} else {
    if (
        (defined('DEBUG_DISABLE_AUDIO_VIDEO_TEST') && DEBUG_DISABLE_AUDIO_VIDEO_TEST)
        || count($gestorBD->obte_llistat_tandems($course_id, $user_id, 0,
            -1,
            0,
            0,
            '',
            '',
            1)) > 0) {
        header('Location: autoAssignTandemRoom.php');
    } else {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Tandem</title>
            <meta charset="UTF-8"/>
            <link rel="stylesheet" type="text/css" media="all" href="css/autoAssignTandem.css?id=28"/>
            <link rel="stylesheet" type="text/css" media="all" href="css/tandem-waiting-room.css?id=21"/>
            <link rel="stylesheet" type="text/css" media="all" href="css/jquery-ui.css"/>
            <!-- 10082012: nfinney> ADDED COLORBOX CSS LINK -->
            <link rel="stylesheet" type="text/css" media="all" href="css/colorbox.css"/>
            <!-- END -->
            <!-- Timer End -->
            <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
            <script type="text/javascript" src="js/jquery.ui.core.js"></script>
            <script type="text/javascript" src="js/jquery.ui.widget.js"></script>
            <script type="text/javascript" src="js/jquery.ui.button.js"></script>
            <script type="text/javascript" src="js/jquery.ui.position.js"></script>
            <script type="text/javascript" src="js/jquery.ui.autocomplete.js"></script>
            <script type="text/javascript" src="js/jquery.colorbox-min.js"></script>
            <script type="text/javascript" src="js/common.js"></script>
            <script type="text/javascript" src="js/jquery.animate-colors.min.js"></script>
            <script type="text/javascript" src="js/jquery.simplemodal.1.4.2.min.js"></script>
            <script type="text/javascript" src="js/jquery.iframe-auto-height.plugin.1.7.1.min.js"></script>
            <script type="text/javascript" src="js/jquery.infotip.min.js"></script>
            <script type="text/javascript" src="js/jquery.timeline-clock.min.js"></script>
            <script type="text/javascript" src="js/swfobject.js"></script>
            <script type="text/javascript" src="js/jquery.ui.progressbar.js"></script>
            <style>
                .refreshPage {
                    padding: 5px;
                    color: #FFF;
                }
            </style>
        </head>
        <body>
        <!-- /wrapper -->
        <div id="wrapper">
            <!-- main-container -->
            <div id="main-container">
                <!-- main -->
                <div id="main">
                    <!-- content -->
                    <div id="content">

                        <h1 class="waiting_room"><?php echo $LanguageInstance->get('Waiting Room Test') ?></h1>
                        <span class="welcome"><?php echo $LanguageInstance->get('welcome') ?> <?php echo $user_obj->fullname; ?>
                            !</span>
                        <div id="logo_waiting_room">
                            <a href="#" title="<?php echo $LanguageInstance->get('tandem_logo') ?>"><img
                                        src="css/images/logo_Tandem.png"
                                        alt="<?php echo $LanguageInstance->get('tandem_logo') ?>"/></a>
                        </div>
                        <div class="clear"></div>

                        <!-- *********************************** -->
                        <!-- ****WAITING-TANDEM-ROOM-dynamic**** -->
                        <!-- *********************************** -->
                        <h3><?php echo $LanguageInstance->get("You have to pass this test to access Tandem Mooc"); ?>
                            . <?php echo $LanguageInstance->get('Say "hello" to test the audio!'); ?></h3>
                        <!-- WAITING MODAL -->

                        <div class="row wrapper_content">
                            <div id="test-flash">
                                <p><?php echo $LanguageInstance->get("You need Flash to access Tandem Mooc"); ?></p>
                                <p><a href="http://www.adobe.com/go/getflashplayer"><img
                                                src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif"
                                                alt="Get Adobe Flash player"/></a></p>
                            </div>
                        </div>
                        <div class="clear">
                        </div>
                        <div class="clear"></div>
                        <div class="clear"></div>
                    </div>
                    <!-- /content -->
                </div>
                <!-- /main -->
            </div>
            <!-- /main-container -->
        </div>
        <div id="modal-continue" class="modal">
            <div align="center">
                <div class="text">
                    <p><?php echo $LanguageInstance->get('Congratulations test passed!'); ?></p>
                </div>
                <div class="">
                    <a href="autoAssignTandemRoom.php"
                       class="btn btn-success"><?php echo $LanguageInstance->get('Click here to continue'); ?></a>
                </div>
            </div>
        </div>
        <!-- /wrapper -->
        <!-- footer -->
        <div id="footer-container">
            <div id="footer">
                <div class="footer-tandem" title="<?php echo $LanguageInstance->get('tandem') ?>"></div>
                <div class="footer-logos">
                    <!--img src="css/images/logo_LLP.png" alt="Lifelong Learning Programme" />
                    <img src="css/images/logo_EAC.png" alt="Education, Audiovisual &amp; Culture" /-->
                    <div style="float: left; margin-top: 0pt; text-align: justify; width: 600px;"><span
                                style="font-size:9px;">This project has been funded with support from the Lifelong Learning Programme of the European Commission.  <br/>
This site reflects only the views of the authors, and the European Commission cannot be held responsible for any use which may be made of the information contained therein.</span>
                    </div>
                    &nbsp; <img src="css/images/EU_flag.jpg" alt=""/>
                    <img src="css/images/logo_speakapps.png" alt="Speakapps"/>
                </div>
            </div>
        </div>
        <!-- /footer -->
        <script>
            $(function () {
                var flashvars = {
                    rmtpServer: "rtmp://54.72.157.90/videochat"
                };
                var params = {};
                var attributes = {
                    id: "test-flash_id<?php echo time() . mt_rand(); ?>"
                };
                swfobject.embedSWF("js/recorder_tester.swf?version=3", "test-flash", "477", "280", "11.1.0", "expressInstall.swf", flashvars, params, attributes);
                setTranslations();
            });

            var showing_errorNoMicroFound = false;
            var showing_errorNotAllowedMicro = false;
            var num_max_of_load_translations = 5;

            function loadedFlash(time) {
                setTimeout("setTranslations();", time);
            }

            function setTranslations() {
                var flash = swfobject.getObjectById("test-flash_id");
                var ok = false;
                if (flash != null) {
                    try {
                        flash.setTagFromJS("recorder", "<?php echo $LanguageInstance->get('Recorder') ?>");
                        flash.setTagFromJS("player", "<?php echo $LanguageInstance->get('Player') ?>");
                        flash.setTagFromJS("audio_ok", "<?php echo $LanguageInstance->get('Audio is ok') ?>");
                        flash.setTagFromJS("video_ok", "<?php echo $LanguageInstance->get('Video is ok') ?>");
                        flash.setTagFromJS("audio_ko", "<?php echo $LanguageInstance->get('Can\'t get audio, try to talk to the microphone') ?>");
                        flash.setTagFromJS("video_ko", "<?php echo $LanguageInstance->get('Can\'t get video') ?>");
                        flash.setTagFromJS("general", "<?php echo $LanguageInstance->get('A computer with a webcam and microphone and headphones (to avoid noise) are required.') ?>");
                        flash.setTagFromJS("fpsTextExplication", "<?php echo $LanguageInstance->get('The speed should be greather than 10.') ?>");
                        ok = true;
                    }
                    catch (e) {
                        console.log(e);
                    }
                }
                if (!ok) {
                    num_max_of_load_translations--;
                    if (num_max_of_load_translations > 0) {
                        loadedFlash(400);
                    }
                }
            }

            function errorNoMicroFound() {
                if (!swf_is_ready) {
                    return false;
                }
                if (!showing_errorNoMicroFound) {
                    showing_errorNoMicroFound = true;
                    alert("No se puede encontrar el micrófono");
                    showing_errorNoMicroFound = false;
                }
            }

            function errorNotAllowedMicro() {
                if (!swf_is_ready) {
                    return false;
                }
                if (!showing_errorNotAllowedMicro) {
                    showing_errorNotAllowedMicro = true;
                    alert("No se ha permitido el acceso a la camára y al micrófono");
                    showing_errorNotAllowedMicro = false;
                }
            }

            function errorNoCameraFound() {
                alert("No se puede encontrar la cámara");
            }

            function errorConnectingToServerStreaming() {
                alert("No se puede conectar al servidor de Streaming");
            }

            var videoIsOk = false;
            var audioIsOk = false;

            function videoIsOkJS() {
                videoIsOk = true;
                check();
            }

            function audioIsOkJS() {
                audioIsOk = true;
                check();
            }

            function check() {
                if (audioIsOk && videoIsOk) {
                    $.modal($('#modal-continue'));
                }
            }

        </script>
        <?php include_once __DIR__ . '/js/google_analytics.php' ?>
        <!--    <audio id="foundTandemPartner" src="sweep.wav" preload="auto"></audio>-->
        </body>
        </html>
    <?php }
} ?>