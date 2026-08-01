<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * En-têtes de sécurité appliqués à toutes les réponses.
 *
 * La politique de sécurité de contenu s'appuie sur un nonce engendré par requête plutôt que
 * sur 'unsafe-inline'. C'est ce que le rapatriement des assets a rendu possible : tant que
 * Tailwind venait d'un CDN et que sa configuration vivait en scripts intégrés, il aurait
 * fallu autoriser un domaine tiers et l'exécution de tout script en ligne — une politique
 * qui n'aurait rien protégé.
 *
 * Les gestionnaires en ligne (onclick, onsubmit) ont été convertis en écouteurs délégués :
 * un nonce ne les couvre pas, et les tolérer aurait exigé 'unsafe-hashes'.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Le nonce doit exister avant le rendu des gabarits. Vite l'appose lui-même sur ses
        // balises, et le rend disponible aux scripts intégrés via l'assistant csp_nonce().
        $nonce = Vite::useCspNonce();

        $response = $next($request);

        // Empêche le navigateur de deviner un type MIME : un fichier téléversé servi avec le
        // mauvais type ne sera pas réinterprété comme du HTML ou du JavaScript.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Interdit l'inclusion du site dans un cadre : protège du détournement de clic, en
        // particulier sur les formulaires du back-office.
        $response->headers->set('X-Frame-Options', 'DENY');

        // Ne divulgue pas l'URL complète d'origine aux sites tiers.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Le site n'a besoin d'aucune de ces interfaces.
        $response->headers->set(
            'Permissions-Policy',
            'accelerometer=(), camera=(), geolocation=(), gyroscope=(), microphone=(), payment=(), usb=()'
        );

        $response->headers->set('Content-Security-Policy', $this->policy($nonce));

        return $response;
    }

    private function policy(string $nonce): string
    {
        return implode('; ', [
            // Tout est servi depuis le site lui-même depuis le rapatriement des assets.
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'",
            // Les styles restent permissifs : les attributs style= subsistent dans les
            // gabarits, et un nonce ne les couvre pas. À resserrer une fois qu'ils auront
            // disparu — la valeur du verrou est bien moindre que pour les scripts.
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data:",
            "font-src 'self'",
            // Le site n'appelle aucune interface distante.
            "connect-src 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            // Interdit l'injection d'un <object> ou d'une applet.
            "object-src 'none'",
        ]);
    }
}
