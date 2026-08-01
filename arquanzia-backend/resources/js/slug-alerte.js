// Avertit avant de changer le slug d'un chapitre déjà publié.
//
// Le formulaire ne porte l'attribut que si le chapitre est publié : la présence de
// data-slug-initial suffit donc à savoir si l'avertissement s'applique.
export default function initSlugAlerte() {
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-slug-initial]');
        if (!form) {
            return;
        }

        const champ = form.querySelector('input[name="slug"]');
        if (!champ || champ.value === form.dataset.slugInitial) {
            return;
        }

        const message =
            'Avertissement — ce chapitre est publié.\n\n' +
            'Changer son slug modifie son adresse : les anciens liens cesseront de fonctionner, ' +
            'et le chapitre reparaîtra comme une nouveauté auprès des personnes abonnées au flux RSS.\n\n' +
            'Continuer ?';

        if (!window.confirm(message)) {
            event.preventDefault();
        }
    });
}
