<?php
// admin/pengguna.php

// Proteksi Error & Session
error_reporting(0);
@ini_set('display_errors', 0);
include 'includes/header.php';
require_once '../config/database.php';

$pesan = '';

// 1. PROSES HAPUS PENGGUNA
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    $username_sekarang =$_SESSION['username'] ?? ''; 
    
    // Cek agar tidak menghapus akun yang sedang dipakai
    $cek_admin =$conn->query("SELECT username FROM admin WHERE id = $id_hapus")->fetch_assoc();
    
    if ($cek_admin) {
        if ($cek_admin['username'] !== $username_sekarang) {$conn->query("DELETE FROM admin WHERE id = $id_hapus");
            $pesan = "<div style='color: #166534; background: #DCFCE7; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Akun pengguna berhasil dihapus!</div>";
        } else {
            $pesan = "<div style='color: #991B1B; background: #FEE2E2; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Gagal: Anda tidak dapat menghapus akun Anda sendiri saat sedang login!</div>";
        }
    }
}

// 2. PROSES TAMBAH PENGGUNA BARU
if (isset($_POST['tambah_user'])) {
    $nama_admin =$conn->real_escape_string($_POST['nama_admin']);$username   = $conn->real_escape_string($_POST['username']);
    $password   = md5($_POST['password']); // Menggunakan MD5 sesuai database Anda

    // Cek apakah username sudah ada di database
    $cek =$conn->query("SELECT id FROM admin WHERE username = '$username'");
    if ($cek->num_rows > 0) {$pesan = "<div style='color: #991B1B; background: #FEE2E2; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Gagal: Username sudah digunakan! Silakan gunakan username lain.</div>";
    } else {
        $conn->query("INSERT INTO admin (nama_admin, username, password) VALUES ('$nama_admin', '$username', '$password')");
        $pesan = "<div style='color: #166534; background: #DCFCE7; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Pengguna baru berhasil ditambahkan!</div>";
    }
}

// 3. AMBIL SEMUA DATA ADMIN DARI TABEL `admin`
$q_admin =$conn->query("SELECT * FROM admin ORDER BY id ASC");
?>

<div class="content-wrapper">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-size: 20px; color: #1E3A8A; margin: 0;">👥 Manajemen Pengguna</h2>
    </div>

    <?= $pesan; ?>

    <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
        
        <!-- Form Tambah Pengguna -->
        <div class="card-table" style="flex: 1; min-width: 300px; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 0;">
            <h3 style="color: #3B82F6; font-size: 15px; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #E2E8F0; padding-bottom: 10px;">Tambah Petugas Baru</h3>
            <form action="" method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px;">Nama Lengkap</label>
                    <input type="text" name="nama_admin" required placeholder="Contoh: Muhammad Zailani" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px;">Username</label>
                    <input type="text" name="username" required placeholder="Contoh: zailani24" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-size: 13px; color: #475569; margin-bottom:5px;">Password</label>
                    <input type="password" name="password" required placeholder="Ketik password..." style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                </div>
                <button type="submit" name="tambah_user" style="width: 100%; background: #2563EB; color: white; border: none; padding: 10px; border-radius: 6px; cursor: pointer; font-weight: bold;">➕ Simpan Pengguna</button>
            </form>
        </div>

        <!-- Tabel Daftar Pengguna -->
        <div class="card-table" style="flex: 2; min-width: 400px; margin-top: 0;">
            <div class="card-header">
                <h3 style="color: #1E3A8A; font-size: 15px; text-transform: uppercase;">Daftar Akun Akses</h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th width="10%">No.</th>
                            <th width="35%">Nama Petugas</th>
                            <th width="30%">Username</th>
                            <th width="25%" style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($q_admin && $q_admin->num_rows > 0) {$no = 1;
                            while ($row =$q_admin->fetch_assoc()) { 
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['nama_admin']); ?></td>
                            <td><span style="background: #F1F5F9; padding: 3px 8px; border-radius: 4px; font-size: 12px; border: 1px solid #E2E8F0;">@<?= htmlspecialchars($row['username']); ?></span></td>
                            <td style="text-align: center;">
                                <?php if(isset($_SESSION['username']) && $_SESSION['username'] !==$row['username']) { ?>
                                    <a href="?hapus=<?= $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus pengguna ini?');" style="color: white; background: #EF4444; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px;">🗑️ Hapus</a>
                                <?php } else { ?>
                                    <span style="color: #059669; font-size: 12px; font-weight: bold;">⭐ Akun Anda</span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center;'>Belum ada data admin.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?> 