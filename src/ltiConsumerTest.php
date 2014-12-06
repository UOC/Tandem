<?php
require_once('classes/typeLTI.php');
require_once('classes/manageLTI.php');
require_once('classes/lang.php');
require_once('classes/utils.php');
if (!class_exists("bltiUocWrapper")) {
			if (file_exists(dirname(__FILE__).'/../IMSBasicLTI/uoc-blti/bltiUocWrapper.php')) {
				require_once dirname(__FILE__).'/../IMSBasicLTI/uoc-blti/bltiUocWrapper.php';
				require_once dirname(__FILE__).'/../IMSBasicLTI/ims-blti/blti_util.php';
				require_once dirname(__FILE__).'/../IMSBasicLTI/utils/UtilsPropertiesBLTI.php';
			} else {
				require_once 'IMSBasicLTI/uoc-blti/bltiUocWrapper.php';
				require_once 'IMSBasicLTI/ims-blti/blti_util.php';
				require_once 'IMSBasicLTI/utils/UtilsPropertiesBLTI.php';
			}
		}
	
	//    $context = get_context_instance(CONTEXT_COURSE, $instance->course);
	    $requestparams = array(

"ext_lms" => "bb-9.1.110082.0", 
//"oauth_nonce" => "7630461397002966",
"oauth_consumer_key" => "rug",
"context_label" => "LET-SPEAKAPPS-DEMO",
"resource_link_id" => "_6864437_1",
"lis_person_name_family" => "SpeakApps",
"oauth_callback" => "about:blank",
"launch_presentation_return_url" => "https://nestor.rug.nl/webapps/blackboard/execute/blti/launchReturn?course_id=_81752_1&content_id=_6864437_1&toGC=false",
"lti_version" => "LTI-1p0",
"tool_consumer_info_version" => "9.1.110082.0",
"oauth_signature_method" => "HMAC-SHA1",
"tool_consumer_instance_contact_email" => "nestorsupport@rug.nl",
"lti_message_type" => "basic-lti-launch-request",
"user_id" => "62c75bf5e0d44bd2a6c8fef9f5170fa6",
"tool_consumer_info_product_family_code" => "Blackboard Learn",
"tool_consumer_instance_guid" => "86c48fcfd82d44feb5c141f70f44bab9",
"launch_presentation_document_target" => "window",
"context_title" => "SpeakApps Integration",
"oauth_version" => "1.0",
"tool_consumer_instance_name" => "Rijksuniversiteit Groningen",
"lis_person_name_full" => " Instructor  SpeakApps",
"resource_link_title" => "Open Educational Resources database",
"context_id" => "ecbfab641cd24833bfa3735b67f96e9a",
"roles" => "urn:lti:role:ims/lis/Instructor",
"lis_person_contact_email_primary" => "abertranb@uoc.edu",
"lis_person_name_given" => "Instructor",
"launch_presentation_locale" => "en-US",
//"oauth_timestamp" => "1416303123",
"ext_launch_presentation_css_url" => "https://nestor.rug.nl/common/shared.css,https://nestor.rug.nl/branding/themes/rug-huisstijl/theme.css,https://nestor.rug.nl/branding/colorpalettes/rug-huiskleuren/colorpalette.css"
//'oauth_signature' => 'b00eYd9vgmnfcNf18BVQBWGJ3Ds=',
	    );
	    
$endpoint = 'http://oer.speakapps.org/bin/view/Main/WebHome';

	    $key = 'rug';
	    $secret = 'RUG_campus_2012';
		$parms = signParameters($requestparams, $endpoint, "POST", $key, $secret);

		var_dump($parms);
	