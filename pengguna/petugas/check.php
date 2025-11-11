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
        <style>
        .table td,
        .table th {
            vertical-align: middle;
            text-align: center;
        }

        .highlight {
            transition: 0.3s ease;
        }

        .bg-purple {
            background-color: #6f42c1 !important;
            /* ungu */
        }

        .total-kuota-label {
            box-shadow: 0 0 6px rgba(0, 0, 0, 0.15);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .total-kuota-label:hover {
            transform: scale(1.1);
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.25);
        }

        .form-check-label {
            background-color: #f0f0f0;
            padding: 4px 10px;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .form-check-input:checked+.form-check-label {
            background-color: #00b09b;
            color: #fff;
        }
        </style>

        <?php
        include '../../keamanan/koneksi.php';

        // Ambil semua box_inner
        $boxQuery = "
SELECT DISTINCT box_inner, id_box_inner, color_icon, color_table
FROM box_inner 
ORDER BY CAST(SUBSTRING_INDEX(box_inner, ' ', -1) AS UNSIGNED) ASC
";
        $boxResult = $koneksi->query($boxQuery);
        ?>

        <script>
        const boxColors = {};
        <?php
            $boxResult->data_seek(0);
            while ($row = $boxResult->fetch_assoc()) { ?>
        boxColors["<?= $row['box_inner']; ?>"] = {
            color_table: "<?= $row['color_table']; ?>",
            color_icon: "<?= $row['color_icon']; ?>",
        };
        <?php } ?>
        </script>

        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header text-center fw-bold text-white py-2"
                style="background: linear-gradient(135deg, #00b09b, #96c93d, #00b09b); border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                <i class="bi bi-box-seam"></i> Input SN Chip
            </div>

            <div class="card-body">

                <div class="row mb-3">
                    <!-- Filter Box (Kiri) -->
                    <div class="col-md-6 mt-3">
                        <div class="card shadow-sm border-0 rounded-4">
                            <div class="card-header text-center fw-bold text-white py-2"
                                style="background: linear-gradient(135deg, #00b09b, #96c93d); border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                                <i class="bi bi-box-seam"></i> Filter Box
                            </div>
                            <div class="card-body mt-3" id="boxFilterContainer">
                                <!-- Checkbox dan tombol akan muncul di sini -->
                            </div>
                        </div>
                    </div>

                    <!-- Filter Total Kuota (Kanan) -->
                    <div class="col-md-6 mt-3">
                        <div class="card shadow-sm border-0 rounded-4">
                            <div class="card-header text-center fw-bold text-white py-2"
                                style="background: linear-gradient(135deg, #96c93d, #00b09b); border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                                <i class="bi bi-bar-chart"></i> Filter Total Kuota
                            </div>
                            <div class="card-body mt-3" id="totalKuotaFilterContainer">
                                <!-- Checkbox dan tombol akan muncul di sini -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total data dan tombol download -->
                <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap">

                    <!-- Total di kiri -->
                    <div>
                        <strong>Total Data: <span id="totalData">0</span></strong>
                    </div>

                    <!-- Bagian kanan: search + tombol -->
                    <div class="d-flex align-items-center gap-2 ms-auto">

                        <!-- Input pencarian keren -->
                        <div class="position-relative">
                            <input type="text" class="form-control form-control-sm pe-4" id="searchInput"
                                placeholder="Cari data..." style="width: 180px; min-height: 30px; font-size: 0.85rem;">
                            <i
                                class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-2 text-secondary cursor-pointer"></i>
                        </div>

                        <!-- Tombol download dan tambah -->
                        <button id="downloadExcel" class="btn btn-success btn-sm d-flex align-items-center gap-1">
                            <i class="bi bi-download"></i>
                            <span>Download Excel</span>
                        </button>

                        <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                            data-bs-toggle="modal" data-bs-target="#tambahDataModal">
                            <i class="bi bi-plus"></i>
                            <span>Tambah Data</span>
                        </button>
                    </div>
                </div>


                <div class="table-responsive">
                    <table class="table table-hover align-middle text-center custom-table mb-0" id="chipTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th style="width: 100px;">SN (Nomor Seri)</th>
                                <th>MSISDN</th>
                                <th>Box Inner</th>
                                <th>Tanggal Aktivasi</th>
                                <th class="col-kuota-nasional">Kuota Nasional</th>
                                <th class="col-kuota-lokal">Kuota Lokal</th>
                                <th class="col-kuota-lainnya">Kuota Lainnya</th>
                                <th>Total Kuota</th>
                                <th>Masa Tunggu</th>
                                <th>Masa Waktu</th>
                                <th>Masa Paket</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="input-row">
                                <td>1</td>
                                <td style="padding:0;">
                                    <input type="text" class="sn-input" placeholder="Masukkan SN..."
                                        style="border:none; width:100%; height:100%; text-align:center; font-weight:bold; outline:none;">
                                </td>
                                <td colspan="11" class="text-muted text-center">Masukkan SN untuk memuat data
                                    chip...
                                </td>
                            </tr>
                        </tbody>
                        <thead class="data-hidden">
                            <tr>
                                <th>No</th>
                                <th style="width: 100px;">SN (Nomor Seri)</th>
                                <th>MSISDN</th>
                                <th>Box Inner</th>
                                <th>Tanggal Aktivasi</th>
                                <th class="col-kuota-nasional">Kuota Nasional</th>
                                <th class="col-kuota-lokal">Kuota Lokal</th>
                                <th class="col-kuota-lainnya">Kuota Lainnya</th>
                                <th>Total Kuota</th>
                                <th>Masa Tunggu</th>
                                <th>Masa Waktu</th>
                                <th>Masa Paket</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="data-hidden">
                    <!-- Total data dan tombol download -->
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <strong>Total Data: <span id="totalData_bawa">0</span></strong>
                        <button id="downloadExcel_bawa" class="btn btn-success btn-sm">
                            <i class="bi bi-download"></i> Download Excel
                        </button>
                    </div>
                    <div class="row mb-3">
                        <!-- Filter Box (Kiri) -->
                        <div class="col-md-6 mt-3">
                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-header text-center fw-bold text-white py-2"
                                    style="background: linear-gradient(135deg, #00b09b, #96c93d); border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                                    <i class="bi bi-box-seam"></i> Filter Box
                                </div>
                                <div class="card-body mt-3" id="boxFilterContainer_bawa">
                                    <!-- Checkbox dan tombol akan muncul di sini -->
                                </div>
                            </div>
                        </div>

                        <!-- Filter Total Kuota (Kanan) -->
                        <div class="col-md-6 mt-3">
                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-header text-center fw-bold text-white py-2"
                                    style="background: linear-gradient(135deg, #96c93d, #00b09b); border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                                    <i class="bi bi-bar-chart"></i> Filter Total Kuota
                                </div>
                                <div class="card-body mt-3" id="totalKuotaFilterContainer_bawa">
                                    <!-- Checkbox dan tombol akan muncul di sini -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <style>
        .data-hidden {
            display: none;
        }
        </style>
        <!-- SheetJS -->
        <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
        $(document).ready(function() {
            let counter = 1;
            let enteredSNs = [];
            let addedBoxes = new Set();
            let addedTotalKuotas = new Set();
            let snDataCache = {}; // cache semua hasil API simcheck.php

            $('.sn-input').focus();

            function toggleHiddenSections() {
                const visibleRows = $('#chipTable tbody tr.data-row:visible').length;
                if (visibleRows >= 10) {
                    $('.data-hidden').css('display', 'block');
                } else {
                    $('.data-hidden').css('display', 'none');
                }
            }


            // 🔹 Fungsi untuk update total data
            function updateTotalData() {
                const visibleRows = $('#chipTable tbody tr.data-row:visible').length;
                $('#totalData').text(visibleRows);

                // Urutkan nomor otomatis
                let no = 1;
                $('#chipTable tbody tr.data-row:visible').each(function() {
                    $(this).find('td:first').text(no++);
                });

                // Jika data sudah 10 atau lebih, hapus semua class data-hidden
                if (visibleRows >= 10) {
                    $('.data-hidden').each(function() {
                        $(this).removeClass('data-hidden');
                    });
                }
            }

            function updateTotalData_bawa() {
                const visibleRows = $('#chipTable tbody tr.data-row:visible').length;
                $('#totalData_bawa').text(visibleRows);

                // Update nomor urut
                let no = 1;
                $('#chipTable tbody tr.data-row:visible').each(function() {
                    $(this).find('td:first').text(no++);
                });
            }

            function ensureInputRow() {
                if ($('#chipTable tbody tr.input-row').length === 0) {
                    $('#chipTable tbody').append(`
                <tr class="input-row">
                    <td>${++counter}</td>
                    <td style="padding:0;">
                        <input type="text" class="sn-input" placeholder="Masukkan SN..."
                            style="border:none; width:100%; height:100%; text-align:center; font-weight:bold; outline:none;">
                    </td>
                    <td colspan="10" class="text-muted text-center">Masukkan SN untuk memuat data chip...</td>
                </tr>`);
                }
                $('.sn-input:last').focus();
            }

            // SN input
            $(document).on('input', '.sn-input', function() {
                $(this).val($(this).val().replace(/^0+/, ''));
            });

            // Fungsi format tanggal ke "DD Month YYYY"
            function formatTanggalIndonesia(dateStr) {
                if (!dateStr) return '-';
                const bulan = [
                    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ];
                const dt = new Date(dateStr);
                if (isNaN(dt)) return dateStr; // fallback jika bukan tanggal valid
                const day = String(dt.getDate()).padStart(2, '0');
                const month = bulan[dt.getMonth()];
                const year = dt.getFullYear();
                return `${day} ${month} ${year}`;
            }


            $(document).on('keypress', '.sn-input', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    let sn = $(this).val().trim();
                    const row = $(this).closest('tr');
                    if (!sn) return;
                    sn = sn.replace(/^0+/, '');

                    if (enteredSNs.includes(sn)) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'SN Duplikat!',
                            text: `SN ${sn} sudah dimasukkan.`
                        });
                        $(this).val('');
                        return;
                    }
                    enteredSNs.push(sn);
                    row.data('sn', sn);

                    $.ajax({
                        url: '../../keamanan/api/get_chip_data.php',
                        type: 'GET',
                        data: {
                            sn
                        },
                        dataType: 'json',
                        beforeSend: function() {
                            row.find('td:eq(2)').attr('colspan', 10).html(
                                '<span class="text-primary">Memuat data...</span>');
                        },
                        success: function(res) {
                            if (res.status === 'ok') {
                                const c = res.data;
                                const box = c.box_inner;
                                const colors = boxColors[box] || {
                                    color_table: '#fff',
                                    color_icon: '#000'
                                };

                                // Tambahkan checkbox Box jika belum ada
                                if (!addedBoxes.has(box)) {
                                    addedBoxes.add(box);
                                    const colorIcon = colors.color_icon || '#000';
                                    $('#boxFilterContainer').append(`
                                                            <div class="form-check d-inline-block me-3 mb-2">
                                                                <input class="form-check-input box-filter" type="checkbox" value="${box}" checked id="box-${box}">
                                                                <label class="form-check-label fw-bold" for="box-${box}" style="cursor:pointer; background:${colorIcon}; color:#fff; padding:4px 10px; border-radius:0.5rem;">${box}</label>
                                                            </div>`);

                                    $('#boxFilterContainer_bawa').append(`
                                                            <div class="form-check d-inline-block me-3 mb-2">
                                                                <input class="form-check-input box-filter-bawa" type="checkbox" value="${box}" checked id="box-bawa-${box}">
                                                                <label class="form-check-label fw-bold" for="box-bawa-${box}" style="cursor:pointer; background:${colorIcon}; color:#fff; padding:4px 10px; border-radius:0.5rem;">${box}</label>
                                                            </div>`);
                                }

                                row.removeClass('input-row').addClass('data-row')
                                    .attr('data-box', box).html(`
                                                            <td style="background:${colors.color_table};">${row.index() + 1}</td>
                                                            <td style="background:${colors.color_table}; font-weight:bold; width: 100px;">${c.sn}</td>
                                                            <td style="background:${colors.color_table};">${c.msisdn}</td>
                                                            <td style="background:${colors.color_table};"><span class="badge" style="background:${colors.color_icon};">${box}</span></td>
                                                            <td style="background:${colors.color_table};" class="tanggal-act">Memuat...</td>
                                                            <td style="background:${colors.color_table};" class="col-kuota-nasional">Memuat...</td>
                                                            <td style="background:${colors.color_table};" class="col-kuota-lokal">Memuat...</td>
                                                            <td style="background:${colors.color_table};" class="col-kuota-lainnya">Memuat...</td>
                                                            <td style="background:${colors.color_table};" class="total-kuota">Memuat...</td>
                                                            <td style="background:${colors.color_table};" class="masa-tunggu">Memuat...</td>
                                                            <td style="background:${colors.color_table};" class="masa-waktu">Memuat...</td>
                                                            <td style="background:${colors.color_table};" class="masa-paket">Memuat...</td>
                                                            <td style="background:${colors.color_table};" class="status"><span class="badge bg-info">${c.status}</span></td>
                                                            `);

                                const apiUrl =
                                    `../../keamanan/api/simcheck.php?data=${encodeURIComponent(JSON.stringify([{ sn: c.sn, msisdn: c.msisdn }]))}`;
                                $.getJSON(apiUrl, function(item) {
                                    if (item && item[0]) {
                                        const d = item[0];

                                        // 💾 Simpan ke cache agar tidak perlu panggil API lagi saat download
                                        snDataCache[c.sn] = {
                                            sn: c.sn,
                                            msisdn: c.msisdn,
                                            tanggalAktivasi: d.tanggal_act ||
                                                '-',
                                            circleDates: d.circle_dates || [],
                                            masaPaket: d.masa_paket || '-',
                                            masaTunggu: d.masa_tunggu || '-',
                                            masaWaktu: d.masa_waktu || '-',
                                            kuotaNasional: d.kuota_nasional ||
                                                '0 GB',
                                            kuotaLokal: d.kuota_lokal || '0 GB',
                                            lainnya: d.lainnya || '0 GB',
                                            status: d.status || '-'
                                        };
                                        const total = ['kuota_nasional',
                                                'kuota_lokal', 'lainnya'
                                            ]
                                            .reduce((sum, k) => sum + parseFloat((d[
                                                k] || '0').replace(
                                                /[^\d.]/g, '')), 0);

                                        // Format circle_dates
                                        const circleDatesFormatted = (d
                                            .circle_dates || []).map(
                                            formatTanggalIndonesia);

                                        // Buat tombol tanggal_act dengan click event menampilkan semua circle
                                        const btnTanggal = $(
                                            `<button class="btn btn-sm btn-outline-primary fw-bold">${formatTanggalIndonesia(d.tanggal_act)}</button>`
                                        );
                                        btnTanggal.on('click', function() {
                                            let html = `
        <div style="max-height: 300px; overflow-y: auto;">
            <table class="table table-bordered table-striped text-center">
                <thead style="background-color:#007bff; color:white;">
                    <tr>
                        <th>No</th>
                        <th>Nama Circle</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
    `;

                                            // ✅ Loop untuk menambahkan semua tanggal circle
                                            circleDatesFormatted.forEach((
                                                date, i) => {
                                                html += `
            <tr>
                <td>${i + 1}</td>
                <td>Circle ${i + 1}</td>
                <td>${date}</td>
            </tr>
        `;
                                            });
                                            if (d.masa_paket) {
                                                html += `
                                                                                <tr style="background-color:#f8f9fa; font-weight:bold;">
                                                                                    <td>${circleDatesFormatted.length + 1}</td>
                                                                                    <td>Circle Selanjutnya</td>
                                                                                    <td>${d.masa_paket}</td>
                                                                                </tr>
                                                                            `;
                                            }

                                            html += `
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    `;

                                            Swal.fire({
                                                icon: 'info',
                                                title: '📅 Daftar Tanggal Circle',
                                                html,
                                                width: 600,
                                                confirmButtonText: 'Tutup',
                                                confirmButtonColor: '#007bff'
                                            });
                                        });
                                        row.find('.tanggal-act').html(btnTanggal);

                                        row.find('.col-kuota-nasional').text(d
                                            .kuota_nasional || '0 GB');
                                        row.find('.col-kuota-lokal').text(d
                                            .kuota_lokal || '0 GB');
                                        row.find('.col-kuota-lainnya').text(d
                                            .lainnya || '0 GB');

                                        // Warna total kuota
                                        let colorClass = '';
                                        if (total <= 0) colorClass =
                                            'bg-danger text-white';
                                        else if (total <= 10) colorClass =
                                            'bg-warning text-dark';
                                        else if (total <= 19) colorClass =
                                            'bg-purple text-white';
                                        else if (total <= 29) colorClass =
                                            'bg-info text-white';
                                        else if (total <= 39) colorClass =
                                            'bg-primary text-white';
                                        else colorClass = 'bg-success text-white';

                                        row.find('.total-kuota').html(
                                            `<span class="badge rounded-pill px-3 py-2 fw-bold ${colorClass} total-kuota-label" style="font-size: 0.85rem;">${total} GB</span>`
                                        );
                                        row.attr('data-total-kuota', total);

                                        // Tambahkan checkbox saat memuat Total Kuota
                                        if (!addedTotalKuotas.has(total)) {
                                            addedTotalKuotas.add(total);
                                            let kuotaColor = '';
                                            switch (true) {
                                                case total <= 0:
                                                    kuotaColor = '#dc3545';
                                                    break; // bg-danger
                                                case total <= 10:
                                                    kuotaColor = '#ffc107';
                                                    break; // bg-warning
                                                case total <= 19:
                                                    kuotaColor = '#6f42c1';
                                                    break; // bg-purple
                                                case total <= 29:
                                                    kuotaColor = '#17a2b8';
                                                    break; // bg-info
                                                case total <= 39:
                                                    kuotaColor = '#0d6efd';
                                                    break; // bg-primary
                                                default:
                                                    kuotaColor =
                                                        '#198754'; // bg-success
                                            }

                                            $('#totalKuotaFilterContainer').append(`
                                            <div class="form-check d-inline-block me-3 mb-2">
                                                <input class="form-check-input total-kuota-filter" type="checkbox" value="${total}" checked id="kuota-${total}">
                                                <label class="form-check-label fw-bold" for="kuota-${total}" style="cursor:pointer; background:${kuotaColor}; color:#fff; padding:4px 10px; border-radius:0.5rem;">${total} GB</label>
                                            </div>
                                        `);

                                            // totalKuotaFilterContainer_bawa
                                            $('#totalKuotaFilterContainer_bawa')
                                                .append(`
                                            <div class="form-check d-inline-block me-3 mb-2">
                                                <input class="form-check-input total-kuota-filter-bawa" type="checkbox" value="${total}" checked id="kuota-bawa-${total}">
                                                <label class="form-check-label fw-bold" for="kuota-bawa-${total}" style="cursor:pointer; background:${kuotaColor}; color:#fff; padding:4px 10px; border-radius:0.5rem;">${total} GB</label>
                                            </div>
                                        `);
                                        }


                                        row.find('.masa-tunggu').text(d
                                            .masa_tunggu || '-');
                                        row.find('.masa-waktu').text(d.masa_waktu ||
                                            '-');
                                        row.find('.masa-paket').text(d.masa_paket ||
                                            '-');
                                        row.find('.status').html(
                                            `<span class="badge bg-info">${d.status || '-'}</span>`
                                        );

                                        updateTotalData();
                                        updateTotalData_bawa();
                                    }
                                });

                                ensureInputRow();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'SN Tidak Ditemukan',
                                    text: `Data ${sn} tidak ditemukan.`
                                });
                                enteredSNs = enteredSNs.filter(x => x !== sn);
                            }
                        }
                    });
                }
            });

            // ✅ Fungsi format tanggal ke format Excel-friendly (dd/mm/yyyy)
            function formatTanggalExcel(tanggal) {
                if (!tanggal || tanggal === '-' || tanggal === '') return '-';
                const d = new Date(tanggal);
                if (isNaN(d)) return tanggal; // Jika bukan format tanggal valid, kembalikan apa adanya

                const day = String(d.getDate()).padStart(2, '0');
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const year = d.getFullYear();

                return `${day}/${month}/${year}`;
            }

            // ✅ Tombol download Excel
            $('#downloadExcel, #downloadExcel_bawa').click(async function() {
                const wb = XLSX.utils.book_new();
                const rows = [];

                const trs = $('#chipTable tbody tr.data-row:visible');
                for (const tr of trs) {
                    const row = $(tr);
                    const rowData = [];
                    const sn = row.find('td:eq(1)').text().trim().replace(/\D/g, '');
                    const msisdn = row.find('td:eq(2)').text().trim();

                    // 💾 Ambil dari cache jika sudah pernah dimuat
                    let tanggalAktivasi = '-';
                    let circleDetails = '-';

                    if (snDataCache[sn]) {
                        const d = snDataCache[sn];
                        tanggalAktivasi = formatTanggalExcel(d.tanggalAktivasi);

                        const formattedCircles = (d.circleDates || []).map((date, i) =>
                            `(Circle ${i+1}: ${formatTanggalExcel(date)})`
                        ).join(', ');

                        if (d.masaPaket)
                            circleDetails = formattedCircles ?
                            `${formattedCircles}, (Circle Selanjutnya: ${d.masaPaket})` :
                            `(Circle Selanjutnya: ${d.masaPaket})`;
                    } else {
                        // fallback jika belum ada datanya
                        tanggalAktivasi = '-';
                        circleDetails = '-';
                    }

                    row.find('td').each(function(i, td) {
                        let text = $(td).find('button').length > 0 ?
                            $(td).find('button').text().trim() :
                            $(td).text().trim();

                        // Kolom SN → hanya angka
                        if (i === 1) {
                            text = text.replace(/\D/g, '');
                        }

                        // Kolom tanggal aktivasi (index ke-4) → ganti pakai dari API
                        if (i === 4) {
                            text = tanggalAktivasi;
                        }

                        rowData.push(text);
                    });

                    // ✅ Tambah kolom terakhir (Detail Circle)
                    rowData.push(circleDetails);

                    rows.push(rowData);
                }

                // ✅ Buat sheet Excel
                const ws = XLSX.utils.aoa_to_sheet([
                    ["No", "SN", "MSISDN", "Box Inner", "Tanggal Aktivasi",
                        "Kuota Nasional", "Kuota Lokal", "Kuota Lainnya", "Total Kuota",
                        "Masa Tunggu", "Masa Waktu", "Masa Paket", "Status", "Detail Circle"
                    ],
                    ...rows
                ]);

                // ✅ Format kolom SN agar tampil "#######"
                const range = XLSX.utils.decode_range(ws['!ref']);
                for (let R = 1; R <= range.e.r; ++R) {
                    const cellAddress = XLSX.utils.encode_cell({
                        r: R,
                        c: 1
                    });
                    const cell = ws[cellAddress];
                    if (cell && cell.v) {
                        cell.t = 's';
                        cell.z = '###########';
                    }
                }

                // ✅ Lebar kolom
                ws['!cols'] = [{
                        wch: 5
                    }, {
                        wch: 20
                    }, {
                        wch: 15
                    }, {
                        wch: 12
                    }, {
                        wch: 15
                    },
                    {
                        wch: 15
                    }, {
                        wch: 15
                    }, {
                        wch: 15
                    }, {
                        wch: 15
                    }, {
                        wch: 15
                    },
                    {
                        wch: 15
                    }, {
                        wch: 15
                    }, {
                        wch: 15
                    }, {
                        wch: 60
                    }
                ];

                // ✅ Styling header dan border
                for (let R = range.s.r; R <= range.e.r; ++R) {
                    for (let C = range.s.c; C <= range.e.c; ++C) {
                        const cellAddress = XLSX.utils.encode_cell({
                            r: R,
                            c: C
                        });
                        if (!ws[cellAddress]) continue;

                        // Header
                        if (R === 0) {
                            ws[cellAddress].s = {
                                font: {
                                    bold: true,
                                    color: {
                                        rgb: "FFFFFF"
                                    }
                                },
                                fill: {
                                    fgColor: {
                                        rgb: "00B050"
                                    }
                                },
                                alignment: {
                                    horizontal: "center",
                                    vertical: "center"
                                },
                                border: {
                                    top: {
                                        style: "thin",
                                        color: {
                                            rgb: "000000"
                                        }
                                    },
                                    bottom: {
                                        style: "thin",
                                        color: {
                                            rgb: "000000"
                                        }
                                    },
                                    left: {
                                        style: "thin",
                                        color: {
                                            rgb: "000000"
                                        }
                                    },
                                    right: {
                                        style: "thin",
                                        color: {
                                            rgb: "000000"
                                        }
                                    }
                                }
                            };
                        }

                        // Border tiap sel
                        ws[cellAddress].s = ws[cellAddress].s || {};
                        ws[cellAddress].s.border = {
                            top: {
                                style: "thin",
                                color: {
                                    rgb: "CCCCCC"
                                }
                            },
                            bottom: {
                                style: "thin",
                                color: {
                                    rgb: "CCCCCC"
                                }
                            },
                            left: {
                                style: "thin",
                                color: {
                                    rgb: "CCCCCC"
                                }
                            },
                            right: {
                                style: "thin",
                                color: {
                                    rgb: "CCCCCC"
                                }
                            }
                        };
                    }
                }

                // ✅ Simpan ke file Excel dengan nama dinamis
                XLSX.utils.book_append_sheet(wb, ws, "Data Chip");

                // 🔧 Buat nama file dinamis
                const now = new Date();
                const pad = n => n.toString().padStart(2, '0');
                const formattedDate =
                    `${pad(now.getDate())}-${pad(now.getMonth() + 1)}-${now.getFullYear()}`;
                const formattedTime =
                    `${pad(now.getHours())}-${pad(now.getMinutes())}-${pad(now.getSeconds())}`;
                const fileName = `LIST_DATA_CHIP_${formattedDate}_${formattedTime}.xlsx`;

                // 💾 Download file
                XLSX.writeFile(wb, fileName);

            });




            $(document).on('click', '.box-btn', function() {
                const box = $(this).data('box');
                const checkbox = $(`.box-filter[value='${box}']`);
                checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
            });
            $(document).on('click', '.box-btn-bawa', function() {
                const box = $(this).data('box');
                const checkbox = $(`.box-filter-bawa[value='${box}']`);
                checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
            });

            $(document).on('click', '.total-kuota-btn', function() {
                const total = $(this).data('total');
                const checkbox = $(`.total-kuota-filter[value='${total}']`);
                checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
            });
            $(document).on('click', '.total-kuota-btn-bawa', function() {
                const total = $(this).data('total');
                const checkbox = $(`.total-kuota-filter-bawa[value='${total}']`);
                checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
            });

            // Filter box
            $(document).on('change', '.box-filter', function() {
                const selectedBoxes = $('.box-filter:checked').map(function() {
                    return this.value;
                }).get();
                $('.data-row').each(function() {
                    $(this).toggle(selectedBoxes.includes($(this).data('box')));
                });
                updateTotalData();
                updateTotalData_bawa();
            });
            $(document).on('change', '.box-filter-bawa', function() {
                const selectedBoxes = $('.box-filter-bawa:checked').map(function() {
                    return this.value;
                }).get();
                $('.data-row').each(function() {
                    $(this).toggle(selectedBoxes.includes($(this).data('box')));
                });
                updateTotalData();
                updateTotalData_bawa();
            });

            // Filter total kuota
            $(document).on('change', '.total-kuota-filter', function() {
                const selectedTotals = $('.total-kuota-filter:checked').map(function() {
                    return this.value;
                }).get();

                $('.data-row').each(function() {
                    const total = $(this).data('total-kuota'); // ambil dari data attribute
                    $(this).toggle(selectedTotals.includes(String(total)));
                });

                updateTotalData();
                updateTotalData_bawa();
            });
            $(document).on('change', '.total-kuota-filter-bawa', function() {
                const selectedTotals = $('.total-kuota-filter-bawa:checked').map(function() {
                    return this.value;
                }).get();

                $('.data-row').each(function() {
                    const total = $(this).data('total-kuota'); // ambil dari data attribute
                    $(this).toggle(selectedTotals.includes(String(total)));
                });

                updateTotalData();
                updateTotalData_bawa();
            });

            // 🔍 Fitur Pencarian Global di Tabel
            $('#searchInput').on('keyup', function() {
                const value = $(this).val().toLowerCase().trim();

                // Loop setiap baris data (bukan baris input)
                $('#chipTable tbody tr.data-row').each(function() {
                    const rowText = $(this).text().toLowerCase();

                    // Jika teks pencarian cocok dengan isi baris, tampilkan; jika tidak, sembunyikan
                    $(this).toggle(rowText.indexOf(value) > -1);
                });

                // Perbarui total data yang terlihat setelah filter
                updateTotalData();
                updateTotalData_bawa();
            });


        });
        </script>

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
                                <small class="text-muted">
                                    Format kolom harus sesuai dengan template (SN, MSISDN, BOX INNER, IN, ALOKASI, Tgl
                                    Actv, STATUS AKTIVASI, dll)
                                </small>
                            </div>

                            <div class="text-end mb-3">
                                <button type="button" id="btnPreview" class="btn btn-success text-white">
                                    <i class="bi bi-eye"></i> Preview and Seve
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
            const progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
            const tambahDataModal = bootstrap.Modal.getOrCreateInstance(document.getElementById(
                'tambahDataModal'));
            const progressBar = document.getElementById('progressBar');
            const progressTitle = document.getElementById('progressTitle');
            const progressStatus = document.getElementById('progressStatus');

            function updateProgress(percent, status) {
                progressBar.style.width = percent + '%';
                progressBar.textContent = Math.floor(percent) + '%';
                if (status) progressStatus.textContent = status;
            }

            // === PREVIEW FILE EXCEL ===
            btnPreview.addEventListener('click', async function() {
                const fileInput = document.getElementById('data_report');
                if (fileInput.files.length === 0) {
                    Swal.fire("Peringatan", "Pilih file Excel terlebih dahulu!", "warning");
                    return;
                }

                const formData = new FormData();
                formData.append('data_report', fileInput.files[0]);

                progressTitle.textContent = "Membaca File Excel...";
                updateProgress(0, "Memproses file...");
                progressModal.show();

                try {
                    const res = await fetch('proses/chip/preview_excel.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    updateProgress(100, "Selesai memuat data.");
                    setTimeout(() => progressModal.hide(), 700);

                    const header = document.getElementById('headerPreview');
                    const body = document.getElementById('bodyPreview');
                    header.innerHTML = '';
                    body.innerHTML = '';

                    if (!Array.isArray(data) || data.length === 0) {
                        Swal.fire("Kosong", "Data Excel tidak ditemukan!", "info");
                        return;
                    }

                    // Buat header tabel
                    const headers = Object.keys(data[0]);
                    header.innerHTML = headers.map(h => `<th>${h}</th>`).join('');

                    // Isi tabel
                    body.innerHTML = data.map(row => {
                        return '<tr>' + headers.map(h => `<td>${row[h] ?? ''}</td>`).join(
                            '') + '</tr>';
                    }).join('');

                    Swal.fire("Berhasil", `Menampilkan ${data.length} data dari Excel.`, "success");
                    sessionStorage.setItem('excelData', JSON.stringify(data));

                    // === Proses semua SN langsung ke tabel utama ===
                    const snList = data.map(d => d.SN || d.sn).filter(sn => sn && sn.trim() !== "");
                    if (snList.length === 0) return;

                    Swal.fire({
                        title: 'Proses SN ke tabel utama?',
                        text: `${snList.length} data akan dikirim ke tabel chip.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, proses',
                        cancelButtonText: 'Batal'
                    }).then(async (result) => {
                        if (!result.isConfirmed) return;

                        // Tutup modal tambah data sebelum mulai proses
                        tambahDataModal.hide();

                        progressModal.show();
                        progressTitle.textContent = "Mengambil data SN...";
                        let processed = 0;

                        for (let sn of snList) {
                            updateProgress((processed / snList.length) * 100,
                                `Memproses SN: ${sn}`);

                            try {
                                const res = await fetch(
                                    `../../keamanan/api/get_chip_data.php?sn=${sn}`);
                                const json = await res.json();

                                if (json.status === 'ok') {
                                    // trigger input SN otomatis ke tabel utama
                                    const lastInput = $('.sn-input:last');
                                    lastInput.val(sn);
                                    lastInput.trigger($.Event("keypress", {
                                        which: 13
                                    }));
                                }
                            } catch (err) {
                                console.warn(`Gagal ambil data SN ${sn}`, err);
                            }

                            processed++;
                            await new Promise(r => setTimeout(r,
                                400)); // jeda biar server gak overload
                        }

                        updateProgress(100, "Semua SN telah diproses");
                        setTimeout(() => progressModal.hide(), 800);
                    });

                } catch (err) {
                    console.error(err);
                    progressModal.hide();
                    Swal.fire("Error", "Gagal membaca file Excel.", "error");
                }
            });
        });
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


        <?php include 'fitur/js.php'; ?>

    </main>
    <!-- End #main -->

    <?php include 'fitur/bagian_akhir.php'; ?>

</body>

</html>