<?php
require_once __DIR__ . '/classes/lang.php';
require_once __DIR__ . '/classes/constants.php';
require_once __DIR__ . '/classes/gestorBD.php';
require_once __DIR__ . '/classes/utils.php';
require_once __DIR__ . '/IMSBasicLTI/uoc-blti/lti_utils.php';

$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;

if (!$user_obj) {
    // Tornem a l'index.
	header('Location: index.php');
	die();
}

$gestorBD = new GestorBD();
$show_teacher_view = false;
if ($user_obj->instructor== 1 || $user_obj->admin==1) {
    $isTeacher = true;
    $show_teacher_view = isset($_GET['student_view']) && $_GET['student_view']==1?false:true;
} else {
    $userPointsData = $gestorBD->getUserRankingPoints($user_obj->id, $course_id);
}
$usersRanking = $gestorBD->getUsersRanking($course_id, $_SESSION[USE_WAITING_ROOM_NO_TEAMS]);
$feedback_selfreflection_form = isset($_SESSION[FEEDBACK_SELFREFLECTION_FORM]) ? $_SESSION[FEEDBACK_SELFREFLECTION_FORM] : false;


$gestorBD->set_userInTandem($user_obj->id,$course_id,0);
/*
 * Print ranking table helpers
 */

/**
 * @param $position
 * @param $student
 * @param $LanguageInstance
 * @param $showGrades
 * @param bool $anonymize
 * @param bool $highlight
 */
function print_student_row($position, $student, $LanguageInstance, $showGrades = false, $anonymize = false, $highlight = false, $feedback_selfreflection_form=false) {
    if ($anonymize) {
        $studentName = '-';
    } else {
        $studentName = isset($student['user']) ? $student['user'] : '';
    }
    $trFontStyle = $highlight ? 'font-weight:bold;' : '';

    echo "<tr class='" . ($position <= 1 ? 'success' : 'warning') . "' style='" . $trFontStyle . "'>";
    echo "<td class='text-center'>" . $position . '</td>';
    echo '<td>' . $studentName . '</td>';
    echo '<td>' . (isset($student['points']) ? $student['points'] : '') . '</td>';
    if ($showGrades) {
        $obj = secondsToTime($student['total_time']);
        $time = '';
        if ($obj['h'] > 0) {
            $time .= ($obj['h'] < 10 ? '0' : '') . $obj['h'] . ':';
        }
        $time .= ($obj['m'] < 10 ? '0' : '') . $obj['m'] . ':';
        $time .= ($obj['s'] < 10 ? '0' : '') . $obj['s'];
        echo '<td>' . $time . '</td>';
        echo '<td>' . (int) $student['number_of_tandems'] . '</td>';
        if (!$feedback_selfreflection_form) {
            echo '<td>' . $student['accuracy'] . '</td>';
            echo '<td>' . $student['fluency'] . '</td>';
            echo '<td>' . getSkillsLevel(getOverallAsIdentifier($student['overall_grade']),
                    $LanguageInstance) . '</td>';
        }
    }
    echo '</tr>';
}

/**
 * @param array $students ¡Ordered! list of students
 * @param int $currentUserPosition
 * @param bool $teacherMode if true, all students and extra information of all students will be shown
 * @param $LanguageInstance
 */
function print_ranking_table_rows($students, $currentUserPosition, $teacherMode, $LanguageInstance, $feedback_selfreflection_form=false) {
    if (empty($students)) {
        return;
    }

    $currentUserPosition = (int) $currentUserPosition;
    $totalStudentsInList = count($students);
    $skipStudents = !$teacherMode;
    $skipMiddleTableStudentsUntilTheLast = $skipStudents && $totalStudentsInList > 11
        && $currentUserPosition !== $totalStudentsInList;
    $skipMiddleTableStudentsUntilTheCurrent = $skipStudents && $totalStudentsInList > 11
        && $currentUserPosition > 11 && $currentUserPosition <= $totalStudentsInList;
    $skippedMiddleTableStudentsUntilTheLastOnce = false;
    $skippedMiddleTableStudentsUntilTheCurrentOnce = false;

    // If user position has no sense, default to 0
    if ($currentUserPosition < 0 || $currentUserPosition > $totalStudentsInList) {
        $currentUserPosition = 0;
    }

    $i = 1;
    foreach ($students as $student) {
        if ($teacherMode) {
            print_student_row($i, $student, $LanguageInstance, true, false, false, $feedback_selfreflection_form);
        } else {
            if ($i <= 10) {
                print_student_row($i, $student, $LanguageInstance, false, false, $i === $currentUserPosition, $feedback_selfreflection_form);
            } else {
                if ($i === $currentUserPosition) {
                    print_student_row($i, $student, $LanguageInstance, false, false, true, $feedback_selfreflection_form);
                } else if ($skipMiddleTableStudentsUntilTheCurrent && !$skippedMiddleTableStudentsUntilTheCurrentOnce) {
                    $skippedMiddleTableStudentsUntilTheCurrentOnce = true;
                    echo '<tr><td class="text-center" colspan="' . ($teacherMode === 'student' ? '3' : '8') . '">&#8942;</td>';
                } else if ($skipMiddleTableStudentsUntilTheLast && !$skippedMiddleTableStudentsUntilTheLastOnce
                    && $i > $currentUserPosition && $currentUserPosition !== $totalStudentsInList - 1) {
                    $skippedMiddleTableStudentsUntilTheLastOnce = true;
                    echo '<tr><td class="text-center" colspan="' . ($teacherMode === 'student' ? '3' : '8') . '">&#8942;</td>';
                } else if ($i === $totalStudentsInList) {
                    print_student_row($i, $student, $LanguageInstance, false, true, false, $feedback_selfreflection_form);
                }
            }
        }
        ++$i;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<link href="css/tandem-waiting-room.css" rel="stylesheet">
<style>
.green-for-english{
	color:#7E9F0B;
}
.purple-for-spanish{
	color:#4F2F78;
}
</style>
</head>
<body>
<div class="container" style='margin-top:20px'>
	<div class='row'>
		<div class='col-md-8'>
			<?php if (!empty($_SESSION[LMS_RETURN_URL])) { ?>
                <a class="btn btn-success" type='button'
                   href="<?php echo $_SESSION[LMS_RETURN_URL]?>"><?php echo $LanguageInstance->get('Return to course') ?></a>
			<?php } ?>
			<button class="btn btn-success" type='button' onclick="window.location ='portfolio.php';">
				<?php echo $LanguageInstance->get('Go to your portfolio') ?></button>
		  		<?php if ($show_teacher_view) { ?>
					<button class="btn btn-success" type='button' onclick="window.location ='ranking.php?student_view=1';">
					<?php echo $LanguageInstance->get('Show student view') ?></button>
					<button class="btn btn-success" type='button' onclick="window.location ='ranking_excel.php';">
					<?php echo $LanguageInstance->get('Export to excel') ?></button>
				<?php
				}else{
						if( isset($_REQUEST['student_view']) && !empty($isTeacher) ){ ?>
							<button class="btn btn-success" type='button' onclick="window.location ='ranking.php';">
							<?php echo $LanguageInstance->get('Show teacher view') ?>
							</button>
				<?php 	}
					}
				?>
        </div>
		<div class='col-md-4'>
	  	<p class='text-right'>
			<a href="#" title="<?php echo $LanguageInstance->get('tandem_logo')?>"><img src="css/images/logo_Tandem.png" alt="<?php echo $LanguageInstance->get('tandem_logo')?>" /></a>
	  	</p>
  	</div>
	</div>
  	<div class="row">
	  	<div class='col-md-6'>
	  		<h1 class='title'><?php echo $LanguageInstance->get('Users ranking');?></h1>
	  	</div>
	  	<div class='col-md-6'>
	  		<div class='welcomeMessage text-right'>
				<?php
                $userStudiesSpanish = 0 === strpos($_SESSION[LANG], 'en');
                $globalRankingPosition = 0;
                $spanishRankingPosition = 0;
                $englishRankingPosition = 0;
                $userRankingPosition = 0;
                if ($_SESSION[USE_WAITING_ROOM_NO_TEAMS]) {
                    $globalRankingPosition = $gestorBD->getUserRankingPosition($user_obj->id, '', $course_id, true);
                    $userRankingPosition = $globalRankingPosition;
                } else {
                    if ($userStudiesSpanish) {
                        $spanishRankingPosition = $gestorBD->getUserRankingPosition($user_obj->id, 'es_ES', $course_id, false);
                        $userRankingPosition = $spanishRankingPosition;
                    } else {
                        $englishRankingPosition = $gestorBD->getUserRankingPosition($user_obj->id, 'en_US', $course_id, false);
                        $userRankingPosition = $englishRankingPosition;
                    }
                }
                $positionInRankingTxt = $LanguageInstance->get('Hello %1');
                $positionInRankingTxt = str_replace('%1', $gestorBD->getUserName($user_obj->id), $positionInRankingTxt);
                if ($userRankingPosition > 0) {
                    $positionInRankingTxt .= $LanguageInstance->get(', your position in the ranking is ') . '<b>' . $userRankingPosition . '</b>';
                }
                echo $positionInRankingTxt;
			?>
			</div>
	  	</div>
  	</div>
  	<div class='row'>
  		<div class='col-md-12'>
  			<div class="alert alert-info" role="alert"><?php echo $LanguageInstance->get('What give you points for making it to the top of the ranking?')?><ul>
				<li>
                    <?php
                    $pointspergivenfeedback = defined('POINTS_PER_GIVEN_FEEDBACK') ? POINTS_PER_GIVEN_FEEDBACK : 10;
                    echo $LanguageInstance->getTag('Giving feedback to my partner after each tandem: %s points', $pointspergivenfeedback);
                    ?>.
                </li>
                    <li>
                        <?php
                        $pointsperspeakingminute = defined('POINTS_PER_SPEAKING_MINUTE') ? POINTS_PER_SPEAKING_MINUTE : 1;
                        echo $LanguageInstance->getTag('Speaking time: %s point per minute (only if you gave feedback!)', $pointsperspeakingminute);
                        ?>.
                    </li>

                    <li>
                    <?php
                    $pointsperfeedbackstarreceived = defined('POINTS_PER_FEEDBACK_STAR_RECEIVED') ? POINTS_PER_FEEDBACK_STAR_RECEIVED : 2;
                    echo $LanguageInstance->getTag('The ratings of the feedback I have given: %s points for each star I receive', $pointsperfeedbackstarreceived);
                    ?>.
                </li>
				<li>
                    <?php
                    $pointsperratedpartnerfeedback = defined('POINTS_PER_RATED_PARTNER_FEEDBACK') ? POINTS_PER_RATED_PARTNER_FEEDBACK : 5;
                    echo $LanguageInstance->getTag('Giving a star-rating to the feedback I receive: %s points', $pointsperratedpartnerfeedback);
                    ?>.
                </li>
				<li>
                    <?php
                    $pointspersurvey    = defined( 'POINTS_PER_SURVEY_COMPLETED' ) ? POINTS_PER_SURVEY_COMPLETED : 50;
                    echo $LanguageInstance->getTag('Complete first survey: %s points', $pointspersurvey);
                    ?>.
                </li>
				<!--li>
                    <?php
                    $pointsperbadge    = 50;
                    echo $LanguageInstance->getTag('Each badge: %s points', $pointspersurvey);
                    ?>.
                </li-->
			</ul>
			    <?php echo $LanguageInstance->get('The Ranking closes at 10pm Barcelona time on Tuesdays. If you\'re the top of the Ranking at this time, you win a prize!')?>
  			</div>
  		</div>
  	</div>
  	<!--div class='row'>
  		<div class='col-md-12'>
  			<div class="alert alert-info" role="alert"><?php //echo $LanguageInstance->get('top_10_ranking_message'); ?><br>
            <?php //echo $LanguageInstance->get('For each Tandem activity you complete, you will receive points based on the feedback you provide, and the feedback you receive'); ?>.
  			</div>
  		</div>
  	</div-->
  	<div class='row'>
	<?php if (!$_SESSION[USE_WAITING_ROOM_NO_TEAMS]) { ?>
	  <div class="col-md-6">
	  <h3 class='green-for-english'><?php echo $LanguageInstance->get('Ranking for learners of English');?></h3>
  		<table class="table table-striped <?php if($show_teacher_view) echo 'table-condensed'; ?>">
  		<tr>
		  	<th class='text-center'><?php echo $LanguageInstance->get('Position');?></th>
		  	<th><?php echo $LanguageInstance->get('User');?></th>
		  	<th><?php echo $LanguageInstance->get('Points');?></th>
		  	<?php if ($show_teacher_view) {echo "<th>".$LanguageInstance->get('Total time')."</th>";
		  	 	echo "<th>".$LanguageInstance->get('Number of Tandems')."</th>";
		  	 	if (!$feedback_selfreflection_form) {
                    echo "<th>" . $LanguageInstance->get('Accuracy') . "</th>";
                    echo "<th>" . $LanguageInstance->get('Fluency') . "</th>";
                    echo "<th>" . $LanguageInstance->get('Overall Grade') . "</th>";
                }
		  }
		  	?>
  		</tr>
        <?php print_ranking_table_rows($usersRanking['en'], $englishRankingPosition, $show_teacher_view, $LanguageInstance, $feedback_selfreflection_form); ?>
  </table>
  </div>
  <div class='col-md-6'>
    <h3 class='purple-for-spanish'><?php echo $LanguageInstance->get('Ranking for learners of Spanish');?></h3>
  	<table class="table table-striped <?php if($show_teacher_view) { echo 'table-condensed'; } ?>">
  	<tr>
	  	<th class='text-center'><?php echo $LanguageInstance->get('Position');?></th>
	  	<th><?php echo $LanguageInstance->get('User');?></th>
	  	<th><?php echo $LanguageInstance->get('Points');?></th>
	  	<?php
		  	 if($show_teacher_view){
		  	 	echo "<th>".$LanguageInstance->get('Total time')."</th>";
		  	 	echo "<th>".$LanguageInstance->get('Number of Tandems')."</th>";
                if (!$feedback_selfreflection_form) {
                    echo "<th>" . $LanguageInstance->get('Accuracy') . "</th>";
                    echo "<th>" . $LanguageInstance->get('Fluency') . "</th>";
                    echo "<th>" . $LanguageInstance->get('Overall Grade') . "</th>";
                }
		  	 }
		?>
  	</tr>
 	<?php print_ranking_table_rows($usersRanking['es'], $spanishRankingPosition, $show_teacher_view, $LanguageInstance, $feedback_selfreflection_form); ?>
  </table>
  </div>
	<?php } else { // No language teams. ?>
        <div class='col-md-offset-3  col-md-6'>
            <h3 class='purple-for-spanish'><?php echo $LanguageInstance->get('Ranking for learners'); ?></h3>
            <table class="table table-striped <?php if ($show_teacher_view) { echo 'table-condensed'; } ?>">
                <tr>
                    <th class='text-center'><?php echo $LanguageInstance->get('Position'); ?></th>
                    <th><?php echo $LanguageInstance->get('User'); ?></th>
                    <th><?php echo $LanguageInstance->get('Points'); ?></th>
                    <?php
                    if ($show_teacher_view) {
                        echo "<th>" . $LanguageInstance->get('Total time') . "</th>";
                        echo "<th>" . $LanguageInstance->get('Number of Tandems') . "</th>";
                        if (!$feedback_selfreflection_form) {
                            echo "<th>" . $LanguageInstance->get('Accuracy') . "</th>";
                            echo "<th>" . $LanguageInstance->get('Fluency') . "</th>";
                            echo "<th>" . $LanguageInstance->get('Overall Grade') . "</th>";
                        }
                    }
                    ?>
                </tr>
                <?php print_ranking_table_rows($usersRanking['all_lang'], $globalRankingPosition, $show_teacher_view, $LanguageInstance, $feedback_selfreflection_form); ?>
            </table>
        </div>
	<?php } ?>
  </div>
</div>
<?php include_once __DIR__ .'/js/google_analytics.php';?>
</body>
</html>
