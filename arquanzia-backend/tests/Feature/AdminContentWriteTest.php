<?php

namespace Tests\Feature;

use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use App\Models\Post;
use App\Models\Wikilink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsAdmin;
use Tests\TestCase;

/**
 * Écritures du back-office pour les billets, l'encyclopédie, les fragments et les wikilinks.
 * On vérifie l'effet en base, pas seulement le code de retour : c'est ce qui attrape un champ
 * retiré du $fillable ou une validation devenue incohérente avec le formulaire.
 */
class AdminContentWriteTest extends TestCase
{
    use ActsAsAdmin, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAdmin();
    }

    // — Billets —

    public function test_creer_un_billet_fonctionne(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.posts.store', absolute: false), [
                'title' => 'Une annonce',
                'preview_text' => 'Le résumé.',
                'content_full' => 'Le texte complet.',
            ])
            ->assertRedirect(route('admin.posts.index'));

        $this->assertDatabaseHas('posts', ['title' => 'Une annonce', 'preview_text' => 'Le résumé.']);
    }

    public function test_modifier_un_billet_fonctionne(): void
    {
        $post = Post::factory()->create();

        $this->actingAsAdmin()
            ->put(route('admin.posts.update', $post, false), [
                'title' => 'Titre révisé',
                'preview_text' => 'Résumé révisé.',
                'content_full' => 'Texte révisé.',
            ])
            ->assertRedirect(route('admin.posts.index'));

        $this->assertSame('Titre révisé', $post->fresh()->title);
    }

    /** Post utilise SoftDeletes : la suppression doit être logique, pas définitive. */
    public function test_supprimer_un_billet_est_une_suppression_logique(): void
    {
        $post = Post::factory()->create();

        $this->actingAsAdmin()
            ->delete(route('admin.posts.destroy', $post, false))
            ->assertRedirect(route('admin.posts.index'));

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_un_billet_sans_titre_est_refuse_et_rien_n_est_ecrit(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.posts.store', absolute: false), ['preview_text' => 'Sans titre.'])
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('posts', 0);
    }

    // — Encyclopédie —

    public function test_creer_une_entree_d_encyclopedie_fonctionne(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.encyclopedia.store', absolute: false), [
                'title' => 'Les Arcanes',
                'type' => 'article',
                'order_index' => 0,
                'is_published' => '1',
                'teaser_md' => 'Un aperçu.',
                'content_md' => 'Le contenu.',
            ])
            ->assertRedirect(route('admin.encyclopedia.index'));

        $this->assertDatabaseHas('encyclopedia_nodes', [
            'title' => 'Les Arcanes',
            'slug' => 'les-arcanes',
            'is_published' => true,
        ]);
    }

    public function test_creer_une_entree_en_brouillon_fonctionne(): void
    {
        $this->actingAsAdmin()->post(route('admin.encyclopedia.store', absolute: false), [
            'title' => 'Brouillon',
            'type' => 'article',
            'order_index' => 0,
        ]);

        $this->assertDatabaseHas('encyclopedia_nodes', ['title' => 'Brouillon', 'is_published' => false]);
    }

    public function test_publier_une_entree_d_encyclopedie_fonctionne(): void
    {
        $node = EncyclopediaNode::factory()->draft()->create();

        $this->actingAsAdmin()->put(route('admin.encyclopedia.update', $node, false), [
            'title' => $node->title,
            'slug' => $node->slug,
            'type' => $node->type,
            'order_index' => 0,
            'is_published' => '1',
        ]);

        $this->assertTrue($node->fresh()->is_published);
    }

    public function test_supprimer_une_categorie_emporte_ses_enfants(): void
    {
        $category = EncyclopediaNode::factory()->category()->create();
        $child = EncyclopediaNode::factory()->create(['parent_id' => $category->id]);

        $this->actingAsAdmin()
            ->delete(route('admin.encyclopedia.destroy', $category, false))
            ->assertRedirect(route('admin.encyclopedia.index'));

        $this->assertDatabaseMissing('encyclopedia_nodes', ['id' => $category->id]);
        $this->assertDatabaseMissing('encyclopedia_nodes', ['id' => $child->id]);
    }

    public function test_un_type_d_entree_invalide_est_refuse(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.encyclopedia.store', absolute: false), [
                'title' => 'Type douteux',
                'type' => 'chimere',
                'order_index' => 0,
            ])
            ->assertSessionHasErrors('type');

        $this->assertDatabaseCount('encyclopedia_nodes', 0);
    }

    // — Fragments —

    public function test_creer_une_categorie_de_fragments_fonctionne(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.fragments.store', absolute: false), [
                'title' => 'Croquis',
                'type' => 'category',
                'order_index' => 0,
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.fragments.index'));

        $this->assertDatabaseHas('fragment_nodes', [
            'title' => 'Croquis',
            'type' => 'category',
            'is_published' => true,
        ]);
    }

    public function test_un_fragment_de_type_item_exige_un_type_de_media(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.fragments.store', absolute: false), [
                'title' => 'Sans média',
                'type' => 'item',
                'order_index' => 0,
            ])
            ->assertSessionHasErrors('media_type');

        $this->assertDatabaseCount('fragment_nodes', 0);
    }

    public function test_supprimer_un_fragment_fonctionne(): void
    {
        $fragment = FragmentNode::factory()->create();

        $this->actingAsAdmin()
            ->delete(route('admin.fragments.destroy', $fragment, false))
            ->assertRedirect(route('admin.fragments.index'));

        $this->assertDatabaseMissing('fragment_nodes', ['id' => $fragment->id]);
    }

    // — Wikilinks —

    public function test_creer_un_wikilink_fonctionne(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.wikilinks.store', absolute: false), [
                'term' => 'Sortilège',
                'custom_url' => 'https://example.test/sortilege',
            ])
            ->assertRedirect(route('admin.wikilinks.index'));

        $this->assertDatabaseHas('wikilinks', ['term' => 'Sortilège']);
    }

    public function test_modifier_puis_supprimer_un_wikilink_fonctionne(): void
    {
        $wikilink = Wikilink::factory()->create();

        $this->actingAsAdmin()->put(route('admin.wikilinks.update', $wikilink, false), [
            'term' => 'Terme révisé',
            'custom_url' => 'https://example.test/revise',
        ]);
        $this->assertSame('Terme révisé', $wikilink->fresh()->term);

        $this->actingAsAdmin()->delete(route('admin.wikilinks.destroy', $wikilink, false));
        $this->assertDatabaseMissing('wikilinks', ['id' => $wikilink->id]);
    }
}
