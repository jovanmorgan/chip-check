<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
        class="bi bi-arrow-up-short"></i></a>

<!-- Vendor JS Files -->
<script src="../assets_admin/vendor/apexcharts/apexcharts.min.js?v=<?= time(); ?>"></script>
<script src="../assets_admin/vendor/bootstrap/js/bootstrap.bundle.min.js?v=<?= time(); ?>"></script>
<script src="../assets_admin/vendor/chart.js/chart.umd.js?v=<?= time(); ?>"></script>
<script src="../assets_admin/vendor/echarts/echarts.min.js?v=<?= time(); ?>"></script>
<script src="../assets_admin/vendor/quill/quill.js?v=<?= time(); ?>"></script>
<script src="../assets_admin/vendor/simple-datatables/simple-datatables.js?v=<?= time(); ?>"></script>
<script src="../assets_admin/vendor/tinymce/tinymce.min.js?v=<?= time(); ?>"></script>
<script src="../assets_admin/vendor/php-email-form/validate.js?v=<?= time(); ?>"></script>

<!-- Template Main JS File -->
<script src="../assets_admin/js/main.js?v=<?= time(); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
document.getElementById("login").addEventListener("submit", function(event) {
    event.preventDefault();

    var formData = new FormData(this);
    var rememberMe = document.getElementById("rememberMe").checked;

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../keamanan/login.php", true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                var response = xhr.responseText;
                var responseArray = response.split(':');
                var nomorPenggunaInput = document.getElementById("username");
                var passwordInput = document.getElementById("yourPassword");
                var passwordFeedback = document.getElementById("pesan-password");

                // Reset previous error messages
                nomorPenggunaInput.classList.remove("is-invalid");
                passwordInput.classList.remove("is-invalid");
                nomorPenggunaInput.nextElementSibling.textContent = "";
                passwordFeedback.textContent = ""; // Reset pesan kesalahan

                if (responseArray[0].trim() === "success") {
                    Swal.fire({
                        icon: 'success',
                        title: 'Login berhasil!',
                        text: 'Selamat datang ' + responseArray[1],
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    }).then((result) => {
                        window.location.href = responseArray[3].trim();
                    });

                    if (rememberMe) {
                        var username = formData.get('username');
                        var password = formData.get('password');
                        document.cookie = "username=" + encodeURIComponent(username) +
                            "; path=/";
                        document.cookie = "password=" + encodeURIComponent(password) + "; path=/";
                    }
                } else {
                    let errorMessage = "Terjadi kesalahan saat proses login";

                    switch (responseArray[0].trim()) {
                        case "error_password":
                            errorMessage = "Password yang dimasukkan salah";
                            passwordInput.classList.add("is-invalid");
                            passwordFeedback.textContent = errorMessage;
                            break;
                        case "error_username":
                            errorMessage = "Username tidak ditemukan";
                            nomorPenggunaInput.classList.add("is-invalid");
                            nomorPenggunaInput.nextElementSibling.textContent = errorMessage;
                            break;
                        case "username_tidak_ada":
                            errorMessage = "Username belum diisi";
                            nomorPenggunaInput.classList.add("is-invalid");
                            nomorPenggunaInput.nextElementSibling.textContent = errorMessage;
                            break;
                        case "password_tidak_ada":
                            errorMessage = "Password belum diisi";
                            passwordInput.classList.add("is-invalid");
                            passwordFeedback.textContent = errorMessage;
                            break;
                        case "tidak_ada_data":
                            errorMessage = "Username dan Password belum diisi";
                            nomorPenggunaInput.classList.add("is-invalid");
                            passwordInput.classList.add("is-invalid");
                            nomorPenggunaInput.nextElementSibling.textContent = errorMessage;
                            passwordFeedback.textContent = errorMessage;
                            break;
                    }
                    Swal.fire("Error", errorMessage, "error");
                }
            } else {
                Swal.fire("Error", "Gagal", "error");
            }
        }
    };
    xhr.onerror = function() {
        Swal.fire("Error", "Gagal melakukan request", "error");
    };
    xhr.send(formData);
});



function togglePasswordVisibility() {
    const passwordInput = document.getElementById("yourPassword");
    const passwordIcon = document.getElementById("passwordIcon");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        passwordIcon.classList.remove("bx-hide");
        passwordIcon.classList.add("bx-show");
    } else {
        passwordInput.type = "password";
        passwordIcon.classList.remove("bx-show");
        passwordIcon.classList.add("bx-hide");
    }
}
</script>