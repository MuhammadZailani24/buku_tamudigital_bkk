<?php
// admin/export_pdf.php

// Sinkronisasi Zona Waktu WITA
date_default_timezone_set('Asia/Makassar');
require_once '../config/database.php';

// Menangkap parameter filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'harian';
$where_clause = "1=1"; 
$periode = "Semua Data";

// Logika Filter
switch ($filter) {
    case 'harian':
        $tgl = isset($_GET['tgl_harian']) ? $conn->real_escape_string($_GET['tgl_harian']) : date('Y-m-d');
        $where_clause = "DATE(waktu_masuk) = '$tgl'";
        $periode = date('d F Y', strtotime($tgl));
        break;
    case 'bulanan':
        $bln = isset($_GET['bln_bulanan']) ? $conn->real_escape_string($_GET['bln_bulanan']) : date('Y-m');
        $where_clause = "DATE_FORMAT(waktu_masuk, '%Y-%m') = '$bln'";
        $periode = date('F Y', strtotime($bln . "-01"));
        break;
    case 'tahunan':
        $thn = isset($_GET['thn_tahunan']) ? $conn->real_escape_string($_GET['thn_tahunan']) : date('Y');
        $where_clause = "YEAR(waktu_masuk) = '$thn'";
        $periode = "Tahun " . $thn;
        break;
    case 'custom':
        $tgl_mulai = isset($_GET['tgl_mulai']) ? $conn->real_escape_string($_GET['tgl_mulai']) : date('Y-m-d');
        $tgl_selesai = isset($_GET['tgl_selesai']) ? $conn->real_escape_string($_GET['tgl_selesai']) : date('Y-m-d');
        $where_clause = "DATE(waktu_masuk) BETWEEN '$tgl_mulai 00:00:00' AND '$tgl_selesai 23:59:59'";
        $periode = date('d/m/Y', strtotime($tgl_mulai)) . " s.d " . date('d/m/Y', strtotime($tgl_selesai));
        break;
    case 'semua':
        $where_clause = "1=1";
        $periode = "Semua Data Kunjungan";
        break;
}

// Eksekusi Query
$query = "SELECT * FROM kunjungan WHERE $where_clause ORDER BY waktu_masuk DESC";
$result = $conn->query($query);

// Fungsi Format Tanggal Indonesia untuk TTD
$bulan_indo = array(
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
);
$tanggal_cetak = date('d') . ' ' . $bulan_indo[date('m')] . ' ' . date('Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Buku Tamu</title>
    <style>
        /* Pengaturan Dasar Halaman Kertas */
        body { 
            font-family: 'Times New Roman', Times, serif; /* Sesuai standar surat resmi */
            font-size: 12px; 
            color: #000; 
            margin: 0;
            padding: 20px 40px;
        }
        
        /* Layout Kop Surat */
        .kop-surat {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .kop-surat td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .teks-kop {
            text-align: center;
        }
        .teks-kop h3 {
            margin: 0;
            font-size: 16px;
            font-weight: normal;
        }
        .teks-kop h1 {
            margin: 2px 0;
            font-size: 19px;
            font-weight: bold;
        }
        .teks-kop p {
            margin: 2px 0;
            font-size: 11.5px;
        }
        
        /* Garis Ganda Kop Surat */
        .garis-ganda {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin-bottom: 20px;
            padding: 0;
        }

        /* Judul Laporan */
        .judul-laporan {
            text-align: center;
            margin-bottom: 20px;
        }
        .judul-laporan h2 {
            margin: 0;
            font-size: 14px;
            text-decoration: underline;
            font-weight: bold;
        }
        .judul-laporan p {
            margin: 5px 0 0 0;
            font-size: 12px;
        }

        /* Tabel Data */
        table.data-tabel { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
            font-family: 'Arial', sans-serif; /* Sesuai contoh tabel di gambar */
            font-size: 11px;
        }
        table.data-tabel th, table.data-tabel td { 
            border: 1px solid #000; 
            padding: 8px 6px; 
            vertical-align: middle;
        }
        table.data-tabel th { 
            font-weight: bold;
            text-align: center;
            background-color: #ffffff;
        }
        .text-center { text-align: center; }

        /* Tanda Tangan */
        .signature-box {
            width: 100%;
            margin-top: 40px;
            font-family: 'Times New Roman', Times, serif;
        }
        .signature-content {
            float: right;
            width: 280px;
            text-align: center;
        }
        .signature-content p {
            margin: 2px 0;
            font-size: 12px;
        }
        .signature-name {
            margin-top: 70px !important;
            font-weight: bold;
            text-decoration: underline;
        }

        /* Mode Print Out */
        @media print {
            @page {
                size: A4 landscape; /* Menggunakan Landscape agar kolom leluasa seperti contoh */
                margin: 10mm;
            }
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="kop-surat">
        <tr>
            <td width="15%" class="text-center">
                <!-- Logo Kiri Kemenkes -->
                <img src="../assets/images/logo_bkk.webp" style="width: 140px; height: auto;">
            </td>
            <td width="70%" class="teks-kop">
                <h3>KEMENTERIAN KESEHATAN REPUBLIK INDONESIA</h3>
                <h1>BALAI KEKARANTINAAN KESEHATAN KELAS I BANJARMASIN</h1>
                <p>Jl. Belitung Darat No.118A, Belitung Selatan, Kec. Banjarmasin Barat, Kalimantan Selatan</p>
                <p>Telepon: (0511) 3353948 | Email: balaikarkesbanjarmasin@kemkes.go.id</p>
            </td>
            <td width="15%" class="text-center">
                <!-- Logo Kanan Karantina -->
                <img src="../assets/images/karantina.webp" style="width: 90px; height: auto;">
            </td>
        </tr>
    </table>
    
    <!-- Garis Pembatas -->
    <div class="garis-ganda"></div>

    <!-- Judul Dokumen -->
    <div class="judul-laporan">
        <h2>LAPORAN REKAPITULASI DATA KUNJUNGAN BUKU TAMU</h2>
        <p>Periode: <?= $periode; ?></p>
    </div>

    <!-- Tabel Data Kunjungan -->
    <table class="data-tabel">
        <thead>
            <tr>
                <th width="4%">NO</th>
                <th width="14%">WAKTU KUNJUNGAN</th>
                <th width="18%">NAMA PEMOHON / TAMU</th>
                <th width="18%">INSTANSI / ASAL</th>
                <th width="12%">KONTAK (WA)</th>
                <th width="20%">RINCIAN KEPERLUAN</th>
                <th width="14%">BERTEMU DENGAN</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if($result->num_rows > 0) {
                $no = 1;
                while($row = $result->fetch_assoc()) { 
            ?>
            <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td class="text-center"><?= date('d/m/Y - H:i', strtotime($row['waktu_masuk'])); ?> WITA</td>
                <td><?= $row['nama_lengkap']; ?></td>
                <td><?= $row['instansi']; ?></td>
                <td class="text-center"><?= $row['no_telepon']; ?></td>
                <td><?= $row['keperluan']; ?></td>
                <td class="text-center"><?= $row['bertemu_dengan']; ?></td>
            </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='7' class='text-center' style='padding: 20px; font-weight: bold;'>TIDAK ADA DATA PADA PERIODE INI</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Area Tanda Tangan -->
    <div class="signature-box">
        <div class="signature-content">
            <p>Banjarmasin, <?= $tanggal_cetak; ?></p>
            <p style="font-weight: bold;">PPID BKK Kelas I Banjarmasin</p>
            
            <!-- Area Kosong Untuk Tanda Tangan / Stempel -->
            <p class="signature-name">Tim Pelayanan Informasi Publik</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <!-- Tombol Manual (Disembunyikan saat dicetak) -->
    <div class="no-print" style="margin-top: 40px; text-align: center;">
        <button onclick="window.print()" style="padding: 12px 25px; background-color: #1E3A8A; color: white; border: none; border-radius: 5px; cursor: pointer; font-family: 'Arial', sans-serif; font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            🖨️ Cetak / Simpan PDF Sekarang
        </button>
        <button onclick="window.close()" style="padding: 12px 25px; margin-left: 10px; background-color: #64748B; color: white; border: none; border-radius: 5px; cursor: pointer; font-family: 'Arial', sans-serif; font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            ❌ Tutup Halaman
        </button>
    </div>

    <!-- Skrip Pemicu Cetak Otomatis -->
    <script>
        window.onload = function() {
            window.print();
        }
    </script>

</body>
</html>