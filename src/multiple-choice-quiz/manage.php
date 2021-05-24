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
    header('Location: index.php');
    die();
}
$questions = $gestorBD->get_questions_quiz_manage();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" media="all" href="../css/tandem-waiting-room.css">
    <link rel="stylesheet" type="text/css" media="all" href="../css/tandemInfo.css"/>
</head>
<body>
<div class='container'>
    <div class='row'>
        <div class='col-md-12 text-right'>
            <p><a href="#" title="<?php echo $LanguageInstance->get('tandem_logo') ?>"><img
                            src="../css/images/logo_Tandem.png"
                            alt="<?php echo $LanguageInstance->get('tandem_logo') ?>"/></a>
            </p>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-12'>
            <h1><?php echo $LanguageInstance->get('Manage Questions') ?></h1>
        </div>
    </div>
    <nav class="navbar navbar-light justify-content-center mt-4">
        <form class="form">
            <p><a href="edit.php" class="btn btn-primary"><?php echo $LanguageInstance->get('Add new question')?></a></p>
            <p><a href="index.php?lang=es_ES" target="_blank" class="btn btn-primary"><?php echo $LanguageInstance->get('Spanish Preview')?></a>
            <a href="index.php?lang=en_US" target="_blank" class="btn btn-primary"><?php echo $LanguageInstance->get('English Preview')?></a></p>
            <input class="form-control mr-sm-2" type="search"
                   placeholder="<?php echo $LanguageInstance->get('Search by question') ?>"
                   aria-label="<?php echo $LanguageInstance->get('Search') ?>" id="search">
            <select class="form-control mr-sm-2" type="search"

                    aria-label="<?php echo $LanguageInstance->get('Search') ?>" id="enabled">
                <option value=""><?php echo $LanguageInstance->get('All') ?></option>
                <option value="1"><?php echo $LanguageInstance->get('Enabled') ?></option>
                <option value="0"><?php echo $LanguageInstance->get('Disabled') ?></option>
            </select>
            <select class="form-control mr-sm-2" type="search"

                    aria-label="<?php echo $LanguageInstance->get('Language') ?>" id="language">
                <option value=""><?php echo $LanguageInstance->get('All') ?></option>
                <option value="1"><?php echo $LanguageInstance->get('English') ?></option>
                <option value="0"><?php echo $LanguageInstance->get('Spanish') ?></option>
            </select>
            <!-- <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button> -->
        </form>
    </nav>
    <div id="card-deck" class="col-md-12">
        <?php foreach ($questions as $question) { ?>
            <div class="card">
                <div class="card-header">
                    <?php echo $question['title'] ?>
                    <span class="float-md-right">
                        <span class="question_active_<?php echo $question['active'] ?>">
                            <?php echo $LanguageInstance->get($question['active'] ? 'Question enabled' : 'Question disabled') ?></span>
                        <br>
                        <span class="float-md-right language_<?php echo $question['language'] ?>">
                            <?php echo $LanguageInstance->get('Language:') ?>&nbsp;<?php echo $question['language'] ?>
                        </span>
                    </span>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $question['question'] ?></h5>
                    <p class="card-text">
                        <p><strong><?php echo $LanguageInstance->get('Caregory')?>: <?php echo $LanguageInstance->get($question['category'])?></strong></p>
                        <?php $answers = $gestorBD->get_questions_answers($question['id']);
                        $indAnswer = 0;
                        foreach ($answers as $answer) {
                            if ($indAnswer > 0) {
                                echo '<br>';
                            }
                            $indAnswer++;
                            $pre_question = '';
                            $post_question = '';
                            if ($question['correctAnswer'] == $answer['answer']) {
                                $pre_question = '<strong>';
                                $post_question = '</strong> <small>' . $LanguageInstance->get('Correct answer') . '</small>';

                            }
                            echo $pre_question . $answer['answer'] . ': "' . str_replace('"', '\"',
                                    $answer['answerText']) . '"' . $post_question;
                        } ?></p>
                    <a href="edit.php?id=<?php echo $question['id']?>" class="btn btn-primary"><?php echo $LanguageInstance->get('Edit') ?></a>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
<script>
    function search_by_text(filter) {
        $('#card-deck').find('.card .card-body h5:not(:contains("' + filter + '"))').parent().parent().addClass('d-none');
    }

    function search_by_status(selected) {
        if (selected !== '') {
            var filter_enabled = selected === '0' ? '1' : '0';
            $('#card-deck').find('.card .card-header .question_active_' + filter_enabled).parent().parent().parent().addClass('d-none');

        }
    }
    function search_by_language(selected) {
        if (selected !== '') {
            var filter_enabled = selected === '0' ? 'en_US' : 'es_ES';
            $('#card-deck').find('.card .card-header .language_' + filter_enabled).parent().parent().parent().addClass('d-none');

        }
    }
    function performSearch() {
        $('.card').removeClass('d-none');
        var filter = $('#search').val(); // get the value of the input, which we filter on
        search_by_text(filter);
        var selected = $('#enabled').val();
        search_by_status(selected);
        var selected = $('#language').val();
        search_by_language(selected);
    }
    $('#search').keyup(function () {
        performSearch();
    });
    $('#enabled').change(function () {
        performSearch();
    });
    $('#language').change(function () {
        performSearch();
    });
</script>
<?php include_once __DIR__ . '/../js/google_analytics.php'; ?>
</body>
</html>