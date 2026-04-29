    <?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

include __DIR__ . '/config/koneksi.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Barang</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>

<body>

<div class="wrapper">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h3>📦 Inventory AI</h3>
        <a href="index.php">🏠 Dashboard</a>
        <a href="barang.php">📋 Data Barang</a>
        <a href="tambah_barang.php">➕ Tambah Barang</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <h2>➕ Tambah Barang</h2>

        <div class="card">
        <form method="POST" action="simpan.php">
            <input type="text" name="nama" placeholder="Nama Barang" required>
            <input type="number" name="jumlah" placeholder="Jumlah" required>

            <select name="status">
                <option>Available</option>
                <option>On Hold</option>
                <option>Reject</option>
            </select>

            <button type="submit">Simpan</button>
        </form>
        </div>

    </div>
</div>

</body>
</html>