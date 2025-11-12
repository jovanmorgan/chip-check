<?php
// api_send_at.php
header('Content-Type: application/json; charset=utf-8');

require 'serial_helper.php';

// ambil input POST
$com = $_POST['com'] ?? null;
$cmd = $_POST['cmd'] ?? 'AT';
$timeout = isset($_POST['timeout']) ? intval($_POST['timeout']) : 4;

if(!$com){
    echo json_encode(['ok'=>false, 'err'=>'Parameter com diperlukan']);
    exit;
}

// special: kirim SMS
if(strtoupper($cmd) === 'SEND_SMS'){
    $number = $_POST['sms_number'] ?? '';
    $text = $_POST['sms_text'] ?? '';
    if(!$number || !$text){
        echo json_encode(['ok'=>false, 'err'=>'sms_number & sms_text diperlukan untuk SEND_SMS']);
        exit;
    }
    $res = send_sms_via_modem($com, $number, $text, max(8,$timeout));
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
    exit;
}

// normal AT command
$res = send_at_command($com, $cmd, max(1,$timeout));
echo json_encode($res, JSON_UNESCAPED_UNICODE);
