<?php include 'nama_halaman.php'; ?>
<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title" style="font-size: 30px;">Halaman <?= $page_title ?></h5>
                    <?php if ($page_title === "Kontak" || $page_title === "Profile" || $page_title === "Check") {
                    ?>
                        <p>
                            Silakan melihat halaman <?= $page_title ?>! anda bisa mengirim pesan dan menghapus
                            data
                            pada
                            halaman
                            ini 😊.
                        </p>
                    <?php
                    } else if (
                        $page_title === "Profile" || $page_title === "Check"
                    ) {
                    ?>
                        <p>
                            Silakan melihat halaman <?= $page_title ?> 😊!
                        </p>
                    <?php
                    } else {
                    ?>
                        <p>
                            Silakan melihat halaman <?= $page_title ?>! anda bisa menambah, mengedit, dan menghapus data
                            pada
                            halaman
                            ini 😊.
                        </p>
                    <?php
                    }
                    ?>
                    <?php if ($page_title === "Roti") {
                    ?>
                        <a href="roti_tambah" type="button" class="btn btn-primary text-white" data-bs-toggle="tooltip"
                            data-bs-placement="bottom" title="Klik untuk menambah data">
                            Tambah Data
                        </a>
                    <?php
                    } else if ($page_title === "Chack Out") {
                    ?>
                        <a href="chack_out_tambah" type="button" class="btn btn-primary text-white" data-bs-toggle="tooltip"
                            data-bs-placement="bottom" title="Klik untuk menambah data">
                            Tambah Data
                        </a>
                    <?php
                    } else if ($page_title === "Detail Chack Out") {
                    ?>
                        <a href="chack_out" type="button" class="btn btn-secondary text-white" data-bs-toggle="tooltip"
                            data-bs-placement="bottom" title="Klik untuk kembali">
                            Kembali
                        </a>
                    <?php
                    } else if ($page_title === "Detail Belum Bayar") {
                    ?>
                        <a href="belum_bayar" type="button" class="btn btn-secondary text-white" data-bs-toggle="tooltip"
                            data-bs-placement="bottom" title="Klik untuk kembali">
                            Kembali
                        </a>
                    <?php
                    } else if ($page_title === "Detail Dikemas") { ?>
                        <a href="dikemas" type="button" class="btn btn-secondary text-white" data-bs-toggle="tooltip"
                            data-bs-placement="bottom" title="Klik untuk kembali">
                            Kembali
                        </a>
                    <?php
                    } else if ($page_title === "Detail Dikirim") { ?>

                        <a href="dikirim" type="button" class="btn btn-secondary text-white" data-bs-toggle="tooltip"
                            data-bs-placement="bottom" title="Klik untuk kembali">
                            Kembali
                        </a>
                    <?php
                    } else if ($page_title === "Detail Diterima") { ?>

                        <a href="diterima" type="button" class="btn btn-secondary text-white" data-bs-toggle="tooltip"
                            data-bs-placement="bottom" title="Klik untuk kembali">
                            Kembali
                        </a>
                    <?php
                    } else if ($page_title === "Detail Dibatalkan") { ?>

                        <a href="dibatalkan" type="button" class="btn btn-secondary text-white" data-bs-toggle="tooltip"
                            data-bs-placement="bottom" title="Klik untuk kembali">
                            Kembali
                        </a>
                    <?php
                    } else if ($page_title === "Tambah Roti") {
                    ?>
                        <a href="roti" type="button" class="btn btn-secondary text-white" data-bs-toggle="tooltip"
                            data-bs-placement="bottom" title="Klik untuk kembali">
                            Kembali
                        </a>
                    <?php
                    } else if ($page_title === "Kontak" || $page_title === "Check Out" || $page_title === "Belum Bayar" || $page_title === "Dikemas" || $page_title === "Dikirim" || $page_title === "Diterima" || $page_title === "Dibatalkan" || $page_title === "Profile") {
                    ?>

                    <?php
                    } else {
                    ?>
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-primary text-white" data-bs-toggle="modal"
                            data-bs-target="#tambahDataModal">
                            Tambah Data
                        </button>

                    <?php
                    } ?>



                </div>
            </div>
        </div>
    </div>
</section>