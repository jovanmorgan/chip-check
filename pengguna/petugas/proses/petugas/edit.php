<?php
// Aktifkan laporan error
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Koneksi ke database
include '../../../../keamanan/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $id_mitra = $_POST['id_mitra'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $nomor_hp = $_POST['nomor_hp'];
    $password = $_POST['password'];

    // Pastikan data tidak kosong
    if (!empty($id_mitra) && !empty($nama_lengkap) && !empty($nomor_hp) && !empty($password)) {
        // Validasi nomor HP
        $check_query = "SELECT * FROM mitra WHERE nomor_hp = ? AND id_mitra != ?";
        $stmt = $koneksi->prepare($check_query);
        if (!$stmt) {
            die('Error preparing statement: ' . $koneksi->error);
        }
        $stmt->bind_param("si", $nomor_hp, $id_mitra);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "error_nomor_hp_exists";
            $stmt->close();
            $koneksi->close();
            exit();
        }

        // Validasi password
        if (strlen($password) < 8) {
            echo "error_password_length";
            $stmt->close();
            $koneksi->close();
            exit();
        }

        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/", $password)) {
            echo "error_password_strength";
            $stmt->close();
            $koneksi->close();
            exit();
        }

        // Query untuk memperbarui data di tabel mitra
        $update_query = "UPDATE mitra SET nama_lengkap = ?, nomor_hp = ?, password = ? WHERE id_mitra = ?";
        $stmt = $koneksi->prepare($update_query);
        if (!$stmt) {
            die('Error preparing statement: ' . $koneksi->error);
        }
        $stmt->bind_param("sssi", $nama_lengkap, $nomor_hp, $password, $id_mitra);

        // Eksekusi query
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Gagal: " . $stmt->error;
        }

        // Tutup statement
        $stmt->close();
    } else {
        echo "data_tidak_lengkap";
    }

    // Tutup koneksi
    $koneksi->close();
}
