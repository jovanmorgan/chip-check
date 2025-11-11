<?php
// Daftar halaman yang tersedia
$pages = [
    '../dashboard' => 'Dashboard',
    '../chip' => 'Chip kouta Kartu',
    '../check' => 'cek kartu cek chip cek data chek check',
    '../aktivasi' => 'Aktivasi aktif act aktivasi kartu',
    '../transaksi' => 'Transakasi',
    '../petugas' => 'Petugas operator operasional ',
    '../profile' => 'Profile',
    '../log_out' => 'Log Out'
];

// Dapatkan query pencarian dari input
$query = isset($_POST['query']) ? strtolower(trim($_POST['query'])) : '';

// Variabel untuk menyimpan halaman yang cocok
$matched_page = null;

if ($query) {
    // Cari halaman yang tepat atau mendekati
    foreach ($pages as $page => $title) {
        if (strpos(strtolower($title), $query) !== false) {
            $matched_page = $page;
            break;
        }
    }

    // Redirect ke halaman yang cocok, atau ke halaman 404 jika tidak ada yang cocok
    if ($matched_page) {
        header("Location: $matched_page");
    } else {
        header("Location: ../404");
    }
    exit;
}
