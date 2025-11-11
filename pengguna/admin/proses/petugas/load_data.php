<div id="load_data">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body text-center">
                        <!-- 🔍 Form Pencarian -->
                        <form method="GET" action="">
                            <div class="input-group mt-3">
                                <input type="text" class="form-control" placeholder="Cari Data <?= $page_title ?>..."
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
    include '../../../../keamanan/koneksi.php';

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