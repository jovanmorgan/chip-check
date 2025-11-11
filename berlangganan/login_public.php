<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Chip Check | SignIn & SignUp</title>
    <link rel="stylesheet" href="../home_page/css/login.css?v=<?= time(); ?>" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css?v=<?= time(); ?>"
        integrity="sha512-oG8rzM+eZZp58dA9DdeHhUxzIqNka4yhvHH3ctM0AkF9zF7/Z/mJ1QeIfbNsTGoBQ5U9DFck+qiZyTbL6c9Hyw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Favicon -->
    <link href="../assets_admin/img/kohinor/logo_kohinor.png?v=<?= time(); ?>" rel="icon" />
</head>

<body translate="no">

    <!-- Modal untuk verifikasi kode -->
    <div id="verifyModal" class="modal">
        <div class="modal-content">
            <!-- Tombol X untuk menutup pop-up -->
            <span class="close">&times;</span>
            <h4>Verifikasi Kode</h4>
            <p>Masukkan kode yang telah dikirimkan ke WhatsApp Anda.</p>
            <div class="code-inputs">
                <input type="hidden" id="otp_yang_terkirim">
                <input type="number" inputmode="numeric" maxlength="1" id="code1" class="code-input" />
                <input type="number" inputmode="numeric" maxlength="1" id="code2" class="code-input" />
                <input type="number" inputmode="numeric" maxlength="1" id="code3" class="code-input" />
                <input type="number" inputmode="numeric" maxlength="1" id="code4" class="code-input" />
                <input type="number" inputmode="numeric" maxlength="1" id="code5" class="code-input" />
                <input type="number" inputmode="numeric" maxlength="1" id="code6" class="code-input" />
            </div>

            <div class="timer-text">
                <p id="timer">Kirim Ulang Kode dalam 60 detik</p>
            </div>
            <div class="button-group">
                <button id="resendCodeBtn" class="btn_code" disabled>
                    Reset Kode
                </button>
                <button id="verifyCodeBtn" class="btn_code">Verifikasi</button>
            </div>
        </div>
    </div>

    <div class="container">

        <div class="forms-container">
            <div class="signin-signup">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" />

                <form action="proses_login.php" class="sign-in-form" method="POST">
                    <h2 class="title">Sign In</h2>
                    <div class="input-field">
                        <i class="bx bx-phone"></i>
                        <input type="text" name="nomor_hp" placeholder="Nomor ponsel" required />
                    </div>
                    <div class="input-field">
                        <i class="bx bx-lock"></i>
                        <input id="signinPassword" type="password" name="password" placeholder="Password" required />
                        <i class="bx bx-hide show-password" onclick="togglePasswordVisibility('signinPassword')"></i>
                    </div>
                    <button type="submit" class="btn solid">Login</button>
                </form>

                <form id="signupForm" class="sign-up-form" method="POST" action="proses_register.php">
                    <h2 class="title">Sign Up</h2>
                    <div class="input-field">
                        <i class="bx bx-user"></i>
                        <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required />
                    </div>
                    <div class="input-field">
                        <i class="bx bx-phone"></i>
                        <input type="number" min="0" name="nomor_hp" id="nomor_hp" placeholder="Nomor ponsel"
                            required />
                    </div>
                    <div class="input-field">
                        <i class="bx bx-lock"></i>
                        <input id="signupPassword" type="password" name="password" placeholder="Password" required />
                        <i class="bx bx-hide show-password" onclick="togglePasswordVisibility('signupPassword')"></i>
                    </div>
                    <button type="submit" id="tombol_register" class="btn solid">Sign Up</button>
                </form>
            </div>
        </div>

        <div class="panels-container">
            <div class="panel left-panel">
                <div class="content">
                    <h3>Belum Punya Akun?</h3>
                    <p>Silakan Klik tombol Sign Up untuk registrasi!</p>
                    <button class="btn transparent" id="sign-up-btn">Sign Up</button>
                </div>
                <img src="img/log.svg" class="image" alt="" />
            </div>

            <div class="panel right-panel">
                <div class="content">
                    <h3 style="z-index: 5">Sudah Punya Akun?</h3>
                    <p style="z-index: 5">
                        Silakan tekan tombol Sign in untuk masuk ke halaman selanjutnya.
                    </p>
                    <button class="btn transparent" id="sign-in-btn" style="z-index: 5">
                        Sign In
                    </button>
                </div>
                <img src="img/register.svg" class="image" alt="" />
            </div>
        </div>
    </div>

    <!-- Script untuk icon -->
    <script src="https://kit.fontawesome.com/64d58efce2.js?v=<?= time(); ?>" crossorigin="anonymous"></script>
    <!-- Tambahkan SweetAlert butuh internet -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js?v=<?= time(); ?>"></script>
    <!-- <script src="../home_page/js/login.js?v=<?= time(); ?>"></script> -->
    <script>
        const sign_in_btn = document.querySelector("#sign-in-btn");
        const sign_up_btn = document.querySelector("#sign-up-btn");
        const container = document.querySelector(".container");

        sign_up_btn.addEventListener("click", () => {
            container.classList.add("sign-up-mode");
            container.classList.remove("putar-kekanan");
        });

        sign_in_btn.addEventListener("click", () => {
            container.classList.remove("sign-up-mode");
            container.classList.add("putar-kekanan");
        });

        document.querySelectorAll(".code-input").forEach((input, index, inputs) => {
            input.addEventListener("input", function() {
                if (this.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                } else if (index === inputs.length - 1 && this.value.length === 1) {
                    this.blur(); // Hentikan di input terakhir
                }
            });

            input.addEventListener("keydown", function(event) {
                if (event.key === "Backspace" && this.value.length === 0 && index > 0) {
                    inputs[index - 1].focus(); // Pindah ke input sebelumnya jika backspace ditekan
                }
            });
        });

        document.getElementById("signupForm").addEventListener("submit", function(event) {
            event.preventDefault();

            // Kirim data form untuk pengecekan
            var formData = new FormData(this);

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "../keamanan/proses_check.php", true);
            xhr.onreadystatechange = function() {
                var response = xhr.responseText;
                var responseArray = response.split(":");
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var response = xhr.responseText.trim();

                        if (response === "error_nomor_hp_exists") {
                            handleRegistrationError("error_nomor_hp_exists");
                        } else if (response === "error_password_length") {
                            handleRegistrationError("error_password_length");
                        } else if (response === "error_password_strength") {
                            handleRegistrationError("error_password_strength");
                        } else if (responseArray[0].trim() === "otp_sent") {
                            // Jika OTP berhasil dikirim, tampilkan modal verifikasi
                            document.getElementById("otp_yang_terkirim").value = responseArray[1];
                            let tombol_register = document.getElementById("tombol_register");
                            tombol_register.disabled = true;
                            tombol_register.style.backgroundColor = "#aeaeae";

                            // Tampilkan modal verifikasi
                            document.getElementById("verifyModal").style.display = "block";

                            // Mulai countdown 60 detik untuk tombol kirim ulang kode
                            startResendCountdown();
                        } else {
                            swal("Error", "Terjadi kesalahan saat proses", "error");
                        }
                    } else {
                        swal("Error", "Gagal melakukan request", "error");
                    }
                }
            };
            xhr.onerror = function() {
                swal("Error", "Gagal melakukan request", "error");
            };
            xhr.send(formData);
        });

        // bagian verivikasi
        document.getElementById("verifyCodeBtn").addEventListener("click", function() {
            let code1 = document.getElementById("code1").value;
            let code2 = document.getElementById("code2").value;
            let code3 = document.getElementById("code3").value;
            let code4 = document.getElementById("code4").value;
            let code5 = document.getElementById("code5").value;
            let code6 = document.getElementById("code6").value;

            let otp_yang_terkirim = document.getElementById("otp_yang_terkirim").value;
            let kumpulan_kode = code1 + code2 + code3 + code4 + code5 + code6;

            if (kumpulan_kode == otp_yang_terkirim) {
                // Kode benar, kirim data form registrasi
                submitRegistration();
            } else {
                // Kode salah, tampilkan pesan kesalahan
                swal("Error", "Kode yang Anda masukkan salah", "error");
            }
        });

        function submitRegistration() {
            var formData = new FormData(document.getElementById("signupForm"));

            // Tambahkan OTP ke form data
            let otp_yang_terkirim = document.getElementById("otp_yang_terkirim").value;

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "../keamanan/proses_register.php", true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var response = xhr.responseText.trim();
                        if (response === "success") {
                            swal({
                                title: "Success",
                                text: "Anda Berhasil Registrasi, akun anda sudah dikirim ke WhatsApp.",
                                icon: "success",
                                buttons: {
                                    confirm: {
                                        text: "OK",
                                        value: "ok",
                                    },
                                },
                            }).then((value) => {
                                if (value === "ok") {
                                    closeModal();
                                    document.getElementById("signupForm").reset();
                                    container.classList.remove("sign-up-mode");
                                    container.classList.add("putar-kekanan");
                                    let tombol_register = document.getElementById("tombol_register");
                                    tombol_register.disabled = false;
                                    tombol_register.style.backgroundColor = "hsl(30, 100%, 50%)";

                                    // Reset input code
                                    resetCodeInputs();
                                }
                            });
                        } else if (response === "success_but_sms_failed") {
                            swal({
                                title: "Data Berhasil",
                                text: "Anda Berhasil Registrasi, namun pesan WhatsApp gagal terkirim.",
                                icon: "warning",
                                buttons: {
                                    confirm: {
                                        text: "OK",
                                        value: "ok",
                                    },
                                },
                            }).then((value) => {
                                if (value === "ok") {
                                    closeModal();
                                    document.getElementById("signupForm").reset();
                                    container.classList.remove("sign-up-mode");
                                    container.classList.add("putar-kekanan");
                                    let tombol_register = document.getElementById("tombol_register");
                                    tombol_register.disabled = false;
                                    tombol_register.style.backgroundColor = "hsl(30, 100%, 50%)";

                                    // Reset input code
                                    resetCodeInputs();
                                }
                            });
                        } else {
                            handleRegistrationError(response);
                        }
                    } else {
                        swal("Error", "Gagal melakukan request", "error");
                    }
                }
            };
            xhr.onerror = function() {
                swal("Error", "Gagal melakukan request", "error");
            };
            xhr.send(formData);
        }

        function resetCodeInputs() {
            var codeInputs = document.querySelectorAll(".code-input");
            codeInputs.forEach(function(input) {
                input.value = ""; // Reset each input to empty
            });
        }

        function handleRegistrationError(response) {
            if (response === "error_nomor_hp_exists") {
                swal("Error", "Nomor ponsel sudah terdaftar, silakan gunakan nomor ponsel lain", "error");
            } else if (response === "error_password_length") {
                swal("Error", "Password harus terdiri dari minimal 8 karakter", "error");
            } else if (response === "error_password_strength") {
                swal("Error", "Password harus mengandung huruf besar, huruf kecil, dan angka", "error");
            } else if (response === "error_otp_invalid") {
                swal("Error", "Kode OTP yang Anda masukkan tidak valid", "error");
            } else {
                swal("Error", "Terjadi kesalahan saat proses", "error");
            }
        }

        // Countdown untuk tombol kirim ulang kode
        function startResendCountdown() {
            var countdown = 60;
            var resendBtn = document.getElementById("resendCodeBtn");
            var timerElement = document.getElementById("timer");

            resendBtn.disabled = true;
            timerElement.innerText = `Kirim Ulang Kode dalam ${countdown} detik`;

            var interval = setInterval(function() {
                countdown--;
                timerElement.innerText = `Kirim Ulang Kode dalam ${countdown} detik`;

                if (countdown <= 0) {
                    clearInterval(interval);
                    resendBtn.disabled = false;
                    timerElement.innerText = "";
                    resendBtn.innerText = "Kirim Ulang Kode";
                }
            }, 1000);
        }

        document.getElementById("resendCodeBtn").addEventListener("click", function() {
            // Mengirimkan kode baru melalui API (misalnya dengan AJAX)
            var formData = new FormData();
            formData.append("nomor_hp", document.getElementById("nomor_hp").value);

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "../keamanan/kirim_ulang_otp.php", true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var response = xhr.responseText.trim();
                        try {
                            var responseJson = JSON.parse(response);
                            if (responseJson.status === "otp_success") {
                                document.getElementById("otp_yang_terkirim").value = responseJson.otp;
                                // Mulai ulang countdown 60 detik untuk tombol kirim ulang kode
                                startResendCountdown();
                            } else {
                                swal("Error", "Gagal mengirimkan kode ulang", "error");
                            }
                        } catch (e) {
                            swal("Error", "Respon tidak valid dari server", "error");
                        }
                    } else {
                        swal("Error", "Gagal melakukan request", "error");
                    }
                }
            };
            xhr.onerror = function() {
                swal("Error", "Gagal melakukan request", "error");
            };
            xhr.send(formData);
        });

        document.querySelector(".sign-in-form").addEventListener("submit", function(event) {
            event.preventDefault();

            var formData = new FormData(this);

            var nomor_hp = formData.get("nomor_hp");
            var password = formData.get("password");

            if (nomor_hp === "" || password === "") {
                swal("Error", "Nomor ponsel dan password wajib diisi", "error");
                return;
            }

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "../keamanan/proses_login_public.php", true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var response = xhr.responseText;
                        var responseArray = response.split(":"); // Memisahkan respons menjadi array

                        if (responseArray[0].trim() === "success") {
                            var nama_lengkap = responseArray[1].trim();
                            var redirect_url = responseArray[3].trim();

                            // Menampilkan alert dengan SweetAlert versi 1 tanpa tombol OK dan melakukan pengalihan otomatis
                            swal({
                                title: "Login berhasil!",
                                text: "Selamat datang " + nama_lengkap,
                                icon: "success",
                                buttons: false, // Menghilangkan tombol OK
                                timer: 2000, // Durasi tampilan dalam milidetik
                            }).then(function() {
                                window.location.href = redirect_url;
                            });

                        } else if (responseArray[0].trim() === "error_password") {
                            swal("Error", "Password yang dimasukkan salah", "error");
                        } else if (response.trim() === "error_nomor_hp") {
                            swal("Error", "Nomor HP tidak ditemukan", "error");
                        } else if (response.trim() === "tidak_ada_data") {
                            swal("Error", "Nomor ponsel dan password wajib diisi", "error");
                        } else if (response.trim() === "nomor_hp_tidak_ada") {
                            swal("Error", "Nomor ponsel wajib diisi", "error");
                        } else if (response.trim() === "password_tidak_ada") {
                            swal("Error", "Password wajib diisi", "error");
                        } else {
                            swal("Error", "Terjadi kesalahan saat proses login", "error");
                        }
                    } else {
                        swal("Error", "Gagal melakukan request", "error");
                    }
                }
            };
            xhr.onerror = function() {
                swal("Error", "Gagal melakukan request", "error");
            };
            xhr.send(formData);
        });


        // bagian login baru
        var modal = document.getElementById("verifyModal");

        // Ambil elemen tombol close
        var closeButton = document.getElementsByClassName("close")[0];

        // Fungsi untuk menutup modal
        function closeModal() {
            modal.style.display = "none";
        }

        // Tutup modal saat tombol X diklik
        closeButton.onclick = function() {
            closeModal();
        };

        // Tutup modal saat pengguna mengklik di luar konten modal
        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        };

        function togglePasswordVisibility(inputId) {
            var passwordInput = document.getElementById(inputId);
            var passwordIcon = passwordInput.nextElementSibling;

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                passwordIcon.classList.remove("bx-hide");
                passwordIcon.classList.add("bx-show-alt");
            } else {
                passwordInput.type = "password";
                passwordIcon.classList.remove("bx-show-alt");
                passwordIcon.classList.add("bx-hide");
            }
        }
    </script>

</body>

</html>