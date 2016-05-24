<?php 
require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/utils.php';
require_once dirname(__FILE__).'/classes/gestorBD.php';

$is_videochat = isset($_GET['is_videochat'])?$_GET['is_videochat']==1:false;
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
	showVideochatW = function(){
		<?php if ($is_videochat) {?>
		parent.$.fn.showVideochatEvent();
		<?php } else {?>
		parent.$.fn.hideVideochatEvent();
		<?php } ?>
		//parent.$.fn.colorbox.close();
	}
	$(document).ready(function(){
		$('#imgVideochat').css('cursor','pointer');
		$('#imgVideochat').click(function(event) {
			showVideochatW();
		});
	});
</script>
<!-- End Save for Web Styles -->
</head>
<body>
<div class='container'>
	<div class='row'>
		<div class="col-md-12">
		<?php if ($is_videochat) {?>
				<?php echo $LanguageInstance->get('Playing and recording videochat');?>
				<img src="img/participantRecording.gif" id="imgVideochat" />
			<div class='btn_review  col-md-3 col-md-offset-5'>
				<button onclick="showVideochatW();" class="btn btn-success"><?php echo $LanguageInstance->get('Show video');?></button>
			</div>
		<?php } else {?>
				<img src="img/footer_tandem.png" id="imgVideochat" />
			<div class='btn_review  col-md-3 col-md-offset-5'>
				<button onclick="showVideochatW();" class="btn btn-success"><?php echo $LanguageInstance->get('Show task');?></button>
			</div>
		<?php }	?>
		</div>
	</div>
</div>
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
</body>
</html>