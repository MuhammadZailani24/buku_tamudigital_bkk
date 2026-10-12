<!-- form.php -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isi Buku Tamu</title>
    <link rel="stylesheet" href="assets/css/style-tamu.css">
    <style>
        .form-input-custom {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
            background-color: #fff;
            outline: none;
            transition: all 0.3s ease;
        }
        .form-input-custom:focus {
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
    </style>
</head>

<body class="form-body">

    <div class="form-wrapper">

        <div class="form-header">
            <div class="logo-title">
                <img src="assets/images/logo_bkk.webp" alt="Logo Kantor" style="width: 38px; height: 38px; object-fit: contain;">
                <h2>BUKU TAMU DIGITAL</h2>
            </div>
            
            <a href="index.php" class="btn-back" style="position: relative; z-index: 9999; text-decoration: none; display: inline-block;">
                ← Kembali ke Awal
            </a>
        </div>

        <form action="proses_simpan.php" method="POST" id="formKunjungan">
            <div class="form-grid">

                <!-- Kolom Kiri: Input Data -->
                <div class="left-col">
                    <h3 class="section-title">DATA KUNJUNGAN</h3>

                    <div class="input-group">
                        <label>No. Telepon / WhatsApp (Aktif) *</label>
                        <input type="number" name="no_telepon" id="input_telepon" placeholder="Ketik No WA Anda disini..." required oninput="cekDataTamu(this.value)">
                        
                        <div id="status_autofill" style="display: none; margin-top: 8px; padding: 8px; background: #DCFCE7; border-left: 4px solid #10B981; border-radius: 4px;">
                            <span style="color: #065F46; font-size: 12px; font-weight: bold;">
                                ✨ Data otomatis terisi berdasarkan kunjungan sebelumnya!
                            </span>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="nama_lengkap" id="input_nama" placeholder="Ketik nama lengkap" required>
                    </div>

                    <div class="input-group">
                        <label>Asal (Instansi , Perorangan , Perusahaan) *</label>
                        <input type="text" name="instansi" id="input_instansi" placeholder="Nama instansi atau perorangan atau perusahaan" required>
                    </div>

                    <!-- KOLOM BARU: Alamat -->
                    <div class="input-group">
                        <label>Alamat *</label>
                        <input type="text" name="alamat" id="input_alamat" placeholder="Ketik alamat lengkap Anda" required>
                    </div>

                    <!-- Kategori Kunjungan & Input Manual "Lainnya" -->
                    <div class="input-group">
                        <label>Kategori Kunjungan *</label>
                        <select id="input_kategori" class="form-input-custom" onchange="aturKategori()" required>
                            <option value="" disabled selected>-- Pilih Kategori Kunjungan --</option>
                            <option value="Pelayanan vaksinasi">1. Pelayanan vaksinasi</option>
                            <option value="Pemeriksaan alat angkut">2. Pemeriksaan alat angkut (Dokumen Kekarantinaan)</option>
                            <option value="Pengantaran dokumen/barang">3. Pengantaran dokumen atau barang</option>
                            <option value="Pertemuan">4. Pertemuan</option>
                            <option value="Keperluan dinas">5. Keperluan dinas</option>
                            <option value="Permohonan SKLT">6. Permohonan SKLT</option>
                            <option value="Lainnya">7. Lainnya (Ketik Manual)</option>
                        </select>
                        
                        <!-- Muncul otomatis jika pilih Lainnya -->
                        <input type="text" id="input_kategori_lainnya" class="form-input-custom" placeholder="Ketik Kategori Anda secara manual..." style="display:none; margin-top:10px;">
                        
                        <!-- Value yang benar-benar akan dikirim ke database -->
                        <input type="hidden" name="kategori" id="final_kategori">
                    </div>

                    <div class="input-group">
                        <label>Rincian Keperluan *</label>
                        <input type="text" name="keperluan" id="input_keperluan" placeholder="Ketik detail keperluan spesifik Anda" required>
                    </div>

                    <div class="input-group">
                        <label>Bertemu Dengan *</label>
                        <input type="text" name="bertemu_dengan" id="input_bertemu" placeholder="Ketik nama orang / divisi yang ditemui" required>
                    </div>
                </div>

                <!-- Kolom Kanan: Kamera -->
                <div class="right-col">
                    <h3 class="section-title">FOTO TAMU (WAJIB)</h3>
                    <div class="camera-container">
                        <video id="kameraStream" autoplay playsinline muted style="transform: none !important; -webkit-transform: none !important;"></video>
                        <canvas id="kameraCanvas" style="display: none; transform: none !important; -webkit-transform: none !important;"></canvas>
                    </div>
                    <div class="camera-actions">
                        <button type="button" id="btnAmbilFoto" class="btn-outline">📷 AMBIL FOTO</button>
                        <button type="button" id="btnUlangFoto" class="btn-outline" style="display: none;">🔄 ULANGI FOTO</button>
                    </div>
                    <input type="hidden" name="foto_base64" id="fotoBase64" required>
                </div>

            </div>

            <div class="form-footer">
                <div class="tips-box">💡 <span>Tips: Cukup masukkan nomor WA, data Anda (Nama, Instansi, Alamat) akan terisi otomatis jika pernah berkunjung.</span></div>
                <button type="submit" class="btn-success" id="btnSimpan">✓ SIMPAN DATA</button>
            </div>
        </form>

    </div>

    <script src="assets/js/camera.js"></script>

    <script>
        // 1. Logika Kategori (Menampilkan Input Manual)
        function aturKategori() {
            let kategori = document.getElementById('input_kategori').value;
            let lainnya = document.getElementById('input_kategori_lainnya');
            
            if (kategori === 'Lainnya') {
                lainnya.style.display = 'block';
                lainnya.setAttribute('required', 'required'); // Wajib diisi jika pilih lainnya
            } else {
                lainnya.style.display = 'none';
                lainnya.removeAttribute('required');
                lainnya.value = ''; // Reset
            }
        }

        // 2. Fitur Autofill (Sekarang Mengisi Nama, Instansi, dan Alamat)
        let timeoutId;
        function cekDataTamu(nomor) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                if (nomor.length >= 10) { 
                    fetch('cek_tamu.php?no=' + nomor)
                    .then(res => res.json())
                    .then(data => {
                        if (data.found) {
                            document.getElementById('input_nama').value = data.nama;
                            document.getElementById('input_instansi').value = data.instansi;
                            document.getElementById('input_alamat').value = data.alamat; // Auto-fill Alamat
                            document.getElementById('status_autofill').style.display = 'block';
                        } else {
                            document.getElementById('status_autofill').style.display = 'none';
                        }
                    }).catch(err => console.error('Gagal mengambil data:', err));
                } else {
                    document.getElementById('status_autofill').style.display = 'none';
                }
            }, 600); 
        }

        // 3. Validasi Form Cerdas
        document.getElementById('formKunjungan').addEventListener('submit', function(event) {
            
            let selKategori = document.getElementById('input_kategori');
            let selLain = document.getElementById('input_kategori_lainnya');
            let finalKategori = document.getElementById('final_kategori');

            // Cek jika kategori belum dipilih
            if(selKategori.value === '') {
                event.preventDefault();
                selKategori.focus();
                alert('Mohon pilih Kategori Kunjungan terlebih dahulu!');
                return;
            }

            // Menyimpan Kategori yang dipilih/diketik ke Input Hidden
            if (selKategori.value === 'Lainnya') {
                if (selLain.value.trim() === '') {
                    event.preventDefault();
                    selLain.focus();
                    alert('Mohon ketikkan Kategori Kunjungan Anda secara manual!');
                    return;
                }
                finalKategori.value = selLain.value; // Jika 'Lainnya', simpan yang diketik manual
            } else {
                finalKategori.value = selKategori.value; // Jika selain 'Lainnya', simpan pilihan default
            }

            // Pengecekan Kolom Input Teks lainnya (Termasuk Alamat)
            const fields = [
                { id: 'input_telepon', namaKolom: 'No. Telepon / WhatsApp' },
                { id: 'input_nama', namaKolom: 'Nama Lengkap' },
                { id: 'input_instansi', namaKolom: 'Instansi / Perusahaan' },
                { id: 'input_alamat', namaKolom: 'Alamat' },
                { id: 'input_keperluan', namaKolom: 'Rincian Keperluan' },
                { id: 'input_bertemu', namaKolom: 'Bertemu Dengan' }
            ];

            for (let i = 0; i < fields.length; i++) {
                let el = document.getElementById(fields[i].id);
                if (el.value.trim() === '') {
                    event.preventDefault(); 
                    el.value = ''; 
                    el.focus(); 
                    alert('Mohon isi kolom "' + fields[i].namaKolom + '"!');
                    return; 
                }
            }

            let fotoInput = document.getElementById('fotoBase64');
            if (fotoInput.value.trim() === '') {
                event.preventDefault(); 
                document.getElementById('btnAmbilFoto').focus(); 
                alert('Mohon ambil foto wajah Anda terlebih dahulu!');
                return;
            }
            
            let btnSubmit = document.getElementById('btnSimpan');
            btnSubmit.innerHTML = '⏳ MENGIRIM DATA...';
            btnSubmit.style.opacity = '0.7';
            btnSubmit.style.pointerEvents = 'none';
        });
    </script>
</body>
</html>