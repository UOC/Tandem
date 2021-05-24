<?php

require_once __DIR__ . '/classes/gestorBD.php';

$idToDelete = $_REQUEST['id'];

$gestorBD = new GestorBD();
$result = $gestorBD->deleteTandem($idToDelete);

if ($result) {
    echo '1';
} else {
    echo '0';
}
