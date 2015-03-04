<?php
require_once dirname(__FILE__) . '/classes/lang.php';
require_once dirname(__FILE__) . '/classes/constants.php';
require_once dirname(__FILE__) . '/classes/gestorBD.php';
require_once dirname(__FILE__) . '/classes/utils.php';
require_once 'IMSBasicLTI/uoc-blti/lti_utils.php';



$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;

if (!$user_obj) {
//Tornem a l'index
	header('Location: index.php');
	die();
} else {	
	$gestorBD = new GestorBD();  	
	$usersRanking = $gestorBD->getUsersRanking($course_id);	
}

$show_teacher_view = false;
if ($user_obj->instructor== 1 || $user_obj->admin==1) {
	$isTeacher = true;
	$show_teacher_view = isset($_GET['student_view']) && $_GET['student_view']==1?false:true;
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

<?php
	//cmoyas change skin 
	if(is_file('skins/css/styleSkin.css')){
			echo '<link rel="stylesheet" type="text/css" media="all" href="skins/css/styleSkin.css" />';
	}
?>
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
			<a href="#" title="<?php echo $LanguageInstance->get('tandem_logo')?>">
				<?php //cmoyas change skin
					if(is_file('skins/img/logo_APP.png')) echo '<img src="skins/img/logo_APP.png" alt="'.$LanguageInstance->get('tandem_logo').'" />';
					else echo '<img src="css/images/logo_Tandem.png" alt="'.$LanguageInstance->get('tandem_logo').'" />';
				?>
			</a>					
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
				$getUserRankingPosition = $gestorBD->getUserRankingPosition($user_obj->id,$_SESSION['lang'],$course_id);			
				$positionInRankingTxt =  $LanguageInstance->get('Hello %1');
				$positionInRankingTxt = str_replace("%1",$gestorBD->getUserName($user_obj->id),$positionInRankingTxt);
				if($getUserRankingPosition > 0)
					$positionInRankingTxt .= $LanguageInstance->get(', your position in the ranking is ')."<b>".$getUserRankingPosition."</b>";			
				echo $positionInRankingTxt;			
			?>
			</div>
	  	</div>
  	</div>
  	<div class='row'>
  		<div class='col-md-12'>
  			<div class="alert alert-info" role="alert"><?php echo $LanguageInstance->get('top_10_ranking_message')?><br>
<?php echo $LanguageInstance->get('For each Tandem activity you complete, you will receive points based on the feedback you provide, and the feedback you receive')?>.
  			</div>
  		</div>  		
  	</div>
  	<div class='row'>
	  <div class="col-md-6">
	  <h3 class='green-for-english'><?php echo $LanguageInstance->get('Ranking for learners of English');?></h3>
  		<table class="table table-striped <?php if($show_teacher_view) echo 'table-condensed'; ?>">
  		<tr>
		  	<th class='text-center'><?php echo $LanguageInstance->get('Position');?></th>
		  	<th><?php echo $LanguageInstance->get('User');?></th>
		  	<th><?php echo $LanguageInstance->get('Points');?></th>
		  	<?php if ($show_teacher_view) {echo "<th>".$LanguageInstance->get('Total time')."</th>";
		  	 	echo "<th>".$LanguageInstance->get('Number of Tandems')."</th>";	
		  	 	echo "<th>".$LanguageInstance->get('Accuracy')."</th>";	
		  	 	echo "<th>".$LanguageInstance->get('Fluency')."</th>";	
		  	 	echo "<th>".$LanguageInstance->get('Overall Grade')."</th>";	
		  }	  	 
		  	?>
  		</tr>
 	<?php
	  if(!empty($usersRanking['en'])){
	  	$cont = 1;
	  	foreach($usersRanking['en'] as $f){
		  			  	
			  	$class='';
			  	if($cont <= 3) $class = 'class="success"';
			  	if($cont > 3 && $cont <= 10) $class = 'class="warning"';	
			  	echo "<tr $class>";
			  	echo "<td class='text-center'>".$cont."</td>";	  		  	
			  	echo "<td>".(isset($f['user'])?$f['user']:'')."</td>";			  	
			  	echo "<td>".(isset($f['points'])?$f['points']:'')."</td>";			  	
			  	if( $show_teacher_view) {$obj = secondsToTime($f['total_time']);$time = '';
                        if ($obj['h']>0) {
                            $time .= ($obj['h']<10?'0':'').$obj['h'].':';
                        }
                        $time .= ($obj['m']<10?'0':'').$obj['m'].':';
                        $time .= ($obj['s']<10?'0':'').$obj['s'];
		  	 	echo "<td>".$time."</td>";
		  	 	echo "<td>".intval($f['number_of_tandems'])."</td>";	
		  	 		echo "<td>".$f['accuracy']."</td>";
		  	 		echo "<td>".$f['fluency']."</td>";
		  	 		echo "<td>".getSkillsLevel(getOverallAsIdentifier($f['overall_grade']), $LanguageInstance)."</td>";
		  	 	}
	  		  	echo "</tr>";	
			  	$cont++;			
	  	}
	  }
  	?>
  </table>
  </div>
  <div class='col-md-6'>
    <h3 class='purple-for-spanish'><?php echo $LanguageInstance->get('Ranking for learners of Spanish');?></h3>
  	<table class="table table-striped <?php if($show_teacher_view) echo 'table-condensed'; ?>">
  	<tr>
	  	<th class='text-center'><?php echo $LanguageInstance->get('Position');?></th>
	  	<th><?php echo $LanguageInstance->get('User');?></th>
	  	<th><?php echo $LanguageInstance->get('Points');?></th>
	  	<?php
		  	 if($show_teacher_view){
		  	 	echo "<th>".$LanguageInstance->get('Total time')."</th>";	
		  	 	echo "<th>".$LanguageInstance->get('Number of Tandems')."</th>";	
		  	 	echo "<th>".$LanguageInstance->get('Accuracy')."</th>";	
		  	 	echo "<th>".$LanguageInstance->get('Fluency')."</th>";	
		  	 	echo "<th>".$LanguageInstance->get('Overall Grade')."</th>";	
		  	 }	  	 
		?>
  	</tr>
 	<?php
	  if(!empty($usersRanking['es'])){
	  	$cont = 1;
	  	foreach($usersRanking['es'] as $f){
		  	$class='';
		  	if($cont <= 3) $class = 'class="success"';
		  	if($cont > 3 && $cont <= 10) $class = 'class="warning"';	
		  	echo "<tr $class>";
		  	echo "<td class='text-center'>".$cont."</td>";
		  	//we only want to show the name of the top 3 , the rest just ....	  	
		  	echo "<td>".$f['user']."</td>";		  	
		  	echo "<td>".$f['points']."</td>";
		  	if($show_teacher_view){
		  		$obj = secondsToTime($f['total_time']);
                        $time = '';
                        if ($obj['h']>0) {
                            $time .= ($obj['h']<10?'0':'').$obj['h'].':';
                        }
                        $time .= ($obj['m']<10?'0':'').$obj['m'].':';
                        $time .= ($obj['s']<10?'0':'').$obj['s'];
		  	 		echo "<td>".$time."</td>";
		  	 		echo "<td>".intval($f['number_of_tandems'])."</td>";	
		  	 		echo "<td>".$f['accuracy']."</td>";
		  	 		echo "<td>".$f['fluency']."</td>";
		  	 		echo "<td>".getSkillsLevel(getOverallAsIdentifier($f['overall_grade']), $LanguageInstance)."</td>";

		  	}
		  	echo "</tr>";	

		  	$cont++;
	  	}
	  }
  	?>
  </table>
  </div>
  </div>
</div>



</body>
</html>


