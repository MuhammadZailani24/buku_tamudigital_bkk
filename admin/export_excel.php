<?php
// admin/export_excel.php

// Sinkronisasi Zona Waktu
date_default_timezone_set('Asia/Makassar');
require_once '../config/database.php';

// PANGGIL LIBRARY PHPSPREADSHEET
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Menangkap parameter filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'harian';
$where_clause = "1=1"; 
$periode = "Semua Data";
$nama_file = "Rekap_Tamu_Semua.xlsx"; // Ganti format menjadi XLSX murni

// Logika Filter
switch ($filter) {
    case 'harian':
        $tgl = isset($_GET['tgl_harian']) ? $conn->real_escape_string($_GET['tgl_harian']) : date('Y-m-d');
        $where_clause = "DATE(waktu_masuk) = '$tgl'";
        $periode = date('d F Y', strtotime($tgl));
        $nama_file = "Rekap_Tamu_Harian_" . $tgl . ".xlsx";
        break;
    case 'bulanan':
        $bln = isset($_GET['bln_bulanan']) ? $conn->real_escape_string($_GET['bln_bulanan']) : date('Y-m');
        $where_clause = "DATE_FORMAT(waktu_masuk, '%Y-%m') = '$bln'";
        $periode = date('F Y', strtotime($bln . "-01"));
        $nama_file = "Rekap_Tamu_Bulanan_" . $bln . ".xlsx";
        break;
    case 'tahunan':
        $thn = isset($_GET['thn_tahunan']) ? $conn->real_escape_string($_GET['thn_tahunan']) : date('Y');
        $where_clause = "YEAR(waktu_masuk) = '$thn'";
        $periode = "Tahun " . $thn;
        $nama_file = "Rekap_Tamu_Tahunan_" . $thn . ".xlsx";
        break;
    case 'custom':
        $tgl_mulai = isset($_GET['tgl_mulai']) ? $conn->real_escape_string($_GET['tgl_mulai']) : date('Y-m-d');
        $tgl_selesai = isset($_GET['tgl_selesai']) ? $conn->real_escape_string($_GET['tgl_selesai']) : date('Y-m-d');
        $where_clause = "DATE(waktu_masuk) BETWEEN '$tgl_mulai 00:00:00' AND '$tgl_selesai 23:59:59'";
        $periode = date('d/m/Y', strtotime($tgl_mulai)) . " s.d " . date('d/m/Y', strtotime($tgl_selesai));
        $nama_file = "Rekap_Tamu_Kustom_" . $tgl_mulai . "_sd_" . $tgl_selesai . ".xlsx";
        break;
    case 'semua':
        $where_clause = "1=1";
        $periode = "Semua Data Kunjungan";
        $nama_file = "Rekap_Tamu_Semua_Data.xlsx";
        break;
}

// Eksekusi Query Database
$query = "SELECT * FROM kunjungan WHERE $where_clause ORDER BY waktu_masuk DESC";
$result = $conn->query($query);

// Inisialisasi Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data Kunjungan');

// ==========================================
// PENGATURAN LEBAR KOLOM
// ==========================================
$sheet->getColumnDimension('A')->setWidth(5);
$sheet->getColumnDimension('B')->setWidth(22);
$sheet->getColumnDimension('C')->setWidth(35);
$sheet->getColumnDimension('D')->setWidth(30);
$sheet->getColumnDimension('E')->setWidth(18);
$sheet->getColumnDimension('F')->setWidth(40);
$sheet->getColumnDimension('G')->setWidth(25);

// ==========================================
// MEMBUAT KOP SURAT
// ==========================================
$sheet->mergeCells('A1:G1');
$sheet->mergeCells('A2:G2');
$sheet->mergeCells('A3:G3');
$sheet->mergeCells('A4:G4');

$sheet->setCellValue('A1', 'KEMENTERIAN KESEHATAN REPUBLIK INDONESIA');
$sheet->setCellValue('A2', 'BALAI KEKARANTINAAN KESEHATAN KELAS I BANJARMASIN');
$sheet->setCellValue('A3', 'Jl. Belitung Darat No.118A, Belitung Selatan, Kec. Banjarmasin Barat, Kalimantan Selatan');
$sheet->setCellValue('A4', 'Telepon: (0511) 3353948 | Email: balaikarkesbanjarmasin@kemkes.go.id');

// Style Kop Surat (Rata Tengah, Font Times New Roman)
$styleKop = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'font' => ['name' => 'Times New Roman']
];
$sheet->getStyle('A1:G4')->applyFromArray($styleKop);
$sheet->getStyle('A1')->getFont()->setSize(13);
$sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A3:G4')->getFont()->setSize(11);

// Garis Bawah Kop Surat
$sheet->getStyle('A4:G4')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

// Tinggi Baris Kop agar logo pas
$sheet->getRowDimension(1)->setRowHeight(20);
$sheet->getRowDimension(2)->setRowHeight(25);
$sheet->getRowDimension(3)->setRowHeight(15);
$sheet->getRowDimension(4)->setRowHeight(25);

// ==========================================
// MEMASUKKAN GAMBAR LOGO KE EXCEL
// ==========================================
// 1. Logo Kiri (Kemenkes)
if (file_exists('../assets/images/logo_bkk.webp')) {
    $logoKiri = new Drawing();
    $logoKiri->setName('Logo Kemenkes');
    $logoKiri->setPath('../assets/images/logo_bkk.webp');
    $logoKiri->setCoordinates('A1');
    $logoKiri->setHeight(70);
    $logoKiri->setOffsetX(15);
    $logoKiri->setOffsetY(8);
    $logoKiri->setWorksheet($sheet);
}

// 2. Logo Kanan (Karantina)
if (file_exists('../assets/images/karantina.webp')) {
    $logoKanan = new Drawing();
    $logoKanan->setName('Logo Karantina');
    $logoKanan->setPath('../assets/images/karantina.webp');
    $logoKanan->setCoordinates('G1');
    $logoKanan->setHeight(75);
    $logoKanan->setOffsetX(90); // Menggeser gambar agar pas di pojok kanan
    $logoKanan->setOffsetY(5);
    $logoKanan->setWorksheet($sheet);
}

// ==========================================
// JUDUL LAPORAN
// ==========================================
$sheet->mergeCells('A6:G6');
$sheet->setCellValue('A6', 'LAPORAN REKAPITULASI DATA KUNJUNGAN BUKU TAMU');
$sheet->getStyle('A6')->getFont()->setBold(true)->setSize(12)->setUnderline(true);
$sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A7:G7');
$sheet->setCellValue('A7', 'Periode: ' . $periode);
$sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// ==========================================
// HEADER TABEL
// ==========================================
$row = 9;
$sheet->setCellValue('A'.$row, 'NO');
$sheet->setCellValue('B'.$row, 'WAKTU KUNJUNGAN');
$sheet->setCellValue('C'.$row, 'NAMA PEMOHON / TAMU');
$sheet->setCellValue('D'.$row, 'INSTANSI / ASAL');
$sheet->setCellValue('E'.$row, 'KONTAK (WA)');
$sheet->setCellValue('F'.$row, 'RINCIAN KEPERLUAN');
$sheet->setCellValue('G'.$row, 'BERTEMU DENGAN');

$headerStyle = [
    'font' => ['bold' => true, 'name' => 'Arial', 'size' => 11],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']] // Warna Hijau Lembut
];
$sheet->getStyle('A'.$row.':G'.$row)->applyFromArray($headerStyle);

// ==========================================
// ISI DATA TABEL
// ==========================================
$row++;
$startDataRow = $row;
if ($result && $result->num_rows > 0) {
    $no = 1;
    while ($data = $result->fetch_assoc()) {
        $waktu = date('d/m/Y - H:i', strtotime($data['waktu_masuk'])) . ' WITA';
        
        $sheet->setCellValue('A'.$row, $no++);
        $sheet->setCellValue('B'.$row, $waktu);
        $sheet->setCellValue('C'.$row, $data['nama_lengkap']);
        $sheet->setCellValue('D'.$row, $data['instansi']);
        // Gunakan setCellValueExplicit agar angka 0 di depan No HP tidak hilang
        $sheet->setCellValueExplicit('E'.$row, $data['no_telepon'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('F'.$row, $data['keperluan']);
        $sheet->setCellValue('G'.$row, $data['bertemu_dengan']);

        // Rapikan Teks agar Top-Aligned dan Center untuk kolom tertentu
        $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row++;
    }
} else {
    $sheet->mergeCells('A'.$row.':G'.$row);
    $sheet->setCellValue('A'.$row, 'TIDAK ADA DATA KUNJUNGAN PADA PERIODE INI');
    $sheet->getStyle('A'.$row)->getFont()->setBold(true);
    $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row++;
}

// Beri Border pada seluruh tabel data
$sheet->getStyle('A'.$startDataRow.':G'.($row-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$sheet->getStyle('A'.$startDataRow.':G'.($row-1))->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

// ==========================================
// AREA TANDA TANGAN
// ==========================================
$row += 2;
$bulan_indo = array(
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
);
$tanggal_cetak = date('d') . ' ' . $bulan_indo[date('m')] . ' ' . date('Y');

$sheet->mergeCells('F'.$row.':G'.$row);
$sheet->setCellValue('F'.$row, 'Banjarmasin, ' . $tanggal_cetak);
$sheet->getStyle('F'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('F'.$row)->getFont()->setName('Times New Roman')->setSize(11);

$row++;
$sheet->mergeCells('F'.$row.':G'.$row);
$sheet->setCellValue('F'.$row, 'PPID BKK Kelas I Banjarmasin');
$sheet->getStyle('F'.$row)->getFont()->setBold(true)->setName('Times New Roman')->setSize(11);
$sheet->getStyle('F'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Ruang Kosong TTD
$row += 4;
$sheet->mergeCells('F'.$row.':G'.$row);
$sheet->setCellValue('F'.$row, 'Tim Pelayanan Informasi Publik');
$sheet->getStyle('F'.$row)->getFont()->setBold(true)->setUnderline(true)->setName('Times New Roman')->setSize(11);
$sheet->getStyle('F'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// ==========================================
// OUTPUT KE BROWSER SEBAGAI FILE EXCEL
// ==========================================
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $nama_file . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>