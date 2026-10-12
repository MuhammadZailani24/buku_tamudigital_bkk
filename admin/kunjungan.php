<?php
// admin/kunjungan.php

error_reporting(0);
@ini_set('display_errors', 0);
date_default_timezone_set('Asia/Makassar');

include 'includes/header.php';
require_once '../config/database.php';

// Ambil riwayat kunjungan (ditampilkan dari yang terbaru)
$q_tabel = $conn->query("SELECT * FROM kunjungan ORDER BY waktu_masuk DESC");
?>

<div class="content-wrapper">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-size: 20px; color: #1E3A8A; margin: 0;">📋 Daftar Kunjungan Tamu</h2>
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
                        <th width="11%">Waktu</th>
                        <th width="13%">Nama Tamu</th>
                        <th width="12%">Instansi</th>
                        <th width="11%">No. WhatsApp</th>
                        <th width="16%">Kategori Kunjungan</th>
                        <th width="16%">Rincian Keperluan</th>
                        <th width="10%">Bertemu Dengan</th>
                        <th width="7%" style="text-align: center;">Foto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($q_tabel && $q_tabel->num_rows > 0) {
                        $no = 1;
                        while ($row = $q_tabel->fetch_assoc()) { 
                            // Tangani data lama yang belum punya kategori
                            $kategori = !empty($row['kategori']) ? htmlspecialchars($row['kategori']) : 'Umum / Lainnya';
                    ?>
                    <tr>
                        <td style="text-align: center; vertical-align: middle;"><?= $no++; ?></td>
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
                        
                        <!-- KOLOM FOTO DENGAN EFEK KLIK PERBESAR -->
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

<?php include 'includes/footer.php'; ?>