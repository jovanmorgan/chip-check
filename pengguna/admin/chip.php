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


            <?php
            include '../../keamanan/koneksi.php';

            // 🔹 Ambil semua box_inner untuk tombol filter
            $boxQuery = "
SELECT DISTINCT box_inner, id_box_inner, color_icon 
FROM box_inner 
ORDER BY CAST(SUBSTRING_INDEX(box_inner, ' ', -1) AS UNSIGNED) ASC
";
            $boxResult = $koneksi->query($boxQuery);

            // 🔹 Ambil parameter box yang dipilih
            $selected_box = isset($_GET['box']) ? $_GET['box'] : '';
            $search = isset($_GET['search']) ? $_GET['search'] : '';

            // 🔹 Query data chip berdasarkan box_inner
            $query = "
SELECT chip.*, box_inner.* 
FROM chip 
JOIN box_inner ON chip.id_box_inner = box_inner.id_box_inner 
WHERE (chip.sn LIKE ? OR chip.msisdn LIKE ? OR box_inner.box_inner LIKE ?)
" . ($selected_box ? "AND box_inner.box_inner = ?" : "") . "
ORDER BY CAST(SUBSTRING_INDEX(box_inner.box_inner, ' ', -1) AS UNSIGNED) ASC, chip.sn ASC
";

            if ($selected_box) {
                $stmt = $koneksi->prepare($query);
                $param = "%{$search}%";
                $stmt->bind_param("ssss", $param, $param, $param, $selected_box);
            } else {
                $stmt = $koneksi->prepare($query);
                $param = "%{$search}%";
                $stmt->bind_param("sss", $param, $param, $param);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            ?>

            <section class="section">
                <div class="row">
                    <div class="col-lg-12">

                        <!-- 🧭 Card Wrapper untuk Filter Box Inner (Atas) -->
                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header text-center fw-bold text-white py-2"
                                style="background: linear-gradient(135deg, #00b09b, #96c93d); border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                                <i class="bi bi-box-seam"></i> Pilih Box Inner
                            </div>


                            <div class="card-body mt-3">
                                <!-- 🔘 Tombol Filter Box Inner (Atas) -->
                                <div id="filterTop" class="d-flex flex-wrap gap-2 mb-3 justify-content-center">
                                    <?php while ($box = $boxResult->fetch_assoc()):
                                        $active = ($selected_box === $box['box_inner']) ? 'active-box' : ''; ?>
                                    <button class="btn btn-sm fw-bold filter-box <?= $active ?>"
                                        style="background: <?= htmlspecialchars($box['color_icon']); ?>; color:#fff;"
                                        data-box="<?= htmlspecialchars($box['box_inner']); ?>">
                                        <?= htmlspecialchars($box['box_inner']); ?>
                                    </button>
                                    <?php endwhile; ?>
                                    <!-- Tombol Semua -->
                                    <button class="btn btn-sm btn-outline-secondary fw-bold"
                                        onclick="window.location.href='?search=<?= urlencode($search) ?>'">Semua</button>
                                </div>
                            </div>
                        </div>


                        <!-- 🌟 Ringkasan Kuota Chip (Atas) -->
                        <div id="summaryCardTop" class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header text-center fw-bold text-white py-2"
                                style="background: linear-gradient(135deg, #00b09b, #96c93d); border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                                <h6 class="fw-bold mb-0 text-white">
                                    <i class="bi bi-bar-chart-line me-1"></i> Ringkasan Kuota Chip (Atas)
                                </h6>
                            </div>
                            <div class="card-body text-center p-4">
                                <div id="summaryLoadingTop" class="text-center">
                                    <div class="progress w-75 mx-auto" style="height: 20px;">
                                        <div id="progressBarTop"
                                            class="progress-bar progress-bar-striped progress-bar-animated"
                                            style="width: 0%">0%</div>
                                    </div>
                                    <small class="text-muted mt-2 d-block">Memuat data kuota chip...</small>
                                </div>
                                <div id="kuotaSummaryTop"
                                    class="d-flex flex-wrap justify-content-center gap-3 mt-3 d-none"></div>
                            </div>
                        </div>
                        <section class="section">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card" style="border-radius: 20px;">
                                        <div class="card-body text-center">
                                            <!-- Search Form -->
                                            <form method="GET" action="">
                                                <div class="input-group mt-3">
                                                    <input type="text" class="form-control"
                                                        placeholder="Cari Data <?php echo $page_title ?>..."
                                                        name="search"
                                                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                                    <button class="btn btn-outline-secondary"
                                                        type="submit">Cari</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <!-- 📋 Tabel Data -->
                        <div class="card shadow-lg border-0 table-card">
                            <div class="card-body p-4 table-responsive">
                                <?php if ($result->num_rows > 0): ?>
                                <table class="table table-hover align-middle text-center custom-table mb-0"
                                    id="chipTable">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>SN</th>
                                            <th>MSISDN</th>
                                            <th>Box Inner</th>
                                            <th>Kuota Nasional</th>
                                            <th>Kuota Lainnya</th>
                                            <th>Total Kuota</th>
                                            <th>Masa Paket</th>
                                            <th>IN</th>
                                            <th>Alokasi</th>
                                            <th>Tanggal Aktivasi</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                            while ($row = $result->fetch_assoc()): ?>
                                        <tr data-sn="<?= htmlspecialchars($row['sn']); ?>"
                                            data-msisdn="<?= htmlspecialchars($row['msisdn']); ?>">
                                            <td style="background: <?= htmlspecialchars($row['color_table']); ?>;">
                                                <?= $no++; ?>
                                            </td>
                                            <td class="fw-bold"
                                                style="background: <?= htmlspecialchars($row['color_table']); ?>;">
                                                <?= htmlspecialchars($row['sn']); ?>
                                            </td>
                                            <td class="fw-bold"
                                                style="background: <?= htmlspecialchars($row['color_table']); ?>;">
                                                <?= htmlspecialchars($row['msisdn']); ?>
                                            </td>
                                            <td style="background: <?= htmlspecialchars($row['color_table']); ?>;">
                                                <span class="badge"
                                                    style="background: <?= htmlspecialchars($row['color_icon']); ?>;">
                                                    <?= htmlspecialchars($row['box_inner']); ?>
                                                </span>
                                            </td>
                                            <td class="kuota-nasional"
                                                style="background: <?= htmlspecialchars($row['color_table']); ?>;">
                                                Memuat...</td>
                                            <td class="kuota-lainnya"
                                                style="background: <?= htmlspecialchars($row['color_table']); ?>;">
                                                Memuat...</td>
                                            <td class="total-kuota"
                                                style="background: <?= htmlspecialchars($row['color_table']); ?>;">
                                                Memuat...</td>
                                            <td class="masa-paket"
                                                style="background: <?= htmlspecialchars($row['color_table']); ?>;">
                                                Memuat...</td>
                                            <td style="background: <?= htmlspecialchars($row['color_table']); ?>;">
                                                <?= htmlspecialchars($row['in']); ?>
                                            </td>
                                            <td style="background: <?= htmlspecialchars($row['color_table']); ?>;">
                                                <?= htmlspecialchars($row['alokasi']); ?>
                                            </td>
                                            <td style="background: <?= htmlspecialchars($row['color_table']); ?>;">
                                                <?= htmlspecialchars($row['tanggal_aktivasi']); ?>
                                            </td>
                                            <td style="background: <?= htmlspecialchars($row['color_table']); ?>;">
                                                <?php
                                                        $status_badge = match ($row['status']) {
                                                            'pending' => 'warning',
                                                            'aktivasi' => 'info',
                                                            'transaksi' => 'success',
                                                            'rusak' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                        ?>
                                                <span class="badge bg-<?= $status_badge; ?>">
                                                    <?= ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td style="background: <?= htmlspecialchars($row['color_table']); ?>;">
                                                <button class="btn btn-outline-info btn-sm btn-detail"
                                                    data-bs-toggle="modal" data-bs-target="#detailModal"
                                                    onclick="loadDetail(<?= $row['id_chip'] ?>)">
                                                    <i class="bi bi-info-circle"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <p class="text-center mt-4 text-muted">Tidak ada data ditemukan 😖</p>
                                <?php endif; ?>
                            </div>
                        </div>


                        <!-- 🧭 Card Wrapper untuk Filter Box Inner (Atas) -->
                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header text-center fw-bold text-white py-2"
                                style="background: linear-gradient(135deg, #00b09b, #96c93d); border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                                <i class="bi bi-box-seam"></i> Pilih Box Inner
                            </div>
                            <div class="card-body mt-1">
                                <!-- 🔘 Tombol Filter Box Inner (Bawah, hasil duplikat dinamis) -->
                                <div id="filterBottom" class="d-flex flex-wrap gap-2 mt-4 justify-content-center"></div>

                            </div>
                        </div>

                        <!-- 🌟 Ringkasan Kuota Chip (Bawah, duplikat dari atas) -->
                        <div id="summaryCardBottom" class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header text-center fw-bold text-white py-2"
                                style="background: linear-gradient(135deg, #00b09b, #96c93d); border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                                <h6 class="fw-bold mb-0 text-white">
                                    <i class="bi bi-bar-chart-line me-1"></i> Ringkasan Kuota Chip (Atas)
                                </h6>
                            </div>
                            <div class="card-body text-center p-4">
                                <div id="summaryLoadingBottom" class="text-center">
                                    <div class="progress w-75 mx-auto" style="height: 20px;">
                                        <div id="progressBarBottom"
                                            class="progress-bar progress-bar-striped progress-bar-animated"
                                            style="width: 0%">0%</div>
                                    </div>
                                    <small class="text-muted mt-2 d-block">Memuat data kuota chip...</small>
                                </div>
                                <div id="kuotaSummaryBottom"
                                    class="d-flex flex-wrap justify-content-center gap-3 mt-3 d-none"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
            <script>
            $(document).ready(function() {
                // 🔘 Tombol filter klik (untuk box)
                $(document).on('click', '.filter-box', function() {
                    const box = $(this).data('box');
                    window.location.href = '?box=' + encodeURIComponent(box);
                });

                // Duplikasikan tombol filter dari atas ke bawah
                $('#filterBottom').html($('#filterTop').html());

                // Ambil parameter total dari URL
                const urlParams = new URLSearchParams(window.location.search);
                const selectedTotal = urlParams.get('total');

                // 🔁 Ambil data API progresif
                const rows = $('table tbody tr');
                const totalRows = rows.length;
                let loadedCount = 0;
                const kuotaCount = {};
                const getNum = s => parseFloat((s || '0').replace(/[^\d.]/g, '')) || 0;

                rows.each(function(index, tr) {
                    const $row = $(tr);
                    const sn = $row.data('sn');
                    const msisdn = $row.data('msisdn');
                    const apiUrl =
                        `../../keamanan/api/simcheck.php?data=${encodeURIComponent(JSON.stringify([{ sn, msisdn }]))}`;

                    $.getJSON(apiUrl, function(res) {
                            const item = res[0];
                            if (!item) return;

                            const kuotaNasional = item.kuota_nasional || '0 GB';
                            const kuotaLainnya = item.lainnya || '0 GB';
                            const masaPaket = item.masa_paket || '-';
                            const total = getNum(item.kuota_nasional) + getNum(item.lainnya);
                            const totalKuota = total + ' GB';

                            $row.find('.kuota-nasional').html(`<strong>${kuotaNasional}</strong>`);
                            $row.find('.kuota-lainnya').html(`<strong>${kuotaLainnya}</strong>`);
                            $row.find('.total-kuota').html(
                                `<button class="btn btn-outline-primary btn-sm fw-bold px-3 py-1">${totalKuota}</button>`
                            );
                            $row.find('.masa-paket').text(masaPaket);

                            kuotaCount[totalKuota] = (kuotaCount[totalKuota] || 0) + 1;

                            // Highlight otomatis berdasarkan parameter URL
                            if (selectedTotal && totalKuota === selectedTotal) {
                                $row.addClass('highlight-row');
                            }
                        })
                        .fail(() => {
                            $row.find('.kuota-nasional, .kuota-lainnya, .total-kuota, .masa-paket')
                                .html('<span class="text-danger">Gagal API</span>');
                        })
                        .always(() => {
                            loadedCount++;
                            const percent = Math.round((loadedCount / totalRows) * 100);
                            $('#progressBarTop, #progressBarBottom').css({
                                width: percent + '%',
                                transition: 'width 0.3s ease'
                            }).text(percent + '%');

                            if (percent >= 100) {
                                setTimeout(() => renderSummary(), 500);
                            }
                        });
                });

                // 📊 Render summary card (Top & Bottom)
                function renderSummary() {
                    const colors = [
                        'linear-gradient(135deg,#00c6ff,#0072ff)',
                        'linear-gradient(135deg,#6c5ce7,#a29bfe)',
                        'linear-gradient(135deg,#00b894,#00cec9)',
                        'linear-gradient(135deg,#fdcb6e,#e17055)',
                        'linear-gradient(135deg,#ff7675,#d63031)'
                    ];

                    const summaryHtml = Object.keys(kuotaCount)
                        .sort((a, b) => parseFloat(a) - parseFloat(b))
                        .map((key, index) => {
                            const color = colors[index % colors.length];
                            return `
            <div class="mini-kuota-card d-flex align-items-center justify-content-between px-3 py-2 text-white rounded-4 shadow-sm"
                data-total="${key}"
                style="background:${color};min-width:180px;max-width:220px;cursor:pointer;transition:transform 0.3s, box-shadow 0.3s;">
                <div><i class="bi bi-sim-fill fs-4 me-2"></i></div>
                <div class="text-start flex-grow-1">
                    <div class="fw-bold fs-6">${key}</div>
                    <small>${kuotaCount[key]} chip</small>
                </div>
            </div>
        `;
                        }).join('');

                    $('#summaryLoadingTop, #summaryLoadingBottom').fadeOut(300, function() {
                        $('#kuotaSummaryTop, #kuotaSummaryBottom')
                            .removeClass('d-none')
                            .hide()
                            .html(summaryHtml)
                            .fadeIn(600);
                    });

                    // Klik card ringkasan kuota → update URL dengan total kuota
                    $(document).on('click', '.mini-kuota-card', function() {
                        const totalClicked = $(this).data('total');
                        const currentUrl = new URL(window.location.href);
                        currentUrl.searchParams.set('total', totalClicked);
                        window.location.href = currentUrl.toString();
                    });
                }

                // 🔧 CSS tambahan
                $('<style>').prop('type', 'text/css').html(`
        .highlight-row {
            background: rgba(0, 123, 255, 0.15) !important;
            transition: background 0.4s ease;
        }
        .mini-kuota-card:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        .mini-kuota-card[data-total="${selectedTotal}"] {
            transform: scale(1.08);
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
            opacity: 1 !important;
        }
    `).appendTo('head');
            });
            </script>



            <!-- 🧩 Modal Detail -->
            <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">Detail Data Chip</h5>
                            <button type="button" class="btn-close bg-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="detailContent">
                            <p class="text-center text-muted">Memuat data...</p>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            function loadDetail(id_chip) {
                const target = document.getElementById('detailContent');
                target.innerHTML = '<p class="text-center text-muted">Memuat data...</p>';

                fetch('proses/chip/get_chip_detail.php?id_chip=' + id_chip)
                    .then(res => res.text())
                    .then(html => target.innerHTML = html)
                    .catch(() => target.innerHTML = '<p class="text-danger text-center">Gagal memuat data.</p>');
            }
            </script>


            <style>
            .filter-box.active-box {
                position: relative;
                z-index: 2;
                color: #fff !important;
                border: 2px solid #ffe3a7ff !important;
                font-weight: 700;
                transform: scale(1.08);
                /* background: linear-gradient(135deg, #ff9966, #ff5e62) !important; */
                border: none;
                box-shadow:
                    0 0 12px rgba(255, 94, 98, 0.7),
                    0 0 25px rgba(255, 150, 102, 0.5),
                    inset 0 0 6px rgba(255, 255, 255, 0.3);
                transition: all 0.3s ease;
                /* animation: pulseGlow 1.8s infinite ease-in-out; */
                letter-spacing: 0.5px;
                /* border-radius: 10px; */
            }

            @keyframes pulseGlow {
                0% {
                    box-shadow:
                        0 0 8px rgba(255, 94, 98, 0.6),
                        0 0 18px rgba(255, 150, 102, 0.4),
                        inset 0 0 4px rgba(255, 255, 255, 0.3);
                    transform: scale(1.08);
                }

                50% {
                    box-shadow:
                        0 0 18px rgba(255, 94, 98, 0.9),
                        0 0 35px rgba(255, 150, 102, 0.6),
                        inset 0 0 8px rgba(255, 255, 255, 0.4);
                    transform: scale(1.10);
                }

                100% {
                    box-shadow:
                        0 0 8px rgba(255, 94, 98, 0.6),
                        0 0 18px rgba(255, 150, 102, 0.4),
                        inset 0 0 4px rgba(255, 255, 255, 0.3);
                    transform: scale(1.08);
                }
            }

            /* Highlight row saat card diklik */
            .highlight-row {
                transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
                transform: scale(1.01);
                box-shadow: 0 0 15px #007bff;
                z-index: 1;
            }

            /* Ganti background semua td saat row di highlight */
            .highlight-row td {
                background-color: #ffffff !important;
            }

            /* Jika ada badge di td, tetap tampilkan dengan warna asli */
            .highlight-row td .badge {
                color: #fff;
            }

            .mini-kuota-card {
                cursor: pointer;
                transition: 0.3s ease;
            }

            .mini-kuota-card:hover {
                transform: scale(1.05);
                box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
            }

            /* ✨ Tampilan ringkasan mini card yang profesional */
            #summaryCard {
                backdrop-filter: blur(6px);
                background: rgba(255, 255, 255, 0.95);
            }

            .mini-kuota-card {
                border: none;
                font-size: 0.9rem;
                display: flex;
            }

            .mini-kuota-card .icon-wrapper {
                background: rgba(255, 255, 255, 0.2);
                border-radius: 10px;
                padding: 8px;
                width: 40px;
                height: 40px;
            }

            .mini-kuota-card .fs-6 {
                line-height: 1.2;
            }

            .mini-kuota-card small {
                font-size: 0.75rem;
            }

            @media (max-width: 576px) {
                .mini-kuota-card {
                    min-width: 150px;
                    max-width: 180px;
                }
            }

            /* 🌈 GRADIENT TITLE */
            .text-gradient {
                background: linear-gradient(90deg, #00bcd4, #007bff);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            /* 🎨 CARD STYLE */
            .table-card {
                background: #ffffff;
                border-radius: 18px;
                overflow: hidden;
                transition: 0.4s ease;
                border: 2px solid #e6f0ff;
            }

            .table-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 12px 30px rgba(0, 123, 255, 0.15);
            }

            /* 🧩 CUSTOM TABLE */
            .custom-table thead {
                background: linear-gradient(90deg, #00bcd4, #007bff);
                color: #fff;
                font-weight: 600;
            }

            .custom-table th {
                font-size: 0.9rem;
                letter-spacing: 0.5px;
                text-transform: uppercase;
                border: none;
            }

            .custom-table td {
                border: none;
                vertical-align: middle;
                transition: all 0.3s ease;
            }

            .custom-table tbody tr {
                background-color: var(--row-color, #f8f9fa);
                transition: 0.3s ease;
            }

            .custom-table tbody tr:hover {
                background-color: #e3f2fd !important;
                transform: scale(1.01);
                box-shadow: 0 0 10px rgba(0, 123, 255, 0.25);
            }

            /* 🏷️ BOX BADGE */
            .box-badge {
                color: #000;
                font-weight: 600;
                border-radius: 50px;
                padding: 5px 14px;
                box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.1);
            }

            /* 🟢 STATUS BADGE */
            .status-badge {
                padding: 5px 10px;
                border-radius: 30px;
                font-weight: 500;
                text-transform: capitalize;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
            }

            /* 🔘 DETAIL BUTTON */
            .btn-detail {
                transition: all 0.3s ease;
            }

            .btn-detail:hover {
                background-color: #00bcd4;
                color: #fff;
                box-shadow: 0 0 10px rgba(0, 188, 212, 0.6);
            }

            /* 🔄 PAGINATION */
            .custom-pagination .page-link {
                color: #007bff;
                border-radius: 10px;
                margin: 0 4px;
                border: 1px solid #cce5ff;
                transition: 0.3s;
            }

            .custom-pagination .page-link:hover {
                background-color: #007bff;
                color: #fff;
                transform: translateY(-2px);
            }

            .custom-pagination .page-item.active .page-link {
                background: linear-gradient(90deg, #00bcd4, #007bff);
                border: none;
                color: #fff;
                box-shadow: 0 0 10px rgba(0, 123, 255, 0.4);
            }
            </style>
        </div>

        <!-- Modal Tambah Data -->
        <div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="tambahDataModalLabel">Import Data Chip dari Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            id="closeTambahModal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formExcel" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="data_report" class="form-label">Pilih File Excel</label>
                                <input type="file" id="data_report" name="data_report" accept=".xlsx,.xls,.csv"
                                    class="form-control" required>
                                <small class="text-muted">Format kolom harus sesuai dengan template (SN, MSISDN, BOX
                                    INNER, IN, ALOKASI, Tgl Actv, STATUS AKTIVASI, dll)</small>
                            </div>

                            <div class="text-end mb-3">
                                <button type="button" id="btnPreview" class="btn btn-info">
                                    <i class="bi bi-eye"></i> Preview
                                </button>
                            </div>

                            <!-- Tempat preview Excel -->
                            <div class="table-responsive" style="max-height:400px; overflow:auto;">
                                <table class="table table-bordered table-striped" id="previewTable">
                                    <thead class="table-primary">
                                        <tr id="headerPreview"></tr>
                                    </thead>
                                    <tbody id="bodyPreview"></tbody>
                                </table>
                            </div>

                            <div class="text-end mt-3">
                                <button type="submit" id="btnSimpan" class="btn btn-success" style="display:none;">
                                    <i class="bi bi-upload"></i> Simpan ke Database
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Modal -->
        <div class="modal fade" id="progressModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content text-center p-3">
                    <h6 class="mb-2" id="progressTitle">Memproses Data...</h6>
                    <div class="progress" style="height: 20px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar" style="width: 0%">0%</div>
                    </div>
                    <p class="small mt-2" id="progressStatus">Menyiapkan...</p>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnPreview = document.getElementById('btnPreview');
            const btnSimpan = document.getElementById('btnSimpan');
            const progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
            const progressBar = document.getElementById('progressBar');
            const progressTitle = document.getElementById('progressTitle');
            const progressStatus = document.getElementById('progressStatus');

            function updateProgress(percent, status) {
                progressBar.style.width = percent + '%';
                progressBar.textContent = Math.floor(percent) + '%';
                if (status) progressStatus.textContent = status;
            }

            // === 1️⃣ PREVIEW FILE EXCEL ===
            btnPreview.addEventListener('click', async function() {
                let fileInput = document.getElementById('data_report');
                if (fileInput.files.length === 0) {
                    Swal.fire("Peringatan", "Pilih file Excel terlebih dahulu!", "warning");
                    return;
                }

                const formData = new FormData();
                formData.append('data_report', fileInput.files[0]);

                progressTitle.textContent = "Membaca File Excel...";
                updateProgress(0, "Mulai memproses...");
                progressModal.show();

                try {
                    const res = await fetch('proses/chip/preview_excel.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    updateProgress(100, "Selesai memuat data.");
                    setTimeout(() => progressModal.hide(), 700);

                    // Kosongkan tabel lama
                    document.getElementById('headerPreview').innerHTML = '';
                    document.getElementById('bodyPreview').innerHTML = '';

                    if (data.length === 0) {
                        Swal.fire("Kosong", "Data Excel tidak ditemukan!", "info");
                        return;
                    }

                    // Buat header
                    const headerRow = Object.keys(data[0]).map(h => `<th>${h}</th>`).join('');
                    document.getElementById('headerPreview').innerHTML = headerRow;

                    // Render data dengan batching biar gak lag
                    const chunkSize = 500;
                    let currentIndex = 0;

                    async function renderChunk() {
                        const chunk = data.slice(currentIndex, currentIndex + chunkSize);
                        const rows = chunk.map(row => '<tr>' + Object.values(row).map(v =>
                            `<td>${v}</td>`).join('') + '</tr>').join('');
                        document.getElementById('bodyPreview').insertAdjacentHTML('beforeend',
                            rows);

                        currentIndex += chunkSize;
                        const percent = (currentIndex / data.length) * 100;
                        updateProgress(percent,
                            `Menampilkan ${currentIndex}/${data.length} baris...`);

                        if (currentIndex < data.length) {
                            await new Promise(r => setTimeout(r, 30));
                            renderChunk();
                        } else {
                            progressModal.hide();
                            btnSimpan.style.display = 'inline-block';
                            sessionStorage.setItem('excelData', JSON.stringify(data));
                            Swal.fire("Berhasil", `Menampilkan ${data.length} data dari Excel.`,
                                "success");
                        }
                    }

                    progressTitle.textContent = "Menampilkan Data...";
                    renderChunk();

                } catch (err) {
                    console.error(err);
                    progressModal.hide();
                    Swal.fire("Error", "Gagal membaca file Excel.", "error");
                }
            });

            // === 2️⃣ SIMPAN KE DATABASE ===
            document.getElementById('formExcel').addEventListener('submit', function(event) {
                event.preventDefault();

                let jsonData = sessionStorage.getItem('excelData');
                if (!jsonData) {
                    Swal.fire("Peringatan", "Belum ada data yang dipreview.", "warning");
                    return;
                }

                progressTitle.textContent = "Mengupload ke Database...";
                updateProgress(0, "Mulai upload data...");
                progressModal.show();

                const formData = new FormData();
                formData.append('excelData', jsonData);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'proses/chip/import_excel.php', true);

                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        const percent = (e.loaded / e.total) * 100;
                        updateProgress(percent, `Mengupload ${Math.floor(percent)}%...`);
                    }
                };

                xhr.onload = function() {
                    if (xhr.status === 200 && xhr.responseText.trim() === 'success') {
                        updateProgress(100, "Selesai!");
                        setTimeout(() => progressModal.hide(), 500);

                        sessionStorage.removeItem('excelData');
                        document.getElementById('formExcel').reset();
                        btnSimpan.style.display = 'none';
                        document.getElementById('headerPreview').innerHTML = '';
                        document.getElementById('bodyPreview').innerHTML = '';
                        document.getElementById('closeTambahModal').click();

                        Swal.fire({
                            title: "Berhasil!",
                            text: "Data berhasil disimpan ke database.",
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        progressModal.hide();
                        Swal.fire("Error", "Gagal menyimpan data ke database.", "error");
                    }
                };

                xhr.onerror = function() {
                    progressModal.hide();
                    Swal.fire("Error", "Terjadi kesalahan jaringan saat upload.", "error");
                };

                xhr.send(formData);
            });
        });
        </script>



        <!-- Modal Edit Data -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDataModalLabel">Edit pimpinan</h5>
                        <button type="button" class="btn-close" id="closeEditModal" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editForm" method="POST" action="proses/pimpinan/edit.php"
                            enctype="multipart/form-data">
                            <input type="hidden" id="id_pimpinan" name="id_pimpinan">
                            <div class="mb-3">
                                <label for="edit-nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" id="edit-nama_lengkap" name="nama_lengkap" class="form-control"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-username" class="form-label">Nomor Pengguna</label>
                                <input type="text" id="edit-username" name="username" class="form-control" required>
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
        <?php include 'fitur/js.php'; ?>

    </main>
    <!-- End #main -->

    <?php include 'fitur/bagian_akhir.php'; ?>

</body>

</html>