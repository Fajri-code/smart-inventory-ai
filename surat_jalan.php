<?php
include __DIR__ . '/config/koneksi.php';

$id   = intval($_GET['id'] ?? 0);
$data = $conn->query("SELECT * FROM barang_keluar WHERE id=$id")->fetch_assoc();

if(!$data) { die("Data tidak ditemukan."); }

// Update status ke 'kirim' saat surat jalan dibuka
if($data['status'] === 'siap') {
    $conn->query("UPDATE barang_keluar SET status='kirim' WHERE id=$id");
}

$no_surat  = 'SJ-' . date('Ymd') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
$tanggal   = date('d F Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan <?= $no_surat ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Arial', sans-serif;
            background: #f1f5f9;
            display: flex;
            justify-content: center;
            padding: 40px 20px;
        }

        .paper {
            background: white;
            width: 794px;
            min-height: 1123px;
            padding: 48px 56px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.1);
            border-radius: 4px;
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 24px;
            border-bottom: 3px solid #1e293b;
            margin-bottom: 28px;
        }

        .company-name {
            font-size: 22px;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: -0.02em;
        }

        .company-sub {
            font-size: 12px;
            color: #64748b;
            margin-top: 3px;
        }

        .doc-title {
            text-align: right;
        }

        .doc-title h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .doc-title .no-surat {
            font-size: 13px;
            color: #3b82f6;
            font-weight: 600;
            margin-top: 4px;
            font-family: 'Courier New', monospace;
        }

        /* INFO GRID */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 32px;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px 18px;
        }

        .info-box-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            margin-bottom: 10px;
        }

        .info-row {
            display: flex;
            gap: 8px;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .info-row .label { color: #64748b; min-width: 90px; }
        .info-row .value { color: #0f172a; font-weight: 600; }

        /* TABLE */
        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 32px;
        }

        thead th {
            background: #1e293b;
            color: white;
            padding: 11px 14px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.03em;
        }

        thead th:last-child { text-align: center; }

        tbody td {
            padding: 12px 14px;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
            color: #0f172a;
        }

        tbody tr:last-child td { border-bottom: 2px solid #e2e8f0; }
        tbody td:last-child { text-align: center; font-weight: 700; }

        .sku-cell { font-family: 'Courier New', monospace; font-size: 12px; color: #64748b; }

        /* SIGNATURE */
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 40px;
        }

        .sign-box {
            text-align: center;
        }

        .sign-label {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 60px;
        }

        .sign-line {
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            font-size: 12px;
            color: #64748b;
        }

        /* FOOTER */
        .footer {
            margin-top: 48px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-note { font-size: 11px; color: #94a3b8; }
        .footer-brand { font-size: 11px; font-weight: 700; color: #1e293b; }

        /* PRINT BUTTON */
        .print-bar {
            position: fixed;
            bottom: 28px;
            right: 28px;
            display: flex;
            gap: 10px;
            z-index: 999;
        }

        .btn-print {
            background: #1e293b;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.2);
            transition: background 0.15s;
        }

        .btn-print:hover { background: #0f172a; }

        .btn-back {
            background: white;
            color: #475569;
            border: 1.5px solid #e2e8f0;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        }

        @media print {
            body { background: white; padding: 0; }
            .paper { box-shadow: none; padding: 32px 40px; }
            .print-bar { display: none; }
        }
    </style>
</head>
<body>

<div class="paper">

    <!-- HEADER -->
    <div class="header">
        <div>
            <div class="company-name">SMARTINVENTORY</div>
            <div class="company-sub">Sistem Manajemen Inventaris Manufaktur</div>
        </div>
        <div class="doc-title">
            <h2>Surat Jalan</h2>
            <div class="no-surat"><?= $no_surat ?></div>
        </div>
    </div>

    <!-- INFO -->
    <div class="info-grid">
        <div class="info-box">
            <div class="info-box-title">Informasi Pengiriman</div>
            <div class="info-row"><span class="label">Tanggal</span><span class="value"><?= $tanggal ?></span></div>
            <div class="info-row"><span class="label">No. Surat</span><span class="value"><?= $no_surat ?></span></div>
            <div class="info-row"><span class="label">Status</span><span class="value">Dalam Pengiriman</span></div>
        </div>
        <div class="info-box">
            <div class="info-box-title">Tujuan Pengiriman</div>
            <div class="info-row"><span class="label">Tujuan</span><span class="value"><?= htmlspecialchars($data['tujuan']) ?></span></div>
            <div class="info-row"><span class="label">Pengirim</span><span class="value">Gudang Pusat</span></div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="section-title">Detail Barang</div>
    <table>
        <thead>
            <tr>
                <th style="width:40px">No</th>
                <th>Nama Barang</th>
                <th>SKU</th>
                <th style="width:100px">Jumlah</th>
                <th style="width:120px">Satuan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><?= htmlspecialchars($data['nama_barang']) ?></td>
                <td class="sku-cell"><?= htmlspecialchars($data['sku'] ?? '-') ?></td>
                <td><?= $data['jumlah'] ?></td>
                <td>Unit</td>
            </tr>
            <!-- Baris kosong untuk tanda tangan -->
            <tr><td colspan="5" style="height:32px;border-bottom:none;"></td></tr>
        </tbody>
    </table>

    <!-- CATATAN -->
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:14px 18px;margin-bottom:32px;">
        <div style="font-size:11px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">Catatan</div>
        <div style="font-size:13px;color:#78350f;">Mohon periksa kondisi dan jumlah barang sebelum menandatangani surat jalan ini. Tanda tangan merupakan bukti penerimaan barang dalam kondisi baik.</div>
    </div>

    <!-- SIGNATURE -->
    <div class="signature-grid">
        <div class="sign-box">
            <div class="sign-label">Dibuat Oleh</div>
            <div class="sign-line">( _________________ )</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Admin Gudang</div>
        </div>
        <div class="sign-box">
            <div class="sign-label">Disetujui Oleh</div>
            <div class="sign-line">( _________________ )</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Supervisor</div>
        </div>
        <div class="sign-box">
            <div class="sign-label">Diterima Oleh</div>
            <div class="sign-line">( _________________ )</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Penerima</div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <div class="footer-note">Dokumen ini dicetak secara otomatis oleh sistem SmartInventory · <?= date('d/m/Y H:i') ?></div>
        <div class="footer-brand">SMARTINVENTORY</div>
    </div>

</div>

<!-- PRINT BAR -->
<div class="print-bar">
    <a href="barang_keluar.php" class="btn-back">
        ← Kembali
    </a>
    <button class="btn-print" onclick="window.print()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Cetak Surat Jalan
    </button>
</div>

</body>
</html>
