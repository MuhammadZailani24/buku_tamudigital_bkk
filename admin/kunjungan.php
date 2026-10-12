<?php
// admin/kunjungan.php

error_reporting(0);
@ini_set('display_errors', 0);
date_default_timezone_set('Asia/Makassar');

require_once '../config/database.php';

// ==========================================================
// PROSES HAPUS SEMUA DATA DENGAN PIN
// ==========================================================
if (isset($_POST['hapus_semua'])) {
    $pin_input = $_POST['pin_keamanan'];
    
    // Validasi Keamanan PIN Server-Side
    if ($pin_input === '092367') {
        // Hapus data dan reset ID menggunakan TRUNCATE
        if ($conn->query("TRUNCATE TABLE kunjungan")) {
            echo "<script>alert('BERHASIL! Seluruh data kunjungan telah dihapus permanen dari sistem.'); window.location.href='kunjungan.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus data: " . $conn->error . "'); window.location.href='kunjungan.php';</script>";
        }
    } else {
        echo "<script>alert('GAGAL! PIN yang Anda masukkan salah. Akses ditolak.'); window.location.href='kunjungan.php';</script>";
    }
    exit;
}

include 'includes/header.php';

// Ambil riwayat kunjungan (ditampilkan dari yang terbaru)
$q_tabel = $conn->query("SELECT * FROM kunjungan ORDER BY waktu_masuk DESC");
?>

<div class="content-wrapper">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <h2 style="font-size: 20px; color: #1E3A8A; margin: 0;">📋 Daftar Kunjungan Tamu</h2>
        
        <!-- Tombol Hapus Semua dengan Konfirmasi PIN -->
        <button onclick="konfirmasiHapusSemua()" style="background: #EF4444; color: white; padding: 10px 18px; border-radius: 8px; border: none; font-size: 13.5px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2);">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            Hapus Semua Data
        </button>
    </div>

    <div class="card-table">
        <div class="card-header">
            <h3 style="color: #1E3A8A; font-size: 15px; text-transform: uppercase;">Riwayat Seluruh Kunjungan</h3>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="4%" style="text-align: center;">No.</th>
                        <th width="10%">Waktu</th>
                        <th width="13%">Nama Tamu</th>
                        <th width="12%">Asal</th>
                        <th width="13%">Alamat</th>
                        <th width="14%">Kategori Kunjungan</th>
                        <th width="14%">Rincian Keperluan</th>
                        <th width="12%">Bertemu Dengan</th>
                        <th width="8%" style="text-align: center;">Foto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($q_tabel && $q_tabel->num_rows > 0) {
                        $no = 1;
                        while ($row = $q_tabel->fetch_assoc()) { 
                            $kategori = !empty($row['kategori']) ? htmlspecialchars($row['kategori']) : 'Umum / Lainnya';
                            $alamat = !empty($row['alamat']) ? htmlspecialchars($row['alamat']) : '-';
                    ?>
                    <tr>
                        <td style="text-align: center; vertical-align: middle;"><?= $no++; ?></td>
                        <td style="vertical-align: middle;"><?= date('d/m/Y H:i:s', strtotime($row['waktu_masuk'])); ?></td>
                        <td style="vertical-align: middle;"><strong><?= htmlspecialchars($row['nama_lengkap']); ?></strong></td>
                        <td style="vertical-align: middle;"><?= htmlspecialchars($row['instansi']); ?></td>
                        <td style="vertical-align: middle;"><?= $alamat; ?></td>
                        
                        <td style="vertical-align: middle;">
                            <span style="background: #F0FDF4; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 11.5px; border: 1px solid #BBF7D0; display: inline-block; font-weight: bold;">
                                <?= $kategori; ?>
                            </span>
                        </td>
                        
                        <td style="vertical-align: middle; color: #475569; font-size: 13px;">
                            <?= htmlspecialchars($row['keperluan']); ?>
                        </td>

                        <td style="vertical-align: middle;"><?= htmlspecialchars($row['bertemu_dengan']); ?></td>
                        
                        <td style="text-align: center; vertical-align: middle;">
                            <?php if(!empty($row['foto'])) { ?>
                                <a href="../assets/uploads/<?= htmlspecialchars($row['foto']); ?>" target="_blank" title="Klik untuk memperbesar foto">
                                    <img src="../assets/uploads/<?= htmlspecialchars($row['foto']); ?>" alt="Foto" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #E2E8F0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                </a>
                            <?php } else { ?>
                                <span style="font-size: 11px; color: #94A3B8;">No Photo</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='9' style='text-align:center; padding: 20px;'>Belum ada riwayat kunjungan.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SCRIPT AUTENTIKASI HAPUS -->
<script>
function konfirmasiHapusSemua() {
    let konfirmasi_awal = confirm("PERINGATAN BAHAYA!\n\nApakah Anda YAKIN ingin menghapus SELURUH data kunjungan secara permanen? Data yang dihapus tidak dapat dikembalikan.");
    
    if (konfirmasi_awal) {
        let pin = prompt("TINDAKAN OTORISASI DIPERLUKAN:\n\nMasukkan 6-digit PIN Keamanan Anda untuk melanjutkan penghapusan:");
        
        if (pin === null || pin === "") {
            alert("Tindakan dibatalkan.");
        } else if (pin === "092367") {
            // Jika PIN benar, buat form rahasia untuk mengeksekusi PHP penghapusan
            let form = document.createElement("form");
            form.method = "POST";
            form.action = "";
            
            let inputAksi = document.createElement("input");
            inputAksi.type = "hidden";
            inputAksi.name = "hapus_semua";
            inputAksi.value = "1";
            
            let inputPin = document.createElement("input");
            inputPin.type = "hidden";
            inputPin.name = "pin_keamanan";
            inputPin.value = pin;
            
            form.appendChild(inputAksi);
            form.appendChild(inputPin);
            document.body.appendChild(form);
            
            form.submit();
        } else {
            alert("GAGAL! PIN Kemananan yang Anda masukkan SALAH.");
        }
    }
}
</script>

<?php include 'includes/footer.php'; ?>