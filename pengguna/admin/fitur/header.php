<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
        <a href="dashboard" class="logo d-flex align-items-center">
            <img src="../../assets_admin/img/kohinor/logo_kohinor.png" alt="Logo Kohinor" />
            <span class="d-none d-lg-block" style="margin-left: 5px; font-size: 26px; margin-top: 3px;">
                Admin Kohinoor
            </span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>

    <!-- 🔧 Tambahkan teks tengah untuk tampilan mobile -->
    <div class="mobile-title d-lg-none text-center fw-bold">
        Admin Kohinoor
    </div>

    <div class="search-bar">
        <form class="search-form d-flex align-items-center" method="POST" action="fitur/search.php">
            <input type="text" name="query" placeholder="Search" title="Enter search keyword" />
            <button type="submit" title="Search">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>

    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">
            <li class="nav-item d-block d-lg-none">
                <a class="nav-link nav-icon search-bar-toggle" href="#">
                    <i class="bi bi-search"></i>
                </a>
            </li>

            <li class="nav-item dropdown pe-3">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <?php
                    include '../../keamanan/koneksi.php';
                    if (isset($_SESSION['id_admin'])) {
                        $id_admin = $_SESSION['id_admin'];
                        $query = "SELECT * FROM admin WHERE id_admin = '$id_admin'";
                        $result = mysqli_query($koneksi, $query);
                        if ($result && mysqli_num_rows($result) > 0) {
                            $admin = mysqli_fetch_assoc($result);
                            if (!empty($admin['fp'])) {
                                echo '<img src="../../assets_admin/img/fp_pengguna/admin/' . $admin['fp'] . '" alt="Profile" class="rounded-circle" />';
                            } else {
                                echo '<img src="../../assets_admin/img/user.png" alt="Profile" class="rounded-circle" />';
                            }
                            echo '<span class="d-none d-md-block dropdown-toggle ps-2">' . $admin['nama_lengkap'] . '</span>';
                        }
                    }
                    mysqli_close($koneksi);
                    ?>
                </a>

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6><?php echo $admin['nama_lengkap'] ?? ''; ?></h6>
                        <span><?php echo $admin['username'] ?? ''; ?></span>
                    </li>

                    <li>
                        <hr class="dropdown-divider" />
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="profile">
                            <i class="bi bi-person"></i>
                            <span>My Profile</span>
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider" />
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="log_out">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Log Out</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <style>
        /* 🔧 Efek hover logo */
        .logo img {
            transform: scale(1.5);
            transition: transform 0.3s ease;
        }

        .logo img:hover {
            transform: scale(1.7);
        }

        /* 🔧 Teks tengah hanya muncul di mobile */
        .mobile-title {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-size: 18px;
            color: #333;
            letter-spacing: 0.5px;
        }

        /* Hilangkan di layar besar */
        @media (min-width: 992px) {
            .mobile-title {
                display: none !important;
            }
        }

        /* Pastikan header tetap rapi di mobile */
        @media (max-width: 991px) {
            header.header {
                justify-content: space-between;
                padding: 5px 10px;
            }
        }
    </style>
</header>
<!-- End Header -->