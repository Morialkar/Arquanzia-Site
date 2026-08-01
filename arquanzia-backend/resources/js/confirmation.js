// Confirmation avant une action destructrice.
//
// Ces confirmations vivaient dans des attributs onsubmit, que toute politique de sécurité de
// contenu bloque. L'intention est désormais portée par un attribut de données, et un seul
// écouteur délégué la traite — les formulaires ajoutés plus tard en héritent sans effort.
export default function initConfirmation() {
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-confirmer]');
        if (!form) {
            return;
        }

        if (!window.confirm(form.dataset.confirmer)) {
            event.preventDefault();
        }
    });
}
