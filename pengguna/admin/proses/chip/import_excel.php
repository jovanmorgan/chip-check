<?php
include '../../../../keamanan/koneksi.php';

if (isset($_POST['excelData'])) {
    $data = json_decode($_POST['excelData'], true);

    // 🔹 Palet warna cerah untuk color_icon dan warna lembut untuk color_table
    $colorPairs = [
        ['#00bcd4', '#e0f7fa'], // biru muda
        ['#4caf50', '#e8f5e9'], // hijau muda
        ['#ff9800', '#fff3e0'], // oranye lembut
        ['#e91e63', '#fce4ec'], // pink
        ['#9c27b0', '#f3e5f5'], // ungu
        ['#3f51b5', '#e8eaf6'], // biru keputihan
        ['#009688', '#e0f2f1'], // hijau toska
        ['#ff5722', '#fbe9e7'], // oranye gelap lembut
        ['#795548', '#efebe9'], // coklat
        ['#607d8b', '#eceff1'], // abu kebiruan
    ];

    $colorIndex = 0;

    foreach ($data as $row) {
        // --- Kolom dasar ---
        $sn = $row['SN'] ?? '';
        $msisdn = $row['MSISDN'] ?? '';
        $box_inner = trim($row['BOX INNER'] ?? '');
        $in = $row['IN'] ?? '';
        $alokasi = $row['ALOKASI'] ?? '';

        // --- Cari kolom tanggal aktivasi yang fleksibel ---
        $tgl_aktivasi = '';
        foreach ($row as $key => $value) {
            if (preg_match('/tgl.*actv/i', $key)) { // cocokkan TGL ACTV, Tgl Actv 1, dll
                $tgl_aktivasi = $value;
                break;
            }
        }

        $status_aktivasi = strtolower(trim($row['STATUS AKTIVASI'] ?? ''));

        // --- Pastikan box_inner ada ---
        $cek_box = mysqli_query($koneksi, "SELECT id_box_inner FROM box_inner WHERE box_inner='$box_inner'");
        if (mysqli_num_rows($cek_box) > 0) {
            $d = mysqli_fetch_assoc($cek_box);
            $id_box_inner = $d['id_box_inner'];
        } else {
            // Pilih warna unik dari daftar (loop jika sudah habis)
            $colors = $colorPairs[$colorIndex % count($colorPairs)];
            $color_icon = $colors[0];
            $color_table = $colors[1];
            $colorIndex++;

            mysqli_query($koneksi, "INSERT INTO box_inner (box_inner, color_icon, color_table)
                VALUES ('$box_inner', '$color_icon', '$color_table')");
            $id_box_inner = mysqli_insert_id($koneksi);
        }

        // --- Insert ke tabel chip ---
        mysqli_query($koneksi, "INSERT INTO chip 
            (sn, msisdn, id_box_inner, `in`, alokasi, tanggal_aktivasi, status_aktivasi, status)
            VALUES 
            ('$sn', '$msisdn', '$id_box_inner', '$in', '$alokasi', '$tgl_aktivasi', '$status_aktivasi', 'aktivasi')");

        $id_chip = mysqli_insert_id($koneksi);

        // --- Cek data cicle ---
        $cicleCount = 0;
        foreach ($row as $key => $value) {
            if (preg_match('/^cicle\s*(\d+)/i', $key, $match)) {
                $num = $match[1];
                $cicle = trim($row["CICLE $num"] ?? '');
                $habis = trim($row["HABIS C$num"] ?? '');
                $hangus = trim($row["HANGUS C$num"] ?? '');
                $tgl_trx = trim($row["TGL TRX CICLE $num"] ?? '');

                if ($cicle || $habis || $hangus || $tgl_trx) {
                    mysqli_query($koneksi, "INSERT INTO cicle (id_chip, cicle, habis, hangus, tanggal_transaksi)
                        VALUES ('$id_chip', '$cicle', '$habis', '$hangus', '$tgl_trx')");
                    $cicleCount++;
                }
            }
        }

        // --- Jika tidak ada CICLE dalam bentuk bernomor, cek default ---
        if ($cicleCount === 0) {
            $cicle = $row['CICLE'] ?? '';
            $habis = $row['HABIS'] ?? '';
            $hangus = $row['HANGUS'] ?? '';
            $tgl_trx = $row['TGL TRX'] ?? '';
            if ($cicle || $habis || $hangus || $tgl_trx) {
                mysqli_query($koneksi, "INSERT INTO cicle (id_chip, cicle, habis, hangus, tanggal_transaksi)
                    VALUES ('$id_chip', '$cicle', '$habis', '$hangus', '$tgl_trx')");
            }
        }
    }

    echo 'success';
} else {
    echo 'no_data';
}
