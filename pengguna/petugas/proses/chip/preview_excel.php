<?php
require '../../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['data_report']['tmp_name'])) {
    $file_tmp = $_FILES['data_report']['tmp_name'];
    $spreadsheet = IOFactory::load($file_tmp);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    // ambil header
    $header = $rows[0];

    $data = [];
    for ($i = 1; $i < count($rows); $i++) {
        $rowData = [];
        foreach ($header as $key => $colName) {
            $rowData[$colName] = $rows[$i][$key] ?? '';
        }
        $data[] = $rowData;
    }

    echo json_encode($data);
} else {
    echo json_encode([]);
}
