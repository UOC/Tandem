<?php 
require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/constants.php';
require_once dirname(__FILE__).'/classes/gestorBD.php';
require_once dirname(__FILE__).'/classes/utils.php';

$user_obj = $_SESSION[CURRENT_USER];
$course_id = $_SESSION[COURSE_ID];
//20120830 abertranb register the course folder
$course_folder = $_SESSION[TANDEM_COURSE_FOLDER];
//FIIIII
$message = false;
if (!isset($user_obj) || !isset($course_id) || !isset($course_folder) || !$user_obj->instructor) {
	//Tornem a l'index
	header ('Location: index.php');
} else {
		//

	
	$max_upload = (int)(ini_get('upload_max_filesize'));
	$max_post = (int)(ini_get('post_max_size'));
	$memory_limit = (int)(ini_get('memory_limit'));
	$upload_mb = min($max_upload, $max_post, $memory_limit);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Tandem</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="all" href="css/tandem.css" />
<?php
	//cmoyas change skin 
	//if(is_file('skins/css/styleSkin.css')){
			echo '<link rel="stylesheet" type="text/css" media="all" href="skins/css/styleSkin.css" />';
	//}
?>
<link rel="stylesheet" type="text/css" media="all" href="css/jquery-ui.css" />
<script src="js/jquery-1.7.2.min.js"></script>

<link href="js/colorPicker/mcColorPicker.css" rel="stylesheet" type="text/css" />
<script src="js/colorPicker/mcColorPicker.js" type="text/javascript"></script>
<link href="js/colorPicker/uploadify.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="css/font-awesome-4.3.0/css/font-awesome.min.css">



<script src="js/jquery.ui.core.js"></script>
<script src="js/jquery.ui.widget.js"></script>
<script src="js/jquery.ui.button.js"></script>
<script src="js/jquery.ui.position.js"></script>
<script src="js/jquery.ui.autocomplete.js"></script>
<script src="js/jquery.ui.datepicker.js"></script>
<script src="js/jquery.colorbox-min.js"></script>
<script src="js/colorPicker/jquery.uploadify.js"></script>
<script>
	<?php $timestamp = time();?>

	$( document ).ready(function() {
    generateCss = function(){

    	if($("#colorLink").val()!="" || $("#colorP").val()!="" || $("#colorTitulos").val()!="" || $("#colorBotones").val()!="" || $("#fontTipo").val()!=""){
	    	 $.ajax({
				type: 'GET',
				url: "js/colorPicker/generateCss.php",
				data: {
					colorLink: $("#colorLink").val(),colorP:$("#colorP").val(),colorTitulos:$("#colorTitulos").val(),colorBotones:$("#colorBotones").val(),font:$("#fontTipo").val()
				},
				dataType: "text",
				success: function(id){
					$("link").each(function() {
					    if ($(this).attr("type").indexOf("css") > -1) {
					        $(this).attr("href", $(this).attr("href") + "?id=" + new Date().getMilliseconds());
					    }
					});
					$("#cssgen").slideDown(1500).fadeOut(500);
				}
			});
	    }else{
	    	$("#cssgen").toggle();
			$("#cssgen").html("Al menos 1 campo requerido");
	    }
    }

   });

    $(function() {    
      $('#file_uploadLogo').uploadify({
        'formData'     : {
          'timestamp' : '<?php echo $timestamp;?>',
          'token'     : '<?php echo md5('unique_salt' . $timestamp);?>'
        },
        'buttonText' : 'Upload Logo image...',
        'swf'      : 'js/colorPicker/uploadify.swf',
        'uploader' : 'js/colorPicker/uploadify.php?img=logo_APP',
        'onUploadComplete' : function(file) {
            $("#logo").find("a").find("img").attr("src",'skins/img/logo_APP.png');
            $("#file_uploadLogo-queue").hide(1500);
            $("link").each(function() {
			    if ($(this).attr("type").indexOf("css") > -1) {
			        $(this).attr("href", $(this).attr("href") + "?id=" + new Date().getMilliseconds());
			    }
			});
        }
      });

      $('#file_uploadBike').uploadify({
        'formData'     : {
          'timestamp' : '<?php echo $timestamp;?>',
          'token'     : '<?php echo md5('unique_salt' . $timestamp);?>'
        },
        'buttonText' : 'Upload Bike image',
        'swf'      : 'js/colorPicker/uploadify.swf',
        'uploader' : 'js/colorPicker/uploadify.php?img=footer_bike',
        'onUploadComplete' : function(file) {
        	$("#footer").find("div").first().removeClass("footer-tandem").addClass("footer-tandemBike");
        	$("#file_uploadBike-queue").hide("slow");
            $("link").each(function() {
			    if ($(this).attr("type").indexOf("css") > -1) {
			        $(this).attr("href", $(this).attr("href") + "?id=" + new Date().getMilliseconds());
			    }
			});
        }
      });
      $('#file_uploadRoad').uploadify({
        'formData'     : {
          'timestamp' : '<?php echo $timestamp;?>',
          'token'     : '<?php echo md5('unique_salt' . $timestamp);?>'
        },
        'buttonText' : 'Upload Road image',
        'swf'      : 'js/colorPicker/uploadify.swf',
        'uploader' : 'js/colorPicker/uploadify.php?img=footer_road',
        'onUploadComplete' : function(file) {
        	$("#footer-container").addClass("footer-containerRoad");
        	$("#file_uploadRoad-queue").hide("slow");
            $("link").each(function() {
			    if ($(this).attr("type").indexOf("css") > -1) {
			        $(this).attr("href", $(this).attr("href") + "?id=" + new Date().getMilliseconds());
			    }
			});
        }
      });
      $('#file_uploadFooter1').uploadify({
        'formData'     : {
          'timestamp' : '<?php echo $timestamp;?>',
          'token'     : '<?php echo md5('unique_salt' . $timestamp);?>'
        },
        'buttonText' : 'Upload Footer Logo 1 image',
        'swf'      : 'js/colorPicker/uploadify.swf',
        'uploader' : 'js/colorPicker/uploadify.php?img=logo_footer1',
        'onUploadComplete' : function(file) {
        	$(".footer-logos").find("img").first().attr("src","skins/img/logo_footer1.png");
        	$("#file_uploadFooter1-queue").hide("slow");
            $("link").each(function() {
			    if ($(this).attr("type").indexOf("css") > -1) {
			        $(this).attr("href", $(this).attr("href") + "?id=" + new Date().getMilliseconds());
			    }
			});
        }
      });
      $('#file_uploadFooter2').uploadify({
        'formData'     : {
          'timestamp' : '<?php echo $timestamp;?>',
          'token'     : '<?php echo md5('unique_salt' . $timestamp);?>'
        },
        'buttonText' : 'Upload Footer Logo 2 image',
        'swf'      : 'js/colorPicker/uploadify.swf',
        'uploader' : 'js/colorPicker/uploadify.php?img=logo_footer2',
        'onUploadComplete' : function(file) {
        	$(".footer-logos").children().eq(1).attr("src","skins/img/logo_footer2.png");
        	$("#file_uploadFooter2-queue").hide("slow");
            $("link").each(function() {
			    if ($(this).attr("type").indexOf("css") > -1) {
			        $(this).attr("href", $(this).attr("href") + "?id=" + new Date().getMilliseconds());
			    }
			});
        }
      });
      $('#file_uploadFooter3').uploadify({
        'formData'     : {
          'timestamp' : '<?php echo $timestamp;?>',
          'token'     : '<?php echo md5('unique_salt' . $timestamp);?>'
        },
        'buttonText' : 'Upload Footer Logo 2 image',
        'swf'      : 'js/colorPicker/uploadify.swf',
        'uploader' : 'js/colorPicker/uploadify.php?img=logo_footer3',
        'onUploadComplete' : function(file) {
        	$(".footer-logos").children().eq(2).attr("src","skins/img/logo_footer2.png");
        	$("#file_uploadFooter3-queue").hide("slow");
            $("link").each(function() {
			    if ($(this).attr("type").indexOf("css") > -1) {
			        $(this).attr("href", $(this).attr("href") + "?id=" + new Date().getMilliseconds());
			    }
			});
        }
      });
      
  });
</script>
<?php include_once dirname(__FILE__).'/js/google_analytics.php';?>
</head>
<body>

<!-- accessibility -->
	<div id="accessibility">
		<a href="#content" accesskey="s" title="Acceso directo al contenido"><?php echo Language::get('direct_access_to_content')?></a> | 
		<!--
		<a href="#" accesskey="n" title="Acceso directo al men� de navegaci�n">Acceso directo al men� de navegaci�n</a> | 
		<a href="#" accesskey="m" title="Mapa del sitio">Mapa del sitio</a> 
		-->
	</div>
	<!-- /accessibility -->
	
	<!-- /wrapper -->
	<?php //cmoyas change skin
		if(is_file('skins/img/footer_road.png')) echo '<div id="wrapperRoad">';
		else echo '<div id="wrapper">';
	?>
		<!-- main-container -->
  		<div id="main-container">
  			<!-- main -->
			<div id="main">
				<!-- content -->
				<?php if($message) echo '<div class="info">'.$message.'</div>'; ?>
				<div id="content">
					<div id="logo">
						<a href="#" title="<?php echo Language::get('tandem_logo')?>">
							<?php //cmoyas change skin
								if(is_file('skins/img/logo_APP.png')) echo '<img src="skins/img/logo_APP.png" alt="'.Language::get('tandem_logo').'" />';
								else echo '<img src="css/images/logo_Tandem.png" alt="'.Language::get('tandem_logo').'" />';
							?>
						</a>
						<p>187 X 67px</p>
					</div>
				<h4><a href="tandemInfo.php"><i class="fa fa-reply"></i>&nbsp;<?php echo Language::get('back')?></a></h4>
				<p><h1><?php echo Language::get('Skin')?></h1></p>
				
					<div class="clear">&nbsp;</div>
					
					<p><label>Color de links</label>&nbsp;&nbsp;&nbsp;<input id="colorLink" type="hidden" class="color" /></p>
					<p><label>Color de etiqueta &lt;p&gt;</label>&nbsp;&nbsp;&nbsp;<input id="colorP" type="hidden" class="color" /></p>
					<p><label>Color de títulos</label>&nbsp;&nbsp;&nbsp;<input id="colorTitulos" type="hidden" class="color" /></p>
					<p><label>Color de botones</label>&nbsp;&nbsp;&nbsp;<input id="colorBotones" type="hidden" class="color" /></p>
					<p><label>Tipo de fuente</label>&nbsp;&nbsp;&nbsp;
							<select id="fontTipo">
							    <option class="Impact">Impact</option>
							    <option class="Palatino Linotype">Palatino Linotype</option>
							    <option class="Tahoma">Tahoma</option>
							    <option class="Century Gothic">Century Gothic</option>
							    <option class="Lucida Sans Unicode">Lucida Sans Unicode</option>
							    <option class="Arial Black">Arial Black</option>
							    <option class="Times New Roman">Times New Roman</option>
							    <option class="Verdana">Verdana</option>
							    <option class="Courier New">Courier New</option>
								<option class="Arial">Arial</option>
							    <option class="Helvetica">Helvetica</option>
							</select>
						<input type="button" value="Generar CSS" onclick="generateCss();return false;" />
					</p>
					<p id="cssgen" style="display:none;font-size: 16px; color: #99263f; background-color: rgba(164, 175, 170, 0.33); width: 220px; padding: 12px;">Css Generado</p>
					<br/>
						<p><input type="file" name="file_uploadLogo" id="file_uploadLogo" />
						<input type="file" name="file_uploadRoad" id="file_uploadRoad" />
						<input type="file" name="file_uploadBike" id="file_uploadBike" />	
						<input type="file" name="file_uploadFooter1" id="file_uploadFooter1" />
						<!--<input type="file" name="file_uploadFooter2" id="file_uploadFooter2" />-->
						<input type="file" name="file_uploadFooter3" id="file_uploadFooter3" /></p>	
					</div>
				</div>
				<!-- /content -->
			</div>
			<!-- /main -->
		</div>
		<!-- /main-container -->
	</div>
	<!-- /wrapper -->
	<!-- footer -->
	<?php //cmoyas change skin
		if(is_file('skins/img/footer_road.png')) echo '<div class="footer-containerRoad">';
		else echo '<div id="footer-container">';
	?>

		<div id="footer">
			<?php //cmoyas change skin
					if(is_file('skins/img/footer_bike.png')) echo '<div class="footer-tandemBike" title="'.Language::get('tandem').'"><p>269x256px</p></div>';
					else echo '<div class="footer-tandem" title="'.Language::get('tandem').'"><p>269x256px</p></div>';
			?>

			<div class="footer-logos">
				<p>&nbsp;&nbsp;&nbsp;56x36px
				<?php //cmoyas change skin
					if(is_file('skins/img/logo_footer1.png')) echo '<img src="skins/img/logo_footer1.png" alt="" />';
					else echo '<img src="css/images/EU_flag.jpg" alt="Lifelong Learning Programme" />';

					if(is_file('skins/img/logo_footer3.png')) echo '<img src="skins/img/logo_footer3.png" alt="" />';
					else echo '<img src="css/images/logo_speakapps.png" alt="Speakapps" />';
				?></p>
				</div>
			</div>
		</div>
	</div>
	    
	<!-- /footer -->
</body>
</html>
<?php } ?>