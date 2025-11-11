<?php
include '../../../../keamanan/koneksi.php';

if (!isset($_GET['id_chip'])) {
    echo "Data tidak ditemukan.";
    exit;
}

$id = (int)$_GET['id_chip'];

// Ambil data chip + box_inner
$query = "
SELECT chip.*, box_inner.box_inner, box_inner.color_table 
FROM chip
JOIN box_inner ON chip.id_box_inner = box_inner.id_box_inner
WHERE chip.id_chip = ?
";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "Data tidak ditemukan.";
    exit;
}

// Ambil data terakhir dari cicle
$q2 = $koneksi->prepare("SELECT * FROM cicle WHERE id_chip = ? ORDER BY tanggal_transaksi DESC LIMIT 1");
$q2->bind_param("i", $id);
$q2->execute();
$cicle = $q2->get_result()->fetch_assoc();
?>

<div class="row">
    <div class="col-md-6">
        <ul class="list-group">
            <li class="list-group-item"><strong>SN:</strong> <?= htmlspecialchars($data['sn']) ?></li>
            <li class="list-group-item"><strong>MSISDN:</strong> <?= htmlspecialchars($data['msisdn']) ?></li>
            <li class="list-group-item"><strong>Box Inner:</strong> <?= htmlspecialchars($data['box_inner']) ?></li>
            <li class="list-group-item"><strong>IN:</strong> <?= htmlspecialchars($data['in']) ?></li>
            <li class="list-group-item"><strong>Alokasi:</strong> <?= htmlspecialchars($data['alokasi']) ?></li>
            <li class="list-group-item"><strong>Tanggal Aktivasi:</strong>
                <?= htmlspecialchars($data['tanggal_aktivasi']) ?></li>
        </ul>
    </div>
    <div class="col-md-6">
        <ul class="list-group">
            <li class="list-group-item"><strong>Status Aktivasi:</strong>
                <span
                    class="badge bg-<?= $data['status_aktivasi'] == 'sukses' ? 'success' : ($data['status_aktivasi'] == 'gagal' ? 'danger' : 'secondary') ?>">
                    <?= ucfirst($data['status_aktivasi']) ?>
                </span>
            </li>
            <li class="list-group-item"><strong>Status Chip:</strong>
                <span
                    class="badge bg-<?= $data['status'] == 'pending' ? 'warning' : ($data['status'] == 'aktivasi' ? 'info' : ($data['status'] == 'transaksi' ? 'success' : 'danger')) ?>">
                    <?= ucfirst($data['status']) ?>
                </span>
            </li>
            <?php if ($cicle): ?>
                <li class="list-group-item"><strong>Cicle Terakhir:</strong> <?= htmlspecialchars($cicle['cicle']) ?></li>
                <li class="list-group-item"><strong>Tanggal Transaksi:</strong>
                    <?= htmlspecialchars($cicle['tanggal_transaksi']) ?></li>
                <li class="list-group-item"><strong>Habis:</strong> <?= htmlspecialchars($cicle['habis']) ?></li>
                <li class="list-group-item"><strong>Hangus:</strong> <?= htmlspecialchars($cicle['hangus']) ?></li>
            <?php else: ?>
                <li class="list-group-item text-muted">Belum ada data transaksi</li>
            <?php endif; ?>
        </ul>
    </div>
</div>