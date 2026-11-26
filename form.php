<!-- form.php -->
<?php
$wilker_raw = isset($_GET['wilker']) && !empty(trim($_GET['wilker'])) ? trim($_GET['wilker']) : 'BKK Induk Banjarmasin';
$wilker = htmlspecialchars($wilker_raw, ENT_QUOTES, 'UTF-8');

// Pemetaan foto background sesuai nama file di folder assets/images/
$map_bg = [
    'BKK Induk Banjarmasin'  => 'assets/images/kantor_induk.webp',
    'Bandara Syamsudin Noor' => 'assets/images/kantor_bandara.webp',
    'Kintap'                 => 'assets/images/kantor_kintap.webp',
    'Sungai Puting'          => 'assets/images/kantor_sungaiputing.webp',
    'Satui'                  => 'assets/images/kantor_satui.webp',
    'Kotabaru'               => 'assets/images/kantor_kotabaru.webp',
    'Batulicin'              => 'assets/images/kantor_batulicin.webp',
];

$bg_image = isset($map_bg[$wilker_raw]) ? $map_bg[$wilker_raw] : 'assets/images/bg_luar.webp';
if (!file_exists($bg_image)) {
    $bg_image = 'assets/images/bg_luar.webp';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isi Form - <?= $wilker; ?></title>
    <link rel="stylesheet" href="assets/css/style-tamu.css">
    <style>
        body {
            background-image: linear-gradient(rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.45)), url('<?= $bg_image; ?>');
        }
    </style>
</head>
<body class="form-body">
    <div class="form-wrapper">
        <div class="form-header">
            <div class="logo-title">
                <img src="assets/images/logo_bkk.webp" alt="Logo Kantor" style="width: 38px; height: 38px; object-fit: contain;">
                <div>
                    <h2 style="margin: 0;">BUKU TAMU DIGITAL</h2>
                    <span style="font-size: 12px; color: #64748B; font-weight: bold;"><?= $wilker; ?></span>
                </div>
            </div>
            <a href="welcome.php?wilker=<?= urlencode($wilker_raw); ?>" class="btn-back" style="text-decoration: none;">← Batal</a>
        </div>

        <form action="proses_simpan.php" method="POST" id="formKunjungan">
            <!-- INPUT TERSEMBUNYI UNTUK WILKER -->
            <input type="hidden" name="wilker" value="<?= $wilker; ?>">
            
            <div class="form-grid">
                <div class="left-col">
                    <h3 class="section-title">DATA KUNJUNGAN</h3>

                    <div class="input-group">
                        <label>No. Telepon / WhatsApp (Aktif) *</label>
                        <input type="number" name="no_telepon" id="input_telepon" placeholder="Ketik No WA Anda disini..." required oninput="cekDataTamu(this.value)">
                        <div id="status_autofill" style="display: none; margin-top: 8px; padding: 8px; background: #DCFCE7; border-left: 4px solid #10B981; border-radius: 4px;">
                            <span style="color: #065F46; font-size: 12px; font-weight: bold;">✨ Data otomatis terisi dari kunjungan sebelumnya!</span>
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

                    <div class="input-group">
                        <label>Alamat *</label>
                        <input type="text" name="alamat" id="input_alamat" placeholder="Ketik alamat lengkap Anda" required>
                    </div>

                    <div class="input-group">
                        <label>Kategori Kunjungan *</label>
                        <select id="input_kategori" class="form-input-custom" onchange="aturKategori()" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #CBD5E1;" required>
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            <option value="Pelayanan vaksinasi">1. Pelayanan vaksinasi</option>
                            <option value="Pemeriksaan alat angkut">2. Pemeriksaan alat angkut</option>
                            <option value="Pengantaran dokumen/barang">3. Pengantaran dokumen/barang</option>
                            <option value="Pertemuan">4. Pertemuan</option>
                            <option value="Keperluan dinas">5. Keperluan dinas</option>
                            <option value="Permohonan SKLT">6. Permohonan SKLT</option>
                            <option value="Lainnya">7. Lainnya (Ketik Manual)</option>
                        </select>
                        <input type="text" id="input_kategori_lainnya" class="form-input-custom" placeholder="Ketik Kategori Anda..." style="display:none; margin-top:10px; width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #CBD5E1;">
                        <input type="hidden" name="kategori" id="final_kategori">
                    </div>

                    <div class="input-group">
                        <label>Rincian Keperluan *</label>
                        <input type="text" name="keperluan" id="input_keperluan" placeholder="Ketik detail keperluan" required>
                    </div>

                    <div class="input-group">
                        <label>Bertemu Dengan *</label>
                        <input type="text" name="bertemu_dengan" id="input_bertemu" placeholder="Ketik nama / divisi" required>
                    </div>
                </div>

                <div class="right-col">
                    <h3 class="section-title">FOTO TAMU (WAJIB)</h3>
                    <div class="camera-container">
                        <video id="kameraStream" autoplay playsinline muted></video>
                        <canvas id="kameraCanvas" style="display: none;"></canvas>
                    </div>
                    <div class="camera-actions">
                        <button type="button" id="btnAmbilFoto" class="btn-outline">📷 AMBIL FOTO</button>
                        <button type="button" id="btnUlangFoto" class="btn-outline" style="display: none;">🔄 ULANGI FOTO</button>
                    </div>
                    <input type="hidden" name="foto_base64" id="fotoBase64" required>
                </div>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn-success" id="btnSimpan">✓ SIMPAN DATA</button>
            </div>
        </form>
    </div>

    <script src="assets/js/camera.js?v=<?= time(); ?>"></script>
    <script>
        function aturKategori() {
            let k = document.getElementById('input_kategori').value;
            let l = document.getElementById('input_kategori_lainnya');
            if (k === 'Lainnya') { l.style.display = 'block'; l.setAttribute('required', 'required'); } 
            else { l.style.display = 'none'; l.removeAttribute('required'); l.value = ''; }
        }

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
                            document.getElementById('input_alamat').value = data.alamat; 
                            document.getElementById('status_autofill').style.display = 'block';
                        } else { document.getElementById('status_autofill').style.display = 'none'; }
                    }).catch(err => console.error(err));
                } else { document.getElementById('status_autofill').style.display = 'none'; }
            }, 600); 
        }

        document.getElementById('formKunjungan').addEventListener('submit', function(e) {
            let sk = document.getElementById('input_kategori');
            let sl = document.getElementById('input_kategori_lainnya');
            let fk = document.getElementById('final_kategori');
            if(sk.value === '') { e.preventDefault(); alert('Pilih Kategori!'); return; }
            if(sk.value === 'Lainnya') {
                if(sl.value.trim() === '') { e.preventDefault(); alert('Ketik Kategori!'); return; }
                fk.value = sl.value; 
            } else { fk.value = sk.value; }

            let foto = document.getElementById('fotoBase64');
            if (foto.value.trim() === '') { e.preventDefault(); alert('Ambil foto terlebih dahulu!'); return; }
            
            let btn = document.getElementById('btnSimpan');
            btn.innerHTML = '⏳ MENGIRIM...'; btn.style.pointerEvents = 'none'; btn.style.opacity = '0.7';
        });
    </script>
</body>
</html>