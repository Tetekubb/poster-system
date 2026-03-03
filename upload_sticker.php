<?php
if(isset($_FILES["sticker"])){

    $folder = "stickers/";

    if(!is_dir($folder)){
        mkdir($folder);
    }

    $fileName = time() . "_" . $_FILES["sticker"]["name"];
    $target = $folder . $fileName;

    if(move_uploaded_file($_FILES["sticker"]["tmp_name"], $target)){
        file_put_contents("current_sticker.txt", $fileName);
        echo "success";
    }else{
        echo "error";
    }
}
?>