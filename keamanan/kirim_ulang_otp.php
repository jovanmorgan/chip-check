<?php
include 'koneksi.php';
session_start();

// Cek apakah ada data yang dikirim untuk kirim ulang OTP
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nomor_hp'])) {
    $nomor_hp = $_POST['nomor_hp'];
    // Generate OTP
    $otp = rand(100000, 999999);

    // Simpan OTP di sesi atau database
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
        echo json_encode(["status" => "otp_success", "otp" => $otp]); // Kirim respons JSON
    } else {
        echo json_encode(["status" => "error"]); // Kirim respons JSON
    }
} else {
    echo json_encode(["status" => "invalid_request"]); // Kirim respons JSON
}
