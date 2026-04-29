<?php
// Ambil barang dengan stok menipis (≤ 5)
$notif_stok = $conn->query("SELECT nama, jumlah FROM barang WHERE jumlah <= 5 AND jumlah > 0 ORDER BY jumlah ASC LIMIT 5");
$notif_habis = $conn->query("SELECT COUNT(*) as t FROM barang WHERE jumlah <= 0")->fetch_assoc()['t'];
$notif_count = $notif_stok->num_rows + ($notif_habis > 0 ? 1 : 0);
?>
<div style="position:relative;">
    <button class="topbar-notif-btn" onclick="toggleNotif()" style="position:relative;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 01-3.46 0"/>
        </svg>
        <?php if($notif_count > 0): ?>
        <span class="notif-badge-count"><?= $notif_count ?></span>
        <?php endif; ?>
    </button>

    <div id="notifDropdown" style="display:none; position:absolute; top:calc(100% + 10px); right:0; width:300px; background:white; border:1px solid #e2e8f0; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.1); z-index:999; overflow:hidden;">
        <div style="padding:14px 16px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:13px; font-weight:700; color:#0f172a;">Notifikasi Stok</span>
            <?php if($notif_count > 0): ?>
            <span style="background:#fef2f2; color:#ef4444; font-size:11px; font-weight:700; padding:2px 8px; border-radius:20px;"><?= $notif_count ?> peringatan</span>
            <?php endif; ?>
        </div>
        <div style="max-height:280px; overflow-y:auto;">
            <?php if($notif_habis > 0): ?>
            <div style="display:flex; align-items:center; gap:12px; padding:12px 16px; border-bottom:1px solid #f8fafc; background:#fef2f2;">
                <div style="width:36px;height:36px;background:#fee2e2;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#991b1b;"><?= $notif_habis ?> barang habis</div>
                    <div style="font-size:11px;color:#ef4444;">Segera lakukan restock</div>
                </div>
            </div>
            <?php endif; ?>

            <?php if($notif_stok->num_rows > 0): ?>
                <?php while($n = $notif_stok->fetch_assoc()): ?>
                <div style="display:flex; align-items:center; gap:12px; padding:12px 16px; border-bottom:1px solid #f8fafc;">
                    <div style="width:36px;height:36px;background:#fffbeb;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:600;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($n['nama']) ?></div>
                        <div style="font-size:11px;color:#f59e0b;">Sisa stok: <strong><?= $n['jumlah'] ?> unit</strong></div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>

            <?php if($notif_count === 0): ?>
            <div style="padding:24px;text-align:center;color:#94a3b8;font-size:13px;">
                ✅ Semua stok dalam kondisi aman
            </div>
            <?php endif; ?>
        </div>
        <div style="padding:10px 16px; border-top:1px solid #f1f5f9; text-align:center;">
            <a href="inventaris.php" style="font-size:12px;color:#3b82f6;font-weight:600;text-decoration:none;">Lihat Inventaris →</a>
        </div>
    </div>
</div>

<script>
function toggleNotif() {
    const d = document.getElementById('notifDropdown');
    d.style.display = d.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
    const btn = document.querySelector('.topbar-notif-btn');
    const dd  = document.getElementById('notifDropdown');
    if(dd && btn && !btn.contains(e.target) && !dd.contains(e.target)) {
        dd.style.display = 'none';
    }
});
</script>
