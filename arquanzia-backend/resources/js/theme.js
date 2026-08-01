// Bascule jour / nuit / automatique.
//
// La préférence vit dans localStorage et s'applique aussi avant le premier rendu, via un
// court script du gabarit : sans lui, la page s'afficherait un instant en thème clair avant
// de basculer.
export default function initTheme() {
    function setTheme(pref) {
        localStorage.setItem('theme_pref', pref);
        applyTheme(pref);
        updateThemeButtons(pref);
    }

    function applyTheme(pref) {
        let theme = pref;
        if (pref === 'system') {
            theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.classList.remove('light', 'dark');
        document.documentElement.classList.add(theme);
    }

    function updateThemeButtons(pref) {
        document.querySelectorAll('.theme-btn').forEach(btn => {
            const isActive = btn.dataset.theme === pref;
            btn.classList.toggle('bg-arq-forest', isActive);
            btn.classList.toggle('text-arq-parchment', isActive);
            btn.classList.toggle('text-arq-bark', !isActive);
            btn.classList.toggle('hover:bg-arq-parchment-dark', !isActive);
        });
    }

    // Init theme buttons on load
    const savedPref = localStorage.getItem('theme_pref') || 'system';
    updateThemeButtons(savedPref);

    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        const pref = localStorage.getItem('theme_pref') || 'system';
        if (pref === 'system') applyTheme('system');
    });

    // Les boutons portaient un gestionnaire onclick, que toute politique de sécurité de
    // contenu bloque. L'intention est désormais déclarée par un attribut de données.
    document.querySelectorAll('.theme-btn').forEach((btn) => {
        btn.addEventListener('click', () => setTheme(btn.dataset.theme));
    });
}
