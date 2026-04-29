<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}
include __DIR__ . '/config/koneksi.php';
$stok_min   = $conn->query("SELECT nama, jumlah FROM barang WHERE jumlah <= 5") ?? null;
$qc_reject  = $conn->query("SELECT nama_barang FROM barang_masuk WHERE status='ditolak'") ?? null;
$siap_kirim = $conn->query("SELECT nama_barang, tujuan FROM barang_keluar WHERE status='siap'") ?? null;

$total     = $conn->query("SELECT COUNT(*) as t FROM barang_masuk")->fetch_assoc()['t'] ?? 0;
$ditolak   = $conn->query("SELECT COUNT(*) as t FROM barang_masuk WHERE status='ditolak'")->fetch_assoc()['t'] ?? 0;
$ditahan   = $conn->query("SELECT COUNT(*) as t FROM barang_masuk WHERE status='menunggu'")->fetch_assoc()['t'] ?? 0;
$dirilis   = $conn->query("SELECT COUNT(*) as t FROM barang_masuk WHERE status='disetujui'")->fetch_assoc()['t'] ?? 0;
$unreleased = max(0, $total - $ditolak - $ditahan - $dirilis);

// ---- DATA TREN: jumlah barang masuk per bulan (6 bulan terakhir) ----
$tren_labels = [];
$tren_data   = [];
for ($i = 5; $i >= 0; $i--) {
    $bulan     = date('Y-m', strtotime("-$i months"));
    $label     = date('M Y', strtotime("-$i months"));
    $res       = $conn->query("SELECT COUNT(*) as t FROM barang_masuk WHERE DATE_FORMAT(created_at,'%Y-%m')='$bulan'");
    $tren_labels[] = $label;
    $tren_data[]   = (int)($res->fetch_assoc()['t'] ?? 0);
}
$tren_labels_json = json_encode($tren_labels);
$tren_data_json   = json_encode($tren_data);

// ---- AI SARAN ----
$ai_saran = [];
if($stok_min && $stok_min->num_rows > 0){
    $ai_saran[] = ["pesan"=>"Restock segera","detail"=>$stok_min->num_rows." barang hampir habis","prioritas"=>"Tinggi","warna"=>"red","icon"=>"⚠"];
}
if($siap_kirim && $siap_kirim->num_rows > 0){
    $ai_saran[] = ["pesan"=>"Kirim barang sekarang","detail"=>$siap_kirim->num_rows." siap dikirim","prioritas"=>"Sedang","warna"=>"green","icon"=>"🚚"];
}
if($qc_reject && $qc_reject->num_rows > 0){
    $ai_saran[] = ["pesan"=>"Cek kualitas barang","detail"=>"Ada barang gagal QC","prioritas"=>"Sedang","warna"=>"amber","icon"=>"❌"];
}
usort($ai_saran, function($a,$b){ $r=["Tinggi"=>3,"Sedang"=>2,"Rendah"=>1]; return $r[$b['prioritas']]-$r[$a['prioritas']]; });
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartInventory — Dasbor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
    .search-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        width: 100%;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        z-index: 999;
        overflow: hidden;
        max-height: 380px;
        overflow-y: auto;
    }
    .sd-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        text-decoration: none;
        color: inherit;
        transition: background 0.12s;
    }
    .sd-item:last-child { border-bottom: none; }
    .sd-item:hover { background: #f8faff; }
    .sd-icon {
        width: 34px; height: 34px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    .sd-icon.inventaris { background: #eff6ff; }
    .sd-icon.masuk      { background: #ecfdf5; }
    .sd-icon.keluar     { background: #fffbeb; }
    .sd-info { flex: 1; min-width: 0; }
    .sd-label { font-size: 13.5px; font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sd-sub   { font-size: 12px; color: #94a3b8; margin-top: 1px; }
    .sd-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; white-space: nowrap; flex-shrink: 0; }
    .sd-badge.available, .sd-badge.disetujui, .sd-badge.terkirim { background: #dcfce7; color: #166534; }
    .sd-badge.menunggu, .sd-badge.siap, .sd-badge.on\ hold { background: #fef9c3; color: #854d0e; }
    .sd-badge.ditolak, .sd-badge.reject, .sd-badge.habis { background: #fee2e2; color: #991b1b; }
    .sd-empty { padding: 20px; text-align: center; color: #94a3b8; font-size: 13px; }
    .sd-section { padding: 6px 16px 4px; font-size: 10px; font-weight: 700; letter-spacing: 0.08em; color: #94a3b8; text-transform: uppercase; background: #fafafa; }
    </style>
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
            <li class="nav-item active">
                <a href="index.php">
                    <span class="nav-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    </span>
                    Dasbor
                </a>
            </li>
            <li class="nav-item">
                <a href="inventaris.php">
                    <span class="nav-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                    </span>
                    Inventaris
                </a>
            </li>
            <li class="nav-item">
                <a href="barang_masuk.php">
                    <span class="nav-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.88 18.09A5 5 0 0018 9h-1.26A8 8 0 103 16.29"/></svg>
                    </span>
                    Barang Masuk
                </a>
            </li>
            <li class="nav-item">
                <a href="barang_keluar.php">
                    <span class="nav-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 17 20 13 16 9"/><line x1="20" y1="13" x2="4" y2="13"/><path d="M4 6H2m2 6H2m2 6H2"/></svg>
                    </span>
                    Barang Keluar
                </a>
            </li>
             <li class="nav-item">
                <a href="barang_reject.php">
                    <span class="nav-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                    </span>
                    Barang Reject
                </a>
            </li>   
        </ul>

        <div class="sidebar-bottom">
            <div class="ai-card">
                <div class="ai-card-header">
                    <div class="ai-pulse"></div>
                    <span>Asisten AI Aktif</span>
                </div>
                <p class="ai-card-desc">Terdeteksi <strong>3 peluang optimasi</strong> inventaris Anda</p>
                <a href="#" class="ai-card-btn">Lihat Saran &rarr;</a>
            </div>
            <a href="logout.php" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;color:#f87171;text-decoration:none;font-size:13.5px;font-weight:600;margin-top:12px;transition:background 0.15s;" onmouseover="this.style.background='rgba(248,113,113,0.1)'" onmouseout="this.style.background='transparent'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Logout
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="content">

        <!-- TOP BAR -->
        <header class="top-bar">
            <div class="search-wrap" style="position:relative;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="globalSearch" placeholder="Cari barang, batch, atau status..." autocomplete="off">
                <div id="searchDropdown" class="search-dropdown" style="display:none;"></div>
            </div>
            <div class="topbar-right">
                <button class="icon-btn notif-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                    <span class="notif-dot"></span>
                </button>
                <div class="user-chip">
                    <div class="user-avatar">AD</div>
                    <span>Admin</span>
                </div>
            </div>
        </header>

        <!-- PAGE TITLE -->
        <div class="page-title">
            <div>
                <h1>Dasbor Operasional</h1>
                <p>Pemantauan inventaris dan analitik real-time</p>
            </div>
            <div class="title-right">
                <span class="live-badge">
                    <span class="live-dot"></span> LIVE
                </span>
                <span class="date-label" id="current-date"></span>
            </div>
        </div>

        <!-- STAT CARDS -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                </div>
                <div class="stat-top">
                    <div class="stat-value mono"><?= number_format($total, 0, ',', '.') ?></div>
                    <div class="stat-label">Total Barang</div>
                    <div class="stat-bar"><div class="stat-bar-fill blue" style="width:100%"></div></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon amber">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="stat-top">
                    <div class="stat-value mono"><?= number_format($ditahan, 0, ',', '.') ?></div>
                    <div class="stat-label">Ditahan</div>
                    <div class="stat-bar"><div class="stat-bar-fill amber" style="width:<?= $total > 0 ? round($ditahan/$total*100) : 0 ?>%"></div></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                </div>
                <div class="stat-top">
                    <div class="stat-value mono"><?= number_format($ditolak, 0, ',', '.') ?></div>
                    <div class="stat-label">Ditolak</div>
                    <div class="stat-bar"><div class="stat-bar-fill red" style="width:<?= $total > 0 ? round($ditolak/$total*100) : 0 ?>%"></div></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <div class="stat-top">
                    <div class="stat-value mono"><?= number_format($dirilis, 0, ',', '.') ?></div>
                    <div class="stat-label">Dirilis</div>
                    <div class="stat-bar"><div class="stat-bar-fill green" style="width:<?= $total > 0 ? round($dirilis/$total*100) : 0 ?>%"></div></div>
                </div>
            </div>
        </div>

        <!-- CHARTS -->
        <div class="charts-row">
            <div class="chart-card main-chart">
                <div class="chart-header">
                    <div>
                        <h3>Tren Inventaris</h3>
                        <p>Pergerakan stok Jan–Apr</p>
                    </div>
                    <div class="chart-legend">
                        <span class="legend-dot blue"></span> Stok
                    </div>
                </div>
                <div class="chart-wrap">
                    <canvas id="inventoryTrend"></canvas>
                </div>
            </div>

            <div class="chart-card side-chart">
                <div class="chart-header">
                    <div>
                        <h3>Distribusi Status</h3>
                        <p>Komposisi saat ini</p>
                    </div>
                </div>
                <div class="donut-wrap">
                    <canvas id="statusDistribution"></canvas>
                    <div class="donut-center">
                        <span class="mono"><?= $total ?></span>
                        <small>Total</small>
                    </div>
                </div>
                <div class="donut-legend">
                    <div class="dl-item"><span class="dl-dot green"></span>Dirilis <strong><?= $dirilis ?></strong></div>
                    <div class="dl-item"><span class="dl-dot amber"></span>Ditahan <strong><?= $ditahan ?></strong></div>
                    <div class="dl-item"><span class="dl-dot red"></span>Ditolak <strong><?= $ditolak ?></strong></div>
                </div>
            </div>
        </div>

        <div class="bottom-row">
  </div>
    <div class="panel ai-panel">
        <div class="panel-header">
            <h3>
                <span class="ai-spark">⚡</span>
                Saran Otomasi AI
            </h3>
            <span class="badge-new"><?= count($ai_saran) ?> baru</span>
        </div>

        <div class="suggestion-list">

        <?php if(count($ai_saran) > 0): ?>
            <?php foreach($ai_saran as $s): ?>
            <div class="suggestion-row">
                <div class="sug-left">
                    <div class="sug-icon <?= $s['warna'] ?>">
                        <?= $s['icon'] ?>
                    </div>
                    <div class="sug-text">
                        <strong><?= $s['pesan'] ?></strong>
                        <span><?= $s['detail'] ?></span>
                    </div>
                </div>
                <span class="priority <?= strtolower($s['prioritas']) ?>">
                    <?= $s['prioritas'] ?>
                </span>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="padding:10px;">✅ Tidak ada saran saat ini</p>
        <?php endif; ?>

        </div>
    </div>

</div>

        </div><!-- end bottom-row -->

    </div><!-- end content -->
</div><!-- end wrapper -->

<script>
const icons = {
    inventaris: '📦',
    masuk: '📥',
    keluar: '📤'
};
const sections = {
    inventaris: 'Inventaris',
    masuk: 'Barang Masuk',
    keluar: 'Barang Keluar'
};

const input    = document.getElementById('globalSearch');
const dropdown = document.getElementById('searchDropdown');
let debounce;

input.addEventListener('input', function() {
    clearTimeout(debounce);
    const q = this.value.trim();
    if(q.length < 2) { dropdown.style.display = 'none'; return; }

    debounce = setTimeout(() => {
        fetch('search.php?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
            if(!data.length) {
                dropdown.innerHTML = '<div class="sd-empty">Tidak ada hasil untuk "' + q + '"</div>';
                dropdown.style.display = 'block';
                return;
            }

            let html = '';
            let lastType = '';
            data.forEach(item => {
                if(item.type !== lastType) {
                    html += '<div class="sd-section">' + (sections[item.type] || item.type) + '</div>';
                    lastType = item.type;
                }
                const badge = item.badge ? '<span class="sd-badge ' + item.badge.toLowerCase() + '">' + item.badge + '</span>' : '';
                html += '<a href="' + item.url + '" class="sd-item">' +
                    '<div class="sd-icon ' + item.type + '">' + (icons[item.type] || '📋') + '</div>' +
                    '<div class="sd-info">' +
                        '<div class="sd-label">' + item.label + '</div>' +
                        '<div class="sd-sub">' + item.sub + '</div>' +
                    '</div>' +
                    badge +
                '</a>';
            });

            dropdown.innerHTML = html;
            dropdown.style.display = 'block';
        });
    }, 300);
});

document.addEventListener('click', function(e) {
    if(!input.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});

input.addEventListener('keydown', function(e) {
    if(e.key === 'Escape') dropdown.style.display = 'none';
});
</script>

<script>
// Current date
const d = new Date();
document.getElementById('current-date').textContent = d.toLocaleDateString('id-ID', {weekday:'long', day:'numeric', month:'long', year:'numeric'});

// Trend Chart — data real dari DB
const ctxTrend = document.getElementById('inventoryTrend').getContext('2d');
const gradient = ctxTrend.createLinearGradient(0, 0, 0, 260);
gradient.addColorStop(0, 'rgba(59,130,246,0.18)');
gradient.addColorStop(1, 'rgba(59,130,246,0)');
new Chart(ctxTrend, {
    type: 'line',
    data: {
        labels: <?= $tren_labels_json ?>,
        datasets: [{
            label: 'Barang Masuk',
            data: <?= $tren_data_json ?>,
            borderColor: '#3b82f6',
            backgroundColor: gradient,
            fill: true,
            tension: 0.4,
            pointRadius: 5,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#3b82f6',
            pointBorderWidth: 2,
            borderWidth: 2.5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: {
            backgroundColor: '#1e293b',
            titleFont: { family: 'Space Mono', size: 11 },
            bodyFont: { family: 'DM Sans', size: 13 },
            padding: 12, cornerRadius: 8
        }},
        scales: {
            x: { grid: { display: false }, ticks: { font: { family: 'DM Sans', size: 12 }, color: '#94a3b8' }, border: { display: false } },
            y: { grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Space Mono', size: 11 }, color: '#94a3b8', callback: v => v.toLocaleString('id'), stepSize: 1 }, border: { display: false } }
        }
    }
});

// Donut Chart
const ctxDist = document.getElementById('statusDistribution').getContext('2d');
new Chart(ctxDist, {
    type: 'doughnut',
    data: {
        labels: ['Dirilis', 'Ditahan', 'Belum Rilis', 'Ditolak'],
        datasets: [{
            data: [<?= $dirilis ?>, <?= $ditahan ?>, <?= $unreleased ?>, <?= $ditolak ?>],
            backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#ef4444'],
            borderWidth: 0,
            hoverOffset: 6
        }]
    },
    options: {
        cutout: '72%',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: {
            backgroundColor: '#1e293b',
            titleFont: { family: 'DM Sans', size: 12 },
            bodyFont: { family: 'Space Mono', size: 11 },
            padding: 10, cornerRadius: 8
        }}
    }
});

// Animate stat values on load
document.querySelectorAll('.stat-value').forEach(el => {
    const target = parseInt(el.textContent.replace(/\./g,''));
    let count = 0;
    const step = Math.ceil(target / 40);
    const interval = setInterval(() => {
        count = Math.min(count + step, target);
        el.textContent = count.toLocaleString('id');
        if (count >= target) clearInterval(interval);
    }, 30);
});
</script>
</body>
</html>