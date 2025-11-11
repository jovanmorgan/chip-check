<?php
include '../../keamanan/koneksi.php'; // koneksi ke database

// Dapatkan nama halaman dari URL saat ini tanpa ekstensi .php
$current_page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), ".php");

// Ambil parameter 'box' dari URL (jika ada)
$current_box = isset($_GET['box']) ? $_GET['box'] : '';

// Ambil data box_inner dari database dan urutkan dari yang terkecil ke terbesar
$query_box = mysqli_query($koneksi, "SELECT box_inner FROM box_inner ORDER BY LENGTH(box_inner), box_inner ASC");
?>

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">

        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link <?= ($current_page == 'dashboard') ? '' : 'collapsed'; ?>" href="dashboard">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Data Utama -->
        <li class="nav-heading">Data Utama</li>

        <!-- Dropdown Chip -->
        <li class="nav-item">
            <a class="nav-link <?= ($current_page == 'chip') ? '' : 'collapsed'; ?>" data-bs-target="#chip-nav"
                data-bs-toggle="collapse" href="#">
                <i class="bi bi-sim"></i><span>Chip</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="chip-nav" class="nav-content collapse <?= ($current_page == 'chip') ? 'show' : ''; ?>"
                data-bs-parent="#sidebar-nav">

                <!-- Semua Chip -->
                <li>
                    <a href="chip?search="
                        class="<?= ($current_page == 'chip' && $current_box == '') ? 'active' : ''; ?>">
                        <i class="bi bi-circle"></i><span>Semua Chip</span>
                    </a>
                </li>

                <!-- Daftar Box Inner -->
                <?php while ($row = mysqli_fetch_assoc($query_box)) :
                    $box_name = $row['box_inner'];
                    $active_class = ($current_page == 'chip' && $current_box == $box_name) ? 'active' : '';
                ?>
                    <li>
                        <a href="chip?box=<?= urlencode($box_name); ?>" class="<?= $active_class; ?>">
                            <i class="bi bi-circle"></i><span><?= htmlspecialchars($box_name); ?></span>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </li>

        <!-- check -->
        <li class="nav-item">
            <a class="nav-link <?= ($current_page == 'check') ? '' : 'collapsed'; ?>" href="check">
                <i class="bi bi-check-circle"></i>
                <span>Check</span>
            </a>
        </li>

        <!-- Bagian Akun -->
        <li class="nav-heading">Bagian Akun</li>

        <li class="nav-item">
            <a class="nav-link <?= ($current_page == 'profile') ? '' : 'collapsed'; ?>" href="profile">
                <i class="bi bi-person"></i>
                <span>Profile</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?= ($current_page == 'log_out') ? '' : 'collapsed'; ?>" href="log_out">
                <i class="bi bi-box-arrow-right"></i>
                <span>Log Out</span>
            </a>
        </li>

    </ul>
</aside>
<!-- End Sidebar -->