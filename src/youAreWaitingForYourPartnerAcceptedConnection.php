<?php 
require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/utils.php';
require_once dirname(__FILE__).'/classes/gestorBD.php';


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Tandem</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link media="screen" rel="stylesheet" href="css/default.css" />
<link rel="stylesheet" type="text/css" href="css/tandem.css" media="all" />
<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>


<script type="text/javascript">
	$( document ).ready(function() {
		$( "#send_email" ).click(function() {
			$( "#send_email" ).attr('disabled', true);
			$.ajax({
				type: 'POST',
				url: "api/sendEmailToPartner.php",
				data : {
				},
				dataType: "JSON",
				success: function(json){
					if(json  &&  typeof json.emailsent !== "undefined" &&  json.emailsent == 1){
						alert("<?php echo $LanguageInstance->get('Email sent')?>");
					} else {
						alert("<?php echo $LanguageInstance->get('There are is error in sending the email to your partner')?>");
					}

				},
				fail: function() {
					alert("<?php echo $LanguageInstance->get('There are is error in sending the email to your partner')?>");
				}
			});
		});
	});
</script>
<!-- End Save for Web Styles -->
</head>
<body id="home">
<!--div>
	<img id="home" src="images/final1.png" width="310" height="85" alt="" />
</div-->
<div class='container'>
<div class='row'>
<div class='col-md-12'>
<p></p>
	<div class="text">
		<p><?php echo $LanguageInstance->get('You have accepted the videochat session')?>.</p>
		<p><?php echo $LanguageInstance->get('You are waiting for your partner to accept it to start Tandem.')?></p>
		<p><?php echo $LanguageInstance->get('Do you want to send and email to your partner?')?> <?php echo $LanguageInstance->get('This email will provide the details to access the current Tandem')?></p>
		<p><button id="send_email" class="btn btn-primary"><?php echo $LanguageInstance->get('Send email')?></button><small>
				<?php echo $LanguageInstance->get('You only can send one email for Tandem')?>
			</small></p>

	</div>
<p></p>
</div></div></div>
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
</body>
</html>
