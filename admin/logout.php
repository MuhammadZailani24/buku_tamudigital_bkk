<?php
// admin/logout.php
session_start();

// 1. Simpan nama wilker ke variabel sementara SEBELUM sesi dihancurkan
$wilker_tujuan = '';
if (isset($_SESSION['admin_wilker']) && $_SESSION['admin_wilker'] !== 'Semua Wilker') {
    $wilker_tujuan = $_SESSION['admin_wilker'];
}

// 2. Menghapus semua data sesi dengan bersih
session_unset();
session_destroy();

// 3. Logika Pengalihan (Redirect)
if (!empty($wilker_tujuan)) {
    // Jika yang logout adalah Admin Wilker, kembalikan ke halaman Welcome wilkernya
    header("Location: ../welcome.php?wilker=" . urlencode($wilker_tujuan));
} else {
    // Jika yang logout adalah Super Admin, kembalikan ke Landing Page utama
    header("Location: ../index.php");
}
exit;
?>