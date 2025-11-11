<?php
// get_chip_by_box.php
header('Content-Type: application/json; charset=utf-8');
include '../koneksi.php';

$id_box = isset($_GET['id_box']) ? (int) $_GET['id_box'] : 0;

if ($id_box <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $koneksi->prepare("SELECT sn, msisdn FROM chip WHERE id_box_inner = ? AND (sn IS NOT NULL AND sn != '')");
$stmt->bind_param("i", $id_box);
$stmt->execute();
$res = $stmt->get_result();
$data = [];
while ($row = $res->fetch_assoc()) {
    // Pastikan msisdn tanpa prefix 0 jika API butuh tanpa 0 (suaikan jika perlu)
    $data[] = ['sn' => $row['sn'], 'msisdn' => $row['msisdn']];
}
$stmt->close();
$koneksi->close();

echo json_encode($data);
