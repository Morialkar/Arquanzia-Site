import './bootstrap';

import initBascule from './bascule';
import initConfirmation from './confirmation';
import initEtoiles from './etoiles';
import initMenuMobile from './menu-mobile';
import initRecherche from './recherche';
import initSlugAlerte from './slug-alerte';
import initTheme from './theme';
import initWikilinkPopover from './wikilink-popover';

// Chaque module se retire de lui-même si les éléments qu'il vise sont absents : le
// back-office et le site public partagent donc le même paquet sans se gêner.
function demarrer() {
    initConfirmation();
    initSlugAlerte();
    initBascule();
    initTheme();
    initMenuMobile();
    initRecherche();
    initEtoiles();
    initWikilinkPopover();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', demarrer);
} else {
    demarrer();
}
