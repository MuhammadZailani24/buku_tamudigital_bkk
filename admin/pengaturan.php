<?php
// admin/pengaturan.php

// Proteksi Error & Session
error_reporting(0);
@ini_set('display_errors', 0);
include 'includes/header.php';
require_once '../config/database.php';

$pesan = '';
$username_sesi = $_SESSION['username'] ?? ''; // Mengambil sesi username aktif

// 1. PROSES UBAH PROFIL & PASSWORD
if (isset($_POST['simpan_pengaturan'])) {
    $nama_baru = $conn->real_escape_string($_POST['nama_admin']);
    $pass_baru = $_POST['password_baru'];
    
    // Siapkan query update nama
    $query_update = "UPDATE admin SET nama_admin = '$nama_baru'";
    
    // Jika input password baru diisi, maka update juga passwordnya
    if (!empty($pass_baru)) {
        $pass_hash = md5($pass_baru); // MD5 sesuai dengan format database Anda
        $query_update .= ", password = '$pass_hash'";
    }
    
    $query_update .= " WHERE username = '$username_sesi'";
    
    if ($conn->query($query_update)) {
        // Perbarui Session agar nama di pojok kanan atas langsung berubah
        $_SESSION['nama_admin'] = $nama_baru;
        $pesan = "<div style='color: #166534; background: #DCFCE7; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Pengaturan profil dan keamanan berhasil diperbarui!</div>";
    } else {
        $pesan = "<div style='color: #991B1B; background: #FEE2E2; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Gagal memperbarui pengaturan sistem.</div>";
    }
}

// 2. AMBIL DATA ADMIN UNTUK DITAMPILKAN DI FORM
$data_admin = $conn->query("SELECT nama_admin, username FROM admin WHERE username = '$username_sesi'")->fetch_assoc();
?>

<div class="content-wrapper">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-size: 20px; color: #1E3A8A; margin: 0;">⚙️ Pengaturan Akun</h2>
    </div>

    <?= $pesan; ?>

    <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
        
        <!-- Form Pengaturan -->
        <div class="card-table" style="flex: 1; min-width: 400px; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 0;">
            <h3 style="color: #3B82F6; font-size: 15px; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #E2E8F0; padding-bottom: 10px;">Perbarui Profil & Keamanan</h3>
            
            <form action="" method="POST">
                
                <div style="margin-bottom: 15px;">
                    <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px;">Username (Tidak dapat diubah)</label>
                    <input type="text" value="<?= htmlspecialchars($data_admin['username'] ?? ''); ?>" readonly style="width: 100%; padding: 10px 12px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; background: #F1F5F9; color: #64748B; cursor: not-allowed;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px;">Nama Lengkap Tampil</label>
                    <input type="text" name="nama_admin" value="<?= htmlspecialchars($data_admin['nama_admin'] ?? ''); ?>" required style="width: 100%; padding: 10px 12px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 25px; padding-top: 15px; border-top: 1px dashed #E2E8F0;">
                    <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px;">Password Baru</label>
                    <input type="password" name="password_baru" placeholder="Kosongkan jika tidak ingin mengubah password..." style="width: 100%; padding: 10px 12px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                </div>

                <button type="submit" name="simpan_pengaturan" style="width: 100%; background: #10B981; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px;">💾 Simpan Perubahan</button>
            </form>
        </div>

        <!-- Info Panduan -->
        <div style="flex: 1; min-width: 300px; padding: 20px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px;">
            <h3 style="color: #0F172A; font-size: 15px; margin-top: 0; margin-bottom: 10px;">💡 Informasi Sistem</h3>
            <p style="font-size: 13px; color: #475569; line-height: 1.6;">
                Halaman ini digunakan untuk mengelola profil dan keamanan akun Anda yang sedang aktif. <br><br>
                <strong>Catatan Keamanan:</strong><br>
                Demi menjaga kerahasiaan data pengunjung Buku Tamu Digital BKK Banjarmasin, sangat disarankan untuk mengganti password bawaan secara berkala menggunakan kombinasi huruf dan angka.
            </p>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>