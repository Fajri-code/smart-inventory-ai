<?php
include 'config/koneksi.php';

if(isset($_GET['id']) && isset($_GET['status'])) {

    $id     = mysqli_real_escape_string($conn, $_GET['id']);
    $status = $_GET['status'];

    if(!in_array($status, ['disetujui','ditolak'])) {
        header("Location: barang_masuk.php"); exit;
    }

    // Ambil data dulu sebelum update
    $ambil = mysqli_query($conn, "SELECT * FROM barang_masuk WHERE no_penerimaan='$id'");
    $data  = mysqli_fetch_assoc($ambil);

    if(!$data) { die("[DEBUG] Data tidak ditemukan. ID: " . htmlspecialchars($id)); }

    // Update status barang_masuk
    $upd = mysqli_query($conn, "UPDATE barang_masuk SET status='$status' WHERE no_penerimaan='$id'");
    if(!$upd) { die("[DEBUG] Gagal update status: " . mysqli_error($conn)); }

    if($status === 'disetujui') {
        $nama         = mysqli_real_escape_string($conn, $data['nama_barang']);
        $sku          = mysqli_real_escape_string($conn, $data['sku']);
        $jumlah       = (int)$data['jumlah'];
        $reject       = (int)$data['jumlah_reject'];
        $jumlah_bagus = max(0, $jumlah - $reject);

        $sku_val = ($sku !== '') ? "'$sku'" : 'NULL';
        $lokasi  = mysqli_real_escape_string($conn, $data['lokasi'] ?? '');
        $q = mysqli_query($conn, "
            INSERT INTO barang (nama, sku, jumlah, jumlah_baik, jumlah_reject, status, lokasi)
            VALUES ('$nama', $sku_val, $jumlah_bagus, $jumlah_bagus, $reject, 'Available', '$lokasi')
        ");
        if($reject > 0) {
            $conn->query("INSERT INTO barang_reject (nama_barang, sku, jumlah, sumber, alasan) VALUES ('$nama', $sku_val, $reject, 'barang_masuk', 'Reject saat penerimaan')");
        }
        if(!$q) { die("[DEBUG] Gagal INSERT: " . mysqli_error($conn)); }

    } elseif($status === 'ditolak') {
        $nama    = mysqli_real_escape_string($conn, $data['nama_barang']);
        $sku     = mysqli_real_escape_string($conn, $data['sku']);
        $jumlah  = (int)$data['jumlah'];
        $sku_val = ($sku !== '') ? "'$sku'" : 'NULL';
        // Seluruh barang dicatat ke reject, tidak ada yang masuk inventaris
        $conn->query("INSERT INTO barang_reject (nama_barang, sku, jumlah, sumber, alasan) VALUES ('$nama', $sku_val, $jumlah, 'barang_masuk', 'Pengiriman ditolak')");
    }

    header("Location: barang_masuk.php");
    exit;
}

$total        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang_masuk"))['total'];
$menunggu     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang_masuk WHERE status='menunggu'"))['total'];
$disetujui    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang_masuk WHERE status='disetujui'"))['total'];
$ditolak      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang_masuk WHERE status='ditolak'"))['total'];
$reject_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah_reject) as total FROM barang_masuk"))['total'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $no        = $_POST['no_penerimaan'];
    $nama      = $_POST['nama_barang'];
    $sku       = $_POST['sku'];
    $supplier  = $_POST['supplier'];
    $jumlah    = $_POST['jumlah'];
    $reject    = $_POST['jumlah_reject'];
    $tanggal   = $_POST['tanggal_terima'];
    $inspector = $_POST['inspector'];
    $status    = "menunggu";
    $catatan   = $_POST['catatan'];

    $stmt = $conn->prepare("INSERT INTO barang_masuk 
(no_penerimaan, nama_barang, sku, supplier, jumlah, jumlah_reject, tanggal_terima, inspector, status, catatan, lokasi)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if(!$stmt){ die("Prepare gagal: " . $conn->error); }

    $lokasi    = $_POST['lokasi'] ?? '';
    $stmt->bind_param("ssssiiissss", $no, $nama, $sku, $supplier, $jumlah, $reject, $tanggal, $inspector, $status, $catatan, $lokasi);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: " . $stmt->error;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="dicoding:email" content="refifjrn14@gmail.com">
    <title>SmartInventory — Barang Masuk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/barang_masuk.css">
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
                    <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>
                    Dasbor
                </a>
            </li>
            <li class="nav-item">
                <a href="inventaris.php">
                    <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg></span>
                    Inventaris
                </a>
            </li>
            <li class="nav-item active">
                <a href="barang_masuk.php">
                    <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.88 18.09A5 5 0 0018 9h-1.26A8 8 0 103 16.29"/></svg></span>
                    Barang Masuk
                </a>
            </li>
            <li class="nav-item">
                <a href="barang_keluar.php">
                    <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 17 20 13 16 9"/><line x1="20" y1="13" x2="4" y2="13"/><path d="M4 6H2m2 6H2m2 6H2"/></svg></span>
                    Barang Keluar
                </a>
            </li>
            <li class="nav-item">
                <a href="barang_reject.php">
                    <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></span>
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

    <!-- MAIN CONTENT -->
    <main class="main" style="padding-top:0;">
        <div class="main-inner">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Barang Masuk</h1>
                    <p class="page-subtitle">Kelola penerimaan dan inspeksi barang masuk</p>
                </div>
                <div class="page-actions">
                    <button class="btn btn-primary" onclick="openModal()">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Input Barang Masuk
                    </button>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="stat-grid">
                <div class="stat-card blue">
                    <div class="stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $total ?></div>
                        <div class="stat-label">Total Penerimaan</div>
                    </div>
                </div>
                <div class="stat-card amber">
                    <div class="stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $menunggu ?></div>
                        <div class="stat-label">Menunggu Inspeksi</div>
                    </div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $disetujui ?></div>
                        <div class="stat-label">Disetujui</div>
                    </div>
                </div>
                <div class="stat-card red">
                    <div class="stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $ditolak ?></div>
                        <div class="stat-label">Ditolak</div>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-tabs">
                    <button class="filter-tab active" onclick="filterTab(this, 'semua')">Semua</button>
                    <button class="filter-tab" onclick="filterTab(this, 'menunggu')">Menunggu Inspeksi</button>
                    <button class="filter-tab" onclick="filterTab(this, 'disetujui')">Disetujui</button>
                    <button class="filter-tab" onclick="filterTab(this, 'ditolak')">Ditolak</button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-card">
                <div class="table-wrap">
                    <table id="mainTable">
                        <thead>
                            <tr>
                                <th>No. Penerimaan</th>
                                <th>Nama Barang</th>
                                <th>SKU</th>
                                <th>Supplier</th>
                                <th>Jumlah</th>
                                <th>Reject</th>
                                <th>Jumlah Bagus</th>
                                <th>Tanggal Terima</th>
                                <th>Inspector</th>
                                <th>Status</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
$result = mysqli_query($conn, "SELECT * FROM barang_masuk ORDER BY tanggal_terima DESC");
while($row = mysqli_fetch_assoc($result)) {
    if($row['status'] == 'menunggu') {
        $badge = '<span class="badge badge-amber">Menunggu</span>';
    } elseif($row['status'] == 'disetujui') {
        $badge = '<span class="badge badge-green">Disetujui</span>';
    } else {
        $badge = '<span class="badge badge-red">Ditolak</span>';
    }

    if($row['status'] == 'menunggu') {
        $id_row = urlencode($row['no_penerimaan']);
        $aksi = '<div class="action-btns">
                    <a href="barang_masuk.php?id=' . $id_row . '&status=disetujui" class="btn-approve">✓ Approve</a>
                    <a href="barang_masuk.php?id=' . $id_row . '&status=ditolak" class="btn-reject">✗ Reject</a>
                 </div>';
    } else {
        $aksi = '<span style="color:#94a3b8;font-size:13px;">—</span>';
    }
?>
<tr data-status="<?= $row['status'] ?>">
    <td><?= htmlspecialchars($row['no_penerimaan']) ?></td>
    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
    <td><?= htmlspecialchars($row['sku']) ?></td>
    <td><?= htmlspecialchars($row['supplier']) ?></td>
    <td><?= $row['jumlah'] ?></td>
    <td><?= $row['jumlah_reject'] ?></td>
    <td><?= max(0, $row['jumlah'] - $row['jumlah_reject']) ?></td>
    <td><?= $row['tanggal_terima'] ?></td>
    <td><?= htmlspecialchars($row['inspector']) ?></td>
    <td><?= $badge ?></td>
    <td><?= htmlspecialchars($row['catatan']) ?></td>
    <td><?= $aksi ?></td>
</tr>
<?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

</div>

<script>
    function filterTab(el, status) {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
        document.querySelectorAll('#mainTable tbody tr').forEach(row => {
            row.style.display = (status === 'semua' || row.dataset.status === status) ? '' : 'none';
        });
    }

    function openModal() {
        document.getElementById('modalOverlay').classList.add('active');
        document.body.style.overflow = 'hidden';
        const today = new Date();
        const pad = n => String(n).padStart(2,'0');
        const dateStr = today.getFullYear() + pad(today.getMonth()+1) + pad(today.getDate());
        document.getElementById('noPenerimaan').value = 'IN-' + dateStr + '-' + String(Math.floor(Math.random()*900)+100);
        document.getElementById('tanggalTerima').value = today.toISOString().split('T')[0];
    }

    function closeModal() {
        document.getElementById('modalOverlay').classList.remove('active');
        document.body.style.overflow = '';
    }

    function closeModalOutside(e) {
        if (e.target === document.getElementById('modalOverlay')) closeModal();
    }

    function simpanData() {
        const nama   = document.getElementById('namaBarang').value.trim();
        const jumlah = document.getElementById('jumlah').value;
        if (!nama || !jumlah) { alert('Nama Barang dan Jumlah wajib diisi!'); return; }

        const data = new FormData();
        data.append("no_penerimaan", document.getElementById('noPenerimaan').value);
        data.append("nama_barang",   nama);
        data.append("sku",           document.getElementById('sku').value);
        data.append("supplier",      document.getElementById('supplier').value);
        data.append("jumlah",        jumlah);
        data.append("jumlah_reject", document.getElementById('jumlah_reject').value);
        data.append("tanggal_terima",document.getElementById('tanggalTerima').value);
        data.append("inspector",     document.getElementById('inspector').value);
        data.append("lokasi",         document.getElementById('lokasi').value);
        data.append("catatan",        document.getElementById('catatan').value);

        fetch("barang_masuk.php", { method: "POST", body: data })
        .then(res => res.text())
        .then(res => {
            if(res === "success") { alert("Data berhasil disimpan!"); location.reload(); }
            else { alert("Gagal simpan: " + res); }
        });
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>

<!-- MODAL INPUT BARANG MASUK -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModalOutside(event)">
    <div class="modal">
        <div class="modal-header">
            <div>
                <h2 class="modal-title">Form Input Barang Masuk</h2>
                <p class="modal-subtitle">Isi data penerimaan barang baru</p>
            </div>
            <button class="modal-close" onclick="closeModal()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nomor Penerimaan</label>
                    <input type="text" class="form-input" id="noPenerimaan">
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Barang</label>
                    <input type="text" class="form-input" placeholder="Masukkan nama barang" id="namaBarang">
                </div>
                <div class="form-group">
                    <label class="form-label">SKU</label>
                    <input type="text" class="form-input" placeholder="XX-XXX" id="sku">
                </div>
                <div class="form-group">
                    <label class="form-label">Jumlah</label>
                    <input type="number" class="form-input" placeholder="0" id="jumlah" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Jumlah Reject (Rusak)</label>
                    <input type="number" class="form-input" value="0" id="jumlah_reject" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Supplier</label>
                    <input type="text" class="form-input" placeholder="Nama supplier" id="supplier">
                </div>
                <div class="form-group">
                    <label class="form-label">Inspector</label>
                    <input type="text" class="form-input" placeholder="Nama inspector" id="inspector">
                </div>
                <div class="form-group">
                    <label class="form-label">Lokasi Penyimpanan</label>
                    <input type="text" class="form-input" placeholder="Contoh: Gudang A - Rak 1" id="lokasi">
                </div>
                <div class="form-group form-group-full">
                    <label class="form-label">Tanggal Terima</label>
                    <input type="date" class="form-input" id="tanggalTerima">
                </div>
                <div class="form-group form-group-full">
                    <label class="form-label">Catatan</label>
                    <textarea class="form-input form-textarea" placeholder="Catatan tambahan..." id="catatan"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal()">Batal</button>
            <button class="btn btn-primary" onclick="simpanData()">Simpan Data</button>
        </div>
    </div>
</div>

</body>
</html>
