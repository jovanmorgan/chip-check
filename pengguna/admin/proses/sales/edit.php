<?php
include '../../../../keamanan/koneksi.php';

// Terima data dari formulir HTML
$id_sales = $_POST['id_sales'];
$nama_lengkap = $_POST['nama_lengkap'];
$username = $_POST['username'];
$nomor_hp = $_POST['nomor_hp'];
$password = $_POST['password'];

// Lakukan validasi data
if (empty($id_sales) || empty($nama_lengkap) || empty($username) || empty($nomor_hp) || empty($password)) {
    echo "data_tidak_lengkap";
    exit();
}

// Cek apakah username sudah ada di tabel admin
$check_query_admin = "SELECT 1 FROM admin WHERE username = ?";
$stmt_admin = $koneksi->prepare($check_query_admin);
$stmt_admin->bind_param("s", $username);
$stmt_admin->execute();
$stmt_admin->store_result();
if ($stmt_admin->num_rows > 0) {
    echo "error_username_exists";
    $stmt_admin->close();
    exit();
}
$stmt_admin->close();

// Cek apakah username sudah ada di tabel sales kecuali dari id_sales itu sendiri
$check_query_sales = "SELECT 1 FROM sales WHERE username = ? AND id_sales != ?";
$stmt_sales = $koneksi->prepare($check_query_sales);
$stmt_sales->bind_param("si", $username, $id_sales);
$stmt_sales->execute();
$stmt_sales->store_result();
if ($stmt_sales->num_rows > 0) {
    echo "error_username_exists";
    $stmt_sales->close();
    exit();
}
$stmt_sales->close();

// Cek apakah username sudah ada di tabel admin
$check_query_admin = "SELECT 1 FROM admin WHERE username = ?";
$stmt_admin = $koneksi->prepare($check_query_admin);
$stmt_admin->bind_param("s", $username);
$stmt_admin->execute();
$stmt_admin->store_result();
if ($stmt_admin->num_rows > 0) {
    echo "error_username_exists";
    $stmt_admin->close();
    exit();
}
$stmt_admin->close();

// Validasi password
if (strlen($password) < 8) {
    echo "error_password_length";
    exit();
}

if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/", $password)) {
    echo "error_password_strength";
    exit();
}

// Buat query SQL untuk mengupdate data
$query_update = "UPDATE sales SET nama_lengkap = ?, username = ?, nomor_hp = ?, password = ? WHERE id_sales = ?";
$stmt_update = $koneksi->prepare($query_update);
$stmt_update->bind_param("ssssi", $nama_lengkap, $username, $nomor_hp, $password, $id_sales);

// Jalankan query
if ($stmt_update->execute()) {
    echo "success";
} else {
    echo "error";
}

$stmt_update->close();
$koneksi->close();
