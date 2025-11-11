<?php
include '../../../../keamanan/koneksi.php';

// Terima ID mitra yang akan dihapus dari formulir HTML
$id_mitra = $_POST['id']; // Ubah menjadi $_GET jika menggunakan metode GET

// Lakukan validasi data
if (empty($id_mitra)) {
    echo "data_tidak_lengkap";
    exit();
}

// Buat query SQL untuk menghapus data mitra berdasarkan ID
$query_delete_mitra = "DELETE FROM mitra WHERE id_mitra = '$id_mitra'";

// Jalankan query untuk menghapus data
if (mysqli_query($koneksi, $query_delete_mitra)) {
    echo "success";
} else {
    echo "error";
}

// Tutup koneksi ke database
mysqli_close($koneksi);
