<?php
// serial_helper.php
// include this file when needed

function sanitize_com($com){
    // Hanya izinkan pola COMx
    if(preg_match('/^COM(\d+)$/i', $com, $m)){
        return strtoupper("COM" . intval($m[1]));
    }
    return false;
}

function set_port_mode($com, $baud = 115200){
    // Gunakan mode command Windows
    $com_escaped = escapeshellarg($com . ':');
    // contoh: mode COM3: BAUD=115200 PARITY=N DATA=8 STOP=1
    $cmd = "mode " . $com . ": BAUD=" . intval($baud) . " PARITY=N DATA=8 STOP=1";
    exec($cmd . " 2>&1", $out, $ret);
    return $ret === 0;
}

function open_port($com, $mode = "r+"){
    // Coba buka "COMx" dulu, kalau gagal coba "\\.\COMx"
    $fp = @fopen($com, $mode);
    if($fp) return $fp;
    // fallback:
    $path = "\\\\.\\$com";
    $fp = @fopen($path, $mode);
    if($fp) return $fp;
    return false;
}

function send_at_command($com, $cmd, $timeout = 3, $baud = 115200){
    $com_s = sanitize_com($com);
    if(!$com_s) return ['ok'=>false, 'err'=>'Invalid COM'];

    // set mode (may fail but continue)
    set_port_mode($com_s, $baud);

    $fp = open_port($com_s, "r+b");
    if(!$fp) return ['ok'=>false, 'err'=>'Cannot open port: '.$com_s];

    // set non-blocking, but we'll read in loop
    stream_set_blocking($fp, false);

    // Clear buffers
    @stream_select($read = [$fp], $write = null, $except = null, 0, 100000);
    @fwrite($fp, ""); usleep(100000);

    // write command + CRLF
    $to_send = $cmd . "\r\n";
    $w = fwrite($fp, $to_send);
    fflush($fp);

    $resp = '';
    $t0 = microtime(true);
    while ((microtime(true) - $t0) < $timeout) {
        $chunk = fread($fp, 4096);
        if ($chunk !== false && $chunk !== '') {
            $resp .= $chunk;
            // break cepat bila merasakan OK atau ERROR
            if (stripos($resp, "OK") !== false || stripos($resp, "ERROR") !== false) break;
        }
        usleep(100000);
    }

    fclose($fp);
    return ['ok' => true, 'resp' => trim($resp)];
}

function send_sms_via_modem($com, $number, $text, $timeout = 10){
    // Implementasi SMS text-mode (may vary per modem)
    // set text mode
    $r = send_at_command($com, "AT+CMGF=1", 2);
    if(!$r['ok']) return $r;
    // set GSM charset? optional
    send_at_command($com, "AT+CSCS=\"GSM\"", 1);

    // Initiate send
    $fp = open_port($com, "r+b");
    if(!$fp) return ['ok'=>false, 'err'=>'Cannot open port for SMS'];
    stream_set_blocking($fp, false);
    fwrite($fp, "AT+CMGS=\"{$number}\"\r\n");
    fflush($fp);
    usleep(500000);
    // write message + Ctrl+Z
    fwrite($fp, $text . chr(26));
    fflush($fp);

    $resp = '';
    $t0 = microtime(true);
    while ((microtime(true) - $t0) < $timeout) {
        $chunk = fread($fp, 4096);
        if ($chunk !== false && $chunk !== '') {
            $resp .= $chunk;
            if (stripos($resp, "+CMGS") !== false || stripos($resp, "ERROR") !== false) break;
        }
        usleep(100000);
    }
    fclose($fp);
    return ['ok' => true, 'resp' => trim($resp)];
}
