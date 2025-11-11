<?php include 'nama_halaman.php'; ?>

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <title><?= $page_title ?> - Admin Chip Check</title>
    <meta content="" name="description" />
    <meta content="" name="keywords" />

    <!-- Favicons -->
    <link href="../../assets_admin/img/kohinor/logo_kohinor.png" rel="icon" />
    <link href="../../assets_admin/img/apple-touch-icon.png" rel="apple-touch-icon" />

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet" />

    <!-- Vendor CSS Files -->
    <link href="../../assets_admin/vendor/bootstrap/css/bootstrap.min.css?v=<?= time(); ?>" rel="stylesheet" />
    <link href="../../assets_admin/vendor/bootstrap-icons/bootstrap-icons.css?v=<?= time(); ?>" rel="stylesheet" />
    <link href="../../assets_admin/vendor/boxicons/css/boxicons.min.css?v=<?= time(); ?>" rel="stylesheet" />
    <link href="../../assets_admin/vendor/quill/quill.snow.css?v=<?= time(); ?>" rel="stylesheet" />
    <link href="../../assets_admin/vendor/quill/quill.bubble.css?v=<?= time(); ?>" rel="stylesheet" />
    <link href="../../assets_admin/vendor/remixicon/remixicon.css?v=<?= time(); ?>" rel="stylesheet" />
    <link href="../../assets_admin/vendor/simple-datatables/style.css?v=<?= time(); ?>" rel="stylesheet" />

    <!-- Template Main CSS File -->
    <link href="../../assets_admin/css/style.css?v=<?= time(); ?>" rel="stylesheet" />

    <style>
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        background-color: #f8f9fa;
        padding: 12px;
        text-align: center;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
        /* Mencegah pemotongan teks dan membuat lebar kolom mengikuti teks */
    }

    .table tbody td {
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
        white-space: nowrap;
        /* Mencegah pemotongan teks dan membuat lebar kolom mengikuti teks */
    }

    .table tbody tr:hover {
        background-color: #f1f1f1;
        /* Warna latar belakang saat hover */
    }

    .table .btn {
        margin: 2px;
    }
    </style>


</head>