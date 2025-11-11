<?php
// Aktifkan laporan error (untuk debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Koneksi ke database
include '../../../../keamanan/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $lokasi_penempatan = trim($_POST['lokasi_penempatan']);
    $foto = $_FILES['fp'] ?? null;

    // Pastikan data tidak kosong
    if (empty($nama_lengkap) || empty($username) || empty($password) || empty($lokasi_penempatan)) {
        echo "error_data_tidak_lengkap";
        exit;
    }

    // Cek apakah nomor HP sudah ada
    $check_query = "SELECT 1 FROM petugas WHERE username = ?";
    $stmt = $koneksi->prepare($check_query);
    if (!$stmt) {
        die("Error preparing statement: " . $koneksi->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "error_username_exists";
        $stmt->close();
        $koneksi->close();
        exit;
    }
    $stmt->close();

    // Validasi password
    if (strlen($password) < 8) {
        echo "error_password_length";
        exit;
    }
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/", $password)) {
        echo "error_password_strength";
        exit;
    }


    // Proses upload foto profil jika ada
    $nama_file_final = null;
    if ($foto && $foto['error'] == 0) {
        $target_dir = "../../../../assets_admin/img/fp_pengguna/petugas/";
        $ekstensi = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
        $nama_file_final = "fp_" . uniqid() . "." . $ekstensi;
        $target_file = $target_dir . $nama_file_final;

        // Validasi ekstensi file
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ekstensi, $allowed_ext)) {
            echo "error_format_foto";
            exit;
        }

        // Validasi ukuran file (maks 3MB)
        if ($foto['size'] > 3 * 1024 * 1024) {
            echo "error_ukuran_foto";
            exit;
        }

        // Pindahkan file ke folder tujuan
        if (!move_uploaded_file($foto['tmp_name'], $target_file)) {
            echo "error_upload_foto";
            exit;
        }
    }

    // Simpan data ke database
    $insert_query = "INSERT INTO petugas (nama_lengkap, username, password, lokasi_penempatan, fp) VALUES (?, ?, ?, ?, ?)";
    $stmt = $koneksi->prepare($insert_query);
    if (!$stmt) {
        die("Error preparing insert statement: " . $koneksi->error);
    }

    $stmt->bind_param("sssss", $nama_lengkap, $username, $password, $lokasi_penempatan, $nama_file_final);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error_db: " . $stmt->error;
    }

    $stmt->close();
    $koneksi->close();
}