// Copie l'adresse d'un paragraphe au clic sur son ancre.
//
// L'ancre est un vrai lien : sans JavaScript, elle navigue et l'adresse apparaît dans la barre
// du navigateur. Ce module ne fait qu'épargner cette étape.
export default function initAncres() {
    document.addEventListener('click', (event) => {
        const ancre = event.target.closest('.paragraph-anchor');
        if (!ancre || !navigator.clipboard) {
            return;
        }

        event.preventDefault();

        const adresse = window.location.origin + window.location.pathname + '#' + ancre.dataset.anchor;

        navigator.clipboard.writeText(adresse).then(
            () => {
                // Le retour passe par un attribut, que le CSS traduit : pas de texte injecté
                // dans le paragraphe, qui fausserait son empreinte au prochain rendu.
                ancre.dataset.copie = 'oui';
                setTimeout(() => delete ancre.dataset.copie, 1600);
                history.replaceState(null, '', '#' + ancre.dataset.anchor);
            },
            () => { window.location.hash = ancre.dataset.anchor; },
        );
    });
}
