<?php

require_once __DIR__ . '/classes/lang.php';
require_once __DIR__ . '/classes/utils.php';
require_once __DIR__ . '/classes/constants.php';
require_once __DIR__ . '/classes/gestorBD.php';

$user_register_obj  = isset($_SESSION[CURRENT_USER])?$_SESSION[CURRENT_USER]:false;
$course_register_id = isset($_SESSION[COURSE_ID])?$_SESSION[COURSE_ID]:-1;
$id_register_tandem = isset($_SESSION[CURRENT_TANDEM])?$_SESSION[CURRENT_TANDEM]:-1;
$page = basename($_SERVER['PHP_SELF']);
$params = json_encode($_GET);
$user_id = -1;
if ($user_register_obj && isset($user_register_obj->id) && $user_register_obj->id>0) {
    $user_id = $user_register_obj->id;
}

if (!isset($gestorBD)) {
    $gestorBD = new GestorBD();
}

$gestorBD->logAction($user_id, $course_register_id, $id_register_tandem, $page, $params);
if (!in_array($page, array('portfolio_excel.php', 'ranking_excel.php', 'generateTandemUseReport.php', 'pdf.php', 'pdfCertificate.php'))) {
?>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var link = document.querySelector("a"); // It is the method to access the first matched element
        if (link) {
            link.addEventListener("click", function (event) {
                var url = this.getAttribute('href');
                var leaving = url.split('/')[2] != location.hostname;
                if (leaving) {
                    $.post('linkTandemLog.php', {url: url}, function (json) {
                        // proccess results
                    }, 'json');
                }
            });
        }
    });
</script>
<?php }