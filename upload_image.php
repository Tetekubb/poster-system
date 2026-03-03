<?php

if(isset($_FILES["image"])){

    $targetDir = "uploads/";
    $fileName = time() . "_" . basename($_FILES["image"]["name"]);
    $targetFile = $targetDir . $fileName;

    if(move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)){

        // บันทึกชื่อไฟล์ล่าสุด
        file_put_contents("current_image.txt", $fileName);

        echo "success";
    }else{
        echo "error";
    }
}
?>