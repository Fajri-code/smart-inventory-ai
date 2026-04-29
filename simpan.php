<?php
include 'config/koneksi.php';

$nama = $_POST['nama'];
$jumlah = $_POST['jumlah'];
$status = $_POST['status'];

$conn->query("INSERT INTO barang (nama, jumlah, status) 
VALUES ('$nama', '$jumlah', '$status')");
header("Location: barang.php");

?>  