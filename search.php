<?php
include __DIR__ . '/config/koneksi.php';

$q = mysqli_real_escape_string($conn, $_GET['q'] ?? '');

if(strlen($q) < 2) { echo json_encode([]); exit; }

$results = [];

// Cari di tabel barang (inventaris)
$res = $conn->query("SELECT id, nama, sku, jumlah, status FROM barang WHERE nama LIKE '%$q%' OR sku LIKE '%$q%' LIMIT 5");
while($row = $res->fetch_assoc()) {
    $results[] = [
        'type'  => 'inventaris',
        'label' => $row['nama'],
        'sub'   => 'SKU: ' . ($row['sku'] ?? '-') . ' · Stok: ' . $row['jumlah'],
        'badge' => $row['status'],
        'url'   => 'inventaris.php'
    ];
}

// Cari di tabel barang_masuk
$res = $conn->query("SELECT no_penerimaan, nama_barang, supplier, status FROM barang_masuk WHERE nama_barang LIKE '%$q%' OR no_penerimaan LIKE '%$q%' OR supplier LIKE '%$q%' LIMIT 5");
while($row = $res->fetch_assoc()) {
    $results[] = [
        'type'  => 'masuk',
        'label' => $row['nama_barang'],
        'sub'   => 'No: ' . $row['no_penerimaan'] . ' · ' . $row['supplier'],
        'badge' => $row['status'],
        'url'   => 'barang_masuk.php'
    ];
}

// Cari di tabel barang_keluar
$res = $conn->query("SELECT nama_barang, sku, tujuan, status FROM barang_keluar WHERE nama_barang LIKE '%$q%' OR tujuan LIKE '%$q%' LIMIT 5");
while($row = $res->fetch_assoc()) {
    $results[] = [
        'type'  => 'keluar',
        'label' => $row['nama_barang'],
        'sub'   => 'Tujuan: ' . $row['tujuan'],
        'badge' => $row['status'],
        'url'   => 'barang_keluar.php'
    ];
}

echo json_encode($results);
