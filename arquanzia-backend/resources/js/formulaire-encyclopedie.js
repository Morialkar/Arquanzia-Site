// Masque les champs d'article quand on édite une catégorie.
//
// L'affichage passe par la classe `hidden` plutôt que par un style intégré : la politique de
// sécurité de contenu n'autorise plus les attributs style=.
export default function initFormulaireEncyclopedie() {
    const type = document.querySelector('select[name="type"]');
    const champs = document.getElementById('article-fields');

    if (!type || !champs) {
        return;
    }

    const refleter = () => champs.classList.toggle('hidden', type.value !== 'article');

    type.addEventListener('change', refleter);
    refleter();
}
