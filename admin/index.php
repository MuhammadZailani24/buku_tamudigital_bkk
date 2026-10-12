<?php

// ==========================================================
// ADMIN DASHBOARD - BUKU TAMU DIGITAL
// ==========================================================

// Proteksi error
error_reporting(0);
@ini_set('display_errors', 0);

// Zona waktu WITA
date_default_timezone_set('Asia/Makassar');

// Koneksi database
require_once '../config/database.php';


// ==========================================================
// ENDPOINT AJAX: UPDATE STATISTIK DAN TABEL TERBARU
// ==========================================================

if (isset($_GET['ajax']) && $_GET['ajax'] === 'stats') {

    // Bersihkan output buffer agar response benar-benar JSON
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/json; charset=utf-8');

    // 1. TOTAL HARI INI
    $q_hari = $conn->query("SELECT COUNT(id_kunjungan) AS total FROM kunjungan WHERE DATE(waktu_masuk) = CURDATE()");
    $hari_ini = $q_hari ? $q_hari->fetch_assoc()['total'] : 0;

    // 2. TOTAL MINGGU INI
    $q_minggu = $conn->query("SELECT COUNT(id_kunjungan) AS total FROM kunjungan WHERE YEARWEEK(waktu_masuk, 1) = YEARWEEK(CURDATE(), 1)");
    $minggu_ini = $q_minggu ? $q_minggu->fetch_assoc()['total'] : 0;

    // 3. TOTAL BULAN INI
    $q_bulan = $conn->query("SELECT COUNT(id_kunjungan) AS total FROM kunjungan WHERE MONTH(waktu_masuk) = MONTH(CURDATE()) AND YEAR(waktu_masuk) = YEAR(CURDATE())");
    $bulan_ini = $q_bulan ? $q_bulan->fetch_assoc()['total'] : 0;

    // 4. TOTAL TAHUN INI
    $q_tahun = $conn->query("SELECT COUNT(id_kunjungan) AS total FROM kunjungan WHERE YEAR(waktu_masuk) = YEAR(CURDATE())");
    $tahun_ini = $q_tahun ? $q_tahun->fetch_assoc()['total'] : 0;

    // 5. DATA KUNJUNGAN TERBARU (AJAX Render)
    $q_tabel = $conn->query("SELECT * FROM kunjungan ORDER BY waktu_masuk DESC LIMIT 7");
    $html = '';

    if ($q_tabel && $q_tabel->num_rows > 0) {
        while ($row = $q_tabel->fetch_assoc()) {
            $waktu = !empty($row['waktu_masuk']) ? date('d/m/Y H:i', strtotime($row['waktu_masuk'])) : '-';
            $nama = htmlspecialchars($row['nama_lengkap'] ?? '', ENT_QUOTES, 'UTF-8');
            $instansi = htmlspecialchars($row['instansi'] ?? '', ENT_QUOTES, 'UTF-8');
            $alamat = htmlspecialchars($row['alamat'] ?? '-', ENT_QUOTES, 'UTF-8');
            $kategori = !empty($row['kategori']) ? htmlspecialchars($row['kategori'], ENT_QUOTES, 'UTF-8') : 'Umum / Lainnya';
            $keperluan = htmlspecialchars($row['keperluan'] ?? '', ENT_QUOTES, 'UTF-8');
            $bertemu = htmlspecialchars($row['bertemu_dengan'] ?? '', ENT_QUOTES, 'UTF-8');
            $foto = htmlspecialchars($row['foto'] ?? '', ENT_QUOTES, 'UTF-8');

            $html .= '<tr>';
            $html .= '<td style="vertical-align: middle;">' . $waktu . '</td>';
            $html .= '<td style="vertical-align: middle;"><strong>' . $nama . '</strong></td>';
            $html .= '<td style="vertical-align: middle;">' . $instansi . '</td>';
            $html .= '<td style="vertical-align: middle;">' . $alamat . '</td>';
            
            // Kolom Kategori (Label Hijau)
            $html .= '<td style="vertical-align: middle;"><span style="background: #F0FDF4; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 11.5px; border: 1px solid #BBF7D0; display: inline-block; font-weight: bold;">' . $kategori . '</span></td>';
            
            // Kolom Keperluan Terpisah
            $html .= '<td style="vertical-align: middle; color: #475569; font-size: 13px;">' . $keperluan . '</td>';
            $html .= '<td style="vertical-align: middle;">' . $bertemu . '</td>';
            
            // Kolom Foto Bisa Diklik
            $html .= '<td style="text-align: center; vertical-align: middle;">';
            if (!empty($foto)) {
                $html .= '<a href="../assets/uploads/' . $foto . '" target="_blank" title="Klik untuk memperbesar">';
                $html .= '<img src="../assets/uploads/' . $foto . '" alt="Foto" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.2s;" onmouseover="this.style.transform=\'scale(1.1)\'" onmouseout="this.style.transform=\'scale(1)\'">';
                $html .= '</a>';
            } else {
                $html .= '<span style="font-size: 11px; color: #94A3B8;">No Photo</span>';
            }
            $html .= '</td>';
            $html .= '</tr>';
        }
    } else {
        $html = '<tr><td colspan="8" style="text-align: center; padding: 20px;">Belum ada data kunjungan terbaru.</td></tr>';
    }

    echo json_encode([
        'success'     => true,
        'hari_ini'    => $hari_ini,
        'minggu_ini'  => $minggu_ini,
        'bulan_ini'   => $bulan_ini,
        'tahun_ini'   => $tahun_ini,
        'html'        => $html
    ]);
    exit;
}


// ==========================================================
// QUERY STATISTIK UNTUK LOAD PERTAMA (Tanpa AJAX)
// ==========================================================

$q_hari = $conn->query("SELECT COUNT(id_kunjungan) AS total FROM kunjungan WHERE DATE(waktu_masuk) = CURDATE()");
$hari_ini = $q_hari ? $q_hari->fetch_assoc()['total'] : 0;

$q_minggu = $conn->query("SELECT COUNT(id_kunjungan) AS total FROM kunjungan WHERE YEARWEEK(waktu_masuk, 1) = YEARWEEK(CURDATE(), 1)");
$minggu_ini = $q_minggu ? $q_minggu->fetch_assoc()['total'] : 0;

$q_bulan = $conn->query("SELECT COUNT(id_kunjungan) AS total FROM kunjungan WHERE MONTH(waktu_masuk) = MONTH(CURDATE()) AND YEAR(waktu_masuk) = YEAR(CURDATE())");
$bulan_ini = $q_bulan ? $q_bulan->fetch_assoc()['total'] : 0;

$q_tahun = $conn->query("SELECT COUNT(id_kunjungan) AS total FROM kunjungan WHERE YEAR(waktu_masuk) = YEAR(CURDATE())");
$tahun_ini = $q_tahun ? $q_tahun->fetch_assoc()['total'] : 0;

$q_tabel = $conn->query("SELECT * FROM kunjungan ORDER BY waktu_masuk DESC LIMIT 7");


// ==========================================================
// HEADER
// ==========================================================
include 'includes/header.php';
?>

<div class="content-wrapper">

    <!-- GRID STATISTIK -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-title">📅 Hari Ini</span>
            <span class="stat-value" id="valHariIni"><?= $hari_ini; ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-title">🗓️ Minggu Ini</span>
            <span class="stat-value" id="valMingguIni"><?= $minggu_ini; ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-title">📊 Bulan Ini</span>
            <span class="stat-value" id="valBulanIni"><?= $bulan_ini; ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-title">📈 Tahun Ini</span>
            <span class="stat-value" id="valTahunIni"><?= $tahun_ini; ?></span>
        </div>
    </div>


    <!-- TABEL KUNJUNGAN TERBARU -->
    <div class="card-table">
        <div class="card-header">
            <h3 style="color: #1E3A8A; font-size: 16px;">KUNJUNGAN TERBARU</h3>
            <a href="kunjungan.php" style="font-size: 14px; color: #3B82F6; text-decoration: none; font-weight: bold;">Lihat Semua</a>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="10%">Waktu</th>
                        <th width="14%">Nama</th>
                        <th width="12%">Asal</th>
                        <th width="14%">Alamat</th>
                        <th width="13%">Kategori</th>
                        <th width="15%">Keperluan</th>
                        <th width="14%">Bertemu</th>
                        <th width="8%" style="text-align: center;">Foto</th>
                    </tr>
                </thead>
                <tbody id="tabelTerbaruBody">
                    <?php
                    if ($q_tabel && $q_tabel->num_rows > 0) {
                        while ($row = $q_tabel->fetch_assoc()) {
                            $waktu = !empty($row['waktu_masuk']) ? date('d/m/Y H:i', strtotime($row['waktu_masuk'])) : '-';
                            $nama = htmlspecialchars($row['nama_lengkap'] ?? '', ENT_QUOTES, 'UTF-8');
                            $instansi = htmlspecialchars($row['instansi'] ?? '', ENT_QUOTES, 'UTF-8');
                            $alamat = htmlspecialchars($row['alamat'] ?? '-', ENT_QUOTES, 'UTF-8');
                            $kategori = !empty($row['kategori']) ? htmlspecialchars($row['kategori'], ENT_QUOTES, 'UTF-8') : 'Umum / Lainnya';
                            $keperluan = htmlspecialchars($row['keperluan'] ?? '', ENT_QUOTES, 'UTF-8');
                            $bertemu = htmlspecialchars($row['bertemu_dengan'] ?? '', ENT_QUOTES, 'UTF-8');
                            $foto = htmlspecialchars($row['foto'] ?? '', ENT_QUOTES, 'UTF-8');
                    ?>
                            <tr>
                                <td style="vertical-align: middle;"><?= $waktu; ?></td>
                                <td style="vertical-align: middle;"><strong><?= $nama; ?></strong></td>
                                <td style="vertical-align: middle;"><?= $instansi; ?></td>
                                <td style="vertical-align: middle;"><?= $alamat; ?></td>
                                
                                <td style="vertical-align: middle;">
                                    <span style="background: #F0FDF4; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 11.5px; border: 1px solid #BBF7D0; display: inline-block; font-weight: bold;">
                                        <?= $kategori; ?>
                                    </span>
                                </td>
                                
                                <td style="vertical-align: middle; color: #475569; font-size: 13px;"><?= $keperluan; ?></td>
                                <td style="vertical-align: middle;"><?= $bertemu; ?></td>
                                
                                <td style="text-align: center; vertical-align: middle;">
                                    <?php if (!empty($foto)) { ?>
                                        <a href="../assets/uploads/<?= $foto; ?>" target="_blank" title="Klik untuk memperbesar">
                                            <img src="../assets/uploads/<?= $foto; ?>" alt="Foto" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                        </a>
                                    <?php } else { ?>
                                        <span style="font-size: 11px; color: #94A3B8;">No Photo</span>
                                    <?php } ?>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                    ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">Belum ada data kunjungan terbaru.</td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SCRIPT AJAX AUTO REFRESH -->
<script>
(function () {
    function updateDashboard() {
        fetch('index.php?ajax=stats', {
            method: 'GET',
            cache: 'no-store'
        })
        .then(function (response) {
            if (!response.ok) { throw new Error('Gagal mengambil data dashboard.'); }
            return response.json();
        })
        .then(function (data) {
            if (!data || !data.success) { return; }

            // Update Statistik
            var hariIni = document.getElementById('valHariIni');
            var mingguIni = document.getElementById('valMingguIni');
            var bulanIni = document.getElementById('valBulanIni');
            var tahunIni = document.getElementById('valTahunIni');

            if (hariIni) hariIni.textContent = data.hari_ini;
            if (mingguIni) mingguIni.textContent = data.minggu_ini;
            if (bulanIni) bulanIni.textContent = data.bulan_ini;
            if (tahunIni) tahunIni.textContent = data.tahun_ini;

            // Update Tabel
            var tabel = document.getElementById('tabelTerbaruBody');
            if (tabel && typeof data.html === 'string') {
                tabel.innerHTML = data.html;
            }
        })
        .catch(function () {
            // Error ditahan agar auto-refresh terus berjalan
        });
    }

    // Refresh secara otomatis setiap 5 detik
    setInterval(updateDashboard, 5000);
})();
</script>

<?php
// ==========================================================
// FOOTER
// ==========================================================
include 'includes/footer.php';
?>