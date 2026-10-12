<?php
// cek_tamu.php
require_once 'config/database.php';
header('Content-Type: application/json');

if(isset($_GET['no'])) {
    $no = $conn->real_escape_string($_GET['no']);
    
    // Cari tamu berdasarkan nomor WA terakhir kali mereka datang (Ditambah kolom alamat)
    $query = $conn->query("SELECT nama_lengkap, instansi, alamat FROM kunjungan WHERE no_telepon = '$no' ORDER BY waktu_masuk DESC LIMIT 1");
    
    if($query && $query->num_rows > 0) {
        $data = $query->fetch_assoc();
        echo json_encode([
            'found' => true, 
            'nama' => htmlspecialchars($data['nama_lengkap']), 
            'instansi' => htmlspecialchars($data['instansi']),
            'alamat' => htmlspecialchars($data['alamat'])
        ]);
    } else {
        echo json_encode(['found' => false]);
    }
} else {
    echo json_encode(['found' => false]);
}
?>