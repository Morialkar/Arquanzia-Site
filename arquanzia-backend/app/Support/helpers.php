<?php

use Illuminate\Support\Facades\Vite;

if (! function_exists('csp_nonce')) {
    /**
     * Nonce de la requête courante, partagé avec les balises produites par Vite.
     *
     * Vite::useCspNonce() en engendre un par requête et l'appose lui-même sur ses propres
     * balises ; le réutiliser ici garantit qu'assets construits et scripts intégrés sont
     * autorisés par la même valeur.
     */
    function csp_nonce(): string
    {
        return Vite::cspNonce() ?? '';
    }
}
