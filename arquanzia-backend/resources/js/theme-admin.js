// Bascule de thème et navigation du back-office.
//
// Le back-office refaisait en cinquante lignes intégrées ce que theme.js savait déjà faire,
// avec sa propre notion de « thème courant » lue dans les classes du document. Il partage
// désormais la même préférence, sous la même clé, et se contente d'un bouton à deux états.
export default function initThemeAdmin() {
    const bouton = document.getElementById('theme-toggle');
    const icone = document.getElementById('theme-icon');
    const racine = document.documentElement;

    const appliquer = (theme) => {
        racine.classList.toggle('dark', theme === 'dark');
        racine.classList.toggle('light', theme !== 'dark');

        if (icone) {
            icone.textContent = theme === 'dark' ? '☀️' : '🌙';
        }
    };

    const resoudre = (pref) =>
        pref === 'system'
            ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
            : pref;

    if (bouton || icone) {
        appliquer(resoudre(localStorage.getItem('theme_pref') || 'system'));
    }

    bouton?.addEventListener('click', () => {
        const nouveau = racine.classList.contains('dark') ? 'light' : 'dark';
        localStorage.setItem('theme_pref', nouveau);
        appliquer(nouveau);
    });

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if ((localStorage.getItem('theme_pref') || 'system') === 'system') {
            appliquer(resoudre('system'));
        }
    });

    // Navigation repliable du back-office sur petit écran.
    const navBouton = document.getElementById('admin-nav-toggle');
    const nav = document.getElementById('admin-mobile-nav');

    navBouton?.addEventListener('click', () => {
        const ouvert = navBouton.getAttribute('aria-expanded') === 'true';
        navBouton.setAttribute('aria-expanded', String(!ouvert));
        nav?.classList.toggle('hidden');
    });
}
