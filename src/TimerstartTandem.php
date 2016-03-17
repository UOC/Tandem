<!DOCTYPE html>
<html lang="en">
	<head>
	<meta charset=utf-8 />
	<title>Tandem</title>
	</head>
<body>
	<table border="0">
	  <tr>
	    <td height="38" align="center" valign="top"><p><b>Please confirm to start this exercise<br>
	      <br>
	    </b></p>
	      <p><b>It will begin when your partner and you confirm by clicking the button</b></p></td>
      </tr>
	  <tr>
	    <td height="9" align="center" valign="top" id="textWTimer">&nbsp;</td>
      </tr>
	  <tr>
	    <td height="9" align="center" valign="top"><input type="submit" name="timerStartButton" id="timerStartButton" value=" Start exercise " onClick="parent.StartTandemTimer();document.getElementById('textWTimer').innerHTML=' Waiting for confirmation ';this.disabled = true;"></td>
      </tr>
</table>
</body>
</html>