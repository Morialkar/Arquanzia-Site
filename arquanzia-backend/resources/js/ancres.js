// Copie l'adresse d'un paragraphe au clic sur son ancre.
//
// L'ancre est un vrai lien : sans JavaScript, elle navigue et l'adresse apparaît dans la barre
// du navigateur. Ce module ne fait qu'épargner cette étape.
/**
 * Mesure les en-têtes épinglés en haut et publie leurs hauteurs.
 *
 * Une valeur fixe est un pari : la hauteur de l'en-tête d'article dépend de la longueur du
 * titre et de la largeur de la fenêtre — mesurée à 145 px puis à 273 px selon le cas. Trop
 * courte, le paragraphe ciblé atterrit derrière l'interface ; trop longue, il tombe en bas de
 * l'écran.
 *
 * Deux mesures, parce que deux besoins : la barre du site seule dit où l'en-tête de lecture
 * doit se caler, et la somme des deux dit ce qu'un paragraphe visé doit franchir. Le maximum
 * des deux, retenu d'abord, sous-estimait cette zone dès lors qu'ils s'empilent.
 */
function mesurerEnTetesCollants() {
    let site = 0;
    let total = 0;

    document.querySelectorAll('header, [data-site-header]').forEach((el) => {
        const style = getComputedStyle(el);
        if (style.position !== 'sticky' && style.position !== 'fixed') {
            return;
        }

        total += el.offsetHeight;

        // Un en-tête de contenu vit dans son article ; celui du site est au-dessus de tout.
        if (!el.closest('article')) {
            site += el.offsetHeight;
        }
    });

    const racine = document.documentElement.style;
    racine.setProperty('--arq-decalage-site', site + 'px');
    racine.setProperty('--arq-decalage-collant', total + 'px');
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
