<?php
include 'config/koneksi.php';

if (isset($_GET['kirim_id'])) {
    $id = $_GET['kirim_id'];
    $stmt = $conn->prepare("UPDATE barang_keluar SET status='kirim' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: barang_keluar.php");
    exit;
}

if (isset($_GET['terkirim_id'])) {
    $id = $_GET['terkirim_id'];
    $stmt = $conn->prepare("UPDATE barang_keluar SET status='terkirim' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: barang_keluar.php");
    exit;
}

$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang_keluar"))['total'];
$siap = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang_keluar WHERE status='siap'"))['total'];
$dikirim = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang_keluar WHERE status='kirim'"))['total'];
$terkirim = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang_keluar WHERE status='terkirim'"))['total'];

// PROSES INPUT BARANG KELUAR
if (isset($_POST['kirim'])) {
    $id_barang = $_POST['id_barang'];
    $jumlah = $_POST['jumlah'];
    $tujuan = $_POST['tujuan'];

    // ambil data barang
    $stmt_barang = $conn->prepare("SELECT * FROM barang WHERE id=?");
    $stmt_barang->bind_param("i", $id_barang);
    $stmt_barang->execute();
    $barang = $stmt_barang->get_result()->fetch_assoc();

    // VALIDASI STOK
    if ($jumlah > $barang['jumlah']) {
        echo "<script>alert('Stok tidak cukup!');</script>";
    } else {
        // kurangi stok
        $stmt_upd = $conn->prepare("UPDATE barang SET jumlah = jumlah - ?, jumlah_baik = jumlah_baik - ? WHERE id=?");
        $stmt_upd->bind_param("iii", $jumlah, $jumlah, $id_barang);
        $stmt_upd->execute();

        // ambil stok terbaru
        $stmt_cek = $conn->prepare("SELECT jumlah FROM barang WHERE id=?");
        $stmt_cek->bind_param("i", $id_barang);
        $stmt_cek->execute();
        $cek = $stmt_cek->get_result()->fetch_assoc();

        // update status otomatis
        $new_status = 'Available';
        if ($cek['jumlah'] <= 0) {
            $new_status = 'Habis';
        } elseif ($cek['jumlah'] <= 5) {
            $new_status = 'On Hold';
        }
        $stmt_stat = $conn->prepare("UPDATE barang SET status=? WHERE id=?");
        $stmt_stat->bind_param("si", $new_status, $id_barang);
        $stmt_stat->execute();

        // simpan ke barang keluar
        $stmt_insert = $conn->prepare("INSERT INTO barang_keluar (nama_barang, sku, jumlah, tujuan, status) VALUES (?, ?, ?, ?, 'siap')");
        $stmt_insert->bind_param("ssis", $barang['nama'], $barang['sku'], $jumlah, $tujuan);
        $stmt_insert->execute();

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
    <meta name="dicoding:email" content="refifjrn14@gmail.com">
    <title>SmartInventory — Barang Keluar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/barang_keluar.css">
</head>
<body>

<div class="wrapper">

    <!-- SIDEBAR -->
<?php include 'includes/sidebar.php'; ?>

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