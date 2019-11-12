<?php
    require_once(__DIR__."/core.php");
    
    $join = '{"type":"group_join","object":{"user_id":51342508,"join_type":"join"},"group_id":188675502}';
    
    $data = $join;
try {
    $core = new core($data);
}
catch (Esception $e) {
    echo $e->getMessage();
}
?>
