<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * En-têtes de sécurité appliqués à toutes les réponses.
 *
 * La politique de sécurité de contenu (CSP) n'y figure volontairement pas encore : les
 * gabarits chargent Tailwind depuis un CDN et embarquent la configuration ainsi que le
 * sélecteur de thème en scripts intégrés. Une CSP utile exigerait 'unsafe-inline' et
 * autoriserait un domaine tiers, ce qui la viderait de son sens. Elle sera posée avec le
 * lot 5.1, qui rapatrie les assets et supprime les scripts intégrés.
 *
 * Les en-têtes ci-dessous, eux, sont utiles immédiatement et sans contrepartie.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
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

        return $response;
    }
}
