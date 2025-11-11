<?php
include 'koneksi.php';

// Cek apakah ada data yang dikirim dari form sign-up-form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nama_lengkap']) && isset($_POST['nomor_hp']) && isset($_POST['password'])) {
    // Tangkap data dari form
    $nama_lengkap = $_POST['nama_lengkap'];
    $nomor_hp = $_POST['nomor_hp'];
    $password = $_POST['password'];

    // Cek apakah nomor_hp sudah ada di database
    $check_query = "SELECT * FROM mitra WHERE nomor_hp = '$nomor_hp'";
    $result = mysqli_query($koneksi, $check_query);
    if (mysqli_num_rows($result) > 0) {
        echo "error_nomor_hp_exists"; // Kirim respon "error_nomor_hp_exists" jika nomor HP sudah terdaftar
        exit();
    }

    $check_query_admin = "SELECT * FROM admin WHERE nomor_hp = '$nomor_hp'";
    $result_admin = mysqli_query($koneksi, $check_query_admin);
    if (mysqli_num_rows($result_admin) > 0) {
        echo "error_nomor_hp_exists"; // Kirim respon "error_nomor_hp_exists" jika nomor HP sudah terdaftar
        exit();
    }

    $check_query_sales = "SELECT * FROM sales WHERE nomor_hp = '$nomor_hp'";
    $result_sales = mysqli_query($koneksi, $check_query_sales);
    if (mysqli_num_rows($result_sales) > 0) {
        echo "error_nomor_hp_exists"; // Kirim respon "error_nomor_hp_exists" jika nomor HP sudah terdaftar
        exit();
    }

    if (strlen($password) < 8) {
        echo "error_password_length"; // Kirim respon "error_password_length" jika panjang password kurang dari 8 karakter
        exit();
    }

    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/", $password)) {
        echo "error_password_strength"; // Kirim respon "error_password_strength" jika password tidak memenuhi syarat
        exit();
    }

    // Generate OTP
    $otp = rand(100000, 999999);

    // Simpan OTP di sesi atau database
    session_start();
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_expires'] = time() + 60; // OTP berlaku selama 60 detik

    // Kirim OTP melalui API Fonnte
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
            'target' => $nomor_hp,
            'message' => 'Your OTP code is: ' . $otp,
            'countryCode' => '62' // Optional, sesuai dengan kode negara Anda
        ),
        CURLOPT_HTTPHEADER => array(
            'Authorization: 5nTe3rCha@wSptNGT87z' // Ganti dengan token Anda yang sebenarnya
        ),
    ));

    $response_curl = curl_exec($curl);
    curl_close($curl);

    if ($response_curl) {
        echo "otp_sent:" . $otp . ""; // Kirim respon "otp_sent" jika OTP berhasil dikirim
    } else {
        echo "error"; // Kirim respon "error" jika terjadi kesalahan saat pengiriman OTP
    }
}