// Ouverture du menu sur petit écran.
export default function initMenuMobile() {
    document.getElementById('mobile-menu-toggle')?.addEventListener('click', function() {
        document.getElementById('mobile-menu').classList.toggle('hidden');
    });
}
