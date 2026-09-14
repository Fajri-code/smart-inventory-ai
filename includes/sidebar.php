<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
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
        <li class="nav-item <?= ($currentPage == 'index.php') ? 'active' : '' ?>">
            <a href="index.php">
                <span class="nav-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </span>
                Dasbor
            </a>
        </li>
        <li class="nav-item <?= ($currentPage == 'inventaris.php') ? 'active' : '' ?>">
            <a href="inventaris.php">
                <span class="nav-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                </span>
                Inventaris
            </a>
        </li>
        <li class="nav-item <?= ($currentPage == 'barang_masuk.php') ? 'active' : '' ?>">
            <a href="barang_masuk.php">
                <span class="nav-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.88 18.09A5 5 0 0018 9h-1.26A8 8 0 103 16.29"/></svg>
                </span>
                Barang Masuk
            </a>
        </li>
        <li class="nav-item <?= ($currentPage == 'barang_keluar.php') ? 'active' : '' ?>">
            <a href="barang_keluar.php">
                <span class="nav-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 17 20 13 16 9"/><line x1="20" y1="13" x2="4" y2="13"/><path d="M4 6H2m2 6H2m2 6H2"/></svg>
                </span>
                Barang Keluar
            </a>
        </li>
         <li class="nav-item <?= ($currentPage == 'barang_reject.php') ? 'active' : '' ?>">
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

