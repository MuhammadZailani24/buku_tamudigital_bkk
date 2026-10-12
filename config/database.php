<?php
// config/database.php

$host = "localhost";
$user = "root"; // Sesuaikan dengan username database lokal (XAMPP default: root)
$pass = "";     // Sesuaikan dengan password database (XAMPP default: kosong)
$db   = "db_buku_tamu";

// Membuat koneksi
$conn = new mysqli($host, $user, $pass, $db);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>