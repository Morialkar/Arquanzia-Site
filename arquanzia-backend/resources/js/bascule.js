// Affiche ou masque un élément selon l'état d'une case à cocher.
//
// Remplace un gestionnaire onchange en ligne : l'élément visé est déclaré par un attribut.
export default function initBascule() {
    document.querySelectorAll('[data-bascule]').forEach((source) => {
        const cible = document.querySelector(source.dataset.bascule);
        if (!cible) {
            return;
        }

        source.addEventListener('change', () => {
            cible.classList.toggle('hidden', !source.checked);
        });
    });
}
