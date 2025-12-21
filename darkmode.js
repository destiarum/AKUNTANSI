// darkmode.js - Final Version with Smart Badge Coloring

document.addEventListener('DOMContentLoaded', () => {
    const toggleButton = document.getElementById('theme-toggle');
    const body = document.body;
    const icon = toggleButton ? toggleButton.querySelector('i') : null;
    const textSpan = toggleButton ? toggleButton.querySelector('span') : null;

    // 1. Cek Memory (Local Storage) saat load
    if (localStorage.getItem('theme') === 'dark') {
        enableDarkMode();
    }

    // 2. Fungsi Mengaktifkan Dark Mode
    function enableDarkMode() {
        document.documentElement.classList.add('dark-mode'); // Paksa HTML gelap
        body.classList.add('dark-mode');
        
        if(icon) icon.className = 'fas fa-sun'; 
        if(textSpan) textSpan.innerText = 'Mode Terang';
        localStorage.setItem('theme', 'dark');
        
        if (typeof Chart !== 'undefined') updateChartColors('#ececec', '#404040');

        // JALANKAN PEWARNA BADGE OTOMATIS
        fixBadgeColors();
    }

    // 3. Fungsi Matikan Dark Mode
    function disableDarkMode() {
        document.documentElement.classList.remove('dark-mode');
        body.classList.remove('dark-mode');
        
        if(icon) icon.className = 'fas fa-moon'; 
        if(textSpan) textSpan.innerText = 'Mode Gelap';
        localStorage.setItem('theme', 'light');

        if (typeof Chart !== 'undefined') updateChartColors('#666666', '#dddddd');
        
        // Reset warna badge ke default (biar gak nyangkut warnanya)
        resetBadgeColors();
    }

    // 4. Event Listener Tombol
    if (toggleButton) {
        toggleButton.addEventListener('click', () => {
            if (body.classList.contains('dark-mode')) {
                disableDarkMode();
            } else {
                enableDarkMode();
            }
        });
    }

    // --- FUNGSI PINTAR: PEWARNA BADGE OTOMATIS ---
    function fixBadgeColors() {
        // Cari semua elemen span di dalam tabel
        const spans = document.querySelectorAll('.table span'); 
        
        spans.forEach(el => {
            const text = el.innerText.trim().toLowerCase();
            
            // Cek apakah ini Badge Tipe Akun? (Aset, Liabilitas, dll)
            // Kalau iya, kita paksa warnanya.
            
            // 1. ASET (Hijau)
            if (text === 'aset') {
                applyBadgeStyle(el, 'rgba(76, 175, 80, 0.15)', '#81c784', '#2e7d32');
            }
            // 2. LIABILITAS / KEWAJIBAN / HUTANG (Merah)
            else if (text.includes('kewajiban') || text.includes('liabilitas') || text.includes('hutang') || text.includes('kredit')) {
                applyBadgeStyle(el, 'rgba(244, 67, 54, 0.15)', '#e57373', '#c62828');
            }
            // 3. EKUITAS / MODAL (Biru)
            else if (text.includes('ekuitas') || text.includes('modal')) {
                applyBadgeStyle(el, 'rgba(33, 150, 243, 0.15)', '#64b5f6', '#1565c0');
            }
            // 4. PENDAPATAN (Teal/Cyan)
            else if (text.includes('pendapatan')) {
                applyBadgeStyle(el, 'rgba(255, 152, 0, 0.15)', '#ffb74d', '#f57c00');
            }
            // 5. BEBAN (Ungu)
            else if (text.includes('beban')) {
                applyBadgeStyle(el, 'rgba(156, 39, 176, 0.15)', '#ba68c8', '#7b1fa2');
            }
        });
    }

    // Helper untuk pasang style important
    function applyBadgeStyle(el, bg, text, border) {
        el.style.setProperty('background-color', bg, 'important');
        el.style.setProperty('color', text, 'important');
        el.style.setProperty('border', `1px solid ${border}`, 'important');
        el.style.setProperty('box-shadow', 'none', 'important');
    }

    // Helper Reset (Balikin ke style bawaan Bootstrap kalau light mode)
    function resetBadgeColors() {
        const spans = document.querySelectorAll('.table span');
        spans.forEach(el => {
            el.style.removeProperty('background-color');
            el.style.removeProperty('color');
            el.style.removeProperty('border');
            el.style.removeProperty('box-shadow');
        });
    }

    // Helper Chart.js
    function updateChartColors(textColor, gridColor) {
        Chart.defaults.color = textColor;
        Chart.defaults.borderColor = gridColor;
        Chart.helpers.each(Chart.instances, function(instance) {
            instance.options.scales.x.grid.color = gridColor;
            instance.options.scales.y.grid.color = gridColor;
            instance.options.plugins.legend.labels.color = textColor;
            instance.update();
        });
    }
});