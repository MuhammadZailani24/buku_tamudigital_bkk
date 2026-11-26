// ==========================================
// CAMERA.JS
// Kamera depan - NON MIRROR + RING LIGHT & SCREEN FLASH
// ==========================================

document.addEventListener('DOMContentLoaded', function () {

    const video = document.getElementById('kameraStream');
    const canvas = document.getElementById('kameraCanvas');
    const btnAmbilFoto = document.getElementById('btnAmbilFoto');
    const btnUlangFoto = document.getElementById('btnUlangFoto');
    const fotoBase64 = document.getElementById('fotoBase64');
    const cameraContainer = video.parentElement;

    let stream = null;

    // ==========================================
    // ELEMEN SCREEN FLASH & BADGE INDIKATOR LIGHT
    // ==========================================
    const flashOverlay = document.createElement('div');
    flashOverlay.style.position = 'fixed';
    flashOverlay.style.inset = '0';
    flashOverlay.style.backgroundColor = '#FFFFFF';
    flashOverlay.style.zIndex = '999999';
    flashOverlay.style.opacity = '0';
    flashOverlay.style.pointerEvents = 'none';
    flashOverlay.style.transition = 'opacity 0.12s ease';
    document.body.appendChild(flashOverlay);

    const lightBadge = document.createElement('div');
    lightBadge.innerHTML = '💡 Light Aktif';
    lightBadge.style.position = 'absolute';
    lightBadge.style.top = '12px';
    lightBadge.style.left = '12px';
    lightBadge.style.background = 'rgba(15, 23, 42, 0.75)';
    lightBadge.style.color = '#FEF08A';
    lightBadge.style.fontSize = '11px';
    lightBadge.style.fontWeight = '700';
    lightBadge.style.padding = '5px 10px';
    lightBadge.style.borderRadius = '20px';
    lightBadge.style.border = '1px solid rgba(255, 255, 255, 0.4)';
    lightBadge.style.zIndex = '10';
    lightBadge.style.display = 'none';

    if (cameraContainer) {
        cameraContainer.style.position = 'relative';
        cameraContainer.appendChild(lightBadge);
    }

    function nyalakanLight() {
        if (cameraContainer) {
            cameraContainer.style.transition = 'all 0.3s ease';
            cameraContainer.style.border = '6px solid #FFFFFF';
            cameraContainer.style.boxShadow = '0 0 0 4px #1E293B, 0 0 35px 10px rgba(255, 255, 255, 0.95), inset 0 0 30px rgba(255, 255, 255, 0.85)';
        }
        video.style.filter = 'brightness(1.45) contrast(1.10) saturate(1.05)';
        lightBadge.style.display = 'block';
    }

    function matikanLight() {
        if (cameraContainer) {
            cameraContainer.style.border = '';
            cameraContainer.style.boxShadow = '';
        }
        video.style.filter = 'none';
        lightBadge.style.display = 'none';
    }

    // ==========================================
    // AKTIFKAN KAMERA
    // ==========================================
    async function mulaiKamera() {
        try {

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Browser tidak mendukung akses kamera.');
                return;
            }

            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: {
                        ideal: 'user'
                    },
                    width: {
                        ideal: 1280
                    },
                    height: {
                        ideal: 720
                    }
                },
                audio: false
            });

            video.srcObject = stream;

            video.setAttribute('autoplay', '');
            video.setAttribute('playsinline', '');
            video.setAttribute('muted', '');

            // ==========================================
            // PENTING:
            // BALIK PREVIEW AGAR TIDAK MIRROR
            // ==========================================
            video.style.setProperty(
                'transform',
                'scaleX(-1)',
                'important'
            );

            video.style.setProperty(
                '-webkit-transform',
                'scaleX(-1)',
                'important'
            );

            video.style.setProperty(
                'direction',
                'ltr',
                'important'
            );

            await video.play();

            // Nyalakan ring light & pencerah saat kamera siap
            nyalakanLight();

        } catch (error) {

            console.error('Gagal mengakses kamera:', error);

            let pesan = 'Kamera tidak dapat digunakan.';

            if (error.name === 'NotAllowedError') {
                pesan = 'Akses kamera ditolak. Silakan izinkan kamera pada browser.';
            } else if (error.name === 'NotFoundError') {
                pesan = 'Kamera tidak ditemukan pada perangkat.';
            } else if (error.name === 'NotReadableError') {
                pesan = 'Kamera sedang digunakan oleh aplikasi lain.';
            } else if (error.name === 'SecurityError') {
                pesan = 'Akses kamera diblokir oleh keamanan browser.';
            }

            alert(pesan);
        }
    }

    // ==========================================
    // AMBIL FOTO (DENGAN SCREEN FLASH)
    // ==========================================
    btnAmbilFoto.addEventListener('click', function () {

        if (!video.videoWidth || !video.videoHeight) {
            alert('Kamera belum siap. Silakan tunggu beberapa detik.');
            return;
        }

        btnAmbilFoto.disabled = true;

        // Nyalakan kilatan layar putih terang untuk menerangi wajah
        flashOverlay.style.opacity = '0.96';

        // Tunggu 300ms agar sensor kamera menangkap pantulan cahaya layar
        setTimeout(function () {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const context = canvas.getContext('2d');

            context.clearRect(
                0,
                0,
                canvas.width,
                canvas.height
            );

            // ==========================================
            // NON-MIRROR HASIL FOTO + EFEK CERAH
            // ==========================================
            context.save();

            context.filter = 'brightness(1.45) contrast(1.10) saturate(1.05)';
            context.translate(canvas.width, 0);
            context.scale(-1, 1);

            context.drawImage(
                video,
                0,
                0,
                canvas.width,
                canvas.height
            );

            context.restore();

            // Matikan flash & ring light setelah foto tertangkap
            flashOverlay.style.opacity = '0';
            matikanLight();

            // Tampilkan hasil foto
            video.style.display = 'none';
            canvas.style.display = 'block';

            btnAmbilFoto.style.display = 'none';
            btnAmbilFoto.disabled = false;
            btnUlangFoto.style.display = 'inline-block';

            // Simpan hasil foto ke input hidden
            fotoBase64.value = canvas.toDataURL(
                'image/jpeg',
                0.90
            );
        }, 300);
    });

    // ==========================================
    // ULANGI FOTO
    // ==========================================
    btnUlangFoto.addEventListener('click', function () {

        canvas.style.display = 'none';
        video.style.display = 'block';

        btnAmbilFoto.style.display = 'inline-block';
        btnUlangFoto.style.display = 'none';

        fotoBase64.value = '';

        // Pastikan preview tetap NON-MIRROR
        video.style.setProperty(
            'transform',
            'scaleX(-1)',
            'important'
        );

        video.style.setProperty(
            '-webkit-transform',
            'scaleX(-1)',
            'important'
        );

        // Nyalakan kembali light
        nyalakanLight();
    });

    // ==========================================
    // MATIKAN KAMERA SAAT MENINGGALKAN HALAMAN
    // ==========================================
    window.addEventListener('beforeunload', function () {

        if (stream) {
            stream.getTracks().forEach(function (track) {
                track.stop();
            });
        }

    });

    // ==========================================
    // JALANKAN KAMERA
    // ==========================================
    mulaiKamera();

});