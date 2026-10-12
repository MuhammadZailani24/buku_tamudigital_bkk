<?php
// proses_simpan.php

// 1. Panggil koneksi database
require_once 'config/database.php';

// ========================================================
// [PENTING] SET TIMEZONE KE WITA (Banjarmasin / Asia/Makassar)
// ========================================================
date_default_timezone_set('Asia/Makassar');

// 2. Pastikan request datang dari form POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 3. Tangkap data teks dari form
    $nama_lengkap   = $conn->real_escape_string($_POST['nama_lengkap']);
    $instansi       = $conn->real_escape_string($_POST['instansi']);
    $no_telepon     = $conn->real_escape_string($_POST['no_telepon']);
    
    // MENANGKAP VARIABEL BARU: Kategori dan Keperluan
    $kategori       = $conn->real_escape_string($_POST['kategori']); 
    $keperluan      = $conn->real_escape_string($_POST['keperluan']);
    $bertemu_dengan = $conn->real_escape_string($_POST['bertemu_dengan']);
    
    // 4. Tangkap data foto Base64
    $foto_base64 = $_POST['foto_base64'];

    // 5. Proses Konversi Base64 menjadi file gambar fisik
    $image_parts    = explode(";base64,", $foto_base64);
    $image_base64   = base64_decode($image_parts[1]);
    
    // 6. Buat nama file unik berdasarkan waktu
    $nama_file_foto = 'tamu_' . time() . '.jpeg';
    
    // 7. Tentukan lokasi folder penyimpanan
    $lokasi_simpan  = 'assets/uploads/' . $nama_file_foto;
    
    // 8. Simpan file gambar fisik ke dalam folder uploads
    file_put_contents($lokasi_simpan, $image_base64);

    // 9. Ambil Waktu Real-Time Saat Ini secara spesifik
    $waktu_database = date('Y-m-d H:i:s'); 

    // 10. Simpan data ke Database menggunakan Prepared Statement (Termasuk kolom Kategori)
    $stmt = $conn->prepare("INSERT INTO kunjungan (waktu_masuk, nama_lengkap, instansi, no_telepon, kategori, keperluan, bertemu_dengan, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $waktu_database, $nama_lengkap, $instansi, $no_telepon, $kategori, $keperluan, $bertemu_dengan, $nama_file_foto);

    // 11. Eksekusi query
    if ($stmt->execute()) {
        
        // ==========================================
        // FORMAT TANGGAL INDONESIA LENGKAP UNTUK WA
        // ==========================================
        $hari_inggris = date('l');
        $hari_indo = array(
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        );
        $nama_hari = $hari_indo[$hari_inggris];

        $bulan_inggris = date('m');
        $bulan_indo = array(
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        );
        $nama_bulan = $bulan_indo[$bulan_inggris];

        $tanggal_wa = $nama_hari . ', ' . date('d') . ' ' . $nama_bulan . ' ' . date('Y') . ' - Pukul ' . date('H:i:s') . ' WITA';


        // ==========================================
        // KIRIM PESAN OTOMATIS VIA FONNTE API
        // ==========================================
        $token_fonnte = 'hYJytUKE8ixvymnDzm9A'; 

        $pesan_wa = "Halo, Yth. *Bapak/Ibu {$nama_lengkap}* 👋\n\n" .
                    "Kami mengucapkan terima kasih atas kunjungan Anda ke kantor *Balai Kekarantinaan Kesehatan (BKK) Kelas I Banjarmasin* pada hari ini.\n\n" .
                    "Kunjungan Anda telah berhasil diverifikasi dan tercatat secara resmi di dalam sistem Buku Tamu Digital kami dengan rincian sebagai berikut:\n\n" .
                    "📋 *INFORMASI KUNJUNGAN*\n" .
                    "----------------------------------------------------\n" .
                    "👤 *Nama Lengkap*   : {$nama_lengkap}\n" .
                    "🏢 *Instansi/Asal*      : {$instansi}\n" .
                    "📞 *No. WhatsApp* : {$no_telepon}\n" .
                    "📌 *Kategori*              : {$kategori}\n" . 
                    "🎯 *Rincian Keperluan* : {$keperluan}\n" . 
                    "🤝 *Bertemu Dgn*    : {$bertemu_dengan}\n" .
                    "🕒 *Waktu Datang*   : {$tanggal_wa}\n" .
                    "----------------------------------------------------\n\n" .
                    "Semoga pelayanan kami memenuhi harapan Anda. Jika Anda memiliki pertanyaan atau masukan lebih lanjut, jangan ragu untuk menyampaikannya kepada petugas kami.\n\n" .
                    "Selamat melanjutkan aktivitas Anda, dan semoga hari Anda senantiasa menyenangkan! ✨\n\n" .
                    "Hormat kami,\n" .
                    "*Tim Pelayanan Informasi Publik*\n" .
                    "*Balai Kekarantinaan Kesehatan Kelas I Banjarmasin*";

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.fonnte.com/send',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
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

        // 12. Jika sukses, kembalikan ke Halaman Awal
        echo "<script>
            alert('Data kunjungan berhasil disimpan & Pesan WhatsApp telah terkirim! Terima kasih.');
            window.location.href = 'index.php';
        </script>";
    } else {
        // Jika gagal database
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