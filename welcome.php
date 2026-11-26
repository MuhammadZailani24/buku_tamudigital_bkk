<?php
// Tangkap parameter wilker, jika tidak ada, kembalikan ke landing page
$wilker_raw = isset($_GET['wilker']) ? trim($_GET['wilker']) : '';
if (empty($wilker_raw)) {
    header("Location: index.php");
    exit;
}

$wilker = htmlspecialchars($wilker_raw, ENT_QUOTES, 'UTF-8');

// Pemetaan foto background sesuai nama file di folder assets/images/
$map_bg = [
    'BKK Induk Banjarmasin'  => 'assets/images/kantor_induk.webp',
    'Bandara Syamsudin Noor' => 'assets/images/kantor_bandara.webp',
    'Kintap'                 => 'assets/images/kantor_kintap.webp',
    'Sungai Puting'          => 'assets/images/kantor_sungaiputing.webp',
    'Satui'                  => 'assets/images/kantor_satui.webp',
    'Kotabaru'               => 'assets/images/kantor_kotabaru.webp',
    'Batulicin'              => 'assets/images/kantor_batulicin.webp',
];

$bg_image = isset($map_bg[$wilker_raw]) ? $map_bg[$wilker_raw] : 'assets/images/bg_luar.webp';
if (!file_exists($bg_image)) {
    $bg_image = 'assets/images/bg_luar.webp';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tamu - <?= $wilker; ?></title>
    <link rel="stylesheet" href="assets/css/style-tamu.css">
    <style>
        body {
            background-image: linear-gradient(rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.45)), url('<?= $bg_image; ?>');
        }
    </style>
</head>
<body>
    <a href="admin/login.php" class="btn-admin-login">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
        Login Admin
    </a>

    <div class="welcome-container">
        <div class="icon-wrapper">
            <img src="assets/images/logo_bkk.webp" alt="Logo Kantor" style="width: 120px; height: auto; object-fit: contain; margin-bottom: 5px;">
        </div>

        <h1>BUKU TAMU DIGITAL</h1>
        <p class="subtitle" style="color: #2563EB; font-weight: bold; font-size: 17px; margin-bottom: 5px;"><?= $wilker; ?></p>
        <p class="subtitle">Terima kasih atas kunjungan Anda</p>

        <!-- Lempar data wilker ke form.php -->
        <a href="form.php?wilker=<?= urlencode($wilker_raw); ?>" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            MULAI MENGISI BUKU TAMU
        </a>
        <p class="instruction-text">Klik tombol di atas untuk mencatat kunjungan Anda</p>

        <div style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 20px; margin-top: 10px; margin-bottom: 25px; display: flex; align-items: center; justify-content: center; gap: 10px; color: #1E293B; font-size: 15px; font-weight: 600;">
            <span id="liveClockText">Memuat waktu...</span>
        </div>

        <a href="index.php" style="font-size: 13px; color: #64748B; text-decoration: none; font-weight: bold; display: block; margin-top: -10px; margin-bottom: 20px;">← Ganti Wilayah Kerja</a>
    </div>

    <div class="system-info-card">
        <div class="system-text">
            <strong>BKK Kelas I Banjarmasin</strong>
            <p>Buku Tamu Digital v2.0.0 &copy; 2026</p>
        </div>
        <div class="system-developer">
            Code by <span>Muhammad Zailani</span>
        </div>
    </div>

    <script>
        function updateRealTimeClock() {
            const now = new Date();
            const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            document.getElementById('liveClockText').innerText = now.toLocaleDateString('id-ID', options).replace(' pukul', ' -');
        }
        updateRealTimeClock();
        setInterval(updateRealTimeClock, 1000);
    </script>
</body>
</html>