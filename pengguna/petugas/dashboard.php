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
        <?php include 'fitur/nama_halaman.php'; ?>
        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center">
                            <h5 class="card-title mb-3" style="font-size: 30px; font-weight: 600;">
                                Selamat Datang di <div style="color: #FF8000;">Chip Check</div>
                            </h5>
                            <p style="font-size: 16px; line-height: 1.7;">
                                Terima kasih telah mengunjungi sistem <strong>Chip Check</strong>.
                                Aplikasi ini dirancang untuk mempermudah proses pengelolaan dan pemantauan penjualan
                                chip
                                secara efisien dan akurat.
                                Silakan jelajahi informasi dan data yang tersedia pada halaman
                                <strong><?= $page_title ?></strong> untuk membantu kegiatan operasional Anda.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
        include '../../keamanan/koneksi.php';

        // =====================
        // 1️⃣ Ambil data box_inner dengan jumlah chip per status
        // =====================
        $query_chip_box = "
SELECT 
    b.id_box_inner,
    b.box_inner,
    b.color_icon,
    b.color_table,
    COUNT(c.id_chip) AS total_chip,
    SUM(CASE WHEN c.status = 'aktivasi' THEN 1 ELSE 0 END) AS total_aktivasi,
    SUM(CASE WHEN c.status = 'transaksi' THEN 1 ELSE 0 END) AS total_transaksi,
    SUM(CASE WHEN c.status = 'pending' THEN 1 ELSE 0 END) AS total_pending,
    SUM(CASE WHEN c.status = 'rusak' THEN 1 ELSE 0 END) AS total_rusak
FROM box_inner b
LEFT JOIN chip c ON b.id_box_inner = c.id_box_inner
GROUP BY b.id_box_inner, b.box_inner, b.color_icon, b.color_table
ORDER BY CAST(SUBSTRING(b.box_inner, 5) AS UNSIGNED) ASC
";
        $result_chip_box = mysqli_query($koneksi, $query_chip_box);
        $chip_box_data = [];
        while ($row = mysqli_fetch_assoc($result_chip_box)) {
            $chip_box_data[] = $row;
        }
        mysqli_free_result($result_chip_box);

        // =====================
        // 2️⃣ Hitung total data per tabel/status
        // =====================
        $tables = [
            'chip' => ['label' => 'Chip', 'icon' => 'bi bi-sim', 'color' => '#007BFF', 'link' => 'chip.php'],
            'cicle' => ['label' => 'Cicle', 'icon' => 'bi bi-arrow-repeat', 'color' => '#DC3545', 'link' => 'cicle.php'],
            'box_inner' => ['label' => 'Box Inner', 'icon' => 'bi bi-box-seam', 'color' => '#6F42C1', 'link' => 'box_inner.php'],
            'aktivasi' => ['label' => 'Aktivasi', 'icon' => 'bi bi-lightning-charge', 'color' => '#28A745', 'link' => 'aktivasi.php'],
            'transaksi' => ['label' => 'Transaksi', 'icon' => 'bi bi-receipt-cutoff', 'color' => '#0D6EFD', 'link' => 'transaksi.php'],
            'pending' => ['label' => 'Pending', 'icon' => 'bi bi-hourglass-split', 'color' => '#FFC107', 'link' => 'pending.php'],
            'rusak' => ['label' => 'Rusak', 'icon' => 'bi bi-exclamation-triangle', 'color' => '#DC3545', 'link' => 'rusak.php'],
        ];

        $counts = [];
        foreach ($tables as $table => $details) {
            if (in_array($table, ['chip', 'cicle', 'box_inner'])) {
                $query = "SELECT COUNT(*) as count FROM $table";
            } else {
                $query = "SELECT COUNT(*) as count FROM chip WHERE status = '$table'";
            }
            $result = mysqli_query($koneksi, $query);
            $row = mysqli_fetch_assoc($result);
            $counts[$table] = $row['count'];
            mysqli_free_result($result);
        }
        mysqli_close($koneksi);
        ?>
        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center">
                            <h3 class="card-title text-center fw-bold text-success mb-3">
                                <i class="bi bi-speedometer2"></i>
                                Total Kuota Semua Box
                            </h3>
                            <div id="summary-buttons" class="d-flex flex-wrap justify-content-center gap-2">
                                <div class="text-muted">Memuat semua data kuota...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Card 2: List Box Inner -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h3 class="card-title text-center fw-bold text-success mb-3 mt-4">
                    <i class="bi bi-bar-chart-line"></i> Jumlah Kuota Berdasarkan Box Inner
                </h3>
                <div class="row">
                    <?php foreach ($chip_box_data as $box): ?>
                        <div class="col-xxl-4 col-md-6 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header d-flex align-items-center justify-content-between"
                                    style="background: <?= htmlspecialchars($box['color_icon']); ?>15;">
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($box['box_inner']); ?>
                                        </div>
                                        <small class="text-muted">Box Inner</small>
                                    </div>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                        style="width:44px;height:44px;background: <?= htmlspecialchars($box['color_icon']); ?>25;">
                                        <i class="bi bi-sd-card-fill"
                                            style="color: <?= htmlspecialchars($box['color_icon']); ?>;"></i>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="loader-wrapper mt-5 loader-<?= (int)$box['id_box_inner'] ?>">
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                                role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <div
                                            class="text-center mt-2 fw-semibold progress-text-<?= (int)$box['id_box_inner'] ?>">
                                            Memuat data chip...
                                        </div>
                                    </div>

                                    <div class="kuota-list result-<?= (int)$box['id_box_inner'] ?> d-none mt-3">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>


        <!-- 🔹 Modal Info Detail -->
        <div class="modal fade" id="infoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold">Detail Chip Kuota</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div id="info-content" class="table-responsive"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- jQuery & Bootstrap -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
        </script>

        <script>
            const parseGb = v => {
                if (!v) return 0;
                const m = String(v).match(/(\d+(\.\d+)?)/);
                return m ? parseFloat(m[1]) : 0;
            };
            const formatGb = n => Number.isInteger(n) ? n + " GB" : (Math.round(n * 10) / 10) + " GB";

            let globalKuota = {}; // total per kuota (0 GB → 340 chip, dst)
            let globalDetail = {}; // detail chip per kuota

            async function fetchBoxesSequentially(boxes) {
                for (const box of boxes) {
                    await processBox(box.id, box.name, box.color);
                }
            }

            async function processBox(boxId, boxName, color) {
                const loader = $(`.loader-${boxId}`);
                const bar = loader.find(".progress-bar");
                const text = $(`.progress-text-${boxId}`);
                const result = $(`.result-${boxId}`);

                try {
                    const chipRes = await $.getJSON(
                        `../../keamanan/api/get_chip_by_box.php?id_box=${boxId}`);
                    if (!chipRes || !chipRes.length) {
                        result.removeClass('d-none').html(
                            '<div class="text-muted small">Tidak ada chip.</div>');
                        loader.hide();
                        return;
                    }

                    const chips = chipRes;
                    const totals = [];
                    const totalChips = chips.length;
                    let done = 0;

                    for (const chip of chips) {
                        const payload = encodeURIComponent(JSON.stringify([{
                            sn: chip.sn,
                            msisdn: chip.msisdn
                        }]));
                        const url = `../../keamanan/api/simcheck.php?data=${payload}`;

                        try {
                            const apiRes = await $.getJSON(url);
                            if (Array.isArray(apiRes) && apiRes.length > 0) {
                                const d = apiRes[0];
                                const total = parseGb(d.kuota_nasional) + parseGb(d.kuota_lokal) +
                                    parseGb(d.lainnya);
                                const totalLabel = formatGb(total);
                                totals.push(totalLabel);

                                // Tambahkan ke global total
                                globalKuota[totalLabel] = (globalKuota[totalLabel] || 0) + 1;
                                if (!globalDetail[totalLabel]) globalDetail[totalLabel] = [];
                                globalDetail[totalLabel].push({
                                    box: boxName,
                                    number: d.number || '-',
                                    status: d.status || '-',
                                    serial_number: d.serial_number || '-',
                                    masa_tunggu: d.masa_tunggu || '-',
                                    masa_waktu: d.masa_waktu || '-',
                                    masa_paket: d.masa_paket || '-'
                                });
                            }
                        } catch {}

                        done++;
                        const percent = Math.round((done / totalChips) * 100);
                        bar.css("width", percent + "%");
                        text.text(`Memproses ${done}/${totalChips} chip (${percent}%)`);

                        await new Promise(r => setTimeout(r, 200)); // smooth animasi
                    }

                    bar.css("width", "100%");
                    setTimeout(() => loader.fadeOut(400), 400);

                    // 🔹 Tampilkan hasil box
                    const freq = {};
                    totals.forEach(t => freq[t] = (freq[t] || 0) + 1);
                    const entries = Object.entries(freq).sort((a, b) => parseGb(a[0]) - parseGb(b[0]));

                    let html = '<div class="d-flex flex-wrap justify-content-center gap-2">';
                    entries.forEach(([label, count]) => {
                        html += `
            <button class="kuota-btn mt-4 btn btn-outline fw-semibold"
                style="border-color:${color};color:${color};"
                data-box="${encodeURIComponent(boxName)}"
                data-total="${encodeURIComponent(label)}">
                ${label} - ${count} chip
            </button>`;
                    });
                    html += '</div>';
                    result.removeClass('d-none').html(html);

                    // 🔹 Update ringkasan global realtime
                    updateGlobalSummary();

                } catch {
                    loader.hide();
                    result.removeClass('d-none').html(
                        '<div class="text-danger small">Gagal memuat data.</div>');
                }
            }

            // 🔹 Update ringkasan global secara real-time
            function updateGlobalSummary() {
                const btnContainer = $("#summary-buttons");
                btnContainer.empty();

                const sorted = Object.entries(globalKuota)
                    .sort((a, b) => parseGb(a[0]) - parseGb(b[0]));

                sorted.forEach(([kuota, count]) => {
                    btnContainer.append(`
            <button class="btn btn-outline-primary fw-semibold" data-kuota="${encodeURIComponent(kuota)}">
                ${kuota} - ${count} chip
                <i class="bi bi-info-circle ms-1"></i>
            </button>
        `);
                });
            }

            $(async function() {
                const boxes = [
                    <?php foreach ($chip_box_data as $b): ?> {
                            id: <?= (int)$b['id_box_inner'] ?>,
                            name: "<?= htmlspecialchars($b['box_inner']) ?>",
                            color: "<?= htmlspecialchars($b['color_icon']) ?>"
                        },
                    <?php endforeach; ?>
                ];
                await fetchBoxesSequentially(boxes);
            });

            // 🔹 Klik tombol ringkasan global → tampil modal info detail
            $(document).on("click", "#summary-buttons button", function() {
                const kuota = decodeURIComponent($(this).data("kuota"));
                const details = globalDetail[kuota] || [];
                let html = `
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Nomor</th>
                    <th>Box Asal</th>
                    <th>Status</th>
                    <th>Serial Number</th>
                    <th>Masa Tunggu</th>
                    <th>Masa Waktu</th>
                    <th>Masa Paket</th>
                </tr>
            </thead>
            <tbody>
    `;
                details.forEach((d, i) => {
                    html += `<tr>
            <td>${i + 1}</td>
            <td>${d.number}</td>
            <td>${d.box}</td>
            <td>${d.status}</td>
            <td>${d.serial_number}</td>
            <td>${d.masa_tunggu}</td>
            <td>${d.masa_waktu}</td>
            <td>${d.masa_paket}</td>
        </tr>`;
                });
                html += `</tbody></table>`;
                $("#info-content").html(html);
                new bootstrap.Modal("#infoModal").show();
            });

            // 🔹 Klik tombol kuota pada masing-masing box → pindah ke halaman chip.php
            $(document).on('click', '.kuota-btn', function() {
                const box = $(this).data('box');
                const total = $(this).data('total');
                window.location.href = `chip.php?box=${box}&total=${total}`;
            });
        </script>

        <section class="section dashboard">
            <div class="row">

                <!-- === Bagian 1: Jumlah Chip per Box Inner === -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h3 class="card-title text-primary fw-bold mb-4 mt-4 text-center">
                                <i class="bi bi-box-seam"></i> Jumlah Chip Berdasarkan Box Inner
                            </h3>
                            <div class="row">
                                <?php if (!empty($chip_box_data)): ?>
                                    <?php foreach ($chip_box_data as $box): ?>
                                        <div class="col-xxl-4 col-md-4 mb-4">
                                            <div class="card info-card chip-card position-relative overflow-hidden"
                                                style="border-left: 6px solid <?= htmlspecialchars($box['color_table']); ?>;">
                                                <div class="card-body">
                                                    <h5 class="card-title mb-3">
                                                        <?= htmlspecialchars($box['box_inner']); ?>
                                                        <span class="text-muted">| Box Inner</span>
                                                    </h5>

                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"
                                                            style="background: <?= htmlspecialchars($box['color_icon']); ?>20;">
                                                            <i class="bi bi-box-seam"
                                                                style="color: <?= htmlspecialchars($box['color_icon']); ?>;"></i>
                                                        </div>
                                                        <div class="ps-3">
                                                            <h6 class="fw-bold mb-0">
                                                                <?= number_format($box['total_chip']); ?>
                                                            </h6>
                                                            <span class="text-muted small">Total Chip </span>
                                                        </div>
                                                    </div>

                                                    <div class="row text-center">
                                                        <div class="col-6">
                                                            <a href="aktivasi.php?id_box=<?= urlencode($box['id_box_inner']); ?>"
                                                                class="btn btn-outline-success w-100 py-2 rounded-3">
                                                                <div class="fw-bold small">
                                                                    <i class="bi bi-lightning-charge"></i>
                                                                    <?= number_format($box['total_aktivasi']); ?>
                                                                </div>
                                                                <small class="text-muted d-block">Aktivasi</small>
                                                            </a>
                                                        </div>
                                                        <div class="col-6">
                                                            <a href="transaksi.php?id_box=<?= urlencode($box['id_box_inner']); ?>"
                                                                class="btn btn-outline-primary w-100 py-2 rounded-3">
                                                                <div class="fw-bold small">
                                                                    <i class="bi bi-receipt-cutoff"></i>
                                                                    <?= number_format($box['total_transaksi']); ?>
                                                                </div>
                                                                <small class="text-muted d-block">Transaksi</small>
                                                            </a>
                                                        </div>
                                                    </div>

                                                    <div class="row text-center mt-3">
                                                        <div class="col-6">
                                                            <a href="pending.php?id_box=<?= urlencode($box['id_box_inner']); ?>"
                                                                class="btn btn-outline-warning w-100 py-2 rounded-3">
                                                                <div class="fw-bold small">
                                                                    <i class="bi bi-hourglass-split"></i>
                                                                    <?= number_format($box['total_pending']); ?>
                                                                </div>
                                                                <small class="text-muted d-block">Pending</small>
                                                            </a>
                                                        </div>
                                                        <div class="col-6">
                                                            <a href="rusak.php?id_box=<?= urlencode($box['id_box_inner']); ?>"
                                                                class="btn btn-outline-danger w-100 py-2 rounded-3">
                                                                <div class="fw-bold small">
                                                                    <i class="bi bi-exclamation-triangle"></i>
                                                                    <?= number_format($box['total_rusak']); ?>
                                                                </div>
                                                                <small class="text-muted d-block">Rusak</small>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="icon_bagian_luar">
                                                    <i class="<?= htmlspecialchars($box['color_icon']); ?>"
                                                        style="color: <?= htmlspecialchars($box['color_table']); ?>;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center">Belum ada data box inner.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- === Bagian 2: Jumlah Total Data (semua status) === -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h3 class="card-title text-success fw-bold mt-4 mb-4 text-center">
                                <i class="bi bi-bar-chart-line-fill"></i> Jumlah Total Data
                            </h3>
                            <div class="row">
                                <?php foreach ($tables as $table => $details): ?>
                                    <div class="col-xxl-3 col-md-4 mb-4">
                                        <a href="<?= htmlspecialchars($details['link']); ?>" class="text-decoration-none">
                                            <div class="card info-card clickable-card position-relative overflow-hidden"
                                                style="border-left: 6px solid <?= $details['color']; ?>;">
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <?= htmlspecialchars($details['label']); ?>
                                                        <span class="text-muted">| Chip Check</span>
                                                    </h5>
                                                    <div class="d-flex align-items-center">
                                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"
                                                            style="background: <?= $details['color']; ?>20;">
                                                            <i class="<?= $details['icon']; ?>"
                                                                style="color: <?= $details['color']; ?>;"></i>
                                                        </div>
                                                        <div class="ps-3">
                                                            <h6 class="fw-bold mb-0">
                                                                <?= number_format($counts[$table]); ?>
                                                            </h6>
                                                            <span class="text-muted small">Total
                                                                <?= strtolower($details['label']); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="icon_bagian_luar">
                                                    <i class="<?= $details['icon']; ?>"
                                                        style="color: <?= $details['color']; ?>;"></i>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>





        <style>
            .card-title {
                font-family: "Poppins", sans-serif;
                letter-spacing: 0.5px;
            }

            .card-icon {
                width: 55px;
                height: 55px;
                border-radius: 50%;
                font-size: 26px;
            }

            /* Saat tombol outline-success dihover */
            .btn-outline-success:hover,
            .btn-outline-success:hover * {
                color: #fff !important;
            }

            /* Saat tombol outline-warning dihover */
            .btn-outline-primary:hover,
            .btn-outline-primary:hover * {
                color: #fff !important;
            }

            /* Saat tombol outline-danger dihover */
            .btn-outline-danger:hover,
            .btn-outline-danger:hover * {
                color: #fff !important;
            }

            /* Saat tombol outline-warning dihover */
            .btn-outline-warning:hover,
            .btn-outline-warning:hover * {
                color: #fff !important;
            }

            /* Saat tombol outline-danger dihover */
            .btn-outline-dark:hover,
            .btn-outline-dark:hover * {
                color: #fff !important;
            }

            .info-card {
                border-radius: 15px;
                transition: all 0.3s ease;
                overflow: hidden;
                position: relative;
                background: #fff;
            }

            .info-card:hover {
                transform: translateY(-6px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            }

            .info-card .card-icon {
                transition: transform 0.3s ease;
            }

            .info-card:hover .card-icon {
                transform: scale(1.1) rotate(10deg);
            }

            .fw-bold {
                font-weight: 600 !important;
            }
        </style>

    </main>
    <!-- End #main -->

    <?php include 'fitur/bagian_akhir.php'; ?>

</body>

</html>