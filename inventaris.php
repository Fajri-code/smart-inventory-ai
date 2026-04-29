<?php
session_start();
$statusFilter = $_GET['status'] ?? '';
include __DIR__ . '/config/koneksi.php';
include 'ai.php';

$toast = '';
$toastType = '';

// ================= EDIT STOK =================
if(isset($_POST['edit_stok'])){
    $id     = intval($_POST['id']);
    $jumlah = $_POST['jumlah_baru'];
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi_baru'] ?? '');

    if($jumlah === '' || !is_numeric($jumlah) || intval($jumlah) < 0){
        $toast     = "Input tidak valid.\nPeriksa kembali jumlah yang dimasukkan.";
        $toastType = 'error';
    } else {
        $jumlah = intval($jumlah);
        $status_baru = $jumlah <= 0 ? 'Habis' : ($jumlah <= 5 ? 'On Hold' : 'Available');
        $conn->query("UPDATE barang SET jumlah = $jumlah, status = '$status_baru', lokasi = '$lokasi' WHERE id = $id");
        $toast     = "Stok dan lokasi berhasil diperbarui.";
        $toastType = 'success';
    }
}

// ================= TAMBAH STOK =================
if(isset($_POST['tambah_stok'])){
    $id     = intval($_POST['id']);
    $tambah = $_POST['tambah'];

    if($tambah === '' || !is_numeric($tambah) || intval($tambah) <= 0){
        $toast     = "Input tidak valid.\nPeriksa kembali jumlah yang dimasukkan.";
        $toastType = 'error';
    } else {
        $tambah = intval($tambah);
        $conn->query("
            UPDATE barang 
            SET jumlah = jumlah + $tambah,
                status = CASE 
                    WHEN jumlah + $tambah <= 0 THEN 'Habis'
                    WHEN jumlah + $tambah <= 5 THEN 'On Hold'
                    ELSE 'Available'
                END
            WHERE id = $id
        ");
        $toast     = "Stok berhasil ditambahkan.\nData inventaris telah diperbarui.";
        $toastType = 'success';
    }
}

// ================= REJECT =================
if(isset($_POST['reject_barang'])){
    $id            = intval($_POST['id']);
    $jumlah_reject = $_POST['jumlah_reject'];

    if($jumlah_reject === '' || !is_numeric($jumlah_reject) || intval($jumlah_reject) <= 0){
        $toast     = "Input tidak valid.\nPeriksa kembali jumlah yang dimasukkan.";
        $toastType = 'error';
    } else {
        $jumlah_reject = intval($jumlah_reject);
        $barang        = $conn->query("SELECT jumlah, nama FROM barang WHERE id=$id")->fetch_assoc();

        if($jumlah_reject > $barang['jumlah']){
            $toast     = "Jumlah reject melebihi stok tersedia.\nSilakan masukkan angka yang benar.";
            $toastType = 'error';
        } else {
            $sisa = $barang['jumlah'] - $jumlah_reject;
            $status_baru = $sisa <= 0 ? 'Habis' : ($sisa <= 5 ? 'On Hold' : 'Available');
            $conn->query("UPDATE barang SET jumlah = $sisa, jumlah_reject = jumlah_reject + $jumlah_reject, status = '$status_baru' WHERE id=$id");
            // Catat ke barang_reject
            $nama_esc  = mysqli_real_escape_string($conn, $barang['nama']);
            $sku_esc   = mysqli_real_escape_string($conn, $barang['sku'] ?? '');
            $alasan    = mysqli_real_escape_string($conn, $_POST['alasan_reject'] ?? 'Rusak di gudang');
            $conn->query("INSERT INTO barang_reject (nama_barang, sku, jumlah, sumber, alasan) VALUES ('$nama_esc','$sku_esc',$jumlah_reject,'inventaris','$alasan')");
            $toast     = "$jumlah_reject unit ditandai rusak.\nStok dikurangi dari inventaris.";
            $toastType = 'reject';
        }
    }
}

// ================= ON HOLD =================
if(isset($_POST['onhold_barang'])){
    $id = intval($_POST['id']);
    $conn->query("UPDATE barang SET status = 'On Hold' WHERE id=$id");
    $toast     = "Barang dipindahkan ke status On Hold.";
    $toastType = 'success';
}

// ================= HAPUS =================
if(isset($_GET['hapus'])){
    $id = intval($_GET['hapus']);
    $conn->query("DELETE FROM barang WHERE id=$id");
    header("Location: inventaris.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="dicoding:email" content="refifjrn14@gmail.com">
    <title>SmartInventory | Manajemen Inventaris</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/inventaris.css">
</head>
<body>

<div class="wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
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
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                        </svg>
                    </span>
                    Dasbor
                </a>
            </li>
            <li class="nav-item active">
                <a href="inventaris.php">
                    <span class="nav-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                        </svg>
                    </span>
                    Inventaris
                </a>
            </li>
            <li class="nav-item">
                <a href="barang_masuk.php">
                    <span class="nav-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/>
                            <path d="M20.88 18.09A5 5 0 0018 9h-1.26A8 8 0 103 16.29"/>
                        </svg>
                    </span>
                    Barang Masuk
                </a>
            </li>
            <li class="nav-item">
                <a href="barang_keluar.php">
                    <span class="nav-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="16 17 20 13 16 9"/><line x1="20" y1="13" x2="4" y2="13"/>
                            <path d="M4 6H2m2 6H2m2 6H2"/>
                        </svg>
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

    <!-- CONTENT -->
    <div class="content">

        <div class="page-header-alt">
            <div class="header-text">
                <h1>Manajemen Inventaris</h1>
                <p>Lacak dan kelola semua barang inventaris</p>
            </div>
        </div>

        <!-- FILTER & SEARCH -->
        <div class="table-controls">
            <div class="search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" id="searchInput" placeholder="Cari berdasarkan nama atau SKU...">
            </div>
            <div class="filter-group">
                <a href="inventaris.php"                 class="filter-tab <?= ($statusFilter == '')          ? 'active' : '' ?>">Semua</a>
                <a href="inventaris.php?status=On Hold"  class="filter-tab <?= ($statusFilter == 'On Hold')   ? 'active' : '' ?>">Ditahan</a>
                <a href="inventaris.php?status=Available" class="filter-tab <?= ($statusFilter == 'Available') ? 'active' : '' ?>">Dirilis</a>
                <a href="inventaris.php?status=Reject"   class="filter-tab <?= ($statusFilter == 'Reject')    ? 'active' : '' ?>">Ditolak</a>
            </div>
            <div class="action-buttons">
                <button class="btn-outline">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filter
                </button>
                <button class="btn-outline">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Ekspor
                </button>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-container">
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Nama Barang</th>
                        <th>SKU</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Lokasi</th>
                        <th>Catatan</th>
                        <th>Dibuat</th>
                        <th>Update Terakhir</th>
                        <th style="text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                <?php
                if ($statusFilter != '') {
                    $stmt = $conn->prepare("SELECT * FROM barang WHERE status = ? ORDER BY id DESC");
                    $stmt->bind_param("s", $statusFilter);
                    $stmt->execute();
                    $data = $stmt->get_result();
                } else {
                    $data = $conn->query("SELECT * FROM barang ORDER BY id DESC");
                }

                while($row = $data->fetch_assoc()):
                    if ($row['jumlah'] <= 0) {
                        $status      = 'Habis';
                        $statusClass = 'status-red';
                    } elseif ($row['jumlah'] <= 5) {
                        $status      = 'Hampir Habis';
                        $statusClass = 'status-amber';
                    } else {
                        $status      = $row['status'];
                        $statusClass = match($status) {
                            'Available' => 'status-green',
                            'On Hold'   => 'status-amber',
                            'Reject'    => 'status-red',
                            default     => 'status-blue'
                        };
                    }
                    $namaJs = addslashes(htmlspecialchars($row['nama']));
                ?>
                <tr data-nama="<?= strtolower(htmlspecialchars($row['nama'])) ?>"
                    data-sku="brg-<?= strtolower($row['id']) ?>">
                    <td><div class="item-name"><?= htmlspecialchars($row['nama']) ?></div></td>
                    <td class="text-sku">BRG-<?= $row['id'] ?></td>
                    <td><span class="qty-mono"><?= $row['jumlah'] ?></span></td>
                    <td><span class="badge <?= $statusClass ?>"><?= $status ?></span></td>
                    <td><?= htmlspecialchars($row['lokasi'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['catatan'] ?? '-') ?></td>
                    <td class="text-date"><?= $row['created_at'] ? date('d M Y', strtotime($row['created_at'])) : '-' ?></td>
                    <td class="text-date"><?= $row['updated_at'] ? date('d M Y', strtotime($row['updated_at'])) : '-' ?></td>
                    <td>
                        <div class="action-cell">
                            <button type="button" class="btn-action btn-edit"
                                    data-id="<?= $row['id'] ?>"
                                    data-nama="<?= $namaJs ?>"
                                    data-jumlah="<?= $row['jumlah'] ?>"
                                    data-lokasi="<?= htmlspecialchars($row['lokasi'] ?? '') ?>">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </button>
                            <button type="button" class="btn-action btn-stok"
                                    data-id="<?= $row['id'] ?>"
                                    data-nama="<?= $namaJs ?>">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Stok
                            </button>
                            <button type="button" class="btn-action btn-hold"
                                    data-id="<?= $row['id'] ?>"
                                    data-nama="<?= $namaJs ?>">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"/><line x1="10" y1="15" x2="10" y2="9"/><line x1="14" y1="15" x2="14" y2="9"/></svg>
                                Tahan
                            </button>
                            <button type="button" class="btn-action btn-reject"
                                    data-id="<?= $row['id'] ?>"
                                    data-nama="<?= $namaJs ?>"
                                    data-stok="<?= $row['jumlah'] ?>">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                Reject
                            </button>
                            <a href="?hapus=<?= $row['id'] ?>"
                               onclick="return confirm('Hapus barang ini?')"
                               class="btn-action btn-hapus">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                Hapus
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div><!-- /content -->
</div><!-- /wrapper -->


<!-- MODAL — ON HOLD -->
<div id="modalHold" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <button type="button" class="modal-close" id="closeHold">&times;</button>
        <div class="modal-header">
            <div class="modal-icon" style="background:#fffbeb;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="10" y1="15" x2="10" y2="9"/><line x1="14" y1="15" x2="14" y2="9"/></svg>
            </div>
            <div>
                <h3 class="modal-title">Tahan Barang</h3>
                <p class="modal-sub" id="holdNama"></p>
            </div>
        </div>
        <div class="modal-info" style="background:#fffbeb;border-left:4px solid #f59e0b;color:#92400e;">
            ⏸️ Barang akan dipindahkan ke status <strong>On Hold</strong>.<br>
            Gunakan ini untuk barang yang perlu dicek ulang atau di-quarantine.
        </div>
        <form method="POST">
            <input type="hidden" name="id" id="holdId">
            <div class="modal-footer">
                <button type="button" id="cancelHold" class="btn-modal-cancel">Batal</button>
                <button type="submit" name="onhold_barang" class="btn-modal-confirm" style="background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 3px 10px rgba(245,158,11,0.35);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="10" y1="15" x2="10" y2="9"/><line x1="14" y1="15" x2="14" y2="9"/></svg>
                    Konfirmasi Tahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL — EDIT STOK -->
<div id="modalEdit" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <button type="button" class="modal-close" id="closeEdit">&times;</button>
        <div class="modal-header">
            <div class="modal-icon" style="background:#f0fdf4;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </div>
            <div>
                <h3 class="modal-title">Edit Stok</h3>
                <p class="modal-sub" id="editNama"></p>
            </div>
        </div>
        <div class="modal-info" style="background:#f0fdf4;border-left:4px solid #10b981;color:#065f46;">
            ✏️ Masukkan jumlah stok baru. Nilai ini akan menggantikan stok saat ini.
        </div>
        <form method="POST">
            <input type="hidden" name="id" id="editId">
            <label class="form-label">Jumlah Stok Baru</label>
            <input type="number" name="jumlah_baru" id="editInput" min="0"
                   placeholder="Masukkan jumlah..." required class="form-input">
            <label class="form-label" style="margin-top:14px;">Lokasi Barang</label>
            <input type="text" name="lokasi_baru" id="editLokasi" class="form-input"
                   placeholder="Contoh: Gudang A - Rak 1">
            <div class="modal-footer">
                <button type="button" id="cancelEdit" class="btn-modal-cancel">Batal</button>
                <button type="submit" name="edit_stok" class="btn-modal-confirm" style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 3px 10px rgba(16,185,129,0.35);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL — TAMBAH STOK -->
<div id="modalStok" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <button type="button" class="modal-close" id="closeStok">&times;</button>
        <div class="modal-header">
            <div class="modal-icon icon-blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
            </div>
            <div>
                <h3 class="modal-title">Tambah Stok</h3>
                <p class="modal-sub" id="stokNama"></p>
            </div>
        </div>
        <div class="modal-info info-blue">
            📦 Silakan masukkan jumlah stok yang ingin ditambahkan.<br>
            Pastikan jumlah sesuai dengan barang masuk.
        </div>
        <form method="POST">
            <input type="hidden" name="id" id="stokId">
            <label class="form-label">Jumlah Stok</label>
            <input type="number" name="tambah" id="stokInput" min="1"
                   placeholder="Masukkan jumlah..." required class="form-input">
            <div class="modal-footer">
                <button type="button" id="cancelStok" class="btn-modal-cancel">Batal</button>
                <button type="submit" name="tambah_stok" class="btn-modal-confirm confirm-blue">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================
     MODAL — REJECT
     ============================================================ -->
<div id="modalReject" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <button type="button" class="modal-close" id="closeReject">&times;</button>
        <div class="modal-header">
            <div class="modal-icon icon-red">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <div>
                <h3 class="modal-title">Input Reject</h3>
                <p class="modal-sub" id="rejectNama"></p>
            </div>
        </div>
        <div class="modal-info info-red">
            ⚠️ Masukkan jumlah barang yang rusak atau tidak layak pakai (reject).<br>
            Jumlah tidak boleh melebihi stok tersedia.
        </div>
        <p class="stok-info">Stok tersedia: <strong id="stokTersedia"></strong></p>
        <form method="POST">
            <input type="hidden" name="id" id="rejectId">
            <label class="form-label">Jumlah Reject</label>
            <input type="number" name="jumlah_reject" id="rejectInput" min="1"
                   placeholder="Masukkan jumlah reject..." required class="form-input">
            <label class="form-label" style="margin-top:14px;">Alasan Reject</label>
            <input type="text" name="alasan_reject" class="form-input" placeholder="Contoh: Rusak, Pecah, Expired..." value="Rusak di gudang">
            <div class="modal-footer">
                <button type="button" id="cancelReject" class="btn-modal-cancel">Batal</button>
                <button type="submit" name="reject_barang" class="btn-modal-confirm confirm-red">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    Konfirmasi Reject
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================
     TOAST
     ============================================================ -->
<?php if($toast): ?>
<div id="toastBox" class="toast toast-<?= $toastType ?>">
    <strong class="toast-title">
        <?php if($toastType === 'error'):      ?>❌ Gagal
        <?php elseif($toastType === 'reject'): ?>⚠️ Reject Berhasil
        <?php else:                            ?>✅ Berhasil
        <?php endif; ?>
    </strong>
    <?= nl2br(htmlspecialchars($toast)) ?>
    <?php if($toastType === 'reject'): ?>
    <div class="toast-ai">
        💡 <strong>Saran AI:</strong> Periksa barang secara berkala untuk mengurangi jumlah reject dan meningkatkan kualitas stok.
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ============================================================
     JAVASCRIPT — pakai addEventListener, bukan onclick
     ============================================================ -->
<script>
(function () {

    /* ---- Helper ---- */
    function openModal(id) {
        var el = document.getElementById(id);
        el.style.display = 'flex';
    }

    function closeModal(id) {
        var el = document.getElementById(id);
        el.style.display = 'none';
    }

    /* ---- Hold buttons ---- */
    document.querySelectorAll('.btn-hold').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('holdId').value          = this.dataset.id;
            document.getElementById('holdNama').textContent  = this.dataset.nama;
            openModal('modalHold');
        });
    });

    document.getElementById('closeHold').addEventListener('click',  function () { closeModal('modalHold'); });
    document.getElementById('cancelHold').addEventListener('click', function () { closeModal('modalHold'); });

    /* ---- Edit buttons ---- */
    document.querySelectorAll('.btn-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('editId').value          = this.dataset.id;
            document.getElementById('editNama').textContent  = this.dataset.nama;
            document.getElementById('editInput').value       = this.dataset.jumlah;
            document.getElementById('editLokasi').value      = this.dataset.lokasi || '';
            openModal('modalEdit');
            document.getElementById('editInput').focus();
        });
    });

    document.getElementById('closeEdit').addEventListener('click',  function () { closeModal('modalEdit'); });
    document.getElementById('cancelEdit').addEventListener('click', function () { closeModal('modalEdit'); });

    /* ---- Stok buttons ---- */
    document.querySelectorAll('.btn-stok').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('stokId').value         = this.dataset.id;
            document.getElementById('stokNama').textContent = this.dataset.nama;
            document.getElementById('stokInput').value      = '';
            openModal('modalStok');
            document.getElementById('stokInput').focus();
        });
    });

    document.getElementById('closeStok').addEventListener('click',  function () { closeModal('modalStok'); });
    document.getElementById('cancelStok').addEventListener('click', function () { closeModal('modalStok'); });

    /* ---- Reject buttons ---- */
    document.querySelectorAll('.btn-reject').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('rejectId').value           = this.dataset.id;
            document.getElementById('rejectNama').textContent   = this.dataset.nama;
            document.getElementById('stokTersedia').textContent = this.dataset.stok + ' unit';
            document.getElementById('rejectInput').value        = '';
            document.getElementById('rejectInput').max          = this.dataset.stok;
            openModal('modalReject');
            document.getElementById('rejectInput').focus();
        });
    });

    document.getElementById('closeReject').addEventListener('click',  function () { closeModal('modalReject'); });
    document.getElementById('cancelReject').addEventListener('click', function () { closeModal('modalReject'); });

    /* ---- Klik luar modal ---- */
    ['modalHold', 'modalEdit', 'modalStok', 'modalReject'].forEach(function (id) {
        document.getElementById(id).addEventListener('click', function (e) {
            if (e.target === this) closeModal(id);
        });
    });

    /* ---- ESC tutup modal ---- */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModal('modalHold');
            closeModal('modalEdit');
            closeModal('modalStok');
            closeModal('modalReject');
        }
    });

    /* ---- Auto-hide toast ---- */
    var toast = document.getElementById('toastBox');
    if (toast) {
        setTimeout(function () {
            toast.style.opacity   = '0';
            toast.style.transform = 'translateY(16px)';
            setTimeout(function () { if(toast.parentNode) toast.parentNode.removeChild(toast); }, 500);
        }, 4500);
    }

    /* ---- Live search ---- */
    document.getElementById('searchInput').addEventListener('input', function () {
        var q = this.value.toLowerCase();
        document.querySelectorAll('#tableBody tr').forEach(function (row) {
            var match = (row.dataset.nama || '').includes(q) || (row.dataset.sku || '').includes(q);
            row.style.display = match ? '' : 'none';
        });
    });

})();
</script>

</body>
</html>