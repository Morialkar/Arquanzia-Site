// Copie l'adresse d'un paragraphe au clic sur son ancre.
//
// L'ancre est un vrai lien : sans JavaScript, elle navigue et l'adresse apparaît dans la barre
// du navigateur. Ce module ne fait qu'épargner cette étape.
/**
 * Mesure les en-têtes épinglés en haut et publie leur hauteur.
 *
 * Une marge de défilement fixe est un pari : la hauteur de l'en-tête d'article dépend de la
 * longueur du titre et de la largeur de la fenêtre — mesurée à 145 px puis à 273 px selon le
 * cas. Trop courte, le paragraphe ciblé atterrit derrière l'interface ; trop longue, il tombe
 * en bas de l'écran.
 */
function mesurerEnTetesCollants() {
    let hauteur = 0;

    document.querySelectorAll('header, [data-site-header]').forEach((el) => {
        const style = getComputedStyle(el);
        if (style.position !== 'sticky' && style.position !== 'fixed') {
            return;
        }
        hauteur = Math.max(hauteur, el.offsetHeight);
    });

    document.documentElement.style.setProperty('--arq-decalage-collant', hauteur + 'px');
}

export default function initAncres() {
    mesurerEnTetesCollants();
    window.addEventListener('resize', mesurerEnTetesCollants);

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
