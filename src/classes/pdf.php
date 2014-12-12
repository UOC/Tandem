<?php
require_once ("tcpdf_min/tcpdf.php");
require_once dirname(__FILE__) . '/lang.php';


class TandemPDF extends TCPDF {

	protected $LanguageInstance = false;
	protected $username = '';
	protected $image_file = false;

	public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false, $image_file=false, $LanguageInstance=false, $username='') {
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
		$this->LanguageInstance = $LanguageInstance;
		$this->username = $username;
		$this->image_file = $image_file;
	}
	

    //Page header
    public function Header() {
        // Logo
        /*$this->Image($this->image_file, 2, 0, 0, 0, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('times', 'B', 20);
        // Title
        $this->Cell(0, 0, $this->LanguageInstance->get("List of all your tandems").' '.$this->username, 0, false, 'C', 0, '', 0, false, 'M', 'B');
*/

        $this->Image($this->image_file, 10, 10, 26, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('times', 'B', 20);
        $this->SetTextColor(110, 106, 110); 
        // Title
        $this->Cell(0, 55, "         ".$this->LanguageInstance->getTag("%s portfolio",$this->username), 0, false, 'C', 0, '', 0, false, 'M', 'M');        
        $style = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(3, 22, 208, 22, $style);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('times', 'I', 8);
        // Page number
        $this->Cell(0, 10, $this->LanguageInstance->getTag("Generated on %s", date('Y-m-d')).'- Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, 'R', 0, false, 'T', 'M');
    }
}



function generatePDF($user_id,$course_id){

global $LanguageInstance;
$gestorBD = new GestorBD();
$feedBacks = $gestorBD->getAllUserFeedbacks($user_id,$course_id);	
$username = $gestorBD->getUserName($user_id);
/*$feedBacks[0]['feedback_form'] = @unserialize($feedBacks[0]['feedback_form']);
echo "<pre>";
print_r($feedBacks);
echo "</pre>";
die();*/
// create new PDF document
// 					$orientation='P', $unit='mm', 	$format='A4', 	$unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false, $image_file=false, $LanguageInstance=false, $username='') {
$pdf = new TandemPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 		'UTF-8', 		 false, 			false, dirname(__FILE__).'/tcpdf_min/logo_Tandem.png', $LanguageInstance, $username);

// set document information
$pdf->SetCreator('UOC');
$pdf->SetTitle('Tandem');
$pdf->SetSubject('Tandem Portfolio '.$username);

// set default header data
//$pdf->SetHeaderData('logo_Tandem.png', PDF_HEADER_LOGO_WIDTH, $LanguageInstance->get("List of all your tandems").' '.$username, '', array(0,64,255), array(87,87,89));
//$pdf->setFooterData(array(0,64,0), array(87,87,89));

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// times or times to reduce file size.
$pdf->SetFont('times', '', 12, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

// set text shadow effect
//$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

// Set some content to print
$html = "
	<style>
	 .tit {font-size:14pt}
	 .green {color: #6E6A6E}
	 h2 {color: rgb(97,45,123)}
	</style>
";
$html .= "<body>";
$user_position_ranking = 0;
$user_data = $gestorBD->getRankingUserData($user_id,$course_id);
if ($user_data && isset($user_data['lang'])) {
	$user_position_ranking = $gestorBD->getUserRankingPosition($user_id,$user_data['lang'],$course_id);
}

$html .= "<h2>".strtoupper($LanguageInstance->get("Before this course"))."</h2>";
$firstProfileForm  = $gestorBD->getUserPortfolioProfile("first",$user_id);
error_log("USER ID $user_id TTT ".serialize($firstProfileForm));
$profileFluency = isset($firstProfileForm['data']->fluency) ? $firstProfileForm['data']->fluency : '0'; 
$accuracyProfile = isset($firstProfileForm['data']->accuracy) ? $firstProfileForm['data']->accuracy : '0';
$myPronunciation = isset($firstProfileForm['data']->improve_pronunciation) ? $firstProfileForm['data']->improve_pronunciation :'';
$myVocabulary = !empty($firstProfileForm['data']->improve_vocabulary) ? $firstProfileForm['data']->improve_vocabulary : '';
$myGrammar = !empty($firstProfileForm['data']->improve_grammar)?$firstProfileForm['data']->improve_grammar:'';

define(SPACE_PX, '8px');

$html .= '<table><tr><td width="'.SPACE_PX.'"></td><td width="99%">';
$html .= "<span class=\"green\"><b>".$LanguageInstance->get("My language level was")."</b></span><br><br>";
$html .= '<strong>'. $LanguageInstance->get('Grade your speaking skills').': </strong>'.getSkillsLevel($firstProfileForm['data']->skills_grade, $LanguageInstance).'<br>'.
	   	'<strong>'. $LanguageInstance->get('Fluency').': </strong>'.$profileFluency.' %<br>'.
	   	'<strong>'. $LanguageInstance->get('Accuracy').': </strong>'.$accuracyProfile.' %<br>'.
	   	'<strong>'. $LanguageInstance->get('My pronunciation').': </strong>'.$myPronunciation.'<br>'.
	   	'<strong>'. $LanguageInstance->get('My vocabulary').': </strong>'.$myVocabulary.'<br>'.
	   	'<strong>'. $LanguageInstance->get('My grammar').': </strong>'.$myGrammar.'<br>';

$html .= '</td></tr></table>';
$html .= "<h2>".strtoupper($LanguageInstance->get("During this course"))."</h2>";
$html .= '<table><tr><td width="'.SPACE_PX.'"></td><td width="99%">';
$html .= "<span class=\"green\"><b>".$LanguageInstance->get("portfolio_header")."</b></span>";
$show_tandem = false;
if(!empty($feedBacks)){

	foreach($feedBacks as $key => $value){
		if(!empty($value['feedback_form'])){

			if (!$show_tandem) {
				$html .="<br><br><span class=\"tit\">".$LanguageInstance->get('SUMMARY')."</span><br>";
				$html .= "<b>".$LanguageInstance->get('Ranking Position').": </b>".$user_position_ranking."<br>
							<b>".$LanguageInstance->get('Fluency').": </b>".$user_data['fluency']." %<br>				
							<b>".$LanguageInstance->get('Accuracy').": </b>".$user_data['accuracy']." %<br>
							<b>".$LanguageInstance->get('Overall Grade').": </b>".getSkillsLevel(getOverallAsIdentifier($user_data['overall_grade']),$LanguageInstance)."<br>	";
			}
			$show_tandem = true;

			$value['feedback_form'] = @unserialize($value['feedback_form']);
			 $title = str_replace("%1",$value['created'],$LanguageInstance->get("Tandem session created %1 with a total duration of %2"));
			 $title = str_replace("%2",$value['total_time'],$title);
			$html .= "<div class=\"tit\"><b>".$title."</b>";
			$html .= "<p class=\"tit\">".strtoupper($LanguageInstance->get('Sent Feedback')) ."</p>";
			$html .="<b>".$LanguageInstance->get('Fluency').": </b>".$value['feedback_form']->fluency."%<br>
							<b>".$LanguageInstance->get('Accuracy').": </b>".$value['feedback_form']->accuracy."%<br>
							<b>".$LanguageInstance->get('Overall Grade').": </b>".getSkillsLevel($value['feedback_form']->grade,$LanguageInstance)."<br>				
							<b>".$LanguageInstance->get('Pronunciation').": </b>".$value['feedback_form']->pronunciation."<br>
							<b>".$LanguageInstance->get('Vocabulary').": </b>".$value['feedback_form']->vocabulary."<br>
							<b>".$LanguageInstance->get('Grammar').": </b>".$value['feedback_form']->grammar."<br>
							<b>".$LanguageInstance->get('Other Observations').": </b>".$value['feedback_form']->other_observations."<br>
				";
			$partnerFeedback = $gestorBD->checkPartnerFeedback($value['id_tandem'],$value['id']);
			$html .= "<p class=\"tit\">".strtoupper($LanguageInstance->get('Received Feedback'))."</p>";
			if(!empty($partnerFeedback)){
				$partnerFeedback = @unserialize($partnerFeedback);			
				if(is_object($partnerFeedback)){				
					$html .="		<b>".$LanguageInstance->get('Fluency').": </b>".$partnerFeedback->fluency."%<br>
									<b>".$LanguageInstance->get('Accuracy').": </b>".$partnerFeedback->accuracy."%<br>
									<b>".$LanguageInstance->get('Overall Grade').": </b>".getSkillsLevel($partnerFeedback->grade,$LanguageInstance)."<br>				
									<b>".$LanguageInstance->get('Pronunciation').": </b>".$partnerFeedback->pronunciation."<br>
									<b>".$LanguageInstance->get('Vocabulary').": </b>".$partnerFeedback->vocabulary."<br>
									<b>".$LanguageInstance->get('Grammar').": </b>".$partnerFeedback->grammar."<br>
									<b>".$LanguageInstance->get('Other Observations').": </b>".$partnerFeedback->other_observations."<br>
						
					<hr>";
			}else
			 $html .= "<b class=\"tit\">".$LanguageInstance->get('partner_feedback_not_available')."</b>";
			}else
			$html .= "<b class=\"tit\">".$LanguageInstance->get('partner_feedback_not_available')."</b>";
			
			$html .= "</div>";
		}
}
}
if (!$show_tandem) {
	$html .= "<br><br>".$LanguageInstance->get('No tandems available');
}
$html .= '</td></tr></table>';

if (SHOW_SECOND_FORM) {
	$secondProfileForm  = $gestorBD->getUserPortfolioProfile("second",$user_id);
	if (!empty($secondProfileForm) && $secondProfileForm) {

			$html .= "<h2>".strtoupper($LanguageInstance->get("After this course"))."</h2>";
		$html .= '<table><tr><td width="'.SPACE_PX.'"></td><td width="99%">';
		$html .= "<span class=\"green\"><b>".$LanguageInstance->get("My language level is")."</b></span><br><br>";


		$profileFluency = isset($secondProfileForm['data']->fluency) ? $secondProfileForm['data']->fluency : '';
		$accuracyProfile = isset($secondProfileForm['data']->accuracy) ? $secondProfileForm['data']->accuracy : '';
		$profileVocabulary = isset($secondProfileForm['data']->vocabulary) ? $secondProfileForm['data']->vocabulary : '';
		$myGrammar = isset($secondProfileForm['data']->grammar) ? $secondProfileForm['data']->grammar : '';
		$achived_objectives_proposed = isset($secondProfileForm['data']->achived_objectives_proposed)?ucfirst(getScaleGrade($LanguageInstance,$secondProfileForm['data']->achived_objectives_proposed)):'';
		$what_I_can_do_better = isset($secondProfileForm['data']->what_I_can_do_better) ? $secondProfileForm['data']->what_I_can_do_better : '';
		$how_I_can_do_improve = isset($secondProfileForm['data']->how_I_can_do_improve) ? $secondProfileForm['data']->how_I_can_do_improve : '';
		$received_feedback_help_to_improve = isset($secondProfileForm['data']->received_feedback_help_to_improve)?ucfirst(getScaleGrade($LanguageInstance,$secondProfileForm['data']->received_feedback_help_to_improve)):'';
		$what_feedback_do_received = isset($secondProfileForm['data']->what_feedback_do_received) ? $secondProfileForm['data']->what_feedback_do_received : '';
		$feedback_to_partner_help_me = isset($secondProfileForm['data']->feedback_to_partner_help_me)?ucfirst(getScaleGrade($LanguageInstance,$secondProfileForm['data']->feedback_to_partner_help_me)):'';
		$how_feedback_to_partner_help_me = isset($secondProfileForm['data']->how_feedback_to_partner_help_me) ? $secondProfileForm['data']->how_feedback_to_partner_help_me : '';


		$have_more_confidence = isset($secondProfileForm['data']->have_more_confidence)?ucfirst(getScaleGrade($LanguageInstance,$secondProfileForm['data']->have_more_confidence)):'';
		$can_apply_the_learning = isset($secondProfileForm['data']->can_apply_the_learning) ? ucfirst(getScaleGrade($LanguageInstance,$secondProfileForm['data']->can_apply_the_learning)) : '';
		$know_how_apply_it = isset($secondProfileForm['data']->know_how_apply_it)?ucfirst(getScaleGrade($LanguageInstance,$secondProfileForm['data']->know_how_apply_it)):'';

		$html .= '<strong>'. $LanguageInstance->get('My language level is').': </strong><br>'.$LanguageInstance->get($secondProfileForm['data']->my_language_level).'<br><br>'.
			   	'<strong>'. $LanguageInstance->get('Could you asses your target level now?').' </strong>'.
			   	'<ul><li><strong>'. $LanguageInstance->get('Fluency').': </strong>'.$profileFluency.' %</li>'.
			   	'<li><strong>'. $LanguageInstance->get('Accuracy').': </strong>'.$accuracyProfile.' %</li>'.
			   	'<li><strong>'. $LanguageInstance->get('Vocabulary').': </strong>'.$profileVocabulary.' %</li>'.
			   	'<li><strong>'. $LanguageInstance->get('Grammar').': </strong>'.$myGrammar.' %</li></ul>'.
			   	'<strong>'. $LanguageInstance->get('I have achieved the objectives I set at the beginning of the course').': </strong><br>'.$achived_objectives_proposed.'<br><br>'.
			   	'<strong>'. $LanguageInstance->get('What things could you have done better in this course?').' </strong><br>'.$what_I_can_do_better.'<br><br>'.
			   	'<strong>'. $LanguageInstance->get('After your participation in the course, are you more aware of how to improve your language level?').' </strong><br>'.$how_I_can_do_improve.'<br><br>'.
			   	'<strong>'. $LanguageInstance->get('The feedback I have been given has helped me to improve').': </strong><br>'.$received_feedback_help_to_improve.'<br><br>'.
			   	'<strong>'. $LanguageInstance->get('What aspects of the feedback provided to you were helpful and what were not?').' </strong><br>'.$what_feedback_do_received.'<br><br>'.
			   	'<strong>'. $LanguageInstance->get('Giving feedback to my partners has also helped me in the learning process').': </strong><br>'.$feedback_to_partner_help_me.'<br><br>'.
			   	'<strong>'. $LanguageInstance->get('Explain how').': </strong><br>'.$how_feedback_to_partner_help_me.'<br><br>'.
			   	'<strong>'. $LanguageInstance->get('I feel more confident when speaking the target language outside of the course').': </strong><br>'.$have_more_confidence.'<br><br>'.
			   	'<strong>'. $LanguageInstance->get('Thanks to this course I am able to use the language in different contexts, such as my personal life, at a professional level, in social networks (Facebook, etc)').': </strong><br>'.$can_apply_the_learning.'<br><br>'.
			   	'<strong>'. $LanguageInstance->get('I know how to use what I’ve learned from the course in my daily life').': </strong><br>'.$know_how_apply_it.'<br>';


		$html .= '</td></tr></table>';

	}
}

$html .= "</body>";



// Print text using writeHTMLCell()
//$pdf->writeHTMLCell(0, 0, '', '30', $html, 0, 1, 0, true, '', true);
$pdf->SetY(20);
$pdf->writeHTML($html,true,false,true,false,'');
// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output($username.'_tandems.pdf', 'D');


}