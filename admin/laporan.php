<?php
// admin/laporan.php

// Sinkronisasi Zona Waktu WITA & Pengamanan Error
error_reporting(0);
@ini_set('display_errors', 0);
date_default_timezone_set('Asia/Makassar');

require_once '../config/database.php';

// ==========================================
// 1. ENDPOINT AJAX: CEK DATA TERBARU (JSON RESPONSE)
// ==========================================
if (isset($_GET['ajax']) &&$_GET['ajax'] == 'check_new') {
    while (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();
    
    header('Content-Type: application/json; charset=utf-8');

    $last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'harian';$where_clause = "1=1"; 

    switch ($filter) {
        case 'harian':
            $tgl = isset($_GET['tgl_harian']) && !empty($_GET['tgl_harian']) ?$conn->real_escape_string($_GET['tgl_harian']) : date('Y-m-d');$where_clause = "waktu_masuk LIKE '$tgl%'";
            break;
        case 'bulanan':
            $bln = isset($_GET['bln_bulanan']) && !empty($_GET['bln_bulanan']) ?$conn->real_escape_string($_GET['bln_bulanan']) : date('Y-m');$where_clause = "waktu_masuk LIKE '$bln%'";
            break;
        case 'tahunan':
            $thn = isset($_GET['thn_tahunan']) && !empty($_GET['thn_tahunan']) ?$conn->real_escape_string($_GET['thn_tahunan']) : date('Y');$where_clause = "waktu_masuk LIKE '$thn%'";
            break;
        case 'custom':
            $tgl_mulai = isset($_GET['tgl_mulai']) ?$conn->real_escape_string($_GET['tgl_mulai']) : date('Y-m-01');$tgl_selesai = isset($_GET['tgl_selesai']) ?$conn->real_escape_string($_GET['tgl_selesai']) : date('Y-m-d');$where_clause = "waktu_masuk >= '$tgl_mulai 00:00:00' AND waktu_masuk <= '$tgl_selesai 23:59:59'";
            break;
        case 'semua':
            $where_clause = "1=1";
            break;
    }

    $query = "SELECT * FROM kunjungan WHERE id_kunjungan > $last_id AND ($where_clause) ORDER BY id_kunjungan ASC";
    $result =$conn->query($query);$new_rows = [];
    $max_id =$last_id;

    if ($result &&$result->num_rows > 0) {
        while ($row =$result->fetch_assoc()) {
            if ($row['id_kunjungan'] >$max_id) {
                $max_id = intval($row['id_kunjungan']);
            }
            
            $kategori_label = !empty($row['kategori']) ? htmlspecialchars($row['kategori']) : 'Umum / Lainnya';

            $new_rows[] = [
                'id_kunjungan' => $row['id_kunjungan'],
                'waktu_masuk'  => date('d/m/Y H:i:s', strtotime($row['waktu_masuk'])),
                'nama_lengkap' => htmlspecialchars($row['nama_lengkap']),
                'instansi'     => htmlspecialchars($row['instansi']),
                'no_telepon'   => htmlspecialchars($row['no_telepon']),
                'kategori'     => $kategori_label,
                'keperluan'    => htmlspecialchars($row['keperluan']),
                'bertemu_dengan'=> htmlspecialchars($row['bertemu_dengan']),
                'foto'         => htmlspecialchars($row['foto'])
            ];
        }
    }

    $count_query = "SELECT COUNT(*) as total FROM kunjungan WHERE $where_clause";
    $count_res =$conn->query($count_query);$total_data = ($count_res) ? intval($count_res->fetch_assoc()['total']) : 0;

    echo json_encode([
        'success'  => true,
        'new_rows' => $new_rows, 
        'max_id'   => $max_id, 
        'total'    => $total_data
    ]);
    exit();
}

// ==========================================
// 2. RENDER HALAMAN UTAMA LAPORAN
// ==========================================
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'harian';$where_clause = "1=1"; 
$keterangan = "Semua Data";

switch ($filter) {
    case 'harian':
        $tgl = isset($_GET['tgl_harian']) && !empty($_GET['tgl_harian']) ?$conn->real_escape_string($_GET['tgl_harian']) : date('Y-m-d');$where_clause = "waktu_masuk LIKE '$tgl%'";
        $keterangan = "Harian - Tanggal: " . date('d F Y', strtotime($tgl));
        break;
    case 'bulanan':
        $bln = isset($_GET['bln_bulanan']) && !empty($_GET['bln_bulanan']) ?$conn->real_escape_string($_GET['bln_bulanan']) : date('Y-m');$where_clause = "waktu_masuk LIKE '$bln%'";
        $keterangan = "Bulanan - Periode: " . date('F Y', strtotime($bln . "-01"));
        break;
    case 'tahunan':
        $thn = isset($_GET['thn_tahunan']) && !empty($_GET['thn_tahunan']) ?$conn->real_escape_string($_GET['thn_tahunan']) : date('Y');$where_clause = "waktu_masuk LIKE '$thn%'";
        $keterangan = "Tahunan - Tahun: " . $thn;
        break;
    case 'custom':
        $tgl_mulai = isset($_GET['tgl_mulai']) ?$conn->real_escape_string($_GET['tgl_mulai']) : date('Y-m-01');$tgl_selesai = isset($_GET['tgl_selesai']) ?$conn->real_escape_string($_GET['tgl_selesai']) : date('Y-m-d');$where_clause = "waktu_masuk >= '$tgl_mulai 00:00:00' AND waktu_masuk <= '$tgl_selesai 23:59:59'";
        $keterangan = "Kustom - " . date('d/m/Y', strtotime($tgl_mulai)) . " s.d " . date('d/m/Y', strtotime($tgl_selesai));
        break;
    case 'semua':
        $where_clause = "1=1";
        $keterangan = "Semua Data Kunjungan";
        break;
}

$query = "SELECT * FROM kunjungan WHERE $where_clause ORDER BY waktu_masuk DESC";
$result =$conn->query($query);$total_data = ($result) ?$result->num_rows : 0;

$highest_id = 0;
$all_rows_data = [];
if ($result &&$result->num_rows > 0) {
    while ($row =$result->fetch_assoc()) {
        if ($highest_id == 0) {
            $highest_id = intval($row['id_kunjungan']); 
        }
        $all_rows_data[] =$row;
    }
}

$export_params = http_build_query($_GET); 
include 'includes/header.php';
?>

<style>
    @keyframes kedip {
        0% { opacity: 1; }
        50% { opacity: 0.1; }
        100% { opacity: 1; }
    }
    .bulan-kedip {
        animation: kedip 1.5s infinite;
        font-size: 16px;
    }
    .auto-refresh-box {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background-color: #F0FDF4;
        border: 1px solid #BBF7D0;
        color: #166534;
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 12.5px;
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    @keyframes highlightNew {
        0% { background-color: #DCFCE7; }
        100% { background-color: transparent; }
    }
    .new-row-highlight {
        animation: highlightNew 3s ease-out;
    }
</style>

<div class="content-wrapper">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-size: 20px; color: #1E3A8A; margin: 0;">Rekap / Laporan</h2>
        <div class="auto-refresh-box" title="Sistem mendeteksi tamu baru secara cerdas tanpa memuat ulang data lama">
            <span class="bulan-kedip">🌙</span> Live Delta Stream Aktif
        </div>
    </div>

    <!-- Baris Filter -->
    <div class="report-filter-bar">
        <form action="" method="GET" class="filter-left" style="align-items: flex-end;">
            <div>
                <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px;">Pilih Filter:</label>
                <select name="filter" id="filterType" class="filter-input" onchange="toggleFilterInputs()" style="cursor: pointer;">
                    <option value="harian" <?= $filter == 'harian' ? 'selected' : ''; ?>>Harian</option>
                    <option value="bulanan" <?= $filter == 'bulanan' ? 'selected' : ''; ?>>Bulanan</option>
                    <option value="tahunan" <?= $filter == 'tahunan' ? 'selected' : ''; ?>>Tahunan</option>
                    <option value="custom" <?= $filter == 'custom' ? 'selected' : ''; ?>>Kustom Rentang</option>
                    <option value="semua" <?= $filter == 'semua' ? 'selected' : ''; ?>>Semua Data</option>
                </select>
            </div>

            <div id="input_harian" class="filter-group" style="display: none;">
                <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px;">Pilih Tanggal:</label>
                <input type="date" name="tgl_harian" class="filter-input" value="<?= isset($_GET['tgl_harian']) ?$_GET['tgl_harian'] : date('Y-m-d'); ?>">
            </div>

            <div id="input_bulanan" class="filter-group" style="display: none;">
                <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px;">Pilih Bulan:</label>
                <input type="month" name="bln_bulanan" class="filter-input" value="<?= isset($_GET['bln_bulanan']) ?$_GET['bln_bulanan'] : date('Y-m'); ?>">
            </div>

            <div id="input_tahunan" class="filter-group" style="display: none;">
                <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px;">Pilih Tahun:</label>
                <input type="number" name="thn_tahunan" class="filter-input" placeholder="Contoh: 2026" value="<?= isset($_GET['thn_tahunan']) ?$_GET['thn_tahunan'] : date('Y'); ?>">
            </div>

            <div id="input_custom" class="filter-group" style="display: none; gap:10px;">
                <div>
                    <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px;">Mulai:</label>
                    <input type="date" name="tgl_mulai" class="filter-input" value="<?= isset($_GET['tgl_mulai']) ?$_GET['tgl_mulai'] : date('Y-m-01'); ?>">
                </div>
                <div>
                    <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px;">Selesai:</label>
                    <input type="date" name="tgl_selesai" class="filter-input" value="<?= isset($_GET['tgl_selesai']) ?$_GET['tgl_selesai'] : date('Y-m-d'); ?>">
                </div>
            </div>

            <button type="submit" class="btn-filter" style="margin-bottom: 2px;">Tampilkan</button>
            <a href="laporan.php" class="btn-reset" style="margin-bottom: 2px;">Reset</a>
        </form>

        <div class="filter-right">
            <a href="export_excel.php?<?= $export_params; ?>" class="btn-export-excel" target="_blank">
                <span>📗</span> Export Excel
            </a>
            <a href="export_pdf.php?<?= $export_params; ?>" class="btn-export-pdf" target="_blank">
                <span>📕</span> Export PDF
            </a>
        </div>
    </div>

    <!-- Tabel Hasil Laporan -->
    <div class="card-table">
        <div class="card-header">
            <h3 style="color: #3B82F6; font-size: 14px; text-transform: uppercase;">HASIL REKAP (<?= $keterangan; ?>)</h3>
            <span id="labelTotalTamu" style="font-size: 13px; color: #64748B;">Total Tamu: <?= $total_data; ?></span>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="4%" style="text-align: center;">No.</th>
                        <th width="11%">Jam Masuk</th>
                        <th width="13%">Nama</th>
                        <th width="12%">Instansi</th>
                        <th width="11%">No. Telepon</th>
                        <th width="16%">Kategori Kunjungan</th>
                        <th width="16%">Rincian Keperluan</th>
                        <th width="10%">Bertemu Dengan</th>
                        <th width="7%" style="text-align: center;">Foto</th>
                    </tr>
                </thead>
                <tbody id="tabelKunjunganBody">
                    <?php 
                    if(count($all_rows_data) > 0) {$no = 1;
                        foreach($all_rows_data as $row) {$kategori = !empty($row['kategori']) ? htmlspecialchars($row['kategori']) : 'Umum / Lainnya';
                    ?>
                    <tr id="row-<?= $row['id_kunjungan']; ?>">
                        <td class="row-number" style="text-align: center; vertical-align: middle;"><?= $no++; ?></td>
                        <td style="vertical-align: middle;"><?= date('d/m/Y H:i:s', strtotime($row['waktu_masuk'])); ?></td>
                        <td style="vertical-align: middle;"><strong><?= htmlspecialchars($row['nama_lengkap']); ?></strong></td>
                        <td style="vertical-align: middle;"><?= htmlspecialchars($row['instansi']); ?></td>
                        <td style="vertical-align: middle;"><?= htmlspecialchars($row['no_telepon']); ?></td>
                        
                        <!-- KOLOM KATEGORI TERPISAH -->
                        <td style="vertical-align: middle;">
                            <span style="background: #F0FDF4; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 11.5px; border: 1px solid #BBF7D0; display: inline-block; font-weight: bold;">
                                <?= $kategori; ?>
                            </span>
                        </td>
                        
                        <!-- KOLOM KEPERLUAN TERPISAH -->
                        <td style="vertical-align: middle; color: #475569; font-size: 13px;">
                            <?= htmlspecialchars($row['keperluan']); ?>
                        </td>

                        <td style="vertical-align: middle;"><?= htmlspecialchars($row['bertemu_dengan']); ?></td>
                        
                        <td style="text-align: center; vertical-align: middle;">
                            <?php if(!empty($row['foto'])) { ?>
                                <a href="../assets/uploads/<?= htmlspecialchars($row['foto']); ?>" target="_blank">
                                    <img src="../assets/uploads/<?= htmlspecialchars($row['foto']); ?>" alt="Foto" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                </a>
                            <?php } else { ?>
                                <span style="font-size: 11px; color: #94A3B8;">No Photo</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr id='emptyRow'><td colspan='9' style='text-align:center;'>Tidak ada data kunjungan pada periode ini.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    let lastId = <?= $highest_id; ?>;

    function toggleFilterInputs() {
        const filterType = document.getElementById('filterType').value;
        const groups = ['input_harian', 'input_bulanan', 'input_tahunan', 'input_custom'];
        
        groups.forEach(id => {
            document.getElementById(id).style.display = 'none';
        });

        if (filterType === 'custom') {
            document.getElementById('input_custom').style.display = 'flex';
        } else if (filterType !== 'semua') {
            document.getElementById('input_' + filterType).style.display = 'block';
        }
    }

    toggleFilterInputs();

    // Polling interval setiap 5 detik dengan URL AJAX yang presisi
    setInterval(function() {
        const currentParams = window.location.search;
        const separator = currentParams ? '&' : '?';
        const ajaxUrl = 'laporan.php' + currentParams + separator + 'ajax=check_new&last_id=' + lastId;

        fetch(ajaxUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.new_rows && data.new_rows.length > 0) {
                    const tbody = document.getElementById('tabelKunjunganBody');
                    
                    const emptyRow = document.getElementById('emptyRow');
                    if (emptyRow) {
                        emptyRow.remove();
                    }

                    data.new_rows.forEach(row => {
                        const newTr = document.createElement('tr');
                        newTr.id = 'row-' + row.id_kunjungan;
                        newTr.className = 'new-row-highlight';

                        // Struktur baris baru dengan Kolom Kategori & Keperluan Terpisah
                        newTr.innerHTML = `
                            <td class="row-number" style="text-align: center; vertical-align: middle;">1</td>
                            <td style="vertical-align: middle;">${row.waktu_masuk}</td>
                            <td style="vertical-align: middle;"><strong>${row.nama_lengkap}</strong></td>
                            <td style="vertical-align: middle;">${row.instansi}</td>
                            <td style="vertical-align: middle;">${row.no_telepon}</td>
                            <td style="vertical-align: middle;">
                                <span style="background: #F0FDF4; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 11.5px; border: 1px solid #BBF7D0; display: inline-block; font-weight: bold;">
                                    ${row.kategori}
                                </span>
                            </td>
                            <td style="vertical-align: middle; color: #475569; font-size: 13px;">${row.keperluan}</td>
                            <td style="vertical-align: middle;">${row.bertemu_dengan}</td>
                            <td style="text-align: center; vertical-align: middle;">
                                ${row.foto ? 
                                    `<a href="../assets/uploads/${row.foto}" target="_blank">
                                        <img src="../assets/uploads/${row.foto}" alt="Foto" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    </a>` 
                                    : `<span style="font-size: 11px; color: #94A3B8;">No Photo</span>`
                                }
                            </td>
                        `;

                        tbody.insertBefore(newTr, tbody.firstChild);
                    });

                    // Perbarui nomor urut baris tabel secara otomatis
                    const rows = tbody.querySelectorAll('tr');
                    rows.forEach((tr, index) => {
                        const numCell = tr.querySelector('.row-number');
                        if (numCell) {
                            numCell.innerText = index + 1;
                        }
                    });

                    lastId = data.max_id;
                }

                if (data.total !== undefined) {
                    document.getElementById('labelTotalTamu').innerText = 'Total Tamu: ' + data.total;
                }
            })
            .catch(error => {
                // Abaikan error jaringan sesaat
            });
    }, 5000);
</script>

<?php include 'includes/footer.php'; ?>