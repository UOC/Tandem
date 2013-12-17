<?PHP 
header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); 
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); 
header( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
header( 'Cache-Control: post-check=0, pre-check=0', false ); 
header( 'Pragma: no-cache' ); 

$room = $_REQUEST['room'];
header ("content-type: text/xml");
$xml = '';
if (file_exists($room.'.xml')) {
	$xml = file_get_contents($room.'.xml');
} else {
	error_log("Could not find file $room.xml", 0);	
}
echo $xml;
?>