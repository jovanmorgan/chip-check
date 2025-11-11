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
                                <!-- Search Form -->
                                <form method="GET" action="">
                                    <div class="input-group mt-3">
                                        <input type="text" class="form-control" placeholder="Cari nama atau nomor HP..."
                                            name="search"
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
            // Ambil data dari database
            include '../../keamanan/koneksi.php';

            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;

            // Query untuk mendapatkan data sales dengan pencarian dan pagination
            $query = "SELECT * FROM sales WHERE nama_lengkap LIKE ? OR nomor_hp LIKE ? LIMIT ?, ?";
            $stmt = $koneksi->prepare($query);
            $search_param = '%' . $search . '%';
            $stmt->bind_param("ssii", $search_param, $search_param, $offset, $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            // Hitung total halaman
            $total_query = "SELECT COUNT(*) as total FROM sales WHERE nama_lengkap LIKE ? OR nomor_hp LIKE ?";
            $stmt_total = $koneksi->prepare($total_query);
            $stmt_total->bind_param("ss", $search_param, $search_param);
            $stmt_total->execute();
            $total_result = $stmt_total->get_result();
            $total_row = $total_result->fetch_assoc();
            $total_pages = ceil($total_row['total'] / $limit);
            ?>

            <!-- Tabel Data Sales -->
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body" style="overflow-x: hidden;">
                                <!-- Overflow-x diatur untuk menyembunyikan scrollbar -->
                                <div style="overflow-x: auto;">
                                    <?php if ($result->num_rows > 0): ?>
                                        <table class="table table-hover text-center mt-3">
                                            <thead>
                                                <tr>
                                                    <th>No</th> <!-- Kolom nomor urut -->
                                                    <th>ID Sales</th>
                                                    <th>Nama Lengkap</th>
                                                    <th>Nomor HP</th>
                                                    <th>Nomor Pengguna</th>
                                                    <th>Password</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $nomor = $offset + 1; // Mulai nomor urut dari $offset + 1
                                                while ($row = $result->fetch_assoc()) :
                                                ?>
                                                    <tr>
                                                        <td><?php echo $nomor++; ?></td> <!-- Menampilkan nomor urut -->
                                                        <td><?php echo htmlspecialchars($row['id_sales']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['nomor_hp']); ?></td>
                                                        <!-- HTML untuk tabel -->
                                                        <td>
                                                            <button class="btn btn-success btn-sm" data-bs-toggle="tooltip"
                                                                data-bs-placement="bottom"
                                                                title="Klik untuk menyalin nomor Pengguna"
                                                                onclick="copyToClipboard(this, '<?php echo htmlspecialchars($row['username']); ?>')">
                                                                <?php echo htmlspecialchars($row['username']); ?>
                                                            </button>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-success btn-sm" data-bs-toggle="tooltip"
                                                                data-bs-placement="bottom" title="Klik untuk menyalin password"
                                                                onclick="copyToClipboard(this, '<?php echo htmlspecialchars($row['password']); ?>')">
                                                                <?php echo htmlspecialchars($row['password']); ?>
                                                            </button>
                                                        </td>

                                                        <td>
                                                            <button class=" btn btn-warning btn-sm"
                                                                onclick="openEditModal('<?php echo $row['id_sales']; ?>','<?php echo $row['nama_lengkap']; ?>','<?php echo $row['nomor_hp']; ?>','<?php echo $row['username']; ?>','<?php echo $row['password']; ?>')">Edit</button>
                                                            <button class="btn btn-danger btn-sm"
                                                                onclick="hapus('<?php echo $row['id_sales']; ?>')">Hapus</button>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <p class="text-center mt-4">Data tidak ditemukan 😖.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Pagination Section -->
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <!-- Pagination with icons -->
                                <nav aria-label="Page navigation example" style="margin-top: 2.2rem;">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php if ($page <= 1) {
                                                                    echo 'disabled';
                                                                } ?>">
                                            <a class="page-link" href="<?php if ($page > 1) {
                                                                            echo "?page=" . ($page - 1) . "&search=" . $search;
                                                                        } ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                                            <li class="page-item <?php if ($i == $page) {
                                                                        echo 'active';
                                                                    } ?>">
                                                <a class="page-link"
                                                    href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php } ?>
                                        <li class="page-item <?php if ($page >= $total_pages) {
                                                                    echo 'disabled';
                                                                } ?>">
                                            <a class="page-link" href="<?php if ($page < $total_pages) {
                                                                            echo "?page=" . ($page + 1) . "&search=" . $search;
                                                                        } ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                                <!-- End Pagination with icons -->
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Modal Tambah Data -->
        <div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tambahDataModalLabel">Tambah Sales</h5>
                        <button type="button" class="btn-close" id="closeTambahModal" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="tambahForm" method="POST" action="proses/sales/tambah.php"
                            enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="nomor_hp" class="form-label">Nomor HP</label>
                                <input type="number" id="nomor_hp" name="nomor_hp" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Nomor Pengguna</label>
                                <input type="text" id="username" name="username" class="form-control"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="text" id="passtword" name="password" class="form-control" required>
                            </div>

                            <!-- Wrapper for the submit button to align it to the right -->
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit Data -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDataModalLabel">Edit Sales</h5>
                        <button type="button" class="btn-close" id="closeEditModal" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editForm" method="POST" action="proses/sales/edit.php" enctype="multipart/form-data">
                            <input type="hidden" id="id_sales" name="id_sales">

                            <div class="mb-3">
                                <label for="edit-nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" id="edit-nama_lengkap" name="nama_lengkap" class="form-control"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="edit-nomor_hp" class="form-label">Nomor HP</label>
                                <input type="number" id="edit-nomor_hp" name="nomor_hp" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="edit-username" class="form-label">Nomor Pengguna</label>
                                <input type="text" id="edit-username" name="username" class="form-control"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="edit-password" class="form-label">Password</label>
                                <input type="text" id="edit-password" name="password" class="form-control" required>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('tambahForm').addEventListener('submit', function(event) {
                    event.preventDefault(); // Menghentikan aksi default form submit

                    var form = this;
                    var formData = new FormData(form);

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'proses/sales/tambah.php', true);
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            var response = xhr.responseText.trim();
                            console.log(response); // Debugging

                            // Menangani setiap respons dari PHP
                            switch (response) {
                                case 'success':
                                    form.reset();
                                    document.getElementById('closeTambahModal').click();
                                    loadTable(); // reload table data

                                    Swal.fire({
                                        title: "Berhasil!",
                                        text: "Data berhasil ditambahkan",
                                        icon: "success",
                                        timer: 1200, // 1,2 detik
                                        showConfirmButton: false, // Tidak menampilkan tombol OK
                                    });
                                    break;
                                case 'error_username_exists':
                                    Swal.fire({
                                        title: "Error",
                                        text: "Nomor Penggunah sudah terdaftar. Silakan gunakan nomor Penggunah lain.",
                                        icon: "error",
                                        timer: 2000, // 2 detik
                                        showConfirmButton: false,
                                    });
                                    break;
                                case 'error_password_length':
                                    Swal.fire({
                                        title: "Error",
                                        text: "Password harus terdiri dari minimal 8 karakter.",
                                        icon: "error",
                                        timer: 2000, // 2 detik
                                        showConfirmButton: false,
                                    });
                                    break;
                                case 'error_password_strength':
                                    Swal.fire({
                                        title: "Error",
                                        text: "Password harus mengandung huruf besar, huruf kecil, dan angka.",
                                        icon: "error",
                                        timer: 2000, // 2 detik
                                        showConfirmButton: false,
                                    });
                                    break;
                                case 'data_tidak_lengkap':
                                    Swal.fire({
                                        title: "Error",
                                        text: "Data yang Anda masukkan belum lengkap.",
                                        icon: "error",
                                        timer: 2000, // 2 detik
                                        showConfirmButton: false,
                                    });
                                    break;
                                default:
                                    Swal.fire({
                                        title: "Error",
                                        text: "Gagal menambahkan data. Silakan coba lagi.",
                                        icon: "error",
                                        timer: 2000, // 2 detik
                                        showConfirmButton: false,
                                    });
                                    break;
                            }
                        } else {
                            Swal.fire({
                                title: "Error",
                                text: "Terjadi kesalahan saat mengirim data.",
                                icon: "error",
                                timer: 2000, // 2 detik
                                showConfirmButton: false,
                            });
                        }
                    };
                    xhr.send(formData);
                });
            });

            function openEditModal(id_sales, nama_lengkap, nomor_hp, username, password) {
                let editModal = new bootstrap.Modal(document.getElementById('editModal'));
                document.getElementById('id_sales').value = id_sales;
                document.getElementById('edit-nama_lengkap').value = nama_lengkap; // Perbaiki di sini
                document.getElementById('edit-nomor_hp').value = nomor_hp;
                document.getElementById('edit-username').value = username;
                document.getElementById('edit-password').value = password;
                editModal.show();
            }

            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('editForm').addEventListener('submit', function(event) {
                    event.preventDefault(); // Menghentikan aksi default form submit

                    var form = this;
                    var formData = new FormData(form);

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'proses/sales/edit.php', true);
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            var response = xhr.responseText.trim();
                            console.log(response); // Debugging

                            // Menangani setiap respons dari PHP
                            if (response === 'success') {
                                form.reset();
                                document.getElementById('closeEditModal').click();
                                loadTable(); // Reload table data

                                Swal.fire({
                                    title: "Berhasil!",
                                    text: "Data berhasil diperbarui",
                                    icon: "success",
                                    timer: 1200, // 1,2 detik
                                    showConfirmButton: false, // Tidak menampilkan tombol OK
                                });
                            } else if (response === 'error_username_exists') {
                                Swal.fire({
                                    title: "Error",
                                    text: "Nomor Pengguna sudah terdaftar. Silakan gunakan nomor pengguna lain.",
                                    icon: "info",
                                    timer: 2000, // 2 detik
                                    showConfirmButton: false,
                                });
                            } else if (response === 'error_password_length') {
                                Swal.fire({
                                    title: "Error",
                                    text: "Password harus memiliki panjang minimal 8 karakter.",
                                    icon: "info",
                                    timer: 2000, // 2 detik
                                    showConfirmButton: false,
                                });
                            } else if (response === 'error_password_strength') {
                                Swal.fire({
                                    title: "Error",
                                    text: "Password harus mengandung huruf kapital, huruf kecil, dan angka.",
                                    icon: "info",
                                    timer: 2000, // 2 detik
                                    showConfirmButton: false,
                                });
                            } else if (response === 'data_tidak_lengkap') {
                                Swal.fire({
                                    title: "Error",
                                    text: "Data yang Anda masukkan belum lengkap.",
                                    icon: "info",
                                    timer: 2000, // 2 detik
                                    showConfirmButton: false,
                                });
                            } else {
                                Swal.fire({
                                    title: "Error",
                                    text: "Gagal memperbarui data.",
                                    icon: "error",
                                    timer: 2000, // 2 detik
                                    showConfirmButton: false,
                                });
                            }
                        } else {
                            Swal.fire({
                                title: "Error",
                                text: "Terjadi kesalahan saat mengirim data.",
                                icon: "error",
                                timer: 2000, // 2 detik
                                showConfirmButton: false,
                            });
                        }
                    };
                    xhr.send(formData);
                });
            });

            function hapus(id) {
                Swal.fire({
                    title: "Apakah Anda yakin?",
                    text: "Setelah dihapus, Anda tidak akan dapat memulihkan data ini!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, hapus!",
                    cancelButtonText: "Batal",
                    dangerMode: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika pengguna mengonfirmasi untuk menghapus
                        var xhr = new XMLHttpRequest();

                        xhr.open('POST', 'proses/sales/hapus.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onload = function() {

                            if (xhr.status === 200) {
                                var response = xhr.responseText.trim();
                                if (response === 'success') {
                                    loadTable();
                                    Swal.fire({
                                        title: 'Sukses!',
                                        text: 'Data berhasil dihapus.',
                                        icon: 'success',
                                        timer: 1200, // 1,2 detik
                                        showConfirmButton: false // Menghilangkan tombol OK
                                    }).then(() => {
                                        location.reload()
                                    })
                                } else if (response === 'error') {
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Gagal menghapus Data.',
                                        icon: 'error',
                                        timer: 2000, // 2 detik
                                        showConfirmButton: false // Menghilangkan tombol OK
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Terjadi kesalahan saat mengirim data.',
                                        icon: 'error',
                                        timer: 2000, // 2 detik
                                        showConfirmButton: false // Menghilangkan tombol OK
                                    });
                                }
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Terjadi kesalahan saat mengirim data.',
                                    icon: 'error',
                                    timer: 2000, // 2 detik
                                    showConfirmButton: false // Menghilangkan tombol OK
                                });
                            }
                        };
                        xhr.send("id=" + id);
                    } else {
                        // Jika pengguna membatalkan penghapusan
                        Swal.fire({
                            title: 'Penghapusan dibatalkan',
                            icon: 'info',
                            timer: 1500, // 1,5 detik
                            showConfirmButton: false // Menghilangkan tombol OK
                        });
                    }
                });
            }

            function copyToClipboard(button, text) {
                // Salin teks ke clipboard
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);

                // Perbarui tooltip dengan pesan sukses
                const tooltip = bootstrap.Tooltip.getInstance(
                    button); // Mendapatkan instance tooltip yang ada
                if (tooltip) {
                    tooltip.setContent({
                        '.tooltip-inner': 'Teks berhasil disalin!'
                    });
                }

                // Tampilkan tooltip secara manual
                $(button).tooltip('show');

                // Sembunyikan tooltip setelah beberapa detik
                setTimeout(() => {
                    $(button).tooltip('hide');
                }, 2000);
            }

            // Inisialisasi semua tooltip saat dokumen siap
            document.addEventListener('DOMContentLoaded', function() {
                var tooltips = document.querySelectorAll(
                    '[data-bs-toggle="tooltip"]');
                tooltips.forEach(function(tooltip) {
                    new bootstrap.Tooltip(tooltip);
                });
            });

            function loadTable() {
                // Get current page and search query from URL
                var currentPage = new URLSearchParams(window.location.search).get('page') || 1;
                var searchQuery = new URLSearchParams(window.location.search).get('search') || '';

                var xhrTable = new XMLHttpRequest();
                xhrTable.onreadystatechange = function() {
                    if (xhrTable.readyState == 4 && xhrTable.status == 200) {
                        document.getElementById('load_data').innerHTML = xhrTable.responseText;
                    }
                };

                // Send request with current page and search query
                xhrTable.open('GET', 'proses/sales/load_data.php?page=' + currentPage + '&search=' + encodeURIComponent(
                    searchQuery), true);
                xhrTable.send();
            }
        </script>

    </main>
    <!-- End #main -->

    <?php include 'fitur/bagian_akhir.php'; ?>

</body>

</html>