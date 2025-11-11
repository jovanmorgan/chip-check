<?php
header('Content-Type: application/json; charset=utf-8');

// Pastikan ada parameter 'data' yang berisi JSON array [{sn:..., msisdn:...}, ...]
if (!isset($_GET['data'])) {
    echo json_encode(['error' => 'Parameter data wajib diisi (format JSON)']);
    exit;
}

$input = json_decode($_GET['data'], true);
if (!$input || !is_array($input)) {
    echo json_encode(['error' => 'Format JSON tidak valid']);
    exit;
}

// Fungsi bantu untuk ambil teks dari pola
function extract_value($pattern, $text)
{
    return preg_match($pattern, $text, $m) ? trim($m[1]) : null;
}

// Fungsi bantu untuk ubah teks tanggal Indonesia jadi format standar (YYYY-MM-DD)
function parse_indo_date($str)
{
    if (!$str) return null;
    $bulan = [
        'Januari' => 'January',
        'Februari' => 'February',
        'Maret' => 'March',
        'April' => 'April',
        'Mei' => 'May',
        'Juni' => 'June',
        'Juli' => 'July',
        'Agustus' => 'August',
        'September' => 'September',
        'Oktober' => 'October',
        'November' => 'November',
        'Desember' => 'December'
    ];
    // Hapus jam jika ada, kita hanya butuh tanggal bagian depan
    $str = trim(preg_replace('/\s*\d{1,2}:\d{2}(:\d{2})?$/', '', $str));
    $str = str_ireplace(array_keys($bulan), array_values($bulan), $str);
    $ts = strtotime($str);
    if ($ts === false) return null;
    return date('Y-m-d', $ts);
}

/**
 * Hitung tanggal_act, jumlah_circle, dan daftar tanggal setiap circle.
 * Logika:
 *  - tanggal_awal = masa_paket + 1 hari
 *  - tanggal_act = tanggal_awal - masa_waktu (hari)
 *  - jumlah_circle = ceil(masa_waktu / 30)
 *  - circle_dates:
 *      circle1 = tanggal_act
 *      circle2 = tanggal_act + 29 hari
 *      circle3 = tanggal_act + 29 + 30*(index-2) hari
 */
function hitung_act_dan_circle($masa_paket, $masa_waktu)
{
    if (!$masa_paket || !$masa_waktu) return [null, 0, []];

    // Ambil jumlah hari dari teks masa waktu (misal "60 hari")
    if (preg_match('/(\d+)/', $masa_waktu, $m)) {
        $hari = (int)$m[1];
        if ($hari <= 0) return [null, 0, []];
    } else {
        return [null, 0, []];
    }

    // Konversi tanggal masa paket ke format Y-m-d
    $tanggal_paket_str = parse_indo_date($masa_paket);
    if (!$tanggal_paket_str) return [null, 0, []];

    // buat DateTime dari masa paket
    $dt_masa_paket = new DateTime($tanggal_paket_str);

    // tanggal_awal = masa_paket + 1 day
    $dt_awal = clone $dt_masa_paket;
    $dt_awal->modify('+1 day');

    // tanggal_act = tanggal_awal - masa_waktu (hari)
    $dt_act = clone $dt_awal;
    $dt_act->modify("-{$hari} days");

    // jumlah circle = ceil(hari / 30)
    $jumlah_circle = (int) ceil($hari / 30);

    // bangun daftar tanggal circle
    $circle_dates = [];
    for ($i = 0; $i < $jumlah_circle; $i++) {
        // offset dalam hari dari tanggal_act
        if ($i === 0) {
            $offset = 0;
        } elseif ($i === 1) {
            $offset = 29;
        } else {
            $offset = 29 + ($i - 1) * 30;
        }
        $d = clone $dt_act;
        if ($offset !== 0) $d->modify("+{$offset} days");
        $circle_dates[] = $d->format('Y-m-d'); // format standar, gampang dipakai di frontend
    }

    return [$dt_act->format('Y-m-d'), $jumlah_circle, $circle_dates];
}

// ====================================================================

$results = [];

foreach ($input as $item) {
    if (!isset($item['sn']) || !isset($item['msisdn'])) continue;

    $sn = preg_replace('/\D/', '', $item['sn']);
    $msisdn = preg_replace('/\D/', '', $item['msisdn']);

    $url = "https://digipos.telkomsel.com/simcardchecking/S156X0{$sn}000010{$msisdn}";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; simcheck/1.0)',
    ]);
    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200 || !$html) {
        $results[] = [
            'sn' => $sn,
            'msisdn' => $msisdn,
            'error' => "Gagal mengambil data. HTTP $code"
        ];
        continue;
    }

    // Bersihkan HTML
    $html_no_scripts = preg_replace('#<script.*?>.*?</script>#si', '', $html);
    $html_no_style = preg_replace('#<style.*?>.*?</style>#si', '', $html_no_scripts);
    $text = strip_tags($html_no_style);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5);

    // Ekstraksi nilai
    $masa_paket_raw = extract_value('/Masa\s*Tunggu\s*Paket\s*([0-9]{1,2}\s+\w+\s+[0-9]{4})/i', $text);
    $masa_waktu = extract_value('/Masa\s*Waktu\s*([0-9]+\s*hari)/i', $text);

    // Hitung tanggal act dan jumlah circle (dengan daftar tanggal tiap circle)
    list($tanggal_act, $jumlah_circle, $circle_dates) = hitung_act_dan_circle($masa_paket_raw, $masa_waktu);

    $results[] = [
        'sn' => $sn,
        'msisdn' => $msisdn,
        'serial_number' => extract_value('/Serial\s*Number\s*([0-9]+)/i', $text),
        'number' => extract_value('/Number\s*(08[0-9]+)/i', $text),
        'masa_tunggu' => extract_value('/Masa\s*Tunggu\s*Kartu\s*([0-9]{1,2}\s+\w+\s+[0-9]{4})/i', $text),
        'status' => extract_value('/(Active|Not\s*Active)/i', $text),
        'masa_waktu' => $masa_waktu,
        'kuota_nasional' => extract_value('/Kuota\s*Nasional\s*([0-9]+\s*GB)/i', $text),
        'kuota_lokal' => extract_value('/Kuota\s*Lokal\s*([0-9]+\s*GB)/i', $text),
        'lainnya' => extract_value('/Lainnya\s*([0-9]+\s*GB\s*\w*)/i', $text),
        'masa_paket' => $masa_paket_raw,
        'tanggal_act' => $tanggal_act,
        'jumlah_circle' => $jumlah_circle,
        'circle_dates' => $circle_dates
    ];
}

// Output JSON
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
