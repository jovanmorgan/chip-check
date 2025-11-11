<?php
session_start();

// Periksa apakah pengguna sudah masuk atau belum
if (!isset($_SESSION['id_petugas'])) {
    // Pengguna belum masuk, arahkan kembali ke halaman masuk.php
    header("Location: ../../berlangganan/login_privasi");
    exit; // Pastikan untuk menghentikan eksekusi skrip setelah mengarahkan
}
