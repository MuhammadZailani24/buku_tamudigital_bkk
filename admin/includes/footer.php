<!-- admin/includes/footer.php -->
    </main> <!-- Penutup .main-content -->

    <!-- Script Inti Interaktivitas Admin & Mobile Overlay -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');

            if (menuToggle && sidebar) {
                // 1. Buat elemen overlay secara otomatis untuk mobile
                const overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                document.body.appendChild(overlay);

                // Styling dasar untuk overlay (bisa juga dimasukkan ke CSS)
                overlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100vw;
                    height: 100vh;
                    background-color: rgba(0, 0, 0, 0.4);
                    backdrop-filter: blur(2px);
                    z-index: 998;
                    display: none;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                `;

                // Fungsi Buka/Tutup Sidebar
                function toggleSidebar() {
                    sidebar.classList.toggle('show');
                    if (sidebar.classList.contains('show')) {
                        overlay.style.display = 'block';
                        setTimeout(() => overlay.style.opacity = '1', 10);
                    } else {
                        overlay.style.opacity = '0';
                        setTimeout(() => overlay.style.display = 'none', 300);
                    }
                }

                // Event tombol hamburger
                menuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleSidebar();
                });

                // Tutup sidebar jika overlay diklik
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    overlay.style.opacity = '0';
                    setTimeout(() => overlay.style.display = 'none', 300);
                });

                // Tutup sidebar jika salah satu menu di dalam sidebar diklik (opsional tapi praktis)
                const sidebarLinks = sidebar.querySelectorAll('a');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth <= 768) {
                            sidebar.classList.remove('show');
                            overlay.style.opacity = '0';
                            setTimeout(() => overlay.style.display = 'none', 300);
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>