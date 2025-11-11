<?php
include '../koneksi.php';
header('Content-Type: application/json; charset=utf-8');

$cacheFile = __DIR__ . '/cache_kuota.json';
$cacheDuration = 600; // 10 menit = 600 detik

// 🕒 1. Jika cache masih berlaku, kirim langsung
if (file_exists($cacheFile)) {
    $cacheData = json_decode(file_get_contents($cacheFile), true);
    if (isset($cacheData['timestamp']) && (time() - $cacheData['timestamp'] < $cacheDuration)) {
        echo json_encode($cacheData['data']);
        exit;
    }
}

// 🧠 2. Ambil semua SN & MSISDN dari database chip
$rows = [];
$result = $koneksi->query("SELECT sn, msisdn FROM chip WHERE sn != '' AND msisdn != ''");
while ($r = $result->fetch_assoc()) {
    $rows[] = ['sn' => $r['sn'], 'msisdn' => $r['msisdn']];
}

// Jika tidak ada data chip
if (empty($rows)) {
    echo json_encode(['error' => 'Tidak ada data chip ditemukan']);
    exit;
}

// 🔁 3. Panggil API utama (simcheck.php)
$apiUrl = "simcheck.php?data=" . urlencode(json_encode($rows));
$response = @file_get_contents($apiUrl);

if ($response === FALSE) {
    // Jika gagal API, pakai cache lama kalau ada
    if (isset($cacheData['data'])) {
        echo json_encode($cacheData['data']);
    } else {
        echo json_encode(['error' => 'Gagal mengambil data API']);
    }
    exit;
}

$data = json_decode($response, true);
if (!$data) {
    echo json_encode(['error' => 'Respons API tidak valid']);
    exit;
}

// 💾 4. Simpan ke file cache
file_put_contents($cacheFile, json_encode([
    'timestamp' => time(),
    'data' => $data
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// ✅ 5. Kirim hasil ke frontend
echo json_encode($data);
