<?php
// โหลดสติ๊กเกอร์ล่าสุด
$stickerFile = "sticker.png"; // default

if(file_exists("current_sticker.txt")){
    $stickerFile = "stickers/" . trim(file_get_contents("current_sticker.txt"));
}

// โหลดตำแหน่ง
$positions = [];
if(file_exists("positions.json")){
    $positions = json_decode(file_get_contents("positions.json"), true);
}

// โหลดรูปพื้นหลัง
$imageFile = "";
if(file_exists("current_image.txt")){
    $imageFile = trim(file_get_contents("current_image.txt"));
}

// โหลดเลขที่ปิด
$closedNumbers = [];
if(file_exists("closed.json")){
    $closedNumbers = json_decode(file_get_contents("closed.json"), true);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ระบบปิดเลข 00-99</title>

<style>
body{
    font-family:Arial;
    background:#111;
    color:white;
    text-align:center;
}
.container{
    max-width:1000px;
    margin:auto;
    padding:15px;
}
canvas{
    width:100%;
    max-width:800px;
    border-radius:15px;
    box-shadow:0 0 25px #000;
    margin-top:15px;
}
input,button{
    padding:10px;
    margin:5px;
    border-radius:8px;
    border:none;
}
input{ width:200px; }
button{
    background:#ff4d4d;
    color:white;
    cursor:pointer;
}
button:hover{ opacity:0.8; }
.panel{ margin:10px 0; }
.badge{
    display:inline-block;
    background:#ff4d4d;
    padding:5px 10px;
    border-radius:20px;
    margin:3px;
    animation:pop 0.3s ease;
}
@keyframes pop{
    0%{transform:scale(0);}
    100%{transform:scale(1);}
}
</style>
</head>
<body>

<div class="container">

<h2>ระบบปิดเลข 00-99</h2>

<div class="panel">
<form id="uploadForm" enctype="multipart/form-data">
    <input type="file" name="image" required>
    <button type="submit">อัปโหลดรูป</button>
</form>

<!-- 🔥 ปุ่มอัปโหลดสติ๊กเกอร์ -->
<form id="uploadStickerForm" enctype="multipart/form-data">
    <input type="file" name="sticker" required>
    <button type="submit">อัปโหลดสติ๊กเกอร์</button>
</form>

<button onclick="toggleSetup()">โหมดตั้งค่า</button>
</div>

<div class="panel">
<input type="text" id="numbersInput" placeholder="เช่น 00,12,45">
<button onclick="closeNumbers()">ปิดเลข</button>
<button onclick="downloadImage()">ดาวน์โหลดรูป</button>
<button onclick="clearClosed()">ล้างเลขที่ปิด</button>
</div>

<div id="closedList"></div>
<canvas id="canvas"></canvas>

</div>

<script>
let canvas = document.getElementById("canvas");
let ctx = canvas.getContext("2d");
let img = new Image();

let positions = <?php echo json_encode($positions, JSON_FORCE_OBJECT); ?>;
if (typeof positions !== "object" || Array.isArray(positions)) {
    positions = {};
}

let closedNumbers = <?php echo json_encode($closedNumbers); ?> || [];
let setupMode = false;

// 🔥 โหลดสติ๊กเกอร์จากไฟล์ที่เลือกไว้ล่าสุด
let sticker = new Image();
sticker.src = "<?php echo $stickerFile; ?>";

// โหลดรูปพื้นหลังอัตโนมัติ
<?php if($imageFile!=""){ ?>
img.src = "uploads/<?php echo $imageFile; ?>";
img.onload = function(){
    canvas.width = img.width;
    canvas.height = img.height;
    draw();
}
<?php } ?>

// อัปโหลดรูปพื้นหลัง
document.getElementById("uploadForm").addEventListener("submit", function(e){
    e.preventDefault();
    let formData = new FormData(this);

    fetch("upload_image.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if(data=="success"){
            location.reload();
        }else{
            alert("อัปโหลดไม่สำเร็จ");
        }
    });
});

// 🔥 อัปโหลดสติ๊กเกอร์
document.getElementById("uploadStickerForm")
.addEventListener("submit", function(e){

    e.preventDefault();
    let formData = new FormData(this);

    fetch("upload_sticker.php",{
        method:"POST",
        body:formData
    })
    .then(res=>res.text())
    .then(data=>{
        if(data=="success"){
            location.reload();
        }else{
            alert("อัปโหลดสติ๊กเกอร์ไม่สำเร็จ");
        }
    });
});

// ตั้งค่าตำแหน่ง
canvas.addEventListener("click",function(e){
    if(!setupMode) return;

    let rect = canvas.getBoundingClientRect();
    let x = (e.clientX - rect.left) / rect.width;
    let y = (e.clientY - rect.top) / rect.height;

    let number = prompt("เลขอะไร (00-99)?");
    if(number){
        positions[number] = {x:x,y:y};
        savePositions();
        alert("บันทึกตำแหน่ง "+number);
    }
});

function toggleSetup(){
    setupMode = !setupMode;
    alert(setupMode ? "เปิดโหมดตั้งค่า" : "ปิดโหมดตั้งค่า");
}

function savePositions(){
    fetch("save_positions.php",{
        method:"POST",
        body:JSON.stringify(positions)
    });
}

function saveClosed(){
    fetch("save_closed.php",{
        method:"POST",
        body:JSON.stringify(closedNumbers)
    });
}

function closeNumbers(){
    let input = document.getElementById("numbersInput").value;
    let nums = input.split(",");

    nums.forEach(n=>{
        n=n.trim();
        if(positions[n] && !closedNumbers.includes(n)){
            closedNumbers.push(n);
        }
    });

    saveClosed();
    draw();
    showClosed();
}

function clearClosed(){
    if(confirm("ล้างเลขที่ปิดทั้งหมด?")){
        closedNumbers=[];
        saveClosed();
        draw();
        showClosed();
    }
}

function draw(){
    ctx.clearRect(0,0,canvas.width,canvas.height);
    ctx.drawImage(img,0,0);

    closedNumbers.forEach(n=>{
        if(positions[n]){
            let x = positions[n].x * canvas.width;
            let y = positions[n].y * canvas.height;
            let size = canvas.width * 0.1;

            ctx.drawImage(
                sticker,
                x - size/2,
                y - size/2,
                size,
                size
            );
        }
    });
}

function showClosed(){
    let div=document.getElementById("closedList");
    div.innerHTML="";
    closedNumbers.forEach(n=>{
        div.innerHTML+=`<span class="badge">${n}</span>`;
    });
}

function downloadImage(){
    let link=document.createElement("a");
    link.download="closed_numbers.png";
    link.href=canvas.toDataURL();
    link.click();
}

showClosed();
</script>

</body>
</html>