<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../../../keamanan/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $id_petugas = $_POST['id_petugas'] ?? null;
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $lokasi_penempatan = trim($_POST['lokasi_penempatan']);
    $foto = $_FILES['fp'] ?? null;

    // Validasi data wajib
    if (empty($id_petugas) || empty($nama_lengkap) || empty($username) || empty($lokasi_penempatan)) {
        echo "error_data_tidak_lengkap";
        exit;
    }

    // Cek apakah username sudah dipakai oleh petugas lain
    $check_query = "SELECT 1 FROM petugas WHERE username = ? AND id_petugas != ?";
    $stmt = $koneksi->prepare($check_query);
    if (!$stmt) {
        die("Error preparing statement: " . $koneksi->error);
    }
    $stmt->bind_param("si", $username, $id_petugas);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "error_username_exists";
        $stmt->close();
        $koneksi->close();
        exit;
    }
    $stmt->close();

    // Ambil data lama untuk cek apakah foto lama perlu dihapus
    $query_old = "SELECT fp, password FROM petugas WHERE id_petugas = ?";
    $stmt_old = $koneksi->prepare($query_old);
    $stmt_old->bind_param("i", $id_petugas);
    $stmt_old->execute();
    $result_old = $stmt_old->get_result();
    $data_lama = $result_old->fetch_assoc();
    $foto_lama = $data_lama['fp'] ?? null;
    $password_lama = $data_lama['password'] ?? null;
    $stmt_old->close();

    // Jika password diisi baru, validasi ulang
    if (!empty($password)) {
        if (strlen($password) < 8) {
            echo "error_password_length";
            exit;
        }
        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/", $password)) {
            echo "error_password_strength";
            exit;
        }
    } else {
        // Kalau password tidak diubah, gunakan password lama
        $password = $password_lama;
    }

    // --- Proses upload foto baru jika ada ---
    $nama_file_final = $foto_lama; // Default tetap foto lama
    if ($foto && $foto['error'] === 0) {
        $target_dir = "../../../../assets_admin/img/fp_pengguna/petugas/";
        $ekstensi = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
        $nama_file_final = "fp_" . uniqid() . "." . $ekstensi;
        $target_file = $target_dir . $nama_file_final;

        // Validasi ekstensi
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ekstensi, $allowed_ext)) {
            echo "error_format_foto";
            exit;
        }

        // Validasi ukuran (maks 3MB)
        if ($foto['size'] > 3 * 1024 * 1024) {
            echo "error_ukuran_foto";
            exit;
        }

        // Pindahkan file
        if (!move_uploaded_file($foto['tmp_name'], $target_file)) {
            echo "error_upload_foto";
            exit;
        }

        // Hapus foto lama jika ada dan file-nya masih ada
        if (!empty($foto_lama)) {
            $path_old = $target_dir . $foto_lama;
            if (file_exists($path_old)) {
                unlink($path_old);
            }
        }
    }

    // --- Update data petugas ---
    $update_query = "UPDATE petugas 
                     SET nama_lengkap = ?, username = ?, password = ?, lokasi_penempatan = ?, fp = ? 
                     WHERE id_petugas = ?";
    $stmt = $koneksi->prepare($update_query);
    if (!$stmt) {
        die("Error preparing update statement: " . $koneksi->error);
    }
    $stmt->bind_param("sssssi", $nama_lengkap, $username, $password, $lokasi_penempatan, $nama_file_final, $id_petugas);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error_db: " . $stmt->error;
    }

    $stmt->close();
    $koneksi->close();
}
