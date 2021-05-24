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
<link media="screen" rel="stylesheet" href="css/default.css?version=20191026" />
<link rel="stylesheet" type="text/css" href="css/tandem.css" media="all" />
<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>


<script type="text/javascript">
    $(document).ready(function() {
        $('#reloadVideochat').click(function (event) {
            parent.document.location.reload();
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
		<p><?php echo $LanguageInstance->get('Your partner had accepted the videochat session')?>.</p>
		<p>1. <?php echo $LanguageInstance->get('You have to enable microphone to start Tandem.')?></p>
		<p>2. <?php echo $LanguageInstance->get('You have to enable camera to start Tandem.')?></p>
        <p><img src="images/enableCameraAndVideo.gif" width="150px" class="imagecenter" alt="Enable camera and video"/></p>
	</div>
<p></p>
</div></div></div>
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
</body>
</html>
