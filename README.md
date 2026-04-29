# 📦 SmartInventory

> Sistem Manajemen Inventaris Berbasis Web untuk Perusahaan Manufaktur

![PHP](https://img.shields.io/badge/PHP-8.0-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=flat&logo=javascript&logoColor=black)
![License](https://img.shields.io/badge/License-MIT-green?style=flat)

---

## 🚀 Tentang Proyek

**SmartInventory** adalah sistem manajemen inventaris berbasis web yang dirancang khusus untuk perusahaan manufaktur. Sistem ini mengintegrasikan seluruh alur pergerakan barang mulai dari penerimaan, penyimpanan, hingga pengiriman dalam satu platform terpadu dengan antarmuka yang modern dan mudah digunakan.

### 🎯 Problem yang Diselesaikan
- Pencatatan stok manual yang rawan human error
- Tidak ada visibilitas real-time kondisi gudang
- Barang reject tidak tercatat dengan baik
- Surat jalan ditulis tangan dan mudah hilang
- Tidak ada notifikasi ketika stok hampir habis

---

## ✨ Fitur Utama

| Fitur | Deskripsi |
|-------|-----------|
| 📊 **Dashboard Real-time** | Statistik inventaris, grafik tren 6 bulan, distribusi status stok |
| 📥 **Barang Masuk** | Input penerimaan dengan alur inspeksi Approve/Reject |
| 📋 **Inventaris** | Kelola stok, lokasi penyimpanan, dan status barang |
| 📤 **Barang Keluar** | Input pengiriman dengan tracking status |
| 🧾 **Surat Jalan Digital** | Generate & cetak surat jalan otomatis |
| ❌ **Laporan Reject** | Rekap barang reject dari penerimaan dan gudang |
| 🔔 **Notifikasi Stok** | Alert otomatis ketika stok mencapai batas minimum |
| 🔍 **Global Search** | Pencarian real-time di seluruh data sistem |
| 🤖 **AI Insight** | Saran otomasi berbasis kondisi stok |

---

## 🛠️ Teknologi

- **Backend:** PHP 8 (Native)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Server:** Apache (XAMPP)
- **Chart:** Chart.js
- **Font:** DM Sans, Space Mono (Google Fonts)

---

## ⚙️ Cara Install

### Prasyarat
- XAMPP (PHP 8.0+, MySQL, Apache)
- Browser modern (Chrome, Firefox, Edge)

### Langkah Instalasi

**1. Clone repository**
```bash
git clone https://github.com/Fajri-code/smart-inventory-ai.git
```

**2. Pindahkan ke folder XAMPP**
```bash
# Pindahkan folder ke:
C:\xampp\htdocs\smart-inventory-ai
```

**3. Import database**
- Buka phpMyAdmin → `http://localhost/phpmyadmin`
- Buat database baru: `inventory_ai`
- Import file SQL dari folder `database/`

**4. Konfigurasi koneksi**
```php
// config/koneksi.php
$conn = new mysqli("localhost", "root", "", "inventory_ai");
```

**5. Jalankan aplikasi**
```
http://localhost/smart-inventory-ai
```

---

## 🔐 Kredensial Demo

| Username | Password |
|----------|----------|
| admin | admin123 |

---

## 📁 Struktur Folder

```
smart-inventory-ai/
├── assets/
│   └── css/
│       ├── dashboard.css
│       ├── inventaris.css
│       ├── barang_masuk.css
│       ├── barang_keluar.css
│       └── barang_reject.css
├── config/
│   └── koneksi.php
├── index.php          # Dashboard
├── inventaris.php     # Manajemen Inventaris
├── barang_masuk.php   # Barang Masuk
├── barang_keluar.php  # Barang Keluar
├── barang_reject.php  # Laporan Reject
├── surat_jalan.php    # Cetak Surat Jalan
├── login.php          # Halaman Login
├── logout.php         # Logout
├── search.php         # Global Search API
├── notif.php          # Notifikasi Stok
└── ai.php             # AI Insight Engine
```

---

## 🔄 Alur Sistem

```
Input Barang Masuk
       ↓
   Inspeksi
   ↙      ↘
Approve   Reject
   ↓         ↓
Inventaris  Laporan Reject
   ↓
Barang Keluar
   ↓
Surat Jalan
   ↓
Terkirim
```

---

## 👥 Tim Pengembang

| Nama | Peran |
|------|-------|
| Refi Fajriyani | Full Stack Developer, UI/UX Designer, Database & Backend |

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan kompetisi. © 2026 SmartInventory Team.
