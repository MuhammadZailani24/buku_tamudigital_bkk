<?php
// admin/kunjungan.php
session_start();
if (!isset($_SESSION['admin_username'])) { header("Location: login.php"); exit; }

error_reporting(0);
@ini_set('display_errors', 0);
date_default_timezone_set('Asia/Makassar');

require_once '../config/database.php';

$is_superadmin = ($_SESSION['admin_role'] === 'super_admin');
$admin_wilker  = $conn->real_escape_string($_SESSION['admin_wilker']);
$where_akses   = $is_superadmin ? "1=1" : "wilker = '$admin_wilker'";

// Logika Hapus Data (Super Admin = Truncate Semua | Admin Wilker = Delete Sesuai Wilker)
if (isset($_POST['hapus_semua'])) {
    $pin_input = $_POST['pin_keamanan'];
    if ($pin_input === '092367') {
        if ($is_superadmin) {
            $query_hapus = "TRUNCATE TABLE kunjungan";
        } else {
            $query_hapus = "DELETE FROM kunjungan WHERE wilker = '$admin_wilker'";
        }
        
        if ($conn->query($query_hapus)) {
            echo "<script>alert('BERHASIL! Data kunjungan telah dihapus.'); window.location.href='kunjungan.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus data: " . $conn->error . "'); window.location.href='kunjungan.php';</script>";
        }
    } else {
        echo "<script>alert('GAGAL! PIN yang Anda masukkan salah. Akses ditolak.'); window.location.href='kunjungan.php';</script>";
    }
    exit;
}

include 'includes/header.php';
$q_tabel = $conn->query("SELECT * FROM kunjungan WHERE $where_akses ORDER BY waktu_masuk DESC");
?>

<div class="content-wrapper">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <h2 style="font-size: 20px; color: #1E3A8A; margin: 0;">📋 Daftar Kunjungan Tamu</h2>
        
        <button onclick="konfirmasiHapusSemua()" style="background: #EF4444; color: white; padding: 10px 18px; border-radius: 8px; border: none; font-size: 13.5px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2);">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            <?= $is_superadmin ? 'Hapus Semua Data' : 'Hapus Data Wilker Saya'; ?>
        </button>
    </div>

    <div class="card-table">
        <div class="card-header">
            <h3 style="color: #1E3A8A; font-size: 15px; text-transform: uppercase;">Riwayat Kunjungan <?= $is_superadmin ? '(Semua Wilker)' : "($admin_wilker)"; ?></h3>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="3%" style="text-align: center;">No.</th>
                        <th width="9%">Waktu</th>
                        <th width="10%">Wilker</th>
                        <th width="12%">Nama Tamu</th>
                        <th width="10%">Instansi</th>
                        <th width="11%">Alamat</th>
                        <th width="10%">No. WA</th>
                        <th width="11%">Kategori</th>
                        <th width="11%">Keperluan</th>
                        <th width="8%">Bertemu</th>
                        <th width="5%" style="text-align: center;">Foto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($q_tabel && $q_tabel->num_rows > 0) {
                        $no = 1;
                        while ($row = $q_tabel->fetch_assoc()) { 
                            $wilker = !empty($row['wilker']) ? htmlspecialchars($row['wilker']) : '-';
                            $kategori = !empty($row['kategori']) ? htmlspecialchars($row['kategori']) : 'Umum / Lainnya';
                            $alamat = !empty($row['alamat']) ? htmlspecialchars($row['alamat']) : '-';
                            $no_wa = !empty($row['no_telepon']) ? htmlspecialchars($row['no_telepon']) : '-';
                    ?>
                    <tr>
                        <td style="text-align: center; vertical-align: middle;"><?= $no++; ?></td>
                        <td style="vertical-align: middle;"><?= date('d/m/Y H:i', strtotime($row['waktu_masuk'])); ?></td>
                        <td style="vertical-align: middle;"><span style="color:#0369A1; font-weight:bold; font-size:12px;"><?= $wilker; ?></span></td>
                        <td style="vertical-align: middle;"><strong><?= htmlspecialchars($row['nama_lengkap']); ?></strong></td>
                        <td style="vertical-align: middle;"><?= htmlspecialchars($row['instansi']); ?></td>
                        <td style="vertical-align: middle;"><?= $alamat; ?></td>
                        <td style="vertical-align: middle;"><?= $no_wa; ?></td>
                        <td style="vertical-align: middle;"><span style="background: #F0FDF4; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 11.5px; border: 1px solid #BBF7D0; font-weight: bold;"><?= $kategori; ?></span></td>
                        <td style="vertical-align: middle; color: #475569; font-size: 13px;"><?= htmlspecialchars($row['keperluan']); ?></td>
                        <td style="vertical-align: middle;"><?= htmlspecialchars($row['bertemu_dengan']); ?></td>
                        <td style="text-align: center; vertical-align: middle;">
                            <?php if(!empty($row['foto'])) { ?>
                                <a href="../assets/uploads/<?= htmlspecialchars($row['foto']); ?>" target="_blank"><img src="../assets/uploads/<?= htmlspecialchars($row['foto']); ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0;"></a>
                            <?php } else { ?>
                                <span style="font-size: 11px; color: #94A3B8;">No Photo</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } } else { echo "<tr><td colspan='11' style='text-align:center; padding: 20px;'>Belum ada riwayat kunjungan.</td></tr>"; } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function konfirmasiHapusSemua() {
    let msg = <?= $is_superadmin ? '"Apakah Anda YAKIN ingin menghapus SELURUH data kunjungan secara permanen?"' : '"Apakah Anda YAKIN ingin menghapus data kunjungan di Wilker Anda secara permanen?"'; ?>;
    let konfirmasi_awal = confirm("PERINGATAN BAHAYA!\n\n" + msg);
    if (konfirmasi_awal) {
        let pin = prompt("Masukkan 6-digit PIN Keamanan Anda:");
        if (pin === null || pin === "") { alert("Tindakan dibatalkan."); } 
        else if (pin === "092367") {
            let form = document.createElement("form"); form.method = "POST";
            let inputAksi = document.createElement("input"); inputAksi.type = "hidden"; inputAksi.name = "hapus_semua"; inputAksi.value = "1";
            let inputPin = document.createElement("input"); inputPin.type = "hidden"; inputPin.name = "pin_keamanan"; inputPin.value = pin;
            form.appendChild(inputAksi); form.appendChild(inputPin); document.body.appendChild(form); form.submit();
        } else { alert("GAGAL! PIN Kemananan yang Anda masukkan SALAH."); }
    }
}
</script>

<?php include 'includes/footer.php'; ?>