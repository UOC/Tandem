<?php 
$is_final = true;
include_once(dirname(__FILE__).'/classes/register_action_user.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Tandem Pantalla Inici_ParaExportar</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- Save for Web Styles (Tandem Pantalla Inici_ParaExportar.psd) -->
<style type="text/css">
<!--

#home_ {
	position:absolute;
	left:0px;
	top:0px;
	width:695px;
	height:626px;
}

-->
</style>

<script type="text/javascript">
	desconn = function(){
		$.ajax({
			type: 'GET',
			url: "desconn.php",
			data: {'room':'<?php echo $_GET["room"];?>'},
			success: function(){
				top.document.location.href="selectUserAndRoom.php";
			}
		});
	}
</script>

<!-- End Save for Web Styles -->
</head>
<body id="home_" style="background-color:#FFFFFF;">
<!-- Save for Web Slices (Tandem Pantalla Inici_ParaExportar.psd) -->
<a href="#" onclick="desconn();"><img id="home" src="images/final.jpg" width="310" height="343" alt="" /></a>
<!-- a href="#" onclick="parent.window.close();"><img id="home" src="images/final.jpg" width="310" height="343" alt="" /></a> -->
<!-- End Save for Web Slices -->
</body>
</html>