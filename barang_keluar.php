<?php
include 'config/koneksi.php';
if (isset($_GET['kirim_id'])) {
    $id = $_GET['kirim_id'];
    mysqli_query($conn, "UPDATE barang_keluar SET status='kirim' WHERE id='$id'");
    header("Location: barang_keluar.php");
}

if (isset($_GET['terkirim_id'])) {
    $id = $_GET['terkirim_id'];
    mysqli_query($conn, "UPDATE barang_keluar SET status='terkirim' WHERE id='$id'");
    header("Location: barang_keluar.php");
}

$total = mysqli_fetch_assoc(mysqli_query($conn, 
"SELECT COUNT(*) as total FROM barang_keluar"))['total'];

$siap = mysqli_fetch_assoc(mysqli_query($conn, 
"SELECT COUNT(*) as total FROM barang_keluar WHERE status='siap'"))['total'];

$dikirim = mysqli_fetch_assoc(mysqli_query($conn, 
"SELECT COUNT(*) as total FROM barang_keluar WHERE status='kirim'"))['total'];

$terkirim = mysqli_fetch_assoc(mysqli_query($conn, 
"SELECT COUNT(*) as total FROM barang_keluar WHERE status='terkirim'"))['total'];

// PROSES INPUT BARANG KELUAR
if (isset($_POST['kirim'])) {
    $id_barang = $_POST['id_barang'];
    $jumlah = $_POST['jumlah'];
    $tujuan = $_POST['tujuan'];

    // ambil data barang
    $barang = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT * FROM barang WHERE id='$id_barang'"));

    // VALIDASI STOK
    if ($jumlah > $barang['jumlah']) {
        echo "<script>alert('Stok tidak cukup!');</script>";
    } else {

        // kurangi stok
       mysqli_query($conn, 
    "UPDATE barang SET jumlah = jumlah - $jumlah WHERE id='$id_barang'");

// ambil stok terbaru
$cek = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT jumlah FROM barang WHERE id='$id_barang'"));

// update status otomatis
if ($cek['jumlah'] <= 0) {
    mysqli_query($conn, 
        "UPDATE barang SET status='Habis' WHERE id='$id_barang'");
} else {
    mysqli_query($conn, 
        "UPDATE barang SET status='Available' WHERE id='$id_barang'");
}

        // simpan ke barang keluar
        mysqli_query($conn, 
            "INSERT INTO barang_keluar 
            (nama_barang, sku, jumlah, tujuan, status)
            VALUES 
            ('{$barang['nama']}', '{$barang['sku']}', '$jumlah', '$tujuan', 'siap')");

        echo "<script>alert('Barang berhasil dikirim!');</script>";
    }
}

$query = mysqli_query($conn, "SELECT * FROM barang_keluar ORDER BY id DESC");
$jumlah_data = mysqli_num_rows($query);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartInventory — Barang Keluar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/barang_keluar.css">
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
            <li class="nav-item">
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
            <li class="nav-item active">
                <a href="barang_keluar.php">
                    <span class="nav-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 17 20 13 16 9"/><line x1="20" y1="13" x2="4" y2="13"/><path d="M4 6H2m2 6H2m2 6H2"/></svg>
                    </span>
                    Barang Keluar
                    <span class="nav-dot"></span>
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

    <!-- MAIN -->
    <main class="main">

        <!-- TOPBAR -->
        <div class="topbar" style="display:none;"></div>

        <!-- PAGE CONTENT -->
        <div class="page-content">

            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Barang Keluar</h1>
                    <p class="page-subtitle">Kelola pengiriman dan distribusi barang keluar</p>
                </div>
                <div class="header-actions">
    <button type="button" class="btn btn-outline">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
        </svg>
        Cetak Surat Jalan
    </button> <button type="button" class="btn btn-primary" onclick="openModal()">
        Input Barang Keluar
    </button>
</div>
            </div>
            

            <!-- STAT CARDS -->
            <div class="stat-grid">
                <div class="stat-card" style="border-left:4px solid #3b82f6;">
                    <div class="stat-icon blue">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $total ?></div>
                        <div class="stat-label">Total Pengiriman</div>
                    </div>
                </div>
                <div class="stat-card" style="border-left:4px solid #7c3aed;">
                    <div class="stat-icon purple">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $siap ?></div>
                        <div class="stat-label">Siap Kirim</div>
                    </div>
                </div>
                <div class="stat-card" style="border-left:4px solid #f59e0b;">
                    <div class="stat-icon amber">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $dikirim ?></div>
                        <div class="stat-label">Dalam Pengiriman</div>
                    </div>
                </div>
                <div class="stat-card" style="border-left:4px solid #10b981;">
                    <div class="stat-icon green">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $terkirim ?></div>
                        <div class="stat-label">Terkirim</div>
                    </div>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="table-card">
                <div class="table-toolbar">
                    <div class="filter-tabs">
                        <button class="tab-btn active" onclick="filterTab(this,'semua')">Semua</button>
                        <button class="tab-btn" onclick="filterTab(this,'siap')">Siap Kirim</button>
                        <button class="tab-btn" onclick="filterTab(this,'kirim')">Dalam Pengiriman</button>
                        <button class="tab-btn" onclick="filterTab(this,'terkirim')">Terkirim</button>
                    </div>
                    <div class="table-actions">
                        <button class="btn-sm">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                            Filter Lanjut
                        </button>
                        <button class="btn-sm">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Ekspor
                        </button>
                    </div>
                </div>

                <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>SKU</th>
                <th>Tujuan</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php $no=1; while($row = mysqli_fetch_assoc($query)): ?>
            <tr data-status="<?= $row['status'] ?>">
                <td><?= $no++ ?></td>
                <td><?= $row['nama_barang'] ?></td>
                <td><?= $row['sku'] ?></td>
                <td><?= $row['tujuan'] ?></td>
                <td><?= $row['jumlah'] ?></td>

                <!-- STATUS BADGE -->
                <td>
                    <?php if ($row['status'] == 'siap'): ?>
                        <span class="badge siap">Siap Kirim</span>
                    <?php elseif ($row['status'] == 'kirim'): ?>
                        <span class="badge proses">Dikirim</span>
                    <?php else: ?>
                        <span class="badge terkirim">Terkirim</span>
                    <?php endif; ?>
                </td>

                <!-- AKSI -->
                <td>
                    <?php if ($row['status'] == 'siap'): ?>
                        <a href="surat_jalan.php?id=<?= $row['id'] ?>" class="btn-sm" style="background:#2563eb;color:white;border-color:#2563eb;" target="_blank">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            Cetak Surat Jalan
                        </a>
                    <?php elseif ($row['status'] == 'kirim'): ?>
                        <a href="?terkirim_id=<?= $row['id'] ?>" class="btn-sm">✔ Selesai</a>
                    <?php else: ?>
                        <span style="color:#94a3b8;font-size:13px;">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<div class="table-footer">
    <div class="table-info">
        Menampilkan <strong><?= $jumlah_data ?></strong> dari <strong><?= $jumlah_data ?></strong> pengiriman
    </div>
    <div class="pagination">
        <button class="page-btn" disabled>&lsaquo;</button>
        <button class="page-btn active">1</button>
        <button class="page-btn" disabled>&rsaquo;</button>
    </div>
</div>
            </div>
            

        </div><!-- /page-content -->
    </main>
</div>

<script>
function openModal() {
  const overlay = document.getElementById('modalOverlay');
  overlay.style.display = 'flex';
}

function closeModal() {
  document.getElementById('modalOverlay').style.display = 'none';
}

// Tutup modal kalau klik di luar
document.getElementById('modalOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

function filterTab(btn, status) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');

  const rows = document.querySelectorAll('#tableBody tr');
  let visible = 0;
  rows.forEach(row => {
    if (status === 'semua' || row.dataset.status === status) {
      row.style.display = '';
      visible++;
    } else {
      row.style.display = 'none';
    }
  });

  document.querySelector('.table-info').innerHTML =
    `Menampilkan <strong>${visible}</strong> dari <strong>${rows.length}</strong> pengiriman`;
}
</script>
<!-- MODAL INPUT BARANG KELUAR -->
<div id="modalOverlay" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.55); backdrop-filter:blur(4px); z-index:999; align-items:center; justify-content:center;">
  <div style="background:#fff; border-radius:16px; width:460px; max-width:92vw; box-shadow:0 20px 60px rgba(0,0,0,0.18); overflow:hidden;">
    <div style="display:flex; align-items:center; justify-content:space-between; padding:22px 24px 18px; border-bottom:1px solid #e2e8f0;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:40px;height:40px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#3b82f6;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 17 20 13 16 9"/><line x1="20" y1="13" x2="4" y2="13"/></svg>
            </div>
            <div>
                <div style="font-size:16px;font-weight:700;color:#0f172a;">Input Barang Keluar</div>
                <div style="font-size:12px;color:#94a3b8;">Isi data pengiriman barang</div>
            </div>
        </div>
        <button onclick="closeModal()" style="width:32px;height:32px;border-radius:8px;border:1.5px solid #e2e8f0;background:white;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#64748b;font-size:18px;">&times;</button>
    </div>
    <form method="POST" style="padding:22px 24px;">
      <div style="margin-bottom:16px;">
        <label style="display:block;margin-bottom:7px;font-size:12.5px;font-weight:600;color:#475569;">Pilih Barang</label>
        <select name="id_barang" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13.5px;color:#0f172a;outline:none;">
          <option value="">-- Pilih Barang --</option>
          <?php
            $barang_list = mysqli_query($conn, "SELECT * FROM barang WHERE status='Available' ORDER BY nama ASC");
            while($b = mysqli_fetch_assoc($barang_list)):
          ?>
          <option value="<?= $b['id'] ?>"><?= $b['nama'] ?> (Stok: <?= $b['jumlah'] ?>)</option>
          <?php endwhile; ?>
        </select>
      </div>
      <div style="margin-bottom:16px;">
        <label style="display:block;margin-bottom:7px;font-size:12.5px;font-weight:600;color:#475569;">Jumlah</label>
        <input type="number" name="jumlah" min="1" required placeholder="Masukkan jumlah" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13.5px;color:#0f172a;outline:none;box-sizing:border-box;">
      </div>
      <div style="margin-bottom:22px;">
        <label style="display:block;margin-bottom:7px;font-size:12.5px;font-weight:600;color:#475569;">Tujuan</label>
        <input type="text" name="tujuan" required placeholder="Contoh: Gudang B" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13.5px;color:#0f172a;outline:none;box-sizing:border-box;">
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:16px;border-top:1px solid #e2e8f0;">
        <button type="button" onclick="closeModal()" style="padding:10px 20px;border:1.5px solid #e2e8f0;border-radius:8px;background:#fff;cursor:pointer;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;color:#475569;">Batal</button>
        <button type="submit" name="kirim" style="padding:10px 22px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;border:none;border-radius:8px;cursor:pointer;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:700;box-shadow:0 3px 10px rgba(59,130,246,0.35);">Simpan</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>