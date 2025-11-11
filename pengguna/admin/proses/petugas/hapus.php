<?php
include '../../../../keamanan/koneksi.php';

// Terima ID petugas yang akan dihapus dari formulir HTML
$id_petugas = $_POST['id']; // Ubah menjadi $_GET jika menggunakan metode GET

// Lakukan validasi data
if (empty($id_petugas)) {
    echo "data_tidak_lengkap";
    exit();
}

// Buat query SQL untuk menghapus data petugas berdasarkan ID
$query_delete_petugas = "DELETE FROM petugas WHERE id_petugas = '$id_petugas'";

// Jalankan query untuk menghapus data
if (mysqli_query($koneksi, $query_delete_petugas)) {
    echo "success";
} else {
    echo "error";
}

// Tutup koneksi ke database
mysqli_close($koneksi);
