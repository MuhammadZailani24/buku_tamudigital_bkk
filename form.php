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
                        <label>Instansi / Perusahaan *</label>
                        <input type="text" name="instansi" id="input_instansi" placeholder="Ketik nama instansi" required>
                    </div>

                    <!-- Kategori (Pilihan Dropdown) -->
                    <div class="input-group">
                        <label>Kategori Kunjungan *</label>
                        <select name="kategori" id="input_kategori" class="form-input-custom" required>
                            <option value="" disabled selected>-- Pilih Kategori Kunjungan --</option>
                            <option value="Pelayanan vaksinasi">1. Pelayanan vaksinasi</option>
                            <option value="Pemeriksaan alat angkut">2. Pemeriksaan alat angkut (Dokumen Kekarantinaan)</option>
                            <option value="Pengantaran dokumen/barang">3. Pengantaran dokumen atau barang</option>
                            <option value="Pertemuan">4. Pertemuan</option>
                            <option value="Keperluan dinas">5. Keperluan dinas</option>
                            <option value="Permohonan SKLT">6. Permohonan SKLT</option>
                            <option value="Lainnya">7. Lainnya</option>
                        </select>
                    </div>

                    <!-- Keperluan (Ketik Manual) -->
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
                <div class="tips-box">💡 <span>Tips: Cukup masukkan nomor WA, data Nama dan Instansi Anda akan terisi otomatis jika pernah berkunjung sebelumnya.</span></div>
                <button type="submit" class="btn-success" id="btnSimpan">✓ SIMPAN DATA</button>
            </div>
        </form>

    </div>

    <script src="assets/js/camera.js"></script>

    <script>
        // Fitur Pencarian Data Tamu Otomatis (Autofill)
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

        // Validasi Form Cerdas
        document.getElementById('formKunjungan').addEventListener('submit', function(event) {
            
            let selKategori = document.getElementById('input_kategori');
            if(selKategori.value === '') {
                event.preventDefault();
                selKategori.focus();
                alert('Mohon pilih Kategori Kunjungan terlebih dahulu!');
                return;
            }

            const fields = [
                { id: 'input_telepon', namaKolom: 'No. Telepon / WhatsApp' },
                { id: 'input_nama', namaKolom: 'Nama Lengkap' },
                { id: 'input_instansi', namaKolom: 'Instansi / Perusahaan' },
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