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

$question_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
if (isset($_POST['save'])) {
    $title = isset($_POST['title'])?$_POST['title']:'';
    $language = isset($_POST['language'])?$_POST['language']:'';
    $question = isset($_POST['question'])?$_POST['question']:'';
    $correctAnswer = isset($_POST['correctAnswer'])?$_POST['correctAnswer']:'';
    $correctAnswerText = isset($_POST['correctAnswerText'])?$_POST['correctAnswerText']:'';
    $falseAnswerText = isset($_POST['falseAnswerText'])?$_POST['falseAnswerText']:'';
    $active = isset($_POST['active'])?$_POST['active']:'';
    $category = isset($_POST['category'])?$_POST['category']:'';
    $answersTags = array('a' => '', 'b' => '', 'c' => '', 'd' => '', 'e' => '');
    foreach ($answersTags as $key => $answer) {
        $answersTags[$key] = isset($_POST['answer_'.$key])?$_POST['answer_'.$key]:'';
    }
    $question_id  = $gestorBD->saveQuizQuestion($question_id, $title, $language, $question, $correctAnswer, $correctAnswerText, $falseAnswerText,
        $active, $category, $answersTags);

}
$question = array();
$question['id'] = -1;
$question['title'] = '';
$question['language'] = '';
$question['question'] = '';
$question['correctAnswer'] = '';
$question['correctAnswerText'] = '';
$question['falseAnswerText'] = '';
$question['active'] = 1;
$question['category'] = 1;
if ($question_id > 0) {
    $question = $gestorBD->get_questions_quiz_manage($question_id);
}
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
    <div class='row'>
        <div class="col-md-12">
            <form class="form" method="post">
                <div class="form-group">
                    <label for="title"><?php echo $LanguageInstance->get('Title') ?>:</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo $question['title'] ?>">
                </div>
                <div class="form-group">
                    <label for="language"><?php echo $LanguageInstance->get('Language') ?>:</label>
                    <select id="language" name="language">
                        <option value="en_US" <?php echo 'en_US' == $question['language'] ? 'selected' : '' ?>><?php echo $LanguageInstance->get('English') ?></option>
                        <option value="es_ES" <?php echo 'es_ES' == $question['language'] ? 'selected' : '' ?>><?php echo $LanguageInstance->get('Spanish') ?></option>
                        <option value="all" <?php echo 'all' == $question['language'] ? 'selected' : '' ?>><?php echo $LanguageInstance->get('All') ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="category"><?php echo $LanguageInstance->get('Category') ?>:</label>
                    <select id="category" name="category">
                        <option value="vocabulary" <?php echo 'vocabulary' == $question['category'] ? 'selected' : '' ?>><?php echo $LanguageInstance->get('Vocabulary') ?></option>
                        <option value="grammar" <?php echo 'grammar' == $question['category'] ? 'selected' : '' ?>><?php echo $LanguageInstance->get('Grammar') ?></option>
                        <option value="culture" <?php echo 'culture' == $question['category'] ? 'selected' : '' ?>><?php echo $LanguageInstance->get('Culture') ?></option>
                        <option value="tandemstrategies" <?php echo 'tandemstrategies' == $question['category'] ? 'selected' : '' ?>><?php echo $LanguageInstance->get('Tandem Strategies') ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="question"><?php echo $LanguageInstance->get('Question') ?>:</label>
                    <textarea class="form-control" id="question" name="question" cols="30"
                              rows="5"><?php echo $question['question'] ?></textarea>
                </div>
                <div class="form-group">
                    <label for="correctAnswer"><?php echo $LanguageInstance->get('Correct answer') ?>:</label>
                    <select id="correctAnswer" name="correctAnswer">
                        <option value="a" <?php echo 'a' == $question['correctAnswer'] ? 'selected' : '' ?>><?php echo $LanguageInstance->get('a') ?></option>
                        <option value="b" <?php echo 'b' == $question['correctAnswer'] ? 'selected' : '' ?>><?php echo $LanguageInstance->get('b') ?></option>
                        <option value="c" <?php echo 'c' == $question['correctAnswer'] ? 'selected' : '' ?>><?php echo $LanguageInstance->get('c') ?></option>
                        <option value="d" <?php echo 'd' == $question['correctAnswer'] ? 'selected' : '' ?>><?php echo $LanguageInstance->get('d') ?></option>
                        <option value="e" <?php echo 'e' == $question['correctAnswer'] ? 'selected' : '' ?>><?php echo $LanguageInstance->get('e') ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="correctAnswerText"><?php echo $LanguageInstance->get('Correct Answer Text') ?>:</label>
                    <textarea class="form-control" id="correctAnswerText" name="correctAnswerText" cols="30"
                              rows="5"><?php echo $question['correctAnswerText'] ?></textarea>
                </div>
                <div class="form-group">
                    <label for="falseAnswerText"><?php echo $LanguageInstance->get('Wrong Answer Text') ?>:</label>
                    <textarea class="form-control" id="falseAnswerText" name="falseAnswerText" cols="30"
                              rows="5"><?php echo $question['falseAnswerText'] ?></textarea>
                </div>
                <div class="form-group">
                    <label for="active"><?php echo $LanguageInstance->get('Enabled') ?>:</label>
                    <select id="active" name="active">
                        <option value="1" <?php echo '1' == $question['active'] ? 'selected' : '' ?>><?php echo $LanguageInstance->get('Yes') ?></option>
                        <option value="0" <?php echo '0' == $question['active'] ? 'selected' : '' ?>><?php echo $LanguageInstance->get('No') ?></option>
                    </select>
                </div>
                <h3><?php echo $LanguageInstance->get('Answers')?></h3>

                    <?php $answers = $gestorBD->get_questions_answers($question['id']);

                    $emptyAnswer = array('answerText' => '');
                    $answersTags = array('a' => $emptyAnswer, 'b' => $emptyAnswer, 'c' => $emptyAnswer, 'd' => $emptyAnswer, 'e' => $emptyAnswer);
                    foreach ($answers as $answer) {
                        $answersTags[$answer['answer']] = $answer;
                    }
                    foreach ($answersTags as $key => $answersTag) { ?>
                        <div class="form-group">
                            <label for="answer_<?php echo $key?>"><?php echo $key ?>:</label>
                            <input type="text" id="answer_<?php echo $key?>" name="answer_<?php echo $key?>" value="<?php echo $answersTag['answerText']?>"/>
                        </div>
                    <?php } ?>
                <button type="submit" class="btn btn-primary" name="save"><?php echo $LanguageInstance->get('Submit')?></button>
                <a href="manage.php" class="btn btn-secondary"><?php echo $LanguageInstance->get('Cancel')?></a>
                <input type="hidden" name="id" value="<?php echo $question_id?>">
            </form>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>

<?php include_once __DIR__ . '/../js/google_analytics.php'; ?>
</body>
</html>