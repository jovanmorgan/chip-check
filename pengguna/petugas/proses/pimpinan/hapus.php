<?php
include '../../../../keamanan/koneksi.php';

// Terima ID pimpinan yang akan dihapus dari formulir HTML
$id_pimpinan = $_POST['id']; // Ubah menjadi $_GET jika menggunakan metode GET

// Lakukan validasi data
if (empty($id_pimpinan)) {
    echo "data_tidak_lengkap";
    exit();
}

// Buat query SQL untuk menghapus data pimpinan berdasarkan ID
$query_delete_pimpinan = "DELETE FROM pimpinan WHERE id_pimpinan = '$id_pimpinan'";

// Jalankan query untuk menghapus data
if (mysqli_query($koneksi, $query_delete_pimpinan)) {
    echo "success";
} else {
    echo "error";
}

// Tutup koneksi ke database
mysqli_close($koneksi);
