<?php

namespace App\Services;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Retire d'un SVG tout ce qui peut exécuter du code ou appeler l'extérieur.
 *
 * Un SVG est un document XML que le navigateur exécute : il peut contenir <script>, des
 * attributs d'événement, du contenu HTML via <foreignObject>, ou des références externes.
 * Servi depuis la même origine que le site — ce qui est le cas du logo — il dispose alors de
 * tous les droits d'un script du site.
 *
 * Le téléversement est réservé aux administrateurs, ce n'est donc pas une porte ouverte : la
 * protection vise le cas où un compte d'administration serait compromis, où un SVG piégé
 * constituerait une porte dérobée permanente survivant au changement du mot de passe.
 *
 * L'approche est une liste noire d'éléments et d'attributs dangereux plutôt qu'une liste
 * blanche stricte, afin de ne pas mutiler des SVG légitimes exportés par un logiciel de
 * dessin — le compromis assumé d'une entrée déjà privilégiée.
 */
class SvgSanitizer
{
    /** Éléments retirés avec tout leur contenu. */
    private const FORBIDDEN_ELEMENTS = [
        'script',
        'foreignObject',
        'iframe',
        'embed',
        'object',
        'audio',
        'video',
        'handler',
        'set',
        'animate',
        'animateTransform',
        'animateMotion',
    ];

    /** Attributs retirés partout : href externes traités à part. */
    private const FORBIDDEN_ATTRIBUTES = [
        'onload',
        'onerror',
        'onclick',
        'onmouseover',
        'onmouseout',
        'onmousemove',
        'onfocus',
        'onblur',
        'onbegin',
        'onend',
        'onrepeat',
        'onactivate',
        'formaction',
    ];

    /**
     * @throws \RuntimeException si le contenu n'est pas un SVG exploitable
     */
    public function sanitize(string $svg): string
    {
        if (trim($svg) === '') {
            throw new \RuntimeException('Le fichier SVG est vide.');
        }

        // Les entités externes permettent de lire des fichiers du serveur (XXE). libxml les
        // ignore par défaut depuis PHP 8, mais la substitution reste explicitement refusée.
        $previous = libxml_use_internal_errors(true);

        $dom = new DOMDocument;
        $loaded = $dom->loadXML($svg, LIBXML_NONET | LIBXML_NOENT & ~LIBXML_NOENT);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded || ! $dom->documentElement) {
            throw new \RuntimeException('Le fichier SVG est illisible.');
        }

        if (strtolower($dom->documentElement->localName) !== 'svg') {
            throw new \RuntimeException('Le fichier ne contient pas de SVG.');
        }

        $this->removeDoctype($dom);
        $this->removeForbiddenElements($dom);
        $this->removeForbiddenAttributes($dom);

        $clean = $dom->saveXML($dom->documentElement);

        if ($clean === false) {
            throw new \RuntimeException('Le SVG n’a pas pu être réécrit.');
        }

        return $clean;
    }

    /** Un DOCTYPE peut déclarer des entités : on le supprime dans tous les cas. */
    private function removeDoctype(DOMDocument $dom): void
    {
        if ($dom->doctype) {
            $dom->removeChild($dom->doctype);
        }
    }

    private function removeForbiddenElements(DOMDocument $dom): void
    {
        $xpath = new DOMXPath($dom);
        $doomed = [];

        foreach ($xpath->query('//*') ?: [] as $node) {
            if ($node instanceof DOMElement
                && in_array(strtolower($node->localName), array_map('strtolower', self::FORBIDDEN_ELEMENTS), true)) {
                $doomed[] = $node;
            }
        }

        foreach ($doomed as $node) {
            $node->parentNode?->removeChild($node);
        }
    }

    private function removeForbiddenAttributes(DOMDocument $dom): void
    {
        $xpath = new DOMXPath($dom);
        $forbidden = array_map('strtolower', self::FORBIDDEN_ATTRIBUTES);

        foreach ($xpath->query('//*') ?: [] as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $doomed = [];

            foreach (iterator_to_array($node->attributes ?? []) as $attribute) {
                if (! $attribute instanceof DOMAttr) {
                    continue;
                }

                $name = strtolower($attribute->name);
                $value = trim($attribute->value);

                // Tout attribut d'événement, y compris ceux qui ne sont pas listés.
                if (str_starts_with($name, 'on') || in_array($name, $forbidden, true)) {
                    $doomed[] = $attribute;

                    continue;
                }

                // javascript: et data: dissimulent du code exécutable dans une URL.
                if (in_array($name, ['href', 'xlink:href', 'src', 'from', 'to', 'values'], true)
                    && preg_match('/^\s*(javascript|data|vbscript)\s*:/i', $value)) {
                    $doomed[] = $attribute;
                }
            }

            foreach ($doomed as $attribute) {
                $node->removeAttributeNode($attribute);
            }
        }
    }
}
