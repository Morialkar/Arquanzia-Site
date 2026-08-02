<?php

namespace Tests\Unit;

use App\Support\ReadingTime;
use PHPUnit\Framework\TestCase;

class ReadingTimeTest extends TestCase
{
    public function test_compte_les_mots_d_un_texte_simple(): void
    {
        $this->assertSame(4, ReadingTime::of('Un texte de quatre')->words);
    }

    public function test_le_balisage_n_est_pas_compte(): void
    {
        $simple = ReadingTime::of('Un mot important ici')->words;
        $balise = ReadingTime::of('Un mot **important** ici')->words;

        $this->assertSame($simple, $balise);
    }

    /** L'adresse d'un lien ne se lit pas : seul son libellé compte. */
    public function test_l_adresse_d_un_lien_n_est_pas_comptee(): void
    {
        $this->assertSame(3, ReadingTime::of('Voir [la carte](https://exemple.test/une/adresse/tres/longue)')->words);
    }

    public function test_un_wikilink_compte_pour_son_libelle(): void
    {
        $this->assertSame(2, ReadingTime::of('Voir [[Thalria]]')->words);
        $this->assertSame(3, ReadingTime::of('Voir [[Thalria|les terres]]')->words);
    }

    public function test_une_image_n_est_pas_comptee(): void
    {
        $this->assertSame(2, ReadingTime::of('Du texte ![une illustration](image.jpg)')->words);
    }

    public function test_le_html_n_est_pas_compte(): void
    {
        $this->assertSame(2, ReadingTime::of('<div class="tres longue classe">Du texte</div>')->words);
    }

    public function test_un_texte_vide_est_vide(): void
    {
        foreach ([null, '', '   ', "\n\n"] as $vide) {
            $this->assertTrue(ReadingTime::of($vide)->isEmpty());
            $this->assertSame('', ReadingTime::of($vide)->label());
        }
    }

    /** Un chapitre vide ne doit pas annoncer « 0 min ». */
    public function test_la_duree_minimale_est_d_une_minute(): void
    {
        $this->assertSame(1, ReadingTime::of('Trois petits mots')->minutes());
    }

    public function test_un_texte_court_est_annonce_comme_tel(): void
    {
        $this->assertStringContainsString('moins d’une minute', ReadingTime::of('Court texte')->label());
    }

    public function test_un_texte_long_annonce_ses_minutes(): void
    {
        $label = ReadingTime::of(str_repeat('mot ', 1000))->label();

        $this->assertStringContainsString('5 min', $label);
        $this->assertStringContainsString('1 000 mots', $label);
    }

    public function test_la_duree_est_arrondie_vers_le_haut(): void
    {
        // 201 mots dépassent une minute : on annonce deux, jamais une.
        $this->assertSame(2, ReadingTime::of(str_repeat('mot ', 201))->minutes());
    }

    public function test_plusieurs_textes_s_additionnent(): void
    {
        $total = ReadingTime::ofMany(['Un deux trois', 'quatre cinq', null]);

        $this->assertSame(5, $total->words);
    }

    public function test_une_liste_vide_est_vide(): void
    {
        $this->assertTrue(ReadingTime::ofMany([])->isEmpty());
    }
}
