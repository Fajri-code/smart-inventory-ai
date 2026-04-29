<?php
session_start();
include __DIR__ . '/config/koneksi.php';

$error = '';

if(isset($_POST['login'])){
    $u = $_POST['username'];
    $p = $_POST['password'];

    $cek = $conn->query("SELECT * FROM user WHERE username='$u' AND password='$p'");

    if($cek->num_rows > 0){
        $_SESSION['login'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="dicoding:email" content="refifjrn14@gmail.com">
    <title>SmartInventory — Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            -webkit-font-smoothing: antialiased;
        }

        /* ---- LEFT PANEL ---- */
        .left {
            width: 480px;
            flex-shrink: 0;
            background: linear-gradient(160deg, #0f172a 0%, #1e3a5f 50%, #1e293b 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px;
            position: relative;
            overflow: hidden;
        }

        .left::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 320px; height: 320px;
            background: radial-gradient(circle, rgba(59,130,246,0.2) 0%, transparent 70%);
            pointer-events: none;
        }

        .left::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -40px;
            width: 280px; height: 280px;
            background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            z-index: 1;
        }

        .brand-icon {
            width: 42px; height: 42px;
            background: rgba(59,130,246,0.2);
            border: 1px solid rgba(59,130,246,0.3);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: #93c5fd;
        }

        .brand-name {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: white;
        }

        .brand-sub {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        .left-content {
            position: relative;
            z-index: 1;
        }

        .left-content h1 {
            font-size: 32px;
            font-weight: 700;
            color: white;
            line-height: 1.3;
            letter-spacing: -0.02em;
            margin-bottom: 16px;
        }

        .left-content h1 span {
            color: #93c5fd;
        }

        .left-content p {
            font-size: 14px;
            color: #94a3b8;
            line-height: 1.7;
            margin-bottom: 32px;
        }

        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13.5px;
            color: #cbd5e1;
        }

        .feature-dot {
            width: 8px; height: 8px;
            background: #3b82f6;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .left-footer {
            font-size: 12px;
            color: #475569;
            position: relative;
            z-index: 1;
        }

        /* ---- RIGHT PANEL ---- */
        .right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .login-box {
            width: 100%;
            max-width: 420px;
        }

        .login-header {
            margin-bottom: 36px;
        }

        .login-header h2 {
            font-size: 26px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .login-header p {
            font-size: 14px;
            color: #94a3b8;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: #0f172a;
            background: white;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .form-input::placeholder { color: #cbd5e1; }

        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }

        .error-msg {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #ef4444;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 13px;
            color: #991b1b;
            margin-bottom: 20px;
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: box-shadow 0.2s, transform 0.15s;
            box-shadow: 0 4px 14px rgba(59,130,246,0.35);
            margin-top: 4px;
        }

        .btn-login:hover {
            box-shadow: 0 6px 20px rgba(59,130,246,0.45);
            transform: translateY(-1px);
        }

        .btn-login:active { transform: translateY(0); }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
            color: #cbd5e1;
            font-size: 12px;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .login-footer {
            margin-top: 32px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }

        /* ---- RESPONSIVE ---- */
        @media (max-width: 768px) {
            .left { display: none; }
            .right { padding: 24px; }
        }
    </style>
</head>
<body>

    <!-- LEFT -->
    <div class="left">
        <div class="brand">
            <div class="brand-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
            <div>
                <div class="brand-name">SMARTINVENTORY</div>
                <div class="brand-sub">Operasi Manufaktur</div>
            </div>
        </div>

        <div class="left-content">
            <h1>Kelola Inventaris<br>dengan <span>Lebih Cerdas</span></h1>
            <p>Platform manajemen inventaris manufaktur yang terintegrasi dengan kecerdasan buatan untuk optimasi stok real-time.</p>
            <div class="feature-list">
                <div class="feature-item"><span class="feature-dot"></span> Monitoring stok real-time</div>
                <div class="feature-item"><span class="feature-dot"></span> Manajemen barang masuk & keluar</div>
                <div class="feature-item"><span class="feature-dot"></span> Laporan & analitik otomatis</div>
                <div class="feature-item"><span class="feature-dot"></span> Surat jalan digital</div>
            </div>
        </div>

        <div class="left-footer">
            &copy; <?= date('Y') ?> SmartInventory. All rights reserved.
        </div>
    </div>

    <!-- RIGHT -->
    <div class="right">
        <div class="login-box">

            <div class="login-header">
                <h2>Selamat Datang 👋</h2>
                <p>Masuk ke akun Anda untuk melanjutkan</p>
            </div>

            <?php if($error): ?>
            <div class="error-msg">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <input type="text" name="username" class="form-input" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        </span>
                        <input type="password" name="password" class="form-input" placeholder="Masukkan password" required>
                    </div>
                </div>

                <button type="submit" name="login" class="btn-login">
                    Masuk ke Dashboard
                </button>
            </form>

            <div class="login-footer">
                SmartInventory v1.0 &mdash; Sistem Manajemen Inventaris Manufaktur
            </div>

        </div>
    </div>

</body>
</html>
