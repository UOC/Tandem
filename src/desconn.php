<?php
//Recibimos las variables desde la url
$room = $_GET["room"];
if(is_file($room.".xml")) unlink($room.".xml");
?>