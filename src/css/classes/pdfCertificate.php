<?php
require_once ("tcpdf_min/tcpdf.php");
require_once dirname(__FILE__) . '/lang.php';


class TandemCertificationPDF extends TCPDF {

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
        

        $this->Image($this->image_file, 50, 0, 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->Line(45, 50, 252, 50);
    }

    // Page footer
    public function Footer() {
		$speakapps_logo = 'http://mooc.speakapps.org/wp-content/uploads/2014/08/speakapps_logo3_small-e1407657668714.png';
        //$this->Image($speakapps_logo, 80, 250, 40, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

        $uoc_logo = 'http://mooc.speakapps.org/wp-content/uploads/2014/08/uoc-logo-fase3.png';
        $ub_logo = 'http://mooc.speakapps.org/wp-content/uploads/2016/09/realTIC_marca_DRETA-14.jpg';
        //$urv_logo = 'http://mooc.speakapps.org/wp-content/uploads/2014/08/logo_urv.png';
        $this->Image($speakapps_logo, 76, 186, 35, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->Image($uoc_logo, 123, 190, 60, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->Image($ub_logo, 190, 186, 50, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    }
}



function generateCertificatePdf($user_id, $course_id, $positionRanking, $certification_data){

    global $LanguageInstance;
    $gestorBD = new GestorBD();
    $username = $certification_data['data']->fullname;
    $identifier = $certification_data['data']->identifier;

    $course = $gestorBD->get_course_by_id($course_id);

    $user_data = $gestorBD->getRankingUserData($user_id,$course_id);

    $num_tandems = intval($user_data?$user_data['number_of_tandems']:0);
    $total_time = $user_data?$user_data['total_time']:0;
    $obj = secondsToTime($total_time);
    $time = '';
    $hours = '0';
    if ($obj['m']>20){
        $obj['h']	= $obj['h'] + 1;
    }
    if ($obj['h']>0) {
        $hours = $obj['h'];
    }
    $user_position_ranking = 0;
    if ($user_data && isset($user_data['lang'])) {
        $user_position_ranking = $gestorBD->getUserRankingPosition($user_id,$user_data['lang'],$course_id);
    }

    /*$feedBacks[0]['feedback_form'] = @unserialize($feedBacks[0]['feedback_form']);
    echo "<pre>";
    print_r($feedBacks);
    echo "</pre>";
    die();*/
    // create new PDF document
    // 					$orientation='P', $unit='mm', 	$format='A4', 	$unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false, $image_file=false, $LanguageInstance=false, $username='') {
    $pdf = new TandemCertificationPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 		'UTF-8', 		 false, 			false, 'http://mooc.speakapps.org/wp-content/uploads/2014/08/logo_mooc_speakapps1.png', $LanguageInstance, $username);

    // set document information
    $pdf->SetCreator('UOC');
    $pdf->SetTitle('Tandem');
    $pdf->SetSubject('Tandem MOOC Certificate '.$username);

    $signatura = 'http://mooc.speakapps.org/wp-content/uploads/2014/12/signatura.png';

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
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER+30);
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
         .header {font-size:28pt; color: rgb(97,45,123)}
         .tit {font-size:22pt; color: rgb(97,45,123)}
         .tit2 {font-size:16pt; color: rgb(97,45,123)}
         li {
            margin-top: 0px;
            margin-bottom: 0px;
            padding-right: 0px;
            padding-top: 0px;
         }
         ul {
            margin-left: 30px;
            margin-top: 0px;
            margin-bottom: 0px;
            margin-right: 0px;
            padding-left: 0px;
            padding-right: 0px;
            padding-top: 0px;
            padding-bottom: 0px;
        }
         .small {font-size:11pt}
         .center {text-align: center }
        </style>
    ";
    $html .= "<body>";

    $html .= "<br>";
    $html .= "<br>";

    $html .= "<div align=\"center\">";
    $html .= "<p class=\"header\">".$LanguageInstance->get($user_position_ranking<=10 && $user_position_ranking>0?"Special Mention Certificate":"Certificate of Completion")."</p>";
    if (!$identifier || strlen($identifier)==0) {
    $html .= "<br>";
    }
    $html .= "<span>".$LanguageInstance->get("awarded to")."</span>";
    $html .= "<br>";
    $html .= "<span class=\"tit\">".$username."</span>";
    if ($identifier && strlen($identifier)>0) {
        $html .= "<br>";
        $html .= "<span class=\"tit2\">".$LanguageInstance->get("ID").": ".$identifier."</span>";
    }
    $html .= "<br>";
    $html .= "<br>";
    $html .= "<span>".$LanguageInstance->get("has successfully completed the")."</span>";
    $html .= "<br>";
    $html .= "<br>";
    $html .= "<span class=\"tit\">".$LanguageInstance->get("English-Español Tandem MOOC")."</span>";
    $html .= "<br>";
    $html .= "<br>";
    $html .= "<br>";
    $html .= "<span>".$LanguageInstance->get("from October 17 to November 27, 2016")."</span>";
    $html .= "<br>";
    $html .= "<span>".$LanguageInstance->get("Duration: 6 weeks")."</span>";
    /*$html .= "<br>";
    $html .= "<span class=\"center\">".$LanguageInstance->get("The course objectives in relation to the learning of English/Spanish were").":</span>";
    $html .= "<ul>";
    $html .= "<li>".$LanguageInstance->get("To interact with a degree of fluency and spontaneity that allows for regular interaction with native speakers.")."</li>";
    $html .= "<li>".$LanguageInstance->get("To take an active part in discussion in familiar contexts, accounting for and sustaining one’s views.")."</li>";
    $html .= "<li>".$LanguageInstance->get("To practise basic communication strategies")."</li>";
    $html .= "<li>".$LanguageInstance->get("To self-regulate one's learning regarding speaking skills in a foreign language and evaluate others' speaking skills in your own language")."</li>";
    $html .= "</ul>";*/

    /*if ($user_position_ranking<=10 && $user_position_ranking>0) {
        $html .= "<br>";
        $html .= "<span class=\"small\">".$LanguageInstance->getTag("The aforementioned student was among the highest scorers of the course (position %s) for having achieved", $user_position_ranking)."</span>";
        $html .= "<ul>";
        $html .= "<li class=\"small\">".$LanguageInstance->getTag("%s Tandems", $num_tandems)."</li>";
        $html .= "<li class=\"small\">".$LanguageInstance->getTag("%s Hours of conversation", $hours)."</li>";
        $html .= "</ul>";
    } else {
        $html .= "<br>";
        $html .= "<br>";
        $html .= "<br>";
    }*/
    $html .= "<br>";
    $html .= "<table width=\"90%\">";
    $html .= "<tr>";
    $html .= "<td>";
    $html .= "<b>Dr  Christine Appel</b>";
    $html .= "<br>";
    $html .= "<img src=\"$signatura\" width=\"140px\">";
    $html .= "<br>";
    $html .= $LanguageInstance->get("Course Director");
    $html .= "</td>";
    $html .= "<td align=\"right\">";
    $html .= "<b>Barcelona, November 27, 2016</b>";
    $html .= "</td>";
    $html .= "</tr>";

    $html .= "</table>";
    $html .= "<br>";
    $html .= "<br>";
    $html .= "<div>";
    $html .= "</body>";



    // Print text using writeHTMLCell()
    //$pdf->writeHTMLCell(0, 0, '', '30', $html, 0, 1, 0, true, '', true);
    $pdf->SetY(20);
    $pdf->writeHTML($html,true,false,true,false,'');
    // ---------------------------------------------------------

    // Close and output PDF document
    // This method has several options, check the source code documentation for more information.
    $pdf->Output($username.'_certificate.pdf', 'D');


}

