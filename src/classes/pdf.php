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
        $this->SetFont('helvetica', 'B', 20);
        // Title
        $this->Cell(0, 0, $this->LanguageInstance->get("List of all your tandems").' '.$this->username, 0, false, 'C', 0, '', 0, false, 'M', 'B');
*/

        $this->Image($this->image_file, 10, 10, 26, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
        $this->Cell(0, 26, $this->LanguageInstance->getTag("List of all %s tandems",$this->username), 0, false, 'C', 0, '', 0, false, 'M', 'M');        
        $style = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(3, 22, 208, 22, $style);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
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
// helvetica or times to reduce file size.
$pdf->SetFont('helvetica', '', 14, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

// set text shadow effect
//$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

// Set some content to print
$html = "
	<style>
	 ul li{font-size:11pt}
	 p {font-size:12pt}
	 span{font-size: 17pt;font-weight: bold;}
	</style>
";
$html .= "<body>";
$user_position_ranking = 0;
$user_data = $gestorBD->getRankingUserData($user_id,$course_id);
if ($user_data && isset($user_data['lang'])) {
	$user_position_ranking = $gestorBD->getUserRankingPosition($user_id,$user_data['lang'],$course_id);
}
$show_tandem = false;
if(!empty($feedBacks)){

	foreach($feedBacks as $key => $value){
		if(!empty($value['feedback_form'])){

			if (!$show_tandem) {
				$html .="<p>".$LanguageInstance->get('SUMMARY')."<div align=\"center\"><table width=\"50%\" border=\"1\">";
				if ($user_position_ranking>0) {
					$html  .= "<tr><td>".$LanguageInstance->get('Ranking Position').": <b>".$user_position_ranking."</b></td></tr>";
				}
				$html .="<tr><td>".$LanguageInstance->get('Fluency').": <b>".$user_data['fluency']."%</b></td></tr>
						<tr><td>".$LanguageInstance->get('Accuracy').": <b>".$user_data['accuracy']."%</b></td></tr>
						<tr><td>".$LanguageInstance->get('Overall Grade').": <b>".getSkillsLevel(getOverallAsIdentifier($user_data['overall_grade']),$LanguageInstance)."</b></td></tr>
					</table></div>
				</p>";						
			}
			$show_tandem = true;

			$value['feedback_form'] = @unserialize($value['feedback_form']);
			 $title = str_replace("%1",$value['created'],$LanguageInstance->get("Tandem session created %1 with a total duration of %2"));
			 $title = str_replace("%2",$value['total_time'],$title);
			$html .= "<div ><b>".$title."</b>";
			$html .= "<p style='font-size: 10pt;'>".$LanguageInstance->get('Review your partner\'s contribution') ."</p>";
			$html .="<p><ul>
							<li >".$LanguageInstance->get('Fluency').": <b>".$value['feedback_form']->fluency."%</b></li>
							<li >".$LanguageInstance->get('Accuracy').": <b>".$value['feedback_form']->accuracy."%</b></li>
							<li >".$LanguageInstance->get('Overall Grade').": <b>".getSkillsLevel($value['feedback_form']->grade,$LanguageInstance)."</b></li>				
							<li >".$LanguageInstance->get('Pronunciation').": <b>".$value['feedback_form']->pronunciation."</b></li>
							<li >".$LanguageInstance->get('Vocabulary').": <b>".$value['feedback_form']->vocabulary."</b></li>
							<li >".$LanguageInstance->get('Grammar').": <b>".$value['feedback_form']->grammar."</b></li>
							<li >".$LanguageInstance->get('Other Observations').": <b>".$value['feedback_form']->other_observations."</b></li>
						</ul>
			</p>";
			$partnerFeedback = $gestorBD->checkPartnerFeedback($value['id_tandem'],$value['id']);
			$html .= "<p class='tit'>".$LanguageInstance->get('View received Feedback')."</p>";
			if(!empty($partnerFeedback)){
				$partnerFeedback = @unserialize($partnerFeedback);			
				if(is_object($partnerFeedback)){				
					$html .="<p> <ul>
									<li >".$LanguageInstance->get('Fluency').": <b>".$partnerFeedback->fluency."%</b></li>
									<li >".$LanguageInstance->get('Accuracy').": <b>".$partnerFeedback->accuracy."%</b></li>
									<li >".$LanguageInstance->get('Overall Grade').": <b>".getSkillsLevel($partnerFeedback->grade,$LanguageInstance)."</b></li>				
									<li >".$LanguageInstance->get('Pronunciation').": <b>".$partnerFeedback->pronunciation."</b></li>
									<li >".$LanguageInstance->get('Vocabulary').": <b>".$partnerFeedback->vocabulary."</b></li>
									<li >".$LanguageInstance->get('Grammar').": <b>".$partnerFeedback->grammar."</b></li>
									<li >".$LanguageInstance->get('Other Observations').": <b>".$partnerFeedback->other_observations."</b></li>
						</ul>
					</p>";
			}else
			 $html .= "<ul><li>".$LanguageInstance->get('partner_feedback_not_available')."</li></ul>";
			}else
			$html .= "<ul><li>".$LanguageInstance->get('partner_feedback_not_available')."</li></ul>";
			
			$html .= "</div>";
		}
}
}
if (!$show_tandem) {
	$html .= "<br><br>".$LanguageInstance->get('No tandems available');
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