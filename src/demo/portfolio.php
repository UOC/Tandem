<?php
use consumerlti\lti as lti;
require_once dirname(__FILE__).'/OAuth.php';
require_once dirname(__FILE__).'/TrivialStore.php';
?>
<html>
<head>
	<meta charset="UTF8">
	<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
<link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
</head>

<?php

/**
 * Posts the launch petition HTML
 *
 * @param $newparms     Signed parameters
 * @param $endpoint     URL of the external tool
 * @param $debug        Debug (true/false)
 */
function lti_post_launch_html($newparms, $endpoint, $debug=false) {
    //$debug = true;
    $r = "<form action=\"" . $endpoint .
        "\" name=\"ltiLaunchForm\" id=\"ltiLaunchForm\" method=\"post\" encType=\"application/x-www-form-urlencoded\">\n";

    // Contruct html for the launch parameters.
    foreach ($newparms as $key => $value) {
        $key = htmlspecialchars($key);
        $value = htmlspecialchars($value);
        if ( $key == "ext_submit" ) {
            $r .= "<input type=\"submit\"";
        } else {
            $r .= "<input type=\"hidden\" name=\"{$key}\"";
        }
        $r .= " value=\"";
        $r .= $value;
        $r .= "\"/>\n";
    }

    if ( $debug ) {
        $r .= "<script language=\"javascript\"> \n";
        $r .= "  //<![CDATA[ \n";
        $r .= "function basicltiDebugToggle() {\n";
        $r .= "    var ele = document.getElementById(\"basicltiDebug\");\n";
        $r .= "    if (ele.style.display == \"block\") {\n";
        $r .= "        ele.style.display = \"none\";\n";
        $r .= "    }\n";
        $r .= "    else {\n";
        $r .= "        ele.style.display = \"block\";\n";
        $r .= "    }\n";
        $r .= "} \n";
        $r .= "  //]]> \n";
        $r .= "</script>\n";
        $r .= "<a id=\"displayText\" href=\"javascript:basicltiDebugToggle();\">";
        $r .= "toggle debug data</a>\n";
        $r .= "<div id=\"basicltiDebug\" style=\"display:none\">\n";
        $r .= "<b>basicltiendpoint</b><br/>\n";
        $r .= $endpoint . "<br/>\n&nbsp;<br/>\n";
        $r .= "<b>basiclti_parameters</b><br/>\n";
        foreach ($newparms as $key => $value) {
            $key = htmlspecialchars($key);
            $value = htmlspecialchars($value);
            $r .= "$key = $value<br/>\n";
        }
        $r .= "&nbsp;<br/>\n";
        $r .= "</div>\n";
    }
    $r .= "</form>\n";

    if ( ! $debug ) {
        $r .= " <script type=\"text/javascript\"> \n" .
            "  //<![CDATA[ \n" .
            "    document.ltiLaunchForm.submit(); \n" .
            "  //]]> \n" .
            " </script> \n";
    }
    return $r;
}

/**
 * Signs the petition to launch the external tool using OAuth
 *
 * @param $oldparms     Parameters to be passed for signing
 * @param $endpoint     url of the external tool
 * @param $method       Method for sending the parameters (e.g. POST)
 * @param $oauth_consumoer_key          Key
 * @param $oauth_consumoer_secret       Secret
 */
function lti_sign_parameters($oldparms, $endpoint, $method, $oauthconsumerkey, $oauthconsumersecret) {

    $parms = $oldparms;

    $testtoken = '';

    $hmacmethod = new lti\OAuthSignatureMethod_HMAC_SHA1();
    $testconsumer = new lti\OAuthConsumer($oauthconsumerkey, $oauthconsumersecret, null);
    $accreq = lti\OAuthRequest::from_consumer_and_token($testconsumer, $testtoken, $method, $endpoint, $parms);
    $accreq->sign_request($hmacmethod, $testconsumer, $testtoken);

    $newparms = $accreq->get_parameters();

    return $newparms;
}


# ------------------------------
# START CONFIGURATION SECTION
#
$error_message = '';
if ($_POST){
	if (!isset($_POST['email']) || strlen($_POST['email'])==0 ||
	!isset($_POST['name']) || strlen($_POST['name'])==0
	|| !isset($_POST['language']) || strlen($_POST['language'])==0) {
		$error_message = ("<p class=\"alert alert-danger\">You have to fill all form data</p>");
	} else {
		$send_form = true;
	}
}
if ($send_form) {
	$resource_link_title  = "Tandem";  //Títol de l'enllaç
	$resource_link_title_desc  = "Tandem";  //Descripció de l'enllaç
	$launch_url = "http://tandem.speakapps.org/integration_tool_lti.php";
	$key = "MOOCspeakApps";
	$secret = "MOOCspeakApps2014*";

	$user_id = "mooc.".$_POST['email']; //User id in LMS
	$user_id = str_replace('@', '_', $user_id);
	$roles = "Learner"; //Valors possibles Administrator, Instructor, Learner or Other.
	if (in_array($_POST['email'], array('mappel@uoc.edu','mmalerba@uoc.edu',
		'jtpujola@ub.edu', 'mfondo@uoc.edu', 'abertranb@uoc.edu'))) {
			$roles = "Instructor";
		}
	$resource_link_id = "1"; //Identificador únic a la plataforma de l'enllaç
	$lis_person_name_full = $_POST['name']; //Nom complet
	$lis_person_name_family = ''; //Cognom
	$lis_person_name_given = $_POST['name']; //Nom
	$lis_person_contact_email_primary = $_POST['email']; //Email

	$context_id = "tandem_demo";  //Id del curs
	$context_title = "Tandem Test"; //Títol del curs
	$context_label = "Tandem"; //Nom curt del curs

	$tool_consumer_instance_guid = "tandem"; //Identificador del consumer
	$tool_consumer_instance_description = "Uoc";

	$launch_data = array(
		"user_id" => $user_id,
		"roles" => $roles,
		"resource_link_id" => $resource_link_id,
		"resource_link_title" => $resource_link_title,
		"resource_link_description" => $resource_link_title_desc,
		"lis_person_name_full" => $lis_person_name_full,
		"lis_person_name_family" => $lis_person_name_family,
        "launch_presentation_locale" => $_POST['language'],
        "lis_person_name_given" => $lis_person_name_given,
		"lis_person_contact_email_primary" => $lis_person_contact_email_primary,
		"lis_person_sourcedid" => $key.":".$user_id,
		"context_id" => $context_id,
		"context_title" => $context_title,
		"context_label" => $context_label,
		"tool_consumer_instance_guid" => $tool_consumer_instance_guid,
		"tool_consumer_instance_description" => $tool_consumer_instance_description,
		"custom_portfolio" => 1
	);

	#
	# END OF CONFIGURATION SECTION
	# ------------------------------
	$launch_data["lti_version"] = "LTI-1p0";
	$launch_data["lti_message_type"] = "basic-lti-launch-request";
	$launch_data["oauth_callback"] = "about:blank";

	$parms = lti_sign_parameters($launch_data, $launch_url, "POST", $key, $secret);

	$parms["ext_submit"] = "basiclti_submit";
	$debuglaunch = false;
	$content = lti_post_launch_html($parms, $launch_url, $debuglaunch);

	echo $content;
?>
<?php } else {?>


<body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
          </button>
          <a class="navbar-brand" href="#">Tandem Test</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
        </div><!--/.nav-collapse -->
      </div>
    </nav>
		<br><br>
    <div class="container">

      <div class="starter-template">
        <h1>Welcome</h1>
        <p class="lead">Here you will start a new Tandem.</p>
				<p class="lead"><i class="fa fa-headphones" aria-hidden="true">You will need a headphones. <a href="http://videoconference.speakapps.org/videochat/testEnvironment.htm" target="_blank">Try your audio and video settings</a></i></p>
<?php echo $error_message; ?>
				<form method="POST">
					<p><label for="name">Your name:</label><input name="name" type="text" class="form-control" /></p>
					<p><label for="email">Your email:</label><input name="email" type="email" class="form-control" /></p>
					<p><label for="language">Select language:</label><select name="language" class="form-control"><option value="es">I'm a learner of Spanish</option><option value="en">I'm a learner of English</option></select></p>
					  <p><input type="submit" class="btn btn-primary" value="Start"></p>
				</form>
      </div>

    </div><!-- /.container -->

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
</body>
<?php } ?>
</html>
