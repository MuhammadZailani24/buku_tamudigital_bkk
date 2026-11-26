<?php
// proses_simpan.php

require_once 'config/database.php';
date_default_timezone_set('Asia/Makassar');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Tangkap data tambahan wilker
    $wilker         = $conn->real_escape_string($_POST['wilker']);
    
    $nama_lengkap   = $conn->real_escape_string($_POST['nama_lengkap']);
    $instansi       = $conn->real_escape_string($_POST['instansi']);
    $alamat         = $conn->real_escape_string($_POST['alamat']);
    $no_telepon     = $conn->real_escape_string($_POST['no_telepon']);
    $kategori       = $conn->real_escape_string($_POST['kategori']); 
    $keperluan      = $conn->real_escape_string($_POST['keperluan']);
    $bertemu_dengan = $conn->real_escape_string($_POST['bertemu_dengan']);
    
    $foto_base64 = $_POST['foto_base64'];
    $image_parts = explode(";base64,", $foto_base64);
    
    if (count($image_parts) >= 2) {
        $image_base64   = base64_decode($image_parts[1]);
        $nama_file_foto = 'tamu_' . time() . '.jpeg';
        $lokasi_simpan  = 'assets/uploads/' . $nama_file_foto;
        file_put_contents($lokasi_simpan, $image_base64);
    } else {
        $nama_file_foto = '';
    }

    $waktu_database = date('Y-m-d H:i:s'); 

    // UPDATE: Insert 10 Kolom (Menambahkan wilker)
    $stmt = $conn->prepare("INSERT INTO kunjungan (waktu_masuk, wilker, nama_lengkap, instansi, alamat, no_telepon, kategori, keperluan, bertemu_dengan, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $waktu_database, $wilker, $nama_lengkap, $instansi, $alamat, $no_telepon, $kategori, $keperluan, $bertemu_dengan, $nama_file_foto);

    if ($stmt->execute()) {
        
        $hari_inggris = date('l');
        $hari_indo = array('Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu');
        $nama_hari = $hari_indo[$hari_inggris];
        $bulan_indo = array('01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember');
        $tanggal_wa = $nama_hari . ', ' . date('d') . ' ' . $bulan_indo[date('m')] . ' ' . date('Y') . ' - Pukul ' . date('H:i:s') . ' WITA';

        // UPDATE: Format Teks Pesan WhatsApp untuk mencantumkan Wilker
        $token_fonnte = 'MASUKKAN_TOKEN_FONNTE_ASLI_ANDA_DISINI'; 

        $pesan_wa = "Halo, Yth. *Bapak/Ibu {$nama_lengkap}* 👋\n\n" .
                    "Kami mengucapkan terima kasih atas kunjungan Anda ke kantor *Balai Kekarantinaan Kesehatan (BKK) Kelas I Banjarmasin - Wilayah Kerja {$wilker}*.\n\n" .
                    "Kunjungan Anda telah diverifikasi di dalam sistem Buku Tamu Digital kami dengan rincian sebagai berikut:\n\n" .
                    "📋 *INFORMASI KUNJUNGAN*\n" .
                    "----------------------------------------------------\n" .
                    "🏢 *Lokasi Wilker* : {$wilker}\n" .
                    "👤 *Nama Lengkap*  : {$nama_lengkap}\n" .
                    "💼 *Instansi*      : {$instansi}\n" .
                    "📌 *Kategori*      : {$kategori}\n" . 
                    "🎯 *Keperluan*     : {$keperluan}\n" . 
                    "🤝 *Bertemu Dgn*   : {$bertemu_dengan}\n" .
                    "🕒 *Waktu Datang*  : {$tanggal_wa}\n" .
                    "----------------------------------------------------\n\n" .
                    "Semoga pelayanan kami memenuhi harapan Anda. Selamat melanjutkan aktivitas, dan semoga hari Anda senantiasa menyenangkan! ✨\n\n" .
                    "Hormat kami,\n" .
                    "*Tim Pelayanan Informasi Publik*\n" .
                    "*BKK Kelas I Banjarmasin - {$wilker}*";

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.fonnte.com/send',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 5,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => array(
            'target' => $no_telepon,
            'message' => $pesan_wa,
            'countryCode' => '62',
          ),
          CURLOPT_HTTPHEADER => array(
            "Authorization: $token_fonnte"
          ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        // Setelah sukses, kembalikan tamu ke Landing Page
        echo "<script>
            alert('Data kunjungan berhasil disimpan! Terima kasih.');
            window.location.href = 'index.php';
        </script>";
    } else {
        echo "<script>
            alert('Error: " . $stmt->error . "');
            window.history.back();
        </script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>