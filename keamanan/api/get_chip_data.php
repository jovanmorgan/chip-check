<?php
include '../koneksi.php';
header('Content-Type: application/json; charset=utf-8');

$sn = isset($_GET['sn']) ? trim($_GET['sn']) : '';
if ($sn === '') {
    echo json_encode(['status' => 'error', 'message' => 'SN kosong']);
    exit;
}

$stmt = $koneksi->prepare("
  SELECT chip.*, box_inner.box_inner, box_inner.color_icon 
  FROM chip 
  JOIN box_inner ON chip.id_box_inner = box_inner.id_box_inner 
  WHERE chip.sn = ?
");
$stmt->bind_param("s", $sn);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();

    // Warna status
    $status_color = match (strtolower($row['status'])) {
        'pending' => 'warning',
        'aktivasi' => 'info',
        'transaksi' => 'success',
        'rusak' => 'danger',
        default => 'secondary'
    };

    echo json_encode([
        'status' => 'ok',
        'data' => [
            'sn' => $row['sn'],
            'msisdn' => $row['msisdn'],
            'box_inner' => $row['box_inner'],
            'color_icon' => $row['color_icon'],
            'status' => ucfirst($row['status']),
            'status_color' => $status_color
        ]
    ]);
} else {
    echo json_encode(['status' => 'not_found']);
}
