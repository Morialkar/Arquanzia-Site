import './bootstrap';

import initAncres from './ancres';
import initBascule from './bascule';
import initConfirmation from './confirmation';
import initCompositeurFlux from './compositeur-flux';
import initEtoiles from './etoiles';
import initFormulaireEncyclopedie from './formulaire-encyclopedie';
import initGalerie from './galerie';
import initLecture from './lecture';
import initMenuMobile from './menu-mobile';
import initRecherche from './recherche';
import initSlugAlerte from './slug-alerte';
import initTheme from './theme';
import initThemeAdmin from './theme-admin';
import initWikilinkPopover from './wikilink-popover';

// Chaque module se retire de lui-même si les éléments qu'il vise sont absents : le
// back-office et le site public partagent donc le même paquet sans se gêner.
function demarrer() {
    initConfirmation();
    initSlugAlerte();
    initBascule();
    initAncres();
    initTheme();
    initMenuMobile();
    initRecherche();
    initEtoiles();
    initWikilinkPopover();
    initThemeAdmin();
    initLecture();
    initGalerie();
    initCompositeurFlux();
    initFormulaireEncyclopedie();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', demarrer);
} else {
    demarrer();
}
