<?php
// admin/index.php
session_start();
if (!isset($_SESSION['admin_username'])) { header("Location: login.php"); exit; }

error_reporting(0);
@ini_set('display_errors', 0);
date_default_timezone_set('Asia/Makassar');

require_once '../config/database.php';

// Cek Role Akses
$is_superadmin = ($_SESSION['admin_role'] === 'super_admin');
$admin_wilker  = $conn->real_escape_string($_SESSION['admin_wilker']);
$where_akses   = $is_superadmin ? "1=1" : "wilker = '$admin_wilker'";

// ==========================================================
// HELPER
// ==========================================================
function e($v, $default = '') {
    $v = ($v === null || $v === '') ? $default : $v;
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

function inisial($nama) {
    $nama = trim($nama);
    if ($nama === '') return '?';
    $parts = preg_split('/\s+/', $nama);
    $i = mb_strtoupper(mb_substr($parts[0], 0, 1, 'UTF-8'), 'UTF-8');
    if (count($parts) > 1) {
        $i .= mb_strtoupper(mb_substr($parts[count($parts) - 1], 0, 1, 'UTF-8'), 'UTF-8');
    }
    return $i;
}

// Satu fungsi untuk render baris tabel (dipakai halaman & AJAX)
function render_rows($result) {
    if (!$result || $result->num_rows === 0) {
        return '<tr><td colspan="8"><div class="dash-empty"><span>📭</span><strong>Belum ada kunjungan</strong><small>Data tamu yang baru masuk akan muncul di sini secara otomatis.</small></div></td></tr>';
    }

    $html = '';
    while ($row = $result->fetch_assoc()) {
        $ts        = !empty($row['waktu_masuk']) ? strtotime($row['waktu_masuk']) : 0;
        $tanggal   = $ts ? date('d M Y', $ts) : '-';
        $jam       = $ts ? date('H:i', $ts) : '';
        $nama      = e($row['nama_lengkap'] ?? '');
        $ini       = e(inisial($row['nama_lengkap'] ?? ''));
        $instansi  = e($row['instansi'] ?? '', '-');
        $alamat    = e($row['alamat'] ?? '', '-');
        $no_wa     = e($row['no_telepon'] ?? '', '-');
        $wilker    = e($row['wilker'] ?? '', '-');
        $kategori  = e($row['kategori'] ?? '', 'Umum / Lainnya');
        $keperluan = e($row['keperluan'] ?? '', '-');
        $bertemu   = e($row['bertemu_dengan'] ?? '', '-');
        $foto      = e($row['foto'] ?? '');

        $html .= '<tr>';

        // Waktu
        $html .= '<td data-label="Waktu"><div class="cell-time"><strong>' . $jam . '</strong><small>' . $tanggal . '</small></div></td>';

        // Tamu (avatar + nama + instansi)
        $html .= '<td data-label="Tamu"><div class="cell-guest">';
        if ($foto !== '') {
            $html .= '<a href="../assets/uploads/' . $foto . '" target="_blank" class="avatar avatar-img" title="Lihat foto"><img src="../assets/uploads/' . $foto . '" alt="Foto ' . $nama . '" loading="lazy"></a>';
        } else {
            $html .= '<span class="avatar">' . $ini . '</span>';
        }
        $html .= '<div class="guest-text"><strong>' . $nama . '</strong><small>' . $instansi . '</small></div></div></td>';

        // Kontak
        $html .= '<td data-label="Kontak"><div class="cell-stack"><span>' . $no_wa . '</span><small>' . $alamat . '</small></div></td>';

        // Wilker
        $html .= '<td data-label="Wilker"><span class="chip chip-blue">' . $wilker . '</span></td>';

        // Kategori
        $html .= '<td data-label="Kategori"><span class="chip chip-green">' . $kategori . '</span></td>';

        // Keperluan
        $html .= '<td data-label="Keperluan"><span class="cell-text">' . $keperluan . '</span></td>';

        // Bertemu
        $html .= '<td data-label="Bertemu"><span class="cell-text">' . $bertemu . '</span></td>';

        // Foto
        $html .= '<td data-label="Foto" class="col-foto">';
        if ($foto !== '') {
            $html .= '<a href="../assets/uploads/' . $foto . '" target="_blank" class="thumb"><img src="../assets/uploads/' . $foto . '" alt="Foto ' . $nama . '" loading="lazy"></a>';
        } else {
            $html .= '<span class="no-photo">Tanpa foto</span>';
        }
        $html .= '</td>';

        $html .= '</tr>';
    }
    return $html;
}

// Ambil statistik
function ambil_stats($conn, $where_akses) {
    $queries = [
        'hari_ini'   => "DATE(waktu_masuk) = CURDATE()",
        'minggu_ini' => "YEARWEEK(waktu_masuk, 1) = YEARWEEK(CURDATE(), 1)",
        'bulan_ini'  => "MONTH(waktu_masuk) = MONTH(CURDATE()) AND YEAR(waktu_masuk) = YEAR(CURDATE())",
        'tahun_ini'  => "YEAR(waktu_masuk) = YEAR(CURDATE())",
    ];
    $out = [];
    foreach ($queries as $key => $cond) {
        $q = $conn->query("SELECT COUNT(id_kunjungan) AS total FROM kunjungan WHERE $cond AND $where_akses");
        $out[$key] = $q ? (int)$q->fetch_assoc()['total'] : 0;
    }
    return $out;
}

// ==========================================================
// ENDPOINT AJAX: UPDATE STATISTIK DAN TABEL TERBARU
// ==========================================================
if (isset($_GET['ajax']) && $_GET['ajax'] === 'stats') {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');

    $stats   = ambil_stats($conn, $where_akses);
    $q_tabel = $conn->query("SELECT * FROM kunjungan WHERE $where_akses ORDER BY waktu_masuk DESC LIMIT 7");

    echo json_encode([
        'success'    => true,
        'hari_ini'   => $stats['hari_ini'],
        'minggu_ini' => $stats['minggu_ini'],
        'bulan_ini'  => $stats['bulan_ini'],
        'tahun_ini'  => $stats['tahun_ini'],
        'html'       => render_rows($q_tabel),
        'updated'    => date('H:i:s'),
    ]);
    exit;
}

$stats   = ambil_stats($conn, $where_akses);
$q_tabel = $conn->query("SELECT * FROM kunjungan WHERE $where_akses ORDER BY waktu_masuk DESC LIMIT 7");

// Sapaan sesuai jam
$jam_now = (int)date('G');
if ($jam_now < 11)      $sapaan = 'Selamat pagi';
elseif ($jam_now < 15)  $sapaan = 'Selamat siang';
elseif ($jam_now < 18)  $sapaan = 'Selamat sore';
else                    $sapaan = 'Selamat malam';

$hari_id  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulan_id = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tanggal_lengkap = $hari_id[(int)date('w')] . ', ' . date('j') . ' ' . $bulan_id[(int)date('n')] . ' ' . date('Y');

$nama_admin = e($_SESSION['admin_username']);
$wilker_label = $is_superadmin ? 'Semua Wilayah Kerja' : e($_SESSION['admin_wilker']);
$role_label   = $is_superadmin ? 'Super Admin' : 'Admin Wilker';

include 'includes/header.php';
?>

<style>
    .dash {
        --navy: #166534;
        --navy-deep: #14532D;
        --blue: #16A34A;
        --sky: #22C55E;
        --ink: #0F172A;
        --text: #334155;
        --muted: #64748B;
        --line: #E2E8F0;
        --soft: #F8FAFC;
        --card: #FFFFFF;
        color: var(--text);
    }
    .dash * { box-sizing: border-box; }

    /* ===== Hero / Selamat datang ===== */
    .dash-hero {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        padding: 26px 30px;
        margin-bottom: 22px;
        border-radius: 20px;
        color: #fff;
        background: linear-gradient(120deg, var(--navy-deep) 0%, var(--navy) 55%, var(--blue) 100%);
        box-shadow: 0 14px 30px rgba(22, 101, 52, 0.25);
    }
    .dash-hero::before,
    .dash-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.07);
        pointer-events: none;
    }
    .dash-hero::before { width: 240px; height: 240px; right: -60px; top: -110px; }
    .dash-hero::after  { width: 140px; height: 140px; right: 140px; bottom: -80px; }

    .hero-left { display: flex; align-items: center; gap: 18px; position: relative; z-index: 1; }
    .hero-avatar {
        flex: 0 0 auto;
        width: 58px; height: 58px;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px; font-weight: 800;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .hero-text small { display: block; font-size: 13px; color: #BBF7D0; margin-bottom: 2px; }
    .hero-text h2 { margin: 0; font-size: 22px; line-height: 1.25; font-weight: 800; color: #fff; }

    .hero-right { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
    .hero-badges { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
    .hero-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12.5px; font-weight: 600;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #fff;
    }
    .hero-date { font-size: 13px; color: #DCFCE7; }

    /* ===== Kartu statistik ===== */
    .dash-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 18px;
        margin-bottom: 22px;
    }
    .dash-stat {
        position: relative;
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 18px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 6px 16px rgba(15, 23, 42, 0.04);
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .dash-stat:hover { transform: translateY(-3px); box-shadow: 0 14px 26px rgba(15, 23, 42, 0.09); }
    .stat-icon {
        flex: 0 0 auto;
        width: 54px; height: 54px;
        border-radius: 15px;
        display: flex; align-items: center; justify-content: center;
        font-size: 26px;
    }
    .stat-body { display: flex; flex-direction: column; min-width: 0; }
    .stat-label { font-size: 13px; font-weight: 600; color: var(--muted); }
    .stat-number { font-size: 32px; font-weight: 800; line-height: 1.15; color: var(--ink); font-variant-numeric: tabular-nums; }
    .stat-sub { font-size: 12px; color: var(--muted); }

    .dash-stat .stat-icon { background: #DCFCE7; }
    .dash-stat::after {
        content: "";
        position: absolute; left: 20px; right: 20px; bottom: 0;
        height: 3px; border-radius: 3px 3px 0 0;
    }
    .dash-stat::after { background: #22C55E; }

    .stat-number.bump { animation: bump .5s ease; }
    @keyframes bump { 40% { transform: scale(1.12); color: var(--blue); } }

    /* ===== Panel tabel ===== */
    .dash-panel {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 20px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 8px 22px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }
    .panel-head {
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px; flex-wrap: wrap;
        padding: 18px 24px;
        border-bottom: 1px solid var(--line);
    }
    .panel-title { display: flex; flex-direction: column; gap: 2px; }
    .panel-title h3 { margin: 0; font-size: 17px; font-weight: 800; color: var(--navy); }
    .panel-title small { font-size: 12.5px; color: var(--muted); }

    .panel-actions { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .live-pill {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 6px 12px;
        font-size: 12px; font-weight: 600;
        color: #166534; background: #F0FDF4;
        border: 1px solid #BBF7D0; border-radius: 999px;
    }
    .live-dot { width: 8px; height: 8px; border-radius: 50%; background: #22C55E; animation: pulse 1.8s infinite; }
    @keyframes pulse {
        0%   { box-shadow: 0 0 0 0 rgba(34, 197, 94, .55); }
        70%  { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    .btn-all {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 9px 16px;
        font-size: 13px; font-weight: 700;
        color: #fff; background: var(--blue);
        border-radius: 10px; text-decoration: none;
        transition: background .2s ease, transform .2s ease;
    }
    .btn-all:hover { background: var(--navy); transform: translateX(2px); }

    /* ===== Tabel ===== */
    .dash-scroll { overflow-x: auto; }
    .dash-table { width: 100%; border-collapse: collapse; min-width: 880px; }
    .dash-table thead th {
        position: sticky; top: 0;
        padding: 12px 16px;
        text-align: left;
        font-size: 12px; font-weight: 700; letter-spacing: .3px;
        color: var(--muted);
        background: var(--soft);
        border-bottom: 1px solid var(--line);
        white-space: nowrap;
    }
    .dash-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        font-size: 13.5px;
        border-bottom: 1px solid #F1F5F9;
    }
    .dash-table tbody tr { transition: background .15s ease; }
    .dash-table tbody tr:hover { background: #F7FEF9; }
    .dash-table tbody tr:last-child td { border-bottom: none; }

    .cell-time { display: flex; flex-direction: column; line-height: 1.3; }
    .cell-time strong { font-size: 15px; color: var(--ink); }
    .cell-time small, .cell-stack small, .guest-text small { font-size: 12px; color: var(--muted); }

    .cell-guest { display: flex; align-items: center; gap: 12px; min-width: 190px; }
    .guest-text { display: flex; flex-direction: column; line-height: 1.35; min-width: 0; }
    .guest-text strong { color: var(--ink); font-size: 14px; }

    .avatar {
        flex: 0 0 auto;
        width: 40px; height: 40px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 800;
        color: var(--navy);
        background: linear-gradient(135deg, #DCFCE7, #BBF7D0);
        text-decoration: none; overflow: hidden;
    }
    .avatar.avatar-img img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .cell-stack { display: flex; flex-direction: column; line-height: 1.35; max-width: 200px; }
    .cell-stack span { color: var(--ink); font-weight: 600; }
    .cell-text { display: block; max-width: 190px; color: var(--text); line-height: 1.4; }

    .chip {
        display: inline-block;
        padding: 5px 11px;
        font-size: 12px; font-weight: 700;
        border-radius: 999px;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .chip-blue  { color: #14532D; background: #DCFCE7; border-color: #86EFAC; }
    .chip-green { color: #166534; background: #F0FDF4; border-color: #BBF7D0; }

    .col-foto { text-align: center; }
    .dash-table th.col-foto { text-align: center; }
    .thumb { display: inline-block; width: 44px; height: 44px; border-radius: 12px; overflow: hidden; border: 2px solid var(--line); transition: transform .2s ease, border-color .2s ease; }
    .thumb:hover { transform: scale(1.12); border-color: var(--blue); }
    .thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .no-photo { font-size: 11.5px; color: #94A3B8; }

    .dash-empty { display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 44px 16px; text-align: center; }
    .dash-empty span { font-size: 40px; }
    .dash-empty strong { color: var(--ink); font-size: 15px; }
    .dash-empty small { color: var(--muted); font-size: 13px; }

    /* ===== Tablet & HP ===== */
    /* Foto kecil di kolom Tamu sudah cukup di layar kecil, jadi kolom Foto disembunyikan */
    @media (max-width: 900px) {
        .dash-hero { padding: 22px; }
        .hero-right { align-items: flex-start; }
        .hero-badges { justify-content: flex-start; }
    }

    @media (max-width: 720px) {
        .dash-table { min-width: 0; }
        .dash-table thead { display: none; }
        .dash-table, .dash-table tbody, .dash-table tr, .dash-table td { display: block; width: 100%; }
        .dash-table tbody tr { padding: 14px 16px; border-bottom: 1px solid var(--line); }
        .dash-table tbody td { display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; padding: 6px 0; border: none; text-align: right; }
        .dash-table tbody td::before {
            content: attr(data-label);
            flex: 0 0 76px;
            text-align: left;
            font-size: 12px; font-weight: 700; color: var(--muted);
        }
        .dash-table tbody td[colspan] { display: block; }
        .dash-table tbody td[colspan]::before { display: none; }
        .cell-guest { min-width: 0; }
        .col-foto { display: none !important; }
        .cell-stack, .cell-text { max-width: none; text-align: right; }
        .hero-text h2 { font-size: 19px; }
        .stat-number { font-size: 28px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .dash *, .dash *::before, .dash *::after { animation: none !important; transition: none !important; }
    }
</style>

<div class="content-wrapper dash">

    <!-- Selamat datang (tombol logout sudah ada di header/sidebar, tidak diulang di sini) -->
    <section class="dash-hero">
        <div class="hero-left">
            <div class="hero-avatar"><?= e(mb_strtoupper(mb_substr($_SESSION['admin_username'], 0, 1, 'UTF-8'), 'UTF-8')); ?></div>
            <div class="hero-text">
                <small><?= $sapaan; ?>,</small>
                <h2><?= $nama_admin; ?></h2>
            </div>
        </div>
        <div class="hero-right">
            <div class="hero-badges">
                <span class="hero-badge">🛡️ <?= $role_label; ?></span>
                <span class="hero-badge">📍 <?= $wilker_label; ?></span>
            </div>
            <span class="hero-date"><?= $tanggal_lengkap; ?></span>
        </div>
    </section>

    <!-- Statistik -->
    <section class="dash-stats">
        <div class="dash-stat s-blue">
            <div class="stat-icon">📅</div>
            <div class="stat-body">
                <span class="stat-label">Hari Ini</span>
                <span class="stat-number" id="valHariIni"><?= $stats['hari_ini']; ?></span>
                <span class="stat-sub">tamu berkunjung</span>
            </div>
        </div>
        <div class="dash-stat s-green">
            <div class="stat-icon">🗓️</div>
            <div class="stat-body">
                <span class="stat-label">Minggu Ini</span>
                <span class="stat-number" id="valMingguIni"><?= $stats['minggu_ini']; ?></span>
                <span class="stat-sub">tamu berkunjung</span>
            </div>
        </div>
        <div class="dash-stat s-amber">
            <div class="stat-icon">📊</div>
            <div class="stat-body">
                <span class="stat-label">Bulan Ini</span>
                <span class="stat-number" id="valBulanIni"><?= $stats['bulan_ini']; ?></span>
                <span class="stat-sub">tamu berkunjung</span>
            </div>
        </div>
        <div class="dash-stat s-violet">
            <div class="stat-icon">📈</div>
            <div class="stat-body">
                <span class="stat-label">Tahun Ini</span>
                <span class="stat-number" id="valTahunIni"><?= $stats['tahun_ini']; ?></span>
                <span class="stat-sub">tamu berkunjung</span>
            </div>
        </div>
    </section>

    <!-- Kunjungan terbaru -->
    <section class="dash-panel">
        <div class="panel-head">
            <div class="panel-title">
                <h3>Kunjungan Terbaru</h3>
                <small>7 tamu terakhir yang mengisi buku tamu</small>
            </div>
            <div class="panel-actions">
                <span class="live-pill" title="Data diperbarui otomatis setiap 5 detik">
                    <span class="live-dot"></span>
                    Langsung · <span id="lastUpdate"><?= date('H:i:s'); ?></span>
                </span>
                <a href="kunjungan.php" class="btn-all">Lihat Semua ➜</a>
            </div>
        </div>

        <div class="dash-scroll">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Tamu</th>
                        <th>Kontak</th>
                        <th>Wilker</th>
                        <th>Kategori</th>
                        <th>Keperluan</th>
                        <th>Bertemu</th>
                        <th class="col-foto">Foto</th>
                    </tr>
                </thead>
                <tbody id="tabelTerbaruBody">
                    <?= render_rows($q_tabel); ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
(function () {
    function setAngka(id, nilai) {
        var el = document.getElementById(id);
        if (!el) return;
        if (el.textContent.trim() !== String(nilai)) {
            el.textContent = nilai;
            el.classList.remove('bump');
            void el.offsetWidth; // restart animasi
            el.classList.add('bump');
        }
    }

    function updateDashboard() {
        fetch('index.php?ajax=stats', { cache: 'no-store' })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (!data || !data.success) return;
            setAngka('valHariIni', data.hari_ini);
            setAngka('valMingguIni', data.minggu_ini);
            setAngka('valBulanIni', data.bulan_ini);
            setAngka('valTahunIni', data.tahun_ini);
            var tabel = document.getElementById('tabelTerbaruBody');
            if (tabel && typeof data.html === 'string') tabel.innerHTML = data.html;
            var lu = document.getElementById('lastUpdate');
            if (lu && data.updated) lu.textContent = data.updated;
        }).catch(function () {});
    }

    setInterval(updateDashboard, 5000);
})();
</script>

<?php include 'includes/footer.php'; ?>