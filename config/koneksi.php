<?php
$conn = new mysqli("localhost", "root", "", "inventory_ai");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>