// ==========================================
// CAMERA.JS
// Kamera depan - NON MIRROR
// ==========================================

document.addEventListener('DOMContentLoaded', function () {

    const video = document.getElementById('kameraStream');
    const canvas = document.getElementById('kameraCanvas');
    const btnAmbilFoto = document.getElementById('btnAmbilFoto');
    const btnUlangFoto = document.getElementById('btnUlangFoto');
    const fotoBase64 = document.getElementById('fotoBase64');

    let stream = null;

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
    // AMBIL FOTO
    // ==========================================
    btnAmbilFoto.addEventListener('click', function () {

        if (!video.videoWidth || !video.videoHeight) {
            alert('Kamera belum siap. Silakan tunggu beberapa detik.');
            return;
        }

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
        // NON-MIRROR HASIL FOTO
        // ==========================================
        context.save();

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

        // Tampilkan hasil foto
        video.style.display = 'none';
        canvas.style.display = 'block';

        btnAmbilFoto.style.display = 'none';
        btnUlangFoto.style.display = 'inline-block';

        // Simpan hasil foto ke input hidden
        fotoBase64.value = canvas.toDataURL(
            'image/jpeg',
            0.90
        );
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