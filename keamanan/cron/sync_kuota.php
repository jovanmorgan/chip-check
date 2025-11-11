<?php
include '../../keamanan/koneksi.php';
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: text/html; charset=utf-8');

// ======================================================
// 🔹 Matikan output buffering default PHP
// ======================================================
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', false);
@ini_set('implicit_flush', true);
while (ob_get_level() > 0) {
    ob_end_flush();
}
ob_implicit_flush(true);

// ======================================================
// 🔹 HTML Awal
// ======================================================
echo "<!DOCTYPE html>
<html lang='id'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Sinkronisasi Kuota</title>
<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
<style>
body { background:#0d1117; color:#00ff99; font-family: monospace; padding:20px; }
.card-log { background:#161b22; border:1px solid #30363d; padding:15px; border-radius:10px; max-height:80vh; overflow:auto; }
.log-line { margin:3px 0; }
.time { color:#58a6ff; }
</style>
</head>
<body>
<h3>🛰️ Sinkronisasi Data Kuota</h3>
<div class='mb-3'>";

// ======================================================
// 🔹 Tombol setiap Box Inner
// ======================================================
$boxQuery = "SELECT * FROM box_inner ORDER BY CAST(SUBSTRING_INDEX(box_inner, ' ', -1) AS UNSIGNED) ASC";
$boxResult = $koneksi->query($boxQuery);

if ($boxResult->num_rows == 0) {
    echo "❌ Tidak ada data box_inner ditemukan.<br></body></html>";
    exit;
}

while ($box = $boxResult->fetch_assoc()) {
    $id_box_inner = $box['id_box_inner'];
    $nama_box = $box['box_inner'];

    echo "<form style='display:inline-block;' method='GET'>
            <input type='hidden' name='box' value='{$id_box_inner}'>
            <button class='btn btn-sm btn-outline-success m-1'>{$nama_box}</button>
          </form>";
}
echo "</div><hr><div class='card-log' id='log-box'>";

// ======================================================
// 🔹 Proses jika tombol diklik
// ======================================================
$selected_box = $_GET['box'] ?? null;

if ($selected_box) {
    // Ambil info box
    $boxQuery2 = $koneksi->prepare("SELECT * FROM box_inner WHERE id_box_inner=?");
    $boxQuery2->bind_param("i", $selected_box);
    $boxQuery2->execute();
    $boxRes = $boxQuery2->get_result();
    $boxInfo = $boxRes->fetch_assoc();
    $nama_box = $boxInfo['box_inner'];

    echo "<div class='log-line'><span class='time'>[" . date('H:i:s') . "]</span> 📦 Memproses Box: <b>{$nama_box}</b></div>";
    flush();

    // Ambil semua chip di box
    $chipQuery = $koneksi->prepare("SELECT * FROM chip WHERE id_box_inner=? ORDER BY sn ASC");
    $chipQuery->bind_param("i", $selected_box);
    $chipQuery->execute();
    $chipResult = $chipQuery->get_result();

    if ($chipResult->num_rows == 0) {
        echo "<div class='log-line'>⚠️ Tidak ada chip pada {$nama_box}</div>";
        flush();
    } else {
        // Siapkan data untuk API
        $data_api_request = [];
        while ($chip = $chipResult->fetch_assoc()) {
            $data_api_request[] = ["sn" => $chip['sn'], "msisdn" => $chip['msisdn']];
        }

        $json_data = json_encode($data_api_request);
        $api_url = "../api/simcheck.php?data=" . urlencode($json_data);

        echo "<div class='log-line'>🌐 Mengambil data dari API untuk {$nama_box}...</div>";
        flush();

        // Ambil data dari API
        $response = @file_get_contents($api_url);
        if (!$response) {
            echo "<div class='log-line text-danger'>❌ Gagal mengambil data dari API untuk {$nama_box}</div>";
            flush();
        } else {
            echo "<div class='log-line text-warning'>📥 Response API ({$nama_box}):<br><pre style='color:#f0f0f0;background:#1e1e1e;padding:10px;border-radius:6px;white-space:pre-wrap;'>" . htmlspecialchars($response) . "</pre></div>";
            flush();

            $data_api = json_decode($response, true);
            if (!is_array($data_api)) {
                echo "<div class='log-line text-danger'>❌ Data API tidak valid untuk {$nama_box}</div>";
                flush();
            } else {
                // Loop tiap chip
                foreach ($data_api as $item) {
                    $serial_number = $item['serial_number'] ?? '';
                    $number = $item['number'] ?? '';
                    $masa_tunggu = $item['masa_tunggu'] ?? '';
                    $status = $item['status'] ?? '';
                    $masa_waktu = $item['masa_waktu'] ?? '';
                    $kuota_nasional = $item['kuota_nasional'] ?? '0 GB';
                    $kuota_lokal = $item['kuota_lokal'] ?? null;
                    $lainnya = $item['lainnya'] ?? '0 GB Kuota PASTI';
                    $masa_paket_raw = $item['masa_paket'] ?? '';
                    $masa_paket = date('Y-m-d H:i:s', strtotime($masa_paket_raw));

                    // Cek chip di DB
                    $checkChip = $koneksi->prepare("SELECT id_chip FROM chip WHERE sn=? AND msisdn=?");
                    $checkChip->bind_param("ss", $item['sn'], $item['msisdn']);
                    $checkChip->execute();
                    $resultChip = $checkChip->get_result();
                    $dataChip = $resultChip->fetch_assoc();

                    if (!$dataChip) {
                        echo "<div class='log-line text-danger'>❌ Chip SN {$serial_number} tidak ditemukan di DB, lewati.</div>";
                        flush();
                        continue;
                    }

                    $id_chip = $dataChip['id_chip'];

                    // Cek kuota
                    $cekKuota = $koneksi->prepare("SELECT id_kuota FROM kuota WHERE id_chip=?");
                    $cekKuota->bind_param("i", $id_chip);
                    $cekKuota->execute();
                    $resKuota = $cekKuota->get_result();

                    if ($resKuota->num_rows > 0) {
                        // Update kuota
                        $update = $koneksi->prepare("
                            UPDATE kuota SET
                                serial_number=?, number=?, masa_tunggu=?, status=?, masa_waktu=?,
                                kuota_nasional=?, kuota_lokal=?, lainnya=?, masa_paket=?
                            WHERE id_chip=?
                        ");
                        $update->bind_param(
                            "sssssssssi",
                            $serial_number,
                            $number,
                            $masa_tunggu,
                            $status,
                            $masa_waktu,
                            $kuota_nasional,
                            $kuota_lokal,
                            $lainnya,
                            $masa_paket,
                            $id_chip
                        );
                        $update->execute();
                        echo "<div class='log-line'>🆙 Update kuota chip SN {$serial_number}.</div>";
                    } else {
                        // Insert kuota baru
                        $insert = $koneksi->prepare("
                            INSERT INTO kuota (id_chip, serial_number, number, masa_tunggu, status, masa_waktu, kuota_nasional, kuota_lokal, lainnya, masa_paket)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $insert->bind_param(
                            "isssssssss",
                            $id_chip,
                            $serial_number,
                            $number,
                            $masa_tunggu,
                            $status,
                            $masa_waktu,
                            $kuota_nasional,
                            $kuota_lokal,
                            $lainnya,
                            $masa_paket
                        );
                        $insert->execute();
                        echo "<div class='log-line'>➕ Tambah kuota baru untuk chip SN {$serial_number}.</div>";
                    }
                    flush();

                    // Update otomatis saat masa paket lewat
                    $now = new DateTime();
                    $masa = new DateTime($masa_paket);

                    if ($now >= $masa) {
                        if ($kuota_nasional !== "0 GB" || $lainnya !== "0 GB Kuota PASTI") {
                            $updateZero = $koneksi->prepare("
                                UPDATE kuota SET 
                                kuota_nasional='0 GB', lainnya='0 GB Kuota PASTI'
                                WHERE id_chip=?
                            ");
                            $updateZero->bind_param("i", $id_chip);
                            $updateZero->execute();
                            echo "<div class='log-line text-warning'>⚙️ Masa paket chip {$serial_number} telah lewat → kuota diset 0.</div>";
                        } else {
                            echo "<div class='log-line text-success'>✅ Chip {$serial_number} sudah habis kuota dan masa paket lewat.</div>";
                        }
                        flush();
                    }
                }
            }
        }

        echo "<div class='log-line text-info'>✅ Selesai proses box {$nama_box}.</div><hr>";
        flush();
    }
}

echo "<div class='log-line text-success fw-bold'>🎉 Sinkronisasi selesai semua.</div>";
echo "</div></body></html>";
flush();
