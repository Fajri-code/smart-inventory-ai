<?php
session_start();
include __DIR__ . '/config/koneksi.php';

$total_reject    = $conn->query("SELECT SUM(jumlah) as t FROM barang_reject")->fetch_assoc()['t'] ?? 0;
$dari_masuk      = $conn->query("SELECT SUM(jumlah) as t FROM barang_reject WHERE sumber='barang_masuk'")->fetch_assoc()['t'] ?? 0;
$dari_inventaris = $conn->query("SELECT SUM(jumlah) as t FROM barang_reject WHERE sumber='inventaris'")->fetch_assoc()['t'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="dicoding:email" content="refifjrn14@gmail.com">
    <title>SmartInventory — Barang Reject</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/barang_reject.css">
</head>
<body>
<div class="wrapper">

    <!-- SIDEBAR -->
<?php include 'includes/sidebar.php'; ?>

    <!-- TOPBAR -->
    <header class="topbar" style="display:none;"></header>

    <!-- MAIN -->
    <main class="main" style="padding-top:0;">
        <div class="main-inner">

            <div class="page-header-reject">
                <h1>❌ Barang Reject</h1>
                <p>Rekap semua barang reject dari penerimaan dan gudang</p>
            </div>

            <!-- STAT CARDS -->
            <div class="stat-grid">
                <div class="stat-card red">
                    <div class="stat-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $total_reject ?></div>
                        <div class="stat-label">Total Reject</div>
                    </div>
                </div>
                <div class="stat-card amber">
                    <div class="stat-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.88 18.09A5 5 0 0018 9h-1.26A8 8 0 103 16.29"/></svg>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $dari_masuk ?></div>
                        <div class="stat-label">Dari Barang Masuk</div>
                    </div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $dari_inventaris ?></div>
                        <div class="stat-label">Dari Gudang</div>
                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Barang</th>
                            <th>SKU</th>
                            <th>Jumlah Reject</th>
                            <th>Sumber</th>
                            <th>Alasan</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM barang_reject ORDER BY tanggal DESC");
                    if($result->num_rows === 0):
                    ?>
                        <tr><td colspan="7" class="empty">Belum ada data barang reject</td></tr>
                    <?php else: $no = 1; while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($row['nama_barang']) ?></strong></td>
                            <td class="sku-cell"><?= htmlspecialchars($row['sku'] ?? '-') ?></td>
                            <td class="qty-cell"><?= $row['jumlah'] ?></td>
                            <td>
                                <?php if($row['sumber'] === 'barang_masuk'): ?>
                                    <span class="badge-masuk">📦 Penerimaan</span>
                                <?php else: ?>
                                    <span class="badge-inventaris">🏭 Gudang</span>
                                <?php endif; ?>
                            </td>
                            <td class="note-cell"><?= htmlspecialchars($row['alasan'] ?? '-') ?></td>
                            <td class="date-cell"><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
                        </tr>
                    <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
</div>
</body>
</html>
