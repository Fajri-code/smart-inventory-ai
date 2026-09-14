<?php
$conn = new mysqli("127.0.0.1", "root", "", "inventory_ai", 3308);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>