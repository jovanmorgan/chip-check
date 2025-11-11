<?php
include '../../../../keamanan/koneksi.php';

// Terima ID sales yang akan dihapus dari formulir HTML
$id_sales = $_POST['id']; // Ubah menjadi $_GET jika menggunakan metode GET

// Lakukan validasi data
if (empty($id_sales)) {
    echo "data_tidak_lengkap";
    exit();
}

// Buat query SQL untuk menghapus data sales berdasarkan ID
$query_delete_sales = "DELETE FROM sales WHERE id_sales = '$id_sales'";

// Jalankan query untuk menghapus data
if (mysqli_query($koneksi, $query_delete_sales)) {
    echo "success";
} else {
    echo "error";
}

// Tutup koneksi ke database
mysqli_close($koneksi);
