<?php
require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/constants.php';
require_once dirname(__FILE__).'/classes/utils.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Tandem</title>
    <meta charset="UTF-8"/>
    <link rel="stylesheet" href="css/mailing.css"/>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body>
<!-- Begin page content -->
<div id="wrapper" class="container">
    <div class="row page-header">
        <div class="col-md-12">
            <h1><a href="<?php echo base64_decode($_GET['url'])?>" target="videchat_win"><?php echo $LanguageInstance->get('Open videochat on new tab');?></a></h1>
        </div>
    </div>

</body>
</html>