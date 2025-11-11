<?php
include 'koneksi.php';

function checkpenggunahType($nomor_hp)
{
    global $koneksi;
    $query_mitra = "SELECT * FROM mitra WHERE nomor_hp = '$nomor_hp'";

    $result_mitra = mysqli_query($koneksi, $query_mitra);

    if (mysqli_num_rows($result_mitra) > 0) {
        return "mitra";
    } else {
        return "not_found";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nomor_hp']) && isset($_POST['password'])) {
    $nomor_hp = $_POST['nomor_hp'];
    $password = $_POST['password'];

    // Lakukan validasi data
    if (empty($nomor_hp) && empty($password)) {
        echo "tidak_ada_data";
        exit();
    }
    if (empty($nomor_hp)) {
        echo "nomor_hp_tidak_ada";
        exit();
    }

    if (empty($password)) {
        echo "password_tidak_ada";
        exit();
    }


    $penggunahType = checkpenggunahType($nomor_hp);
    if ($penggunahType !== "not_found") {
        $query_penggunah = "SELECT * FROM $penggunahType WHERE nomor_hp = '$nomor_hp'";
        $result_penggunah = mysqli_query($koneksi, $query_penggunah);

        if (mysqli_num_rows($result_penggunah) > 0) {
            $row = mysqli_fetch_assoc($result_penggunah);
            $nama_lengkap = $row['nama_lengkap'];
            $hashed_password = $row['password'];

            if ($password === $hashed_password) {

                // Process login for other penggunah types
                session_start();
                $_SESSION['nomor_hp'] = $nomor_hp;

                switch ($penggunahType) {
                    case "mitra":
                        $_SESSION['id_mitra'] = $row['id_mitra'];
                        break;
                    default:
                        break;
                }

                // Success response
                switch ($penggunahType) {
                    case "mitra":
                        echo "success:" . $nama_lengkap . ":" . $penggunahType . ":" . "../pengguna/mitra/";
                        break;
                    default:
                        echo "success:" . $nama_lengkap . ":" . $penggunahType . ":" . "../berlangganan/login";
                        break;
                }
            } else {
                echo "error_password";
            }
        } else {
            echo "error_nomor_hp";
        }
    } else {
        echo "error_nomor_hp";
    }
}
