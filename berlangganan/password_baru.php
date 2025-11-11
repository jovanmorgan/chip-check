<!DOCTYPE html>
<html lang="en">

<?php include 'fitur/head_lp.php'; ?>

<body translate="no">
    <main>
        <div class="container">
            <section
                class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
                            <div class="d-flex justify-content-center py-4">
                                <a href="index.html" class="logo d-flex align-items-center w-auto">
                                    <!-- Tambahkan logo di sini jika diperlukan -->
                                </a>
                            </div>
                            <!-- End Logo -->

                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="pt-4 pb-2">
                                        <h5 class="card-title text-center pb-0 fs-4">Ganti Password</h5>
                                        <p class="text-center small">
                                            Halaman ini berguna untuk mengganti password
                                        </p>
                                    </div>

                                    <form id="ganti_password" action="../keamanan/proses_ganti_password.php"
                                        class="row g-3 needs-validation" novalidate>
                                        <div class="col-12">
                                            <label for="nomor_hp" class="form-label">Nomor Telepon</label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text" id="inputGroupPrepend">
                                                    <i class="bx bxs-phone"></i>
                                                </span>
                                                <input type="number" name="nomor_hp" class="form-control" id="nomor_hp"
                                                    required />
                                                <div class="invalid-feedback">
                                                    Masukkan nomor telepon yang valid.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button class="btn orange w-100" type="submit">Ganti Password</button>
                                        </div>
                                        <div class="col-12">
                                            <p class="small mb-0">
                                                Kembali untuk <a href="login_privasi"> Login!</a>
                                            </p>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <div class="credits mt-3">
                                        Dibuat Oleh Jovandry Morgan Mere Guju
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <!-- End #main -->

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
    // Event listener untuk form ganti password
    document.getElementById("ganti_password").addEventListener("submit", function(event) {
        event.preventDefault();

        var formData = new FormData(this);
        var xhr = new XMLHttpRequest();
        xhr.open("POST", this.action, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    var response = xhr.responseText.trim();

                    if (response.startsWith("error_")) {
                        Swal.fire("Gagal", "Nomor telepon tidak terdaftar", "error");
                    } else if (response.startsWith("success")) {
                        Swal.fire("Sukses", "Password baru telah dikirim ke nomor WhatsApp Anda",
                            "success");
                    } else if (response.startsWith("error_nomor_hp")) {
                        Swal.fire("Error", "Password baru telah dikirim ke nomor WhatsApp Anda",
                            "success");
                    } else {
                        Swal.fire("Error", "Terjadi kesalahan saat mengganti password", "error");
                    }
                } else {
                    Swal.fire("Error", "Gagal melakukan request", "error");
                }
            }
        };
        xhr.onerror = function() {
            Swal.fire("Error", "Gagal melakukan request", "error");
        };
        xhr.send(formData);
    });
    </script>
</body>

</html>