<?php

    require_once dirname(__FILE__) . '/classes/gestorBD.php';
    
    $gestorBD = new GestorBD();
    
    $idToDelete = ($_REQUEST['id']); 
    
    $result = $gestorBD->deleteTandem($idToDelete);
    
    if ($result){
        echo 1;
    }else{
        echo 0;
    }
    
    
    
