/**
 * UEP LES System — Theme persistence helper
 * Included on every page to apply saved theme before CSS renders (prevents flash).
 * Also wires up the #themeBtn toggle if present on the page.
 */
(function () {
    var t = localStorage.getItem('ueples_theme') || 'dark';
    document.documentElement.dataset.theme = t;
})();

document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('themeBtn');
    if (!btn) return;
    var cur = localStorage.getItem('ueples_theme') || 'dark';
    btn.innerHTML = cur === 'dark' ? '<i class="fa-solid fa-moon"></i>' : '<i class="fa-solid fa-sun"></i>';
    btn.addEventListener('click', function () {
        var next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = next;
        localStorage.setItem('ueples_theme', next);
        btn.innerHTML = next === 'dark' ? '<i class="fa-solid fa-moon"></i>' : '<i class="fa-solid fa-sun"></i>';
        
        // Push theme update to any child iframes (cross-origin safe for same domain)
        document.querySelectorAll('iframe').forEach(ifr => {
            try {
                if(ifr.contentWindow && ifr.contentWindow.document) {
                    ifr.contentWindow.document.documentElement.dataset.theme = next;
                }
            } catch(e) {}
        });
    });
});

// Sync theme changes from other tabs or from the parent window
window.addEventListener('storage', function(e) {
    if (e.key === 'ueples_theme') {
        const newTheme = e.newValue || 'dark';
        document.documentElement.dataset.theme = newTheme;
        
        const btn = document.getElementById('themeBtn');
        if (btn) {
            btn.innerHTML = newTheme === 'dark' ? '<i class="fa-solid fa-moon"></i>' : '<i class="fa-solid fa-sun"></i>';
        }
    }
});
