<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tamu Digital</title>
    <link rel="stylesheet" href="assets/css/style-tamu.css">
</head>
<body>

    <!-- Tombol Login Admin di Sudut Kanan Atas -->
    <a href="admin/login.php" class="btn-admin-login">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
        Login Admin
    </a>

    <!-- Container Utama yang Presisi di Tengah -->
    <div class="welcome-container">
        
        <!-- ========================================== -->
        <!-- KOLOM FOTO / LOGO KANTOR                   -->
        <!-- ========================================== -->
        <div class="icon-wrapper">
            <!-- Ganti 'nama_foto_logo_anda.png' dengan nama file logo Anda -->
            <img src="assets/images/logo_bkk.webp" alt="Logo Kantor" style="width: 110px; height: auto; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1)); margin-bottom: 10px;">
        </div>
        <!-- ========================================== -->

        <!-- Judul & Subjudul -->
        <h1>BUKU TAMU DIGITAL</h1>
        <p class="subtitle">Terima kasih atas kunjungan Anda</p>

        <!-- Tombol Mulai Mengisi -->
        <a href="form.php" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            MULAI MENGISI
        </a>
        <p class="instruction-text">Klik tombol di atas untuk mengisi buku tamu</p>

        <!-- Card Jam Real-Time -->
        <div style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 12px 20px; margin-top: 15px; margin-bottom: 25px; display: flex; align-items: center; justify-content: center; gap: 10px; color: #334155; font-size: 14.5px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <span id="liveClockText">Memuat waktu...</span>
        </div>

        <!-- Informasi Keamanan -->
        <div class="security-info">
            <div class="security-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0284C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            </div>
            <div class="security-text">
                <strong>INFORMASI KEAMANAN</strong>
                <p>Data dan foto Anda akan digunakan sebagai dokumentasi keamanan dan tidak akan disalahgunakan.</p>
            </div>
        </div>

    </div>

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
            
            // Merapikan format koma menjadi pemisah yang elegan
            timeString = timeString.replace(' pukul', ' -');

            document.getElementById('liveClockText').innerText = timeString;
        }

        // Jalankan fungsi saat pertama kali dimuat
        updateRealTimeClock();
        
        // Perbarui setiap 1 detik
        setInterval(updateRealTimeClock, 1000);
    </script>

</body>
</html>