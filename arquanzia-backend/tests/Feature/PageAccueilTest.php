<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La porte d'entrée doit refléter le travail le plus récent.
 *
 * Le fil n'y figurait pas du tout, alors qu'il est la seule surface qui bouge entre deux
 * chapitres. Et « Dernière parution » classait par `published_at`, saisi à la main : un
 * chapitre publié sans y toucher passait derrière de vieux chapitres datés.
 */
class PageAccueilTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_derniere_chronique_ouvre_la_page(): void
    {
        Post::factory()->create(['title' => 'Billet ancien', 'created_at' => now()->subMonth()]);
        Post::factory()->create(['title' => 'Billet du jour', 'preview_text' => 'Ce qui vient de paraître.']);

        $reponse = $this->get(route('home'))->assertOk();

        $reponse->assertSee('Dernière chronique')
            ->assertSee('Billet du jour')
            ->assertSee('Ce qui vient de paraître.');

        // Le plus récent des deux, et lui seul, occupe le bloc de tête.
        $this->assertLessThan(
            strpos($reponse->getContent(), 'Dernière parution') ?: PHP_INT_MAX,
            strpos($reponse->getContent(), 'Dernière chronique'),
        );
    }

    public function test_un_billet_sans_texte_d_apercu_montre_le_debut_de_son_corps(): void
    {
        // La colonne est non nulle en base : c'est la chaîne vide, pas NULL, qu'il faut couvrir.
        Post::factory()->create([
            'title' => 'Sans aperçu',
            'preview_text' => '',
            'content_full' => 'Le **sel** de brume y abonde.',
        ]);

        // Le balisage brut n'a rien à faire sur la page d'accueil.
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Le **sel** de brume', escape: false)
            ->assertDontSee('&lt;p&gt;');
    }

    public function test_un_chapitre_publie_sans_date_devance_un_ancien_chapitre_date(): void
    {
        $book = Book::factory()->create(['is_published' => true]);

        Chapter::factory()->for($book)->create([
            'title' => 'Chapitre daté de l’an dernier',
            'is_published' => true,
            'published_at' => now()->subYear(),
        ]);
        Chapter::factory()->for($book)->create([
            'title' => 'Chapitre de ce matin',
            'is_published' => true,
            'published_at' => null,
        ]);

        $reponse = $this->get(route('home'))->assertOk();

        $this->assertLessThan(
            strpos($reponse->getContent(), 'Chapitre daté de l’an dernier') ?: PHP_INT_MAX,
            strpos($reponse->getContent(), 'Chapitre de ce matin'),
        );
    }

    /** Une révision compte comme une activité : c'est du travail sur le texte. */
    public function test_un_chapitre_revise_remonte_devant_un_chapitre_plus_ancien(): void
    {
        $book = Book::factory()->create(['is_published' => true]);

        $ancien = Chapter::factory()->for($book)->create([
            'title' => 'Texte repris', 'is_published' => true,
            'published_at' => now()->subYear(), 'created_at' => now()->subYear(),
        ]);
        Chapter::factory()->for($book)->create([
            'title' => 'Texte paru le mois dernier', 'is_published' => true,
            'published_at' => now()->subMonth(),
        ]);

        $ancien->update(['content_md' => 'Une phrase corrigée.']);

        $reponse = $this->get(route('home'))->assertOk();

        $this->assertLessThan(
            strpos($reponse->getContent(), 'Texte paru le mois dernier') ?: PHP_INT_MAX,
            strpos($reponse->getContent(), 'Texte repris'),
        );
    }

    /** La fiche d'un chapitre dont le livre est en brouillon répond 404 : ne pas y mener. */
    public function test_un_chapitre_de_livre_non_publie_n_ouvre_pas_la_page(): void
    {
        $brouillon = Book::factory()->create(['is_published' => false]);
        Chapter::factory()->for($brouillon)->create([
            'title' => 'Chapitre d’un livre en brouillon',
            'is_published' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Chapitre d’un livre en brouillon');
    }

    /** Un chapitre programmé n'est pas encore paru. */
    public function test_un_chapitre_programme_n_ouvre_pas_la_page(): void
    {
        $book = Book::factory()->create(['is_published' => true]);
        Chapter::factory()->for($book)->create([
            'title' => 'Chapitre à venir',
            'is_published' => true,
            'published_at' => now()->addWeek(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Chapitre à venir');
    }
}
