<?php
$data = file_get_contents("php://input");

if($data){
    $decoded = json_decode($data, true);

    if(is_array($decoded)){
        file_put_contents("positions.json", json_encode($decoded, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT), LOCK_EX);
        echo "saved";
    }else{
        echo "error";
    }
}
?>