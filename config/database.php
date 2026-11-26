<?php
// config/database.php

$host = 'localhost'; 
$user = 'root';      
$pass = '';          
$db   = 'db_buku_tamu'; 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// ==========================================================
// 1. AUTO-PATCH: Kolom Wilker di Tabel Kunjungan
// ==========================================================
$check_wilker = $conn->query("SHOW COLUMNS FROM kunjungan LIKE 'wilker'");
if ($check_wilker && $check_wilker->num_rows == 0) {
    $conn->query("ALTER TABLE kunjungan ADD wilker VARCHAR(150) NOT NULL DEFAULT 'BKK Induk Banjarmasin' AFTER waktu_masuk");
}

// ==========================================================
// 2. AUTO-PATCH: Tabel User Admin & Role (RBAC)
// ==========================================================
$check_users = $conn->query("SHOW TABLES LIKE 'admin_users'");
if ($check_users && $check_users->num_rows == 0) {
    $sql_create = "CREATE TABLE admin_users (
        id_admin INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('super_admin', 'admin_wilker') NOT NULL,
        wilker VARCHAR(150) NULL
    )";
    
    if ($conn->query($sql_create)) {
        // Insert Data 8 Akun Admin Default (Password dienkripsi dengan MD5)
        $conn->query("INSERT INTO admin_users (username, password, role, wilker) VALUES 
            ('superadmin', md5('super123'), 'super_admin', 'Semua Wilker'),
            ('admin_induk', md5('induk123'), 'admin_wilker', 'BKK Induk Banjarmasin'),
            ('admin_bandara', md5('bandara123'), 'admin_wilker', 'Bandara Syamsudin Noor'),
            ('admin_kintap', md5('kintap123'), 'admin_wilker', 'Kintap'),
            ('admin_sungaiputing', md5('sungai123'), 'admin_wilker', 'Sungai Puting'),
            ('admin_satui', md5('satui123'), 'admin_wilker', 'Satui'),
            ('admin_kotabaru', md5('kotabaru123'), 'admin_wilker', 'Kotabaru'),
            ('admin_batulicin', md5('batulicin123'), 'admin_wilker', 'Batulicin')
        ");
    }
}
?>