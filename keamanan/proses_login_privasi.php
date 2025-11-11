<?php
include 'koneksi.php';

function checkpenggunahType($username)
{
    global $koneksi;
    $query_admin = "SELECT * FROM admin WHERE username = '$username'";
    $query_petugas = "SELECT * FROM petugas WHERE username = '$username'";

    $result_admin = mysqli_query($koneksi, $query_admin);
    $result_petugas = mysqli_query($koneksi, $query_petugas);

    if (mysqli_num_rows($result_admin) > 0) {
        return "admin";
    } elseif (mysqli_num_rows($result_petugas) > 0) {
        return "petugas";
    } else {
        return "not_found";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Lakukan validasi data
    if (empty($username) && empty($password)) {
        echo "tidak_ada_data";
        exit();
    }
    if (empty($username)) {
        echo "username_tidak_ada";
        exit();
    }

    if (empty($password)) {
        echo "password_tidak_ada";
        exit();
    }

    $penggunahType = checkpenggunahType($username);
    if ($penggunahType !== "not_found") {
        $query_penggunah = "SELECT * FROM $penggunahType WHERE username = '$username'";
        $result_penggunah = mysqli_query($koneksi, $query_penggunah);

        if (mysqli_num_rows($result_penggunah) > 0) {
            $row = mysqli_fetch_assoc($result_penggunah);
            $hashed_password = $row['password'];
            $nama_lengkap = $row['nama_lengkap'];

            if ($password === $hashed_password) {
                // Process login for other penggunah types
                session_start();
                $_SESSION['username'] = $username;

                switch ($penggunahType) {
                    case "admin":
                        $_SESSION['id_admin'] = $row['id_admin'];
                        break;
                    case "petugas":
                        $_SESSION['id_petugas'] = $row['id_petugas'];
                        break;
                    default:
                        break;
                }

                // Success response
                echo "success:" . $nama_lengkap . ":" . $penggunahType . ":" . getRedirectURL($penggunahType);
            } else {
                echo "error_password";
            }
        } else {
            echo "error_username";
        }
    } else {
        echo "error_username";
    }
}


function getRedirectURL($penggunahType)
{
    switch ($penggunahType) {
        case "admin":
            return "../pengguna/admin/";
        case "petugas":
            return "../pengguna/petugas/";
        default:
            return "../berlangganan/login_privasi.php";
    }
}
