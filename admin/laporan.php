<?php
// admin/laporan.php

error_reporting(0);
@ini_set('display_errors', 0);
date_default_timezone_set('Asia/Makassar');

require_once '../config/database.php';

// ==========================================================
// PROSES HAPUS SEMUA DATA DENGAN PIN
// ==========================================================
if (isset($_POST['hapus_semua'])) {
    $pin_input = $_POST['pin_keamanan'];
    if ($pin_input === '092367') {
        if ($conn->query("TRUNCATE TABLE kunjungan")) {
            echo "<script>alert('BERHASIL! Seluruh data laporan telah dihapus permanen.'); window.location.href='laporan.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus data: " . $conn->error . "'); window.location.href='laporan.php';</script>";
        }
    } else {
        echo "<script>alert('GAGAL! PIN yang Anda masukkan salah. Akses ditolak.'); window.location.href='laporan.php';</script>";
    }
    exit;
}

// ==========================================================
// 1. ENDPOINT AJAX: CEK DATA TERBARU (LIVE STREAM)
// ==========================================================
if (isset($_GET['ajax']) && $_GET['ajax'] == 'check_new') {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');

    $last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
    
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'harian';
    $kat_filter = isset($_GET['kategori_filter']) ? $conn->real_escape_string($_GET['kategori_filter']) : 'semua_kategori';
    
    $where_clause = "1=1"; 

    switch ($filter) {
        case 'harian':
            $tgl = isset($_GET['tgl_harian']) && !empty($_GET['tgl_harian']) ? $conn->real_escape_string($_GET['tgl_harian']) : date('Y-m-d');
            $where_clause = "waktu_masuk LIKE '$tgl%'"; break;
        case 'bulanan':
            $bln = isset($_GET['bln_bulanan']) && !empty($_GET['bln_bulanan']) ? $conn->real_escape_string($_GET['bln_bulanan']) : date('Y-m');
            $where_clause = "waktu_masuk LIKE '$bln%'"; break;
        case 'tahunan':
            $thn = isset($_GET['thn_tahunan']) && !empty($_GET['thn_tahunan']) ? $conn->real_escape_string($_GET['thn_tahunan']) : date('Y');
            $where_clause = "waktu_masuk LIKE '$thn%'"; break;
        case 'custom':
            $tgl_mulai = isset($_GET['tgl_mulai']) ? $conn->real_escape_string($_GET['tgl_mulai']) : date('Y-m-01');
            $tgl_selesai = isset($_GET['tgl_selesai']) ? $conn->real_escape_string($_GET['tgl_selesai']) : date('Y-m-d');
            $where_clause = "waktu_masuk >= '$tgl_mulai 00:00:00' AND waktu_masuk <= '$tgl_selesai 23:59:59'"; break;
        case 'semua': 
            $where_clause = "1=1"; break;
    }

    if ($kat_filter !== 'semua_kategori') {
        $where_clause .= " AND kategori = '$kat_filter'";
    }

    $query = "SELECT * FROM kunjungan WHERE id_kunjungan > $last_id AND ($where_clause) ORDER BY id_kunjungan ASC";
    $result = $conn->query($query);
    
    $new_rows = [];
    $max_id = $last_id;

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['id_kunjungan'] > $max_id) $max_id = intval($row['id_kunjungan']);
            $new_rows[] = [
                'id_kunjungan' => $row['id_kunjungan'],
                'waktu_masuk'  => date('d/m/Y H:i:s', strtotime($row['waktu_masuk'])),
                'nama_lengkap' => htmlspecialchars($row['nama_lengkap']),
                'instansi'     => htmlspecialchars($row['instansi']),
                'alamat'       => htmlspecialchars($row['alamat'] ?? '-'),
                'no_telepon'   => htmlspecialchars($row['no_telepon'] ?? '-'),
                'kategori'     => !empty($row['kategori']) ? htmlspecialchars($row['kategori']) : 'Umum / Lainnya',
                'keperluan'    => htmlspecialchars($row['keperluan']),
                'bertemu_dengan'=> htmlspecialchars($row['bertemu_dengan']),
                'foto'         => htmlspecialchars($row['foto'])
            ];
        }
    }

    $count_res = $conn->query("SELECT COUNT(*) as total FROM kunjungan WHERE $where_clause");
    $total_data = ($count_res) ? intval($count_res->fetch_assoc()['total']) : 0;
    
    echo json_encode(['success' => true, 'new_rows' => $new_rows, 'max_id' => $max_id, 'total' =>$total_data]); 
    exit();
}

// ==========================================================
// 2. RENDER HALAMAN UTAMA LAPORAN
// ==========================================================
$filter = isset($_GET['filter']) ?$_GET['filter'] : 'harian';
$kat_filter = isset($_GET['kategori_filter']) ? $conn->real_escape_string($_GET['kategori_filter']) : 'semua_kategori';

$where_clause = "1=1"; 
$keterangan = "Semua Waktu";

switch ($filter) {
    case 'harian':
        $tgl = isset($_GET['tgl_harian']) && !empty($_GET['tgl_harian']) ?$conn->real_escape_string($_GET['tgl_harian']) : date('Y-m-d');$where_clause = "waktu_masuk LIKE '$tgl%'"; 
        $keterangan = "Tanggal: " . date('d F Y', strtotime($tgl)); 
        break;
    case 'bulanan':
        $bln = isset($_GET['bln_bulanan']) && !empty($_GET['bln_bulanan']) ?$conn->real_escape_string($_GET['bln_bulanan']) : date('Y-m');$where_clause = "waktu_masuk LIKE '$bln%'"; 
        $keterangan = "Bulan: " . date('F Y', strtotime($bln . "-01")); 
        break;
    case 'tahunan':
        $thn = isset($_GET['thn_tahunan']) && !empty($_GET['thn_tahunan']) ?$conn->real_escape_string($_GET['thn_tahunan']) : date('Y');$where_clause = "waktu_masuk LIKE '$thn%'"; 
        $keterangan = "Tahun: " . $thn; 
        break;
    case 'custom':
        $tgl_mulai = isset($_GET['tgl_mulai']) ?$conn->real_escape_string($_GET['tgl_mulai']) : date('Y-m-01');$tgl_selesai = isset($_GET['tgl_selesai']) ?$conn->real_escape_string($_GET['tgl_selesai']) : date('Y-m-d');$where_clause = "waktu_masuk >= '$tgl_mulai 00:00:00' AND waktu_masuk <= '$tgl_selesai 23:59:59'";
        $keterangan = date('d/m/Y', strtotime($tgl_mulai)) . " s/d " . date('d/m/Y', strtotime($tgl_selesai)); 
        break;
    case 'semua': 
        $where_clause = "1=1"; 
        $keterangan = "Seluruh Waktu"; 
        break;
}

if ($kat_filter !== 'semua_kategori') {$where_clause .= " AND kategori = '$kat_filter'";
    $keterangan .= " | Kategori: " . htmlspecialchars($kat_filter);
} else {
    $keterangan .= " | Semua Kategori";
}

$result =$conn->query("SELECT * FROM kunjungan WHERE $where_clause ORDER BY waktu_masuk DESC");
$total_data = ($result) ?$result->num_rows : 0;
$highest_id = 0; 
$all_rows_data = [];

if ($result &&$result->num_rows > 0) {
    while ($row =$result->fetch_assoc()) {
        if ($highest_id == 0) $highest_id = intval($row['id_kunjungan']); 
        $all_rows_data[] =$row;
    }
}

$export_params = http_build_query($_GET); 
include 'includes/header.php';
?>

<style>
    @keyframes kedip { 0% { opacity: 1; } 50% { opacity: 0.1; } 100% { opacity: 1; } }
    .bulan-kedip { animation: kedip 1.5s infinite; font-size: 16px; }
    .auto-refresh-box { display: inline-flex; align-items: center; gap: 8px; background-color: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; padding: 6px 14px; border-radius: 50px; font-size: 12.5px; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    @keyframes highlightNew { 0% { background-color: #DCFCE7; } 100% { background-color: transparent; } }
    .new-row-highlight { animation: highlightNew 3s ease-out; }
    
    /* WADAH UTAMA FILTER - SEMUA SEJAJAR */
    .unified-filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: flex-end; /* Memastikan semua elemen sejajar di garis bawah */
        flex-wrap: wrap;
        background: #F8FAFC;
        padding: 15px 20px;
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        gap: 20px;
    }
    .filter-form-group {
        display: flex;
        gap: 15px;
        align-items: flex-end;
        flex-wrap: wrap;
        margin: 0;
    }
    .action-buttons-group {
        display: flex;
        gap: 10px;
        align-items: flex-end;
        flex-wrap: wrap;
    }
</style>

<div class="content-wrapper">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-size: 20px; color: #1E3A8A; margin: 0;">📑 Rekap / Laporan</h2>
        <div class="auto-refresh-box" title="Sistem mendeteksi tamu baru secara otomatis"><span class="bulan-kedip">🌙</span> Live Delta Stream Aktif</div>
    </div>

    <!-- AREA FILTER & EXPORT YANG SUDAH DIJADIKAN SATU BARIS -->
    <div class="unified-filter-bar">
        
        <!-- BAGIAN KIRI: FORM FILTER -->
        <form action="" method="GET" class="filter-form-group">
            <div>
                <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px; font-weight:600;">Pilih Waktu:</label>
                <select name="filter" id="filterType" class="filter-input" onchange="toggleFilterInputs()" style="min-width: 140px;">
                    <option value="harian" <?= $filter == 'harian' ? 'selected' : ''; ?>>Harian</option>
                    <option value="bulanan" <?= $filter == 'bulanan' ? 'selected' : ''; ?>>Bulanan</option>
                    <option value="tahunan" <?= $filter == 'tahunan' ? 'selected' : ''; ?>>Tahunan</option>
                    <option value="custom" <?= $filter == 'custom' ? 'selected' : ''; ?>>Kustom Rentang</option>
                    <option value="semua" <?= $filter == 'semua' ? 'selected' : ''; ?>>Semua Waktu</option>
                </select>
            </div>

            <!-- Input Dinamis Waktu -->
            <div id="input_harian" class="filter-group" style="display: none;">
                <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px; font-weight:600;">Pilih Tanggal:</label>
                <input type="date" name="tgl_harian" class="filter-input" value="<?= isset($_GET['tgl_harian']) ?$_GET['tgl_harian'] : date('Y-m-d'); ?>">
            </div>
            
            <div id="input_bulanan" class="filter-group" style="display: none;">
                <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px; font-weight:600;">Pilih Bulan:</label>
                <input type="month" name="bln_bulanan" class="filter-input" value="<?= isset($_GET['bln_bulanan']) ?$_GET['bln_bulanan'] : date('Y-m'); ?>">
            </div>
            
            <div id="input_tahunan" class="filter-group" style="display: none;">
                <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px; font-weight:600;">Pilih Tahun:</label>
                <input type="number" name="thn_tahunan" class="filter-input" placeholder="Contoh: 2026" value="<?= isset($_GET['thn_tahunan']) ?$_GET['thn_tahunan'] : date('Y'); ?>" style="max-width:100px;">
            </div>
            
            <div id="input_custom" class="filter-group" style="display: none; gap:10px;">
                <div>
                    <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px; font-weight:600;">Mulai:</label>
                    <input type="date" name="tgl_mulai" class="filter-input" value="<?= isset($_GET['tgl_mulai']) ?$_GET['tgl_mulai'] : date('Y-m-01'); ?>">
                </div>
                <div>
                    <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px; font-weight:600;">Selesai:</label>
                    <input type="date" name="tgl_selesai" class="filter-input" value="<?= isset($_GET['tgl_selesai']) ?$_GET['tgl_selesai'] : date('Y-m-d'); ?>">
                </div>
            </div>

            <!-- Filter Kategori -->
            <div>
                <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px; font-weight:600;">Filter Kategori:</label>
                <select name="kategori_filter" class="filter-input" style="min-width: 180px;">
                    <option value="semua_kategori" <?= $kat_filter == 'semua_kategori' ? 'selected' : ''; ?>>Semua Kategori (Tampil Semua)</option>
                    <?php
                    $q_kat =$conn->query("SELECT DISTINCT kategori FROM kunjungan WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC");
                    if($q_kat &&$q_kat->num_rows > 0) {
                        while($r_kat = $q_kat->fetch_assoc()) {$kategori_val = htmlspecialchars($r_kat['kategori']);$selected = ($kat_filter ==$r_kat['kategori']) ? 'selected' : '';
                            echo "<option value=\"$kategori_val\" $selected>$kategori_val</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn-filter" style="margin-bottom: 0; padding: 10px 15px;">Cari Data</button>
                <a href="laporan.php" class="btn-reset" style="margin-bottom: 0; padding: 10px 15px;">Reset Semua</a>
            </div>
        </form>

        <!-- BAGIAN KANAN: TOMBOL EXPORT & HAPUS -->
        <div class="action-buttons-group">
            <a href="export_excel.php?<?= $export_params; ?>" class="btn-export-excel" target="_blank" style="padding: 10px 15px; margin: 0;">📗 Export Excel</a>
            <a href="export_pdf.php?<?= $export_params; ?>" class="btn-export-pdf" target="_blank" style="padding: 10px 15px; margin: 0;">📕 Export PDF</a>
            <button onclick="konfirmasiHapusSemua()" style="background: #EF4444; color: white; padding: 10px 15px; border-radius: 8px; border: none; font-size: 13px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2); margin: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                Hapus Semua
            </button>
        </div>
        
    </div>

    <!-- Tabel Hasil Laporan -->
    <div class="card-table" style="margin-top: 20px;">
        <div class="card-header">
            <h3 style="color: #3B82F6; font-size: 14px; text-transform: uppercase;">HASIL REKAP (<?= $keterangan; ?>)</h3>
            <span id="labelTotalTamu" style="font-size: 13px; color: #64748B; font-weight: 600; background: #F1F5F9; padding: 6px 12px; border-radius: 20px;">
                Total: <?= $total_data; ?> Tamu
            </span>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="3%" style="text-align: center;">No</th>
                        <th width="10%">Waktu</th>
                        <th width="13%">Nama Tamu</th>
                        <th width="11%">Instansi</th>
                        <th width="12%">Alamat</th>
                        <th width="11%">No. WA</th>
                        <th width="12%">Kategori</th>
                        <th width="12%">Keperluan</th>
                        <th width="10%">Bertemu</th>
                        <th width="6%" style="text-align: center;">Foto</th>
                    </tr>
                </thead>
                <tbody id="tabelKunjunganBody">
                    <?php 
                    if(count($all_rows_data) > 0) {$no = 1;
                        foreach($all_rows_data as $row) {$kategori = !empty($row['kategori']) ? htmlspecialchars($row['kategori']) : 'Umum / Lainnya';
                            $alamat = !empty($row['alamat']) ? htmlspecialchars($row['alamat']) : '-';$no_wa = !empty($row['no_telepon']) ? htmlspecialchars($row['no_telepon']) : '-';
                    ?>
                    <tr id="row-<?= $row['id_kunjungan']; ?>">
                        <td class="row-number" style="text-align: center; vertical-align: middle;"><?= $no++; ?></td>
                        <td style="vertical-align: middle;"><?= date('d/m/Y H:i:s', strtotime($row['waktu_masuk'])); ?></td>
                        <td style="vertical-align: middle;"><strong><?= htmlspecialchars($row['nama_lengkap']); ?></strong></td>
                        <td style="vertical-align: middle;"><?= htmlspecialchars($row['instansi']); ?></td>
                        <td style="vertical-align: middle;"><?= $alamat; ?></td>
                        <td style="vertical-align: middle;"><?= $no_wa; ?></td>
                        <td style="vertical-align: middle;">
                            <span style="background: #F0FDF4; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 11.5px; border: 1px solid #BBF7D0; display: inline-block; font-weight: bold;">
                                <?= $kategori; ?>
                            </span>
                        </td>
                        <td style="vertical-align: middle; color: #475569; font-size: 13px;"><?= htmlspecialchars($row['keperluan']); ?></td>
                        <td style="vertical-align: middle;"><?= htmlspecialchars($row['bertemu_dengan']); ?></td>
                        <td style="text-align: center; vertical-align: middle;">
                            <?php if(!empty($row['foto'])) { ?>
                                <a href="../assets/uploads/<?= htmlspecialchars($row['foto']); ?>" target="_blank" title="Klik untuk memperbesar">
                                    <img src="../assets/uploads/<?= htmlspecialchars($row['foto']); ?>" alt="Foto" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                </a>
                            <?php } else { ?>
                                <span style="font-size: 11px; color: #94A3B8;">No Photo</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } } else { ?>
                        <tr id='emptyRow'><td colspan='10' style='text-align:center; padding: 20px;'>Tidak ada data kunjungan yang cocok dengan filter yang dipilih.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function konfirmasiHapusSemua() {
        let konfirmasi_awal = confirm("PERINGATAN BAHAYA!\n\nApakah Anda YAKIN ingin menghapus SELURUH data kunjungan secara permanen? Data yang dihapus tidak dapat dikembalikan.");
        if (konfirmasi_awal) {
            let pin = prompt("TINDAKAN OTORISASI DIPERLUKAN:\n\nMasukkan 6-digit PIN Keamanan Anda untuk melanjutkan penghapusan:");
            if (pin === null || pin === "") {
                alert("Tindakan dibatalkan.");
            } else if (pin === "092367") {
                let form = document.createElement("form"); form.method = "POST";
                let inputAksi = document.createElement("input"); inputAksi.type = "hidden"; inputAksi.name = "hapus_semua"; inputAksi.value = "1";
                let inputPin = document.createElement("input"); inputPin.type = "hidden"; inputPin.name = "pin_keamanan"; inputPin.value = pin;
                form.appendChild(inputAksi); form.appendChild(inputPin); document.body.appendChild(form); form.submit();
            } else { alert("GAGAL! PIN Kemananan yang Anda masukkan SALAH."); }
        }
    }

    let lastId = <?= $highest_id; ?>;

    function toggleFilterInputs() {
        const filterType = document.getElementById('filterType').value;
        const groups = ['input_harian', 'input_bulanan', 'input_tahunan', 'input_custom'];
        groups.forEach(id => { document.getElementById(id).style.display = 'none'; });
        if (filterType === 'custom') { document.getElementById('input_custom').style.display = 'flex'; } 
        else if (filterType !== 'semua') { document.getElementById('input_' + filterType).style.display = 'block'; }
    }
    toggleFilterInputs();

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
                    if (emptyRow) emptyRow.remove();

                    data.new_rows.forEach(row => {
                        const newTr = document.createElement('tr');
                        newTr.id = 'row-' + row.id_kunjungan;
                        newTr.className = 'new-row-highlight';

                        newTr.innerHTML = `
                            <td class="row-number" style="text-align: center; vertical-align: middle;">1</td>
                            <td style="vertical-align: middle;">${row.waktu_masuk}</td>
                            <td style="vertical-align: middle;"><strong>${row.nama_lengkap}</strong></td>
                            <td style="vertical-align: middle;">${row.instansi}</td>
                            <td style="vertical-align: middle;">${row.alamat}</td>
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
                                    `<a href="../assets/uploads/${row.foto}" target="_blank" title="Klik untuk memperbesar"><img src="../assets/uploads/${row.foto}" alt="Foto" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'"></a>` 
                                    : `<span style="font-size: 11px; color: #94A3B8;">No Photo</span>`
                                }
                            </td>
                        `;
                        tbody.insertBefore(newTr, tbody.firstChild);
                    });

                    const rows = tbody.querySelectorAll('tr');
                    rows.forEach((tr, index) => {
                        const numCell = tr.querySelector('.row-number');
                        if (numCell) numCell.innerText = index + 1;
                    });
                    lastId = data.max_id;
                }
                
                if (data.total !== undefined) { 
                    document.getElementById('labelTotalTamu').innerText = 'Total: ' + data.total + ' Tamu'; 
                }
            }).catch(error => {});
    }, 5000);
</script>

<?php include 'includes/footer.php'; ?>