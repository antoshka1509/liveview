<?php
    require_once(__DIR__."/core.php");
    $data = file_get_contents("php://input");
    $data = json_decode($data);
    
    try {
        $core = new core($data);
        echo $core->answer();
    }
    catch (Exception $e) {
        echo "Error: ".$e->getMessage();
    }
?>