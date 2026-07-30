<?php

namespace Tests\Unit;

use App\Helpers\MarkdownHelper;
use App\Models\Wikilink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Le rendu Markdown est assuré par un parseur maison qui pré-traite le texte avant de le
 * confier à CommonMark. Ce pré-traitement existe pour absorber les particularités des notes
 * Obsidian, et rien ne le protégeait. Ces tests fixent son comportement, notamment en vue de
 * son remplacement éventuel par Str::markdown() seul.
 */
class MarkdownHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_markdown_de_base_est_rendu(): void
    {
        $html = MarkdownHelper::render("# Titre\n\nUn paragraphe.");

        $this->assertStringContainsString('<h1>Titre</h1>', $html);
        $this->assertStringContainsString('Un paragraphe.', $html);
    }

    public function test_une_liste_est_rendue(): void
    {
        $html = MarkdownHelper::render("* premier\n* second");

        $this->assertStringContainsString('<ul>', $html);
        $this->assertStringContainsString('premier', $html);
        $this->assertStringContainsString('second', $html);
    }

    public function test_le_gras_non_ferme_est_referme(): void
    {
        $html = MarkdownHelper::render('**du gras jamais fermé');

        $this->assertStringContainsString('<strong>du gras jamais fermé</strong>', $html);
    }

    public function test_l_italique_non_ferme_est_referme(): void
    {
        $html = MarkdownHelper::render('*de l’italique jamais fermé');

        $this->assertStringContainsString('<em>', $html);
    }

    public function test_les_lignes_composees_uniquement_d_asterisques_sont_supprimees(): void
    {
        $html = MarkdownHelper::render("Du texte.\n**\nEncore du texte.");

        $this->assertStringNotContainsString('**', $html);
        $this->assertStringContainsString('Du texte.', $html);
        $this->assertStringContainsString('Encore du texte.', $html);
    }

    public function test_les_fins_de_ligne_windows_sont_normalisees(): void
    {
        $avec = MarkdownHelper::render("Ligne une\r\nLigne deux");
        $sans = MarkdownHelper::render("Ligne une\nLigne deux");

        $this->assertSame($sans, $avec);
    }

    public function test_les_retours_a_la_ligne_simples_sont_preserves(): void
    {
        $html = MarkdownHelper::render("Ligne une\nLigne deux");

        $this->assertStringContainsString('<br', $html);
    }

    public function test_une_chaine_vide_ne_leve_pas_d_erreur(): void
    {
        $this->assertSame('', trim(strip_tags(MarkdownHelper::render(''))));
    }

    public function test_un_wikilink_non_resolu_devient_du_texte_simple(): void
    {
        $html = MarkdownHelper::render('Voir [[Terme Inconnu]] pour la suite.');

        $this->assertStringContainsString('Terme Inconnu', $html);
        $this->assertStringNotContainsString('[[', $html);
        $this->assertStringNotContainsString('wikilink-resolved', $html);
    }

    public function test_un_wikilink_resolu_devient_un_lien(): void
    {
        Wikilink::factory()->create([
            'term' => 'Sortilège',
            'custom_url' => 'https://example.test/sortilege',
        ]);

        $html = MarkdownHelper::render('Voir [[Sortilège]].');

        $this->assertStringContainsString('href="https://example.test/sortilege"', $html);
        $this->assertStringContainsString('wikilink-resolved', $html);
    }

    public function test_un_wikilink_avec_libelle_affiche_le_libelle(): void
    {
        Wikilink::factory()->create([
            'term' => 'Sortilège',
            'custom_url' => 'https://example.test/sortilege',
        ]);

        $html = MarkdownHelper::render('Voir [[Sortilège|les sortilèges]].');

        $this->assertStringContainsString('les sortilèges', $html);
        $this->assertStringContainsString('href="https://example.test/sortilege"', $html);
    }
}
