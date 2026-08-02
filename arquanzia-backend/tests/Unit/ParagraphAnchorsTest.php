<?php

namespace Tests\Unit;

use App\Support\ParagraphAnchors;
use PHPUnit\Framework\TestCase;

class ParagraphAnchorsTest extends TestCase
{
    private function ids(string $html): array
    {
        preg_match_all('/<p id="([^"]+)"/', ParagraphAnchors::apply($html), $m);

        return $m[1];
    }

    public function test_chaque_paragraphe_recoit_un_identifiant(): void
    {
        $this->assertCount(2, $this->ids('<p>Premier</p><p>Second</p>'));
    }

    public function test_l_identifiant_est_stable_d_un_rendu_a_l_autre(): void
    {
        $html = '<p>Un paragraphe.</p>';

        $this->assertSame($this->ids($html), $this->ids($html));
    }

    /**
     * Le point décisif : un identifiant positionnel casserait tous les liens partagés dès
     * qu'un paragraphe est inséré plus haut.
     */
    public function test_inserer_un_paragraphe_en_tete_ne_change_pas_les_autres(): void
    {
        $avant = $this->ids('<p>Alpha</p><p>Beta</p>');
        $apres = $this->ids('<p>Nouveau</p><p>Alpha</p><p>Beta</p>');

        $this->assertSame($avant, array_slice($apres, 1));
    }

    /** Mettre un mot en gras ne doit pas détacher les liens qui visaient ce paragraphe. */
    public function test_le_balisage_ne_change_pas_l_identifiant(): void
    {
        $this->assertSame(
            $this->ids('<p>Un mot important ici</p>'),
            $this->ids('<p>Un mot <strong>important</strong> ici</p>'),
        );
    }

    public function test_l_espacement_ne_change_pas_l_identifiant(): void
    {
        $this->assertSame(
            $this->ids('<p>Deux mots</p>'),
            $this->ids("<p>Deux \n   mots</p>"),
        );
    }

    public function test_deux_paragraphes_identiques_recoivent_des_identifiants_distincts(): void
    {
        $ids = $this->ids('<p>Pareil</p><p>Pareil</p>');

        $this->assertCount(2, array_unique($ids));
    }

    /** Un paragraphe de citation ou de liste appartient à un ensemble : on ne le cite pas seul. */
    public function test_les_paragraphes_imbriques_ne_sont_pas_ancres(): void
    {
        $html = ParagraphAnchors::apply('<blockquote><p>Citation</p></blockquote><ul><li><p>Item</p></li></ul>');

        $this->assertStringNotContainsString('<p id=', $html);
    }

    public function test_un_paragraphe_sans_texte_n_est_pas_ancre(): void
    {
        $html = ParagraphAnchors::apply('<p><img src="/a.jpg"></p>');

        $this->assertStringNotContainsString('id="p-', $html);
    }

    public function test_une_ancre_cliquable_est_ajoutee(): void
    {
        $html = ParagraphAnchors::apply('<p>Texte</p>');

        $this->assertStringContainsString('class="paragraph-anchor"', $html);
        $this->assertMatchesRegularExpression('/href="#p-[0-9a-f]{8}"/', $html);
    }

    public function test_les_accents_survivent(): void
    {
        $this->assertStringContainsString('éàû œ', ParagraphAnchors::apply('<p>éàû œ</p>'));
    }

    public function test_le_html_environnant_est_preserve(): void
    {
        $html = ParagraphAnchors::apply('<h2>Titre</h2><p>Texte</p><ul><li>Item</li></ul>');

        $this->assertStringContainsString('<h2>Titre</h2>', $html);
        $this->assertStringContainsString('<li>Item</li>', $html);
    }

    public function test_un_contenu_vide_ne_leve_pas_d_erreur(): void
    {
        foreach ([null, '', '   '] as $vide) {
            $this->assertSame('', ParagraphAnchors::apply($vide));
        }
    }

    /** C2 devra retrouver l'identifiant d'un paragraphe pour y accrocher une note. */
    public function test_l_identifiant_peut_etre_calcule_depuis_le_texte_seul(): void
    {
        $html = ParagraphAnchors::apply('<p>Un paragraphe.</p>');

        $this->assertStringContainsString(
            'id="'.ParagraphAnchors::identifierFor('Un paragraphe.').'"',
            $html,
        );
    }
}
