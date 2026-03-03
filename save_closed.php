<?php
$data = file_get_contents("php://input");

if($data){
    file_put_contents("closed.json", $data);
    echo "saved";
}else{
    echo "error";
}
?>