<?php 
require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/utils.php';
require_once dirname(__FILE__).'/classes/gestorBD.php';

$is_final = true;
include_once(dirname(__FILE__).'/classes/register_action_user.php');

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
$(document).ready(function(){
	var intTimerNow;
	closeIT = function(){
		//Lets stop the sound();
		parent.$.fn.hideSayGoodbye();
	}
});
</script>
</head>
<body id="home">
<div class='container'>
	<div class='row'>
		<div class='col-md-12'>
			&nbsp;
		</div>
	</div>
	<div class='row'>
		<div class='col-md-12'>
			<?php echo $LanguageInstance->get('Are you sure you want to end this videochat session?');?>
                        &nbsp;<?php echo $LanguageInstance->get('You will no longer be able to speak to your partner. To end Videochat, say good-bye to your partner and click "End". If you are not ready to close Videochat, click "Stay"');?>
		</div>
	</div>
	<div class='row'>
		<div class='col-md-12'>
			&nbsp;
		</div>
	</div>
	<div class='row'>
		<div class='col-md-12 col-md-offset-5'>
			<button onclick="closeIT();" class="btn btn-success"><?php echo $LanguageInstance->get('Stay');?></button>
			<button onclick="parent.$.fn.hideVideochatEvent();parent.$.fn.hideSayGoodbyeAndRedirect();" class="btn btn-success"><?php echo $LanguageInstance->get('End');?></button>
		</div>
	</div>
</div>
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
</body>
</html>