<?php
// admin/includes/header.php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Buku Tamu Digital</title>
    <link rel="stylesheet" href="../assets/css/style-admin.css">
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <!-- Wrapper Atas (Brand & Menu Navigasi) -->
        <div class="sidebar-top-wrapper">
            
            <!-- Brand / Logo & Judul Dikembalikan ke Posisi Tengah -->
            <div class="sidebar-brand" style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 30px 16px 22px; border-bottom: 1px solid rgba(255, 255, 255, 0.06);">
                <!-- Logo Kantor -->
                <img src="../assets/images/logo_bkk.webp" alt="Logo Kantor" style="width: 150px; height: auto; object-fit: contain; margin-bottom: 14px; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.25));">
                <!-- Teks Judul -->
                <span style="font-size: 13.5px; font-weight: 700; line-height: 1.4; letter-spacing: 0.5px; color: #FFFFFF; display: block; width: 100%;">
                    BUKU TAMU DIGITAL <br> ADMIN
                </span>
            </div>
            
            <ul class="nav-menu">
                <li class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                    <a href="index.php">📊 Dashboard</a>
                </li>
                <li><a href="kunjungan.php">📋 Kunjungan</a></li>
                <li class="<?= (basename($_SERVER['PHP_SELF']) == 'laporan.php') ? 'active' : ''; ?>">
                    <a href="laporan.php">📑 Rekap / Laporan</a>
                </li>
                <li><a href="pengguna.php">👥 Pengguna</a></li>
                <li><a href="pengaturan.php">⚙️ Pengaturan</a></li>
            </ul>
        </div>

        <!-- Menu Keluar di Kiri Bawah (Warna Merah) -->
        <ul class="nav-menu logout-menu">
            <li><a href="logout.php">🚪 Keluar</a></li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle">☰</button>
            <h2 style="font-size: 18px; color: var(--text-strong); display:flex; align-items:center; gap:8px;">
                Dashboard
            </h2>
            
            <!-- Bagian Kanan Header: Jam Real-Time & Profil Admin -->
            <div style="display: flex; align-items: center; gap: 20px;">
                <!-- Widget Jam, Hari, Tanggal, Bulan, Tahun Real-Time -->
                <div id="liveClock" style="font-size: 13.5px; font-weight: 600; color: #475569; background: #F1F5F9; padding: 6px 12px; border-radius: 6px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 6px;">
                    <span>🕒</span> <span id="clockText">Memuat waktu...</span>
                </div>

                <div class="user-profile">
                    👤 <?= isset($_SESSION['nama_admin']) ? $_SESSION['nama_admin'] : 'Admin'; ?>
                </div>
            </div>
        </header>

    <!-- Script JavaScript untuk Jam Real-Time -->
    <script>
        function updateRealTimeClock() {
            const now = new Date();
            
            // Konfigurasi format teks Indonesia
            const options = { 
                weekday: 'long', 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: false 
            };
            
            // Format waktu ke string lokal Indonesia (id-ID)
            let timeString = now.toLocaleDateString('id-ID', options);
            
            // Merapikan format koma menjadi titik/pemisah yang elegan
            timeString = timeString.replace(' pukul', ' -');

            document.getElementById('clockText').innerText = timeString;
        }

        // Jalankan fungsi saat pertama kali dimuat
        updateRealTimeClock();
        
        // Perbarui setiap 1 detik (1000 ms) agar bergerak secara real-time
        setInterval(updateRealTimeClock, 1000);
    </script>