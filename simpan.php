<?php
include 'config/koneksi.php';

$nama = $_POST['nama'];
$jumlah = $_POST['jumlah'];
$status = $_POST['status'];

$conn->query("INSERT INTO barang (nama, jumlah, jumlah_baik, status) 
VALUES ('$nama', '$jumlah', '$jumlah', '$status')");
header("Location: inventaris.php");

?>  