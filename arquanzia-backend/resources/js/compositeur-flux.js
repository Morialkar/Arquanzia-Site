// Compose l'adresse d'un flux à partir des cases cochées.
export default function initCompositeurFlux() {
    var sortie = document.getElementById('flux-url');
    if (!sortie) {
        return;
    }

    // L'adresse de base vient du gabarit : un module ne peut pas appeler route().
    var base = sortie.dataset.fluxBase;
    var bouton = document.getElementById('flux-copier');
    var etat = document.getElementById('flux-etat');

    function valeurs(nom) {
        return Array.prototype.slice
            .call(document.querySelectorAll('input[data-flux="' + nom + '"]:checked'))
            .map(function (c) { return c.value; })
            .sort();
    }

    function recomposer() {
        // Le tri et l'ordre des paramètres reproduisent la forme canonique du serveur :
        // une adresse composée ici ne déclenche donc aucune redirection.
        var params = [];
        var livres = valeurs('livres');
        var sections = valeurs('sections');

        if (livres.length) { params.push('livres=' + livres.join(',')); }
        if (sections.length) { params.push('sections=' + sections.join(',')); }

        sortie.textContent = params.length ? base + '?' + params.join('&') : base;
    }

    Array.prototype.forEach.call(document.querySelectorAll('input[data-flux]'), function (c) {
        c.addEventListener('change', recomposer);
    });

    bouton.addEventListener('click', function () {
        var texte = sortie.textContent;

        if (navigator.clipboard) {
            navigator.clipboard.writeText(texte).then(
                function () { etat.textContent = 'Adresse copiée.'; },
                function () { etat.textContent = 'Copie impossible — sélectionnez l’adresse à la main.'; }
            );
        } else {
            etat.textContent = 'Copie impossible — sélectionnez l’adresse à la main.';
        }
    });

    recomposer();
}
