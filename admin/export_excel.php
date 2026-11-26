<?php
// admin/export_excel.php
session_start();
if (!isset($_SESSION['admin_username'])) { header("Location: login.php"); exit; }

date_default_timezone_set('Asia/Makassar');
require_once '../config/database.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$is_superadmin = ($_SESSION['admin_role'] === 'super_admin');
$admin_wilker  = $conn->real_escape_string($_SESSION['admin_wilker']);

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'harian';
$kat_filter = isset($_GET['kategori_filter']) ? $conn->real_escape_string($_GET['kategori_filter']) : 'semua_kategori';
$wilker_filter = $is_superadmin ? (isset($_GET['wilker_filter']) ? $conn->real_escape_string($_GET['wilker_filter']) : 'semua_wilker') : $admin_wilker;

$where_clause = "1=1"; 
$periode = "Semua Data";
$nama_hari_indo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$hari_ini = $nama_hari_indo[date('w')];
$timestamp_custom = $hari_ini . '_' . date('d-m-Y_H-i-s');

switch ($filter) {
    case 'harian':
        $tgl = isset($_GET['tgl_harian']) ? $conn->real_escape_string($_GET['tgl_harian']) : date('Y-m-d');
        $where_clause = "DATE(waktu_masuk) = '$tgl'"; $periode = date('d F Y', strtotime($tgl)); $nama_file = "Rekap_Tamu_Harian_" . $timestamp_custom . ".xlsx"; break;
    case 'bulanan':
        $bln = isset($_GET['bln_bulanan']) ? $conn->real_escape_string($_GET['bln_bulanan']) : date('Y-m');
        $where_clause = "DATE_FORMAT(waktu_masuk, '%Y-%m') = '$bln'"; $periode = date('F Y', strtotime($bln . "-01")); $nama_file = "Rekap_Tamu_Bulanan_" . $timestamp_custom . ".xlsx"; break;
    case 'tahunan':
        $thn = isset($_GET['thn_tahunan']) ? $conn->real_escape_string($_GET['thn_tahunan']) : date('Y');
        $where_clause = "YEAR(waktu_masuk) = '$thn'"; $periode = "Tahun " . $thn; $nama_file = "Rekap_Tamu_Tahunan_" . $timestamp_custom . ".xlsx"; break;
    case 'custom':
        $tgl_mulai = isset($_GET['tgl_mulai']) ? $conn->real_escape_string($_GET['tgl_mulai']) : date('Y-m-01');
        $tgl_selesai = isset($_GET['tgl_selesai']) ? $conn->real_escape_string($_GET['tgl_selesai']) : date('Y-m-d');
        $where_clause = "DATE(waktu_masuk) BETWEEN '$tgl_mulai 00:00:00' AND '$tgl_selesai 23:59:59'";
        $periode = date('d/m/Y', strtotime($tgl_mulai)) . " s.d " . date('d/m/Y', strtotime($tgl_selesai)); $nama_file = "Rekap_Tamu_Kustom_" . $timestamp_custom . ".xlsx"; break;
    case 'semua':
        $where_clause = "1=1"; $periode = "Semua Data Kunjungan"; $nama_file = "Rekap_Tamu_Semua_" . $timestamp_custom . ".xlsx"; break;
}

if ($kat_filter !== 'semua_kategori') { $where_clause .= " AND kategori = '$kat_filter'"; $periode .= " | Kategori: " . htmlspecialchars($kat_filter); }
if ($wilker_filter !== 'semua_wilker') { $where_clause .= " AND wilker = '$wilker_filter'"; $periode .= " | Wilker: " . htmlspecialchars($wilker_filter); }

$query = "SELECT * FROM kunjungan WHERE $where_clause ORDER BY waktu_masuk DESC";
$result = $conn->query($query);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data Kunjungan');

$sheet->getColumnDimension('A')->setWidth(5); $sheet->getColumnDimension('B')->setWidth(20); $sheet->getColumnDimension('C')->setWidth(25); $sheet->getColumnDimension('D')->setWidth(25); $sheet->getColumnDimension('E')->setWidth(25); $sheet->getColumnDimension('F')->setWidth(30); $sheet->getColumnDimension('G')->setWidth(18); $sheet->getColumnDimension('H')->setWidth(25); $sheet->getColumnDimension('I')->setWidth(35); $sheet->getColumnDimension('J')->setWidth(20); $sheet->getColumnDimension('K')->setWidth(15); 
$sheet->mergeCells('A1:K1'); $sheet->mergeCells('A2:K2'); $sheet->mergeCells('A3:K3'); $sheet->mergeCells('A4:K4');

$sheet->setCellValue('A1', 'KEMENTERIAN KESEHATAN REPUBLIK INDONESIA');
$sheet->setCellValue('A2', 'BALAI KEKARANTINAAN KESEHATAN KELAS I BANJARMASIN');
$sheet->setCellValue('A3', 'Jl. Belitung Darat No.118A, Belitung Selatan, Kec. Banjarmasin Barat, Kalimantan Selatan');
$sheet->setCellValue('A4', 'Telepon: (0511) 3353948 | Email: balaikarkesbanjarmasin@kemkes.go.id');

$styleKop = [ 'alignment' => [ 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER ], 'font' => ['name' => 'Times New Roman'] ];
$sheet->getStyle('A1:K4')->applyFromArray($styleKop);
$sheet->getStyle('A1')->getFont()->setSize(13);
$sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A3:K4')->getFont()->setSize(11);
$sheet->getStyle('A4:K4')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
$sheet->getRowDimension(1)->setRowHeight(20); $sheet->getRowDimension(2)->setRowHeight(25); $sheet->getRowDimension(3)->setRowHeight(15); $sheet->getRowDimension(4)->setRowHeight(25);

if (file_exists('../assets/images/logo_bkk.webp')) { $logoKiri = new Drawing(); $logoKiri->setName('Logo Kemenkes'); $logoKiri->setPath('../assets/images/logo_bkk.webp'); $logoKiri->setCoordinates('A1'); $logoKiri->setHeight(70); $logoKiri->setOffsetX(15); $logoKiri->setOffsetY(8); $logoKiri->setWorksheet($sheet); }
if (file_exists('../assets/images/karantina.webp')) { $logoKanan = new Drawing(); $logoKanan->setName('Logo Karantina'); $logoKanan->setPath('../assets/images/karantina.webp'); $logoKanan->setCoordinates('K1'); $logoKanan->setHeight(75); $logoKanan->setOffsetX(30); $logoKanan->setOffsetY(5); $logoKanan->setWorksheet($sheet); }

$sheet->mergeCells('A6:K6'); $sheet->setCellValue('A6', 'LAPORAN REKAPITULASI DATA KUNJUNGAN BUKU TAMU'); $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(12)->setUnderline(true); $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->mergeCells('A7:K7'); $sheet->setCellValue('A7', 'Periode: ' . $periode); $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$row = 9;
$sheet->setCellValue('A'.$row, 'NO'); $sheet->setCellValue('B'.$row, 'WAKTU KUNJUNGAN'); $sheet->setCellValue('C'.$row, 'WILAYAH KERJA'); $sheet->setCellValue('D'.$row, 'NAMA TAMU'); $sheet->setCellValue('E'.$row, 'ASAL'); $sheet->setCellValue('F'.$row, 'ALAMAT'); $sheet->setCellValue('G'.$row, 'KONTAK (WA)'); $sheet->setCellValue('H'.$row, 'KATEGORI'); $sheet->setCellValue('I'.$row, 'RINCIAN KEPERLUAN'); $sheet->setCellValue('J'.$row, 'BERTEMU DENGAN'); $sheet->setCellValue('K'.$row, 'FOTO');
$headerStyle = [ 'font' => ['bold' => true, 'name' => 'Arial', 'size' => 11], 'alignment' => [ 'horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER ], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']] ];
$sheet->getStyle('A'.$row.':K'.$row)->applyFromArray($headerStyle);
$row++; $startDataRow = $row;

if ($result && $result->num_rows > 0) {
    $no = 1;
    while ($data = $result->fetch_assoc()) {
        $sheet->getRowDimension($row)->setRowHeight(55);
        $sheet->setCellValue('A'.$row, $no++);
        $sheet->setCellValue('B'.$row, date('d/m/Y - H:i', strtotime($data['waktu_masuk'])) . ' WITA');
        $sheet->setCellValue('C'.$row, !empty($data['wilker']) ? $data['wilker'] : '-');
        $sheet->setCellValue('D'.$row, $data['nama_lengkap']);
        $sheet->setCellValue('E'.$row, $data['instansi']);
        $sheet->setCellValue('F'.$row, !empty($data['alamat']) ? $data['alamat'] : '-');
        $sheet->setCellValueExplicit('G'.$row, $data['no_telepon'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('H'.$row, !empty($data['kategori']) ? $data['kategori'] : 'Umum / Lainnya');
        $sheet->setCellValue('I'.$row, $data['keperluan']);
        $sheet->setCellValue('J'.$row, $data['bertemu_dengan']);
        if (!empty($data['foto']) && file_exists('../assets/uploads/' . $data['foto'])) {
            $fotoTamu = new Drawing(); $fotoTamu->setName('Foto'); $fotoTamu->setPath('../assets/uploads/' . $data['foto']); $fotoTamu->setCoordinates('K'.$row); $fotoTamu->setHeight(65); $fotoTamu->setOffsetX(10); $fotoTamu->setOffsetY(5); $fotoTamu->setWorksheet($sheet);
        } else { $sheet->setCellValue('K'.$row, 'No Photo'); }

        $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('K'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
    }
} else {
    $sheet->mergeCells('A'.$row.':K'.$row); $sheet->setCellValue('A'.$row, 'TIDAK ADA DATA KUNJUNGAN PADA FILTER & PERIODE INI'); $sheet->getStyle('A'.$row)->getFont()->setBold(true); $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); $row++;
}

$sheet->getStyle('A'.$startDataRow.':K'.($row-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$sheet->getStyle('A'.$startDataRow.':K'.($row-1))->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

$row += 2;
$bulan_indo = array('01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember');
$tanggal_cetak = date('d') . ' ' . $bulan_indo[date('m')] . ' ' . date('Y');

$sheet->mergeCells('J'.$row.':K'.$row); $sheet->setCellValue('J'.$row, 'Banjarmasin, ' . $tanggal_cetak); $sheet->getStyle('J'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); $sheet->getStyle('J'.$row)->getFont()->setName('Times New Roman')->setSize(11);
$row++; $sheet->mergeCells('J'.$row.':K'.$row); $sheet->setCellValue('J'.$row, 'PPID BKK Kelas I Banjarmasin'); $sheet->getStyle('J'.$row)->getFont()->setBold(true)->setName('Times New Roman')->setSize(11); $sheet->getStyle('J'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$row += 4; $sheet->mergeCells('J'.$row.':K'.$row); $sheet->setCellValue('J'.$row, 'Tim Pelayanan Informasi Publik'); $sheet->getStyle('J'.$row)->getFont()->setBold(true)->setUnderline(true)->setName('Times New Roman')->setSize(11); $sheet->getStyle('J'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); header('Content-Disposition: attachment;filename="' . $nama_file . '"'); header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet); $writer->save('php://output'); exit();
?>