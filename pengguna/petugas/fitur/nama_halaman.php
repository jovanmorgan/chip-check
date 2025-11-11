<?php
// Dapatkan nama halaman dari URL saat ini tanpa ekstensi .php
$current_page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), ".php");

// Tentukan judul halaman berdasarkan nama file
switch ($current_page) {
    case 'dashboard':
        $page_title = 'Dashboard';
        break;
    case 'chip':
        $page_title = 'Chip';
        break;
    case 'check':
        $page_title = 'Check';
        break;
    case 'box_inner':
        $page_title = 'Box Inner';
        break;
    case 'transaksi':
        $page_title = 'Transaksi';
        break;
    case 'aktivasi':
        $page_title = 'Aktivasi';
        break;
    case 'petugas':
        $page_title = 'Petugas Operasional';
        break;
    case 'pending':
        $page_title = 'Pending';
        break;
    case 'rusak':
        $page_title = 'Rusak';
        break;
    case 'profile':
        $page_title = 'Profile';
        break;
    case 'cicle':
        $page_title = 'Cicle';
        break;
    case 'log_out':
        $page_title = 'Log Out';
        break;
    default:
        $page_title = 'admin Chip Check';
        break;
}
