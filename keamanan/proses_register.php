<?php
include 'koneksi.php';

// Cek apakah ada data yang dikirim dari form sign-up-form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nama_lengkap']) && isset($_POST['nomor_hp']) && isset($_POST['password'])) {
    // Tangkap data dari form
    $nama_lengkap = $_POST['nama_lengkap'];
    $nomor_hp = $_POST['nomor_hp'];
    $password = $_POST['password'];

    // Lakukan penambahan data ke dalam database
    $query = "INSERT INTO mitra (nama_lengkap, nomor_hp, password) VALUES ('$nama_lengkap', '$nomor_hp', '$password')";
    if (mysqli_query($koneksi, $query)) {
        // Pesan WhatsApp yang akan dikirimkan
        $pesan = "Selamat $nama_lengkap, Anda sudah terdaftar sebagai mitra 😊. Berikut username dan password Anda : \n\n📞 Username : $nomor_hp\n🗝️ Password : $password";

        // Kirim pesan melalui API Fonnte
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
                'Authorization: 4wGgAKpf4eBRKfqomd79' // Ganti dengan token Anda yang sebenarnya
            ),
        ));

        $response_curl = curl_exec($curl);
        curl_close($curl);

        if ($response_curl) {
            // Kirim respon "success" jika data berhasil ditambahkan dan pesan WhatsApp berhasil dikirim
            echo "success";
        } else {
            // Kirim respon "error" jika data berhasil ditambahkan tapi pengiriman pesan WhatsApp gagal
            echo "success_but_sms_failed";
        }
    } else {
        // Kirim respon "error" jika terjadi kesalahan dalam penambahan data
        echo "error";
    }
}
