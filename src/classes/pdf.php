<?php
require_once ("tcpdf_min/tcpdf.php");
require_once dirname(__FILE__) . '/lang.php';



function generatePDF($user_id,$course_id){

global $LanguageInstance;
$gestorBD = new GestorBD();
$feedBacks = $gestorBD->getAllUserFeedbacks($user_id,$course_id);	

/*$feedBacks[0]['feedback_form'] = @unserialize($feedBacks[0]['feedback_form']);
echo "<pre>";
print_r($feedBacks);
echo "</pre>";
die();*/
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator('UOC');
$pdf->SetTitle('Tandem');
$pdf->SetSubject('Tandem Portfolio');


// set default header data
$pdf->SetHeaderData('logo_Tandem.png', PDF_HEADER_LOGO_WIDTH, '', '', array(0,64,255), array(87,87,89));
$pdf->setFooterData(array(0,64,0), array(87,87,89));

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
$html .= "<div><span>List of all your tandems</span></div>";


if(!empty($feedBacks)){
	foreach($feedBacks as $key => $value){
		$value['feedback_form'] = @unserialize($value['feedback_form']);
		 $title = str_replace("%1",$value['created'],$LanguageInstance->get("Tandem session created %1 with a total duration of %2"));
		 $title = str_replace("%2",$value['total_time'],$title);
		$html .= "<div ><b>".$title."</b>";
		$html .= "<p style='font-size: 10pt;'>".$LanguageInstance->get('Review your partner\'s contribution') ."</p>";
		$html .="<p><ul>
						<li >".$LanguageInstance->get('Fluency').": <b>".$value['feedback_form']->fluency."%</b></li>
						<li >".$LanguageInstance->get('Accuracy').": <b>".$value['feedback_form']->accuracy."%</b></li>
						<li >".$LanguageInstance->get('Overall Grade').": <b>".$gestorBD->getSkillsLevel($value['feedback_form']->grade,$LanguageInstance)."</b></li>				
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
								<li >".$LanguageInstance->get('Overall Grade').": <b>".$gestorBD->getSkillsLevel($partnerFeedback->grade,$LanguageInstance)."</b></li>				
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
}else
$html .="No tandems available";


$html .= "</body>";



// Print text using writeHTMLCell()
//$pdf->writeHTMLCell(0, 0, '', '30', $html, 0, 1, 0, true, '', true);
$pdf->writeHTML($html,true,false,true,false,'');
// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('example_001.pdf', 'I');


}