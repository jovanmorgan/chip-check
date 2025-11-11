<!-- penggunah -->
<?php include 'fitur/penggunah.php'; ?>

<!DOCTYPE html>
<html lang="en">

<!-- Head -->
<?php include 'fitur/head.php'; ?>

<body translate="no">

    <!-- Header -->
    <?php include 'fitur/header.php'; ?>

    <!-- sidebar -->
    <?php include 'fitur/sidebar.php'; ?>

    <main id="main" class="main">

        <!-- title -->
        <?php include 'fitur/title.php'; ?>

        <?php include 'fitur/papan_nama.php'; ?>

        <div id="load_data">
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <!-- 🔍 Form Pencarian -->
                                <form method="GET" action="">
                                    <div class="input-group mt-3">
                                        <input type="text" class="form-control"
                                            placeholder="Cari Data <?= $page_title ?>..." name="search"
                                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                        <button class="btn btn-outline-secondary" type="submit">Cari</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <?php
            include '../../keamanan/koneksi.php';

            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;

            // 🔹 Query untuk mendapatkan data petugas dengan pencarian + pagination
            $query = "SELECT * FROM petugas 
              WHERE nama_lengkap LIKE ? 
                 OR username LIKE ? 
                 OR lokasi_penempatan LIKE ? 
              LIMIT ?, ?";
            $stmt = $koneksi->prepare($query);
            $search_param = '%' . $search . '%';
            $stmt->bind_param("sssii", $search_param, $search_param, $search_param, $offset, $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            // 🔹 Hitung total data untuk pagination
            $total_query = "SELECT COUNT(*) as total FROM petugas 
                    WHERE nama_lengkap LIKE ? 
                       OR username LIKE ? 
                       OR lokasi_penempatan LIKE ?";
            $stmt_total = $koneksi->prepare($total_query);
            $stmt_total->bind_param("sss", $search_param, $search_param, $search_param);
            $stmt_total->execute();
            $total_result = $stmt_total->get_result();
            $total_row = $total_result->fetch_assoc();
            $total_pages = ceil($total_row['total'] / $limit);
            ?>

            <!-- 🧾 Tabel Data Petugas -->
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body" style="overflow-x: auto;">
                                <?php if ($result->num_rows > 0): ?>
                                    <table class="table table-hover text-center mt-3 align-middle">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>No</th>
                                                <th>ID Petugas</th>
                                                <th>Nama Lengkap</th>
                                                <th>Username</th>
                                                <th>Password</th>
                                                <th>Lokasi Penempatan</th>
                                                <th>Foto Profil</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $nomor = $offset + 1;
                                            while ($row = $result->fetch_assoc()) :
                                            ?>
                                                <tr>
                                                    <td><?php echo $nomor++; ?></td>
                                                    <td><?php echo htmlspecialchars($row['id_petugas']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                    <td>
                                                        <button class="btn btn-outline-success btn-sm"
                                                            title="Klik untuk menyalin password"
                                                            onclick="copyToClipboard(this, '<?php echo htmlspecialchars($row['password']); ?>')">
                                                            <?php echo htmlspecialchars($row['password']); ?>
                                                        </button>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['lokasi_penempatan']); ?></td>
                                                    <td>
                                                        <?php if (!empty($row['fp'])): ?>
                                                            <img src="../../assets_admin/img/fp_pengguna/petugas/<?php echo $row['fp']; ?>"
                                                                alt="Foto" width="45" height="45" class="rounded-circle border">
                                                        <?php else: ?>
                                                            <img src="../../assets_admin/img/user.png" alt="Foto Default" width="45"
                                                                height="45" class="rounded-circle border">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-warning btn-sm"
                                                            onclick="openEditModal('<?php echo $row['id_petugas']; ?>','<?php echo $row['nama_lengkap']; ?>','<?php echo $row['username']; ?>','<?php echo $row['password']; ?>','<?php echo $row['lokasi_penempatan']; ?>')">
                                                            Edit
                                                        </button>
                                                        <button class="btn btn-danger btn-sm"
                                                            onclick="hapus('<?php echo $row['id_petugas']; ?>')">
                                                            Hapus
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p class="text-center mt-4">Data petugas tidak ditemukan 😖.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 📄 Pagination -->
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <nav aria-label="Page navigation example" style="margin-top: 1.8rem;">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                                            <a class="page-link"
                                                href="<?php if ($page > 1) echo '?page=' . ($page - 1) . '&search=' . $search; ?>"
                                                aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>

                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                                <a class="page-link"
                                                    href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                                            <a class="page-link"
                                                href="<?php if ($page < $total_pages) echo '?page=' . ($page + 1) . '&search=' . $search; ?>"
                                                aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <!-- ======================== MODAL TAMBAH DATA PETUGAS ======================== -->
        <div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold" id="tambahDataModalLabel">Tambah Petugas</h5>
                        <button type="button" class="btn-close" id="closeTambahModal" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <form id="tambahForm" method="POST" action="proses/petugas/tambah.php"
                            enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control"
                                    placeholder="Masukkan nama lengkap" required>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" id="username" name="username" class="form-control"
                                    placeholder="Masukkan username" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="text" id="password" name="password" class="form-control"
                                    placeholder="Masukkan password" required>
                            </div>

                            <div class="mb-3">
                                <label for="lokasi_penempatan" class="form-label">Lokasi Penempatan</label>
                                <input type="text" id="lokasi_penempatan" name="lokasi_penempatan" class="form-control"
                                    placeholder="Masukkan lokasi penempatan" required>
                            </div>

                            <div class="mb-3 text-center">
                                <label class="form-label d-block">Foto Profil</label>
                                <div class="profile-container mx-auto">
                                    <div id="icon-fp-tambah" class="icon-placeholder">
                                        <i class="bi bi-image fs-1 text-secondary"></i>
                                    </div>
                                    <img id="preview-fp-tambah" class="profile-img d-none" alt="Foto Profil">
                                </div>
                                <input type="file" id="fp" name="fp" class="form-control mt-2" accept="image/*">
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary px-4">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================== MODAL EDIT DATA PETUGAS ======================== -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title fw-bold" id="editDataModalLabel">Edit Data Petugas</h5>
                        <button type="button" class="btn-close" id="closeEditModal" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <form id="editForm" method="POST" action="proses/petugas/edit.php"
                            enctype="multipart/form-data">
                            <input type="hidden" id="id_petugas" name="id_petugas">

                            <div class="mb-3">
                                <label for="edit-nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" id="edit-nama_lengkap" name="nama_lengkap" class="form-control"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="edit-username" class="form-label">Username</label>
                                <input type="text" id="edit-username" name="username" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="edit-password" class="form-label">Password</label>
                                <input type="text" id="edit-password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="edit-lokasi_penempatan" class="form-label">Lokasi Penempatan</label>
                                <input type="text" id="edit-lokasi_penempatan" name="lokasi_penempatan"
                                    class="form-control" required>
                            </div>

                            <div class="mb-3 text-center">
                                <label class="form-label d-block">Ganti Foto Profil</label>
                                <div class="profile-container mx-auto">
                                    <div id="icon-fp-edit" class="icon-placeholder">
                                        <i class="bi bi-image fs-1 text-secondary"></i>
                                    </div>
                                    <img id="preview-fp-edit" class="profile-img d-none" alt="Foto Profil">
                                </div>
                                <input type="file" id="edit-fp" name="fp" class="form-control mt-2" accept="image/*">
                                <small class="text-muted">Kosongkan jika tidak ingin mengganti foto.</small>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-warning text-white px-4">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================== STYLE UNTUK FOTO PROFIL & ICON ======================== -->
        <style>
            .profile-container {
                width: 120px;
                height: 120px;
                position: relative;
                border-radius: 50%;
                border: 3px dashed #6c63ff;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }

            .profile-img {
                width: 100%;
                height: 100%;
                border-radius: 50%;
                object-fit: cover;
            }

            .icon-placeholder {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                height: 100%;
            }

            .profile-container:hover {
                opacity: 0.8;
                cursor: pointer;
            }
        </style>

        <!-- ======================== SCRIPT JS UNTUK OPEN EDIT MODAL + PREVIEW CROP ======================== -->
        <script>
            function openEditModal(id_petugas, nama_lengkap, username, password, lokasi_penempatan, fp) {
                document.getElementById('id_petugas').value = id_petugas;
                document.getElementById('edit-nama_lengkap').value = nama_lengkap;
                document.getElementById('edit-username').value = username;
                document.getElementById('edit-password').value = password;
                document.getElementById('edit-lokasi_penempatan').value = lokasi_penempatan;

                const imgPreview = document.getElementById('preview-fp-edit');
                const iconPlaceholder = document.getElementById('icon-fp-edit');

                if (fp && fp !== '') {
                    imgPreview.src = `../../assets_admin/img/fp_pengguna/petugas/${fp}`;
                    imgPreview.classList.remove('d-none');
                    iconPlaceholder.classList.add('d-none');
                } else {
                    imgPreview.classList.add('d-none');
                    iconPlaceholder.classList.remove('d-none');
                }

                const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                editModal.show();
            }

            // Fungsi crop rasio 1:1
            function cropSquare(fileInput, previewElementId, iconElementId) {
                const file = fileInput.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function(event) {
                    const img = new Image();
                    img.onload = function() {
                        const size = Math.min(img.width, img.height);
                        const x = (img.width - size) / 2;
                        const y = (img.height - size) / 2;

                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        canvas.width = 200;
                        canvas.height = 200;

                        ctx.drawImage(img, x, y, size, size, 0, 0, 200, 200);
                        document.getElementById(previewElementId).src = canvas.toDataURL('image/png');

                        // Tampilkan preview, sembunyikan icon
                        document.getElementById(previewElementId).classList.remove('d-none');
                        document.getElementById(iconElementId).classList.add('d-none');
                    };
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }

            // Event listener untuk tambah & edit
            document.getElementById('fp').addEventListener('change', function() {
                cropSquare(this, 'preview-fp-tambah', 'icon-fp-tambah');
            });

            document.getElementById('edit-fp').addEventListener('change', function() {
                cropSquare(this, 'preview-fp-edit', 'icon-fp-edit');
            });
        </script>


    </main>
    <!-- End #main -->
    <?php include 'fitur/js.php'; ?>

    <?php include 'fitur/bagian_akhir.php'; ?>

</body>

</html>