<?php
// list_ports.php
header('Content-Type: application/json; charset=utf-8');

// Jalankan WMIC dan ambil DeviceID & Caption
// Format CSV memudahkan parsing
exec('wmic path Win32_SerialPort get DeviceID,Caption /format:csv 2>&1', $out, $code);

$ports = [];
foreach($out as $line){
    $line = trim($line);
    if($line === '' ) continue;
    // CSV line format: Node,Caption,DeviceID
    $parts = str_getcsv($line);
    if(count($parts) >= 3){
        // last column biasanya DeviceID (COMx)
        $device = end($parts);
        $caption = $parts[1] ?? '';
        if(preg_match('/COM\d+/i', $device)){
            $ports[] = ['device' => $device, 'caption' => $caption];
        }
    } else {
        // fallback: cari COMx di baris
        if(preg_match('/(COM\d+)/i', $line, $m)){
            $ports[] = ['device' => $m[1], 'caption' => $line];
        }
    }
}

echo json_encode(['ok' => true, 'ports' => $ports], JSON_UNESCAPED_UNICODE);
