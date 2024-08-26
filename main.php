<?php
// เริ่มการทำงานของ session
session_start();

// กำหนด URL สำหรับ Linkvertise
$LinkVer1 = "https://link1.com";
$LinkVer2 = "https://link2.com";

// กำหนดการเชื่อมต่อ MongoDB
$mongoClient = new MongoDB\Client("mongodb+srv://admin:natza1243@db.xa6hmef.mongodb.net/?retryWrites=true&w=majority&appName=db");
$database = $mongoClient->myDatabase; // เปลี่ยนชื่อฐานข้อมูล
$collection = $database->keys; // เปลี่ยนชื่อ collection

// สร้างฟังก์ชันสำหรับสร้างคีย์
function generateKey($length = 25)
{
    return strtoupper(bin2hex(random_bytes($length)));
}

// ตรวจสอบว่าผู้ใช้ได้ผ่านแต่ละขั้นตอนของ Linkvertise แล้วหรือยัง
if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 1;
}

switch ($_SESSION['step']) {
    case 1:
        $_SESSION['step']++;
        header("Refresh: 5; url=$LinkVer1");
        showPage(1, $LinkVer1);
        break;
    case 2:
        $_SESSION['step']++;
        header("Refresh: 5; url=$LinkVer2");
        showPage(2, $LinkVer2);
        break;
    case 3:
        // เมื่อผ่านทุกขั้นตอนแล้ว ทำการสร้างคีย์และบันทึกลง MongoDB
        $key = generateKey();
        $collection->insertOne(['key' => $key, 'created_at' => new MongoDB\BSON\UTCDateTime()]);

        // แสดงคีย์ให้กับผู้ใช้
        session_destroy();
        showKeyPage($key);
        break;
}

function showPage($step, $link)
{
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Key Generation Step $step/2</title>
    <link rel="stylesheet" href="MainDesign.css">
    <meta http-equiv="refresh" content="5; url=$link">
</head>

<body>
    <div class="keysyscontainer">
        <div class="box">
            <center><span class="title">Linkvertise Step $step/2</span></center>
            <span class="text">You will be redirected in 5 seconds...</span>
        </div>
    </div>
</body>

</html>
HTML;
    exit();
}

function showKeyPage($key)
{
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Key</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <link rel="stylesheet" href="MainDesign.css">
</head>

<body>
    <div class="keysyscontainer">
        <div class="box">
            <center><span class="title">Enjoy your key!</span></center>
            <span class="text">Here is your key: <span class="bold smool">$key</span></span>
            <button onclick="return copyToClipboard()" class="button Copy">Copy Key</button>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script>
        function copyToClipboard() {
            var e = "$key";
            var tempItem = document.createElement('input');
            tempItem.setAttribute('type', 'text');
            tempItem.setAttribute('display', 'none');
            let content = e;
            if (e instanceof HTMLElement) {
                content = e.innerHTML;
            }
            tempItem.setAttribute('value', content);
            document.body.appendChild(tempItem);
            tempItem.select();
            document.execCommand('Copy');
            tempItem.parentElement.removeChild(tempItem);
            const notyf = new Notyf();
            notyf.success({
                message: "Successfully copied key to clipboard!",
                duration: 3500,
                dismissible: true
            });
        }
    </script>
</body>

</html>
HTML;
    exit();
}
?>
