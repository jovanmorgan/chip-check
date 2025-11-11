<?php
include 'koneksi.php';

function checkUserType($nomor_hp)
{
    global $koneksi;
    $query_admin = "SELECT nama_lengkap FROM admin WHERE nomor_hp = '$nomor_hp'";
    $query_sales = "SELECT nama_lengkap FROM sales WHERE nomor_hp = '$nomor_hp'";

    $result_admin = mysqli_query($koneksi, $query_admin);
    $result_sales = mysqli_query($koneksi, $query_sales);

    if (mysqli_num_rows($result_admin) > 0) {
        return ['type' => 'admin', 'data' => mysqli_fetch_assoc($result_admin)];
    } elseif (mysqli_num_rows($result_sales) > 0) {
        return ['type' => 'sales', 'data' => mysqli_fetch_assoc($result_sales)];
    } else {
        return ['type' => 'not_found', 'data' => null];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nomor_hp'])) {
    $nomor_hp = $_POST['nomor_hp'];

    // Validasi nomor HP
    if (empty($nomor_hp)) {
        echo "error_nomor_hp_tidak_ada";
        exit();
    }

    // Cek jenis pengguna
    $user = checkUserType($nomor_hp);
    if ($user['type'] !== "not_found") {
        $nama_lengkap = $user['data']['nama_lengkap'];
        $password_baru = substr($nama_lengkap, 0, 2) . '-' . rand(100000, 999999);

        // Update password di tabel yang relevan
        if ($user['type'] == 'admin') {
            $query_update = "UPDATE admin SET password = '$password_baru' WHERE nomor_hp = '$nomor_hp'";
        } elseif ($user['type'] == 'sales') {
            $query_update = "UPDATE sales SET password = '$password_baru' WHERE nomor_hp = '$nomor_hp'";
        }

        if (mysqli_query($koneksi, $query_update)) {
            // Kirim password baru ke WhatsApp menggunakan API Fonnte
            // $pesan = "Password baru Anda adalah: " . $password_baru;
            $pesan = "Haloo $nama_lengkap, Anda berhasil mengganti password 😊. Berikut adalah password baru Anda : \n\n🗝️ Password : $password_baru";

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
                    'message' => $pesan,
                    'countryCode' => '62' // Optional, sesuai dengan kode negara Anda
                ),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: 5nTe3rCha@wSptNGT87z' // Ganti dengan token Anda yang sebenarnya
                ),
            ));

            $response_curl = curl_exec($curl);
            curl_close($curl);

            if ($response_curl === false) {
                echo "error_api";
            } else {
                echo "success";
            }
        } else {
            echo "error_update_gagal";
        }
    } else {
        echo "error_nomor_hp";
    }
} else {
    echo "invalid_request";
}
