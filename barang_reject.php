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
    <title>SmartInventory — Barang Reject</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/barang_reject.css">
</head>
<body>
<div class="wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            </div>
            <div>
                <span class="brand-name">SMARTINVENTORY</span>
                <p class="brand-sub">Operasi Manufaktur</p>
            </div>
        </div>

        <div class="nav-section-label">MENU UTAMA</div>
        <ul class="nav-list">
            <li class="nav-item"><a href="index.php"><span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>Dasbor</a></li>
            <li class="nav-item"><a href="inventaris.php"><span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg></span>Inventaris</a></li>
            <li class="nav-item"><a href="barang_masuk.php"><span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.88 18.09A5 5 0 0018 9h-1.26A8 8 0 103 16.29"/></svg></span>Barang Masuk</a></li>
            <li class="nav-item"><a href="barang_keluar.php"><span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 17 20 13 16 9"/><line x1="20" y1="13" x2="4" y2="13"/></svg></span>Barang Keluar</a></li>
            <li class="nav-item active"><a href="barang_reject.php"><span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></span>Barang Reject</a></li>
        </ul>

        <div class="sidebar-bottom">
            <div class="ai-card">
                <div class="ai-card-header"><div class="ai-pulse"></div><span>Asisten AI Aktif</span></div>
                <p class="ai-card-desc">AI mendeteksi <strong>3 optimasi</strong> inventaris</p>
                <a href="#" class="ai-card-btn">Lihat Saran &rarr;</a>
            </div>
            <a href="logout.php" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;color:#f87171;text-decoration:none;font-size:13.5px;font-weight:600;margin-top:12px;transition:background 0.15s;" onmouseover="this.style.background='rgba(248,113,113,0.1)'" onmouseout="this.style.background='transparent'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Logout
            </a>
        </div>
    </aside>

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
