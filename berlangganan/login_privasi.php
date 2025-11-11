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
                                </a>
                            </div>
                            <!-- End Logo -->

                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="pt-4 pb-2">
                                        <h5 class="card-title text-center pb-0 fs-4">
                                            Login Privasi
                                        </h5>
                                        <p class="text-center small">
                                            Halaman ini hanya dikususkan untuk penggunah privasi
                                        </p>
                                    </div>

                                    <style>
                                    .is-invalid {
                                        background-color: #00000000;
                                        color: red;
                                    }
                                    </style>

                                    <form id="login" class="row g-3 needs-validation" novalidate>
                                        <div class="col-12">
                                            <label for="username" class="form-label">Username</label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text" id="inputGroupPrepend">
                                                    <i class="bx bxs-user"></i>
                                                </span>
                                                <input type="text" name="username" class="form-control" id="username"
                                                    required />
                                                <div class="invalid-feedback">
                                                    <!-- Pesan kesalahan akan dimasukkan ke sini -->
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label for="yourPassword" class="form-label">Password</label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text">
                                                    <i class="bx bxs-lock-alt"></i>
                                                </span>
                                                <input type="password" name="password" class="form-control"
                                                    id="yourPassword" required />
                                                <span class="input-group-text mata"
                                                    onclick="togglePasswordVisibility()">
                                                    <i id="passwordIcon" class="bx bx-hide"></i>
                                                </span>
                                                <div class="invalid-feedback" id="pesan-password">
                                                    <!-- Pesan kesalahan akan dimasukkan ke sini -->
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="remember"
                                                    value="true" id="rememberMe" />
                                                <label class="form-check-label" for="rememberMe">Remember me</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button class="btn orange w-100" type="submit">Login</button>
                                        </div>
                                        <div class="col-12">
                                            <p class="small mb-0">
                                                Lupah Password? <a href="password_baru">Buat password baru!</a>
                                            </p>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="credits mt-3">
                                        Dibuat Olleh Jovandry Morgan Mere Guju
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

    <?php include 'fitur/akhir_lp.php'; ?>
</body>

</html>