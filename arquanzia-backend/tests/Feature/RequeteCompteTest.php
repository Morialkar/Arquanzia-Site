<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use App\Models\Post;
use App\Services\FeedBuilder;
use App\Services\SearchService;
use App\Support\FeedSelection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Le nombre de requêtes ne doit pas croître avec le contenu.
 *
 * `getFullPath()` remontait les parents un par un, chacun déclenchant sa propre requête :
 * seize pour un flux clairsemé, quarante-six dès que le contenu triplait. Invisible sur un
 * site de quinze documents, mais le flux est interrogé par chaque abonné toutes les quinze à
 * soixante minutes, indéfiniment — c'est le seul endroit où ce coût s'accumule vraiment.
 *
 * Ces tests comparent deux volumes plutôt que de figer un nombre absolu : ce qui compte est
 * l'absence de croissance, pas la valeur exacte, qui bougera au gré des évolutions.
 */
class RequeteCompteTest extends TestCase
{
    use RefreshDatabase;

    private function compter(callable $action): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $action();
        $n = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $n;
    }

    /**
     * Terme présent dans tous les contenus créés.
     *
     * Le contenu doit être déterministe : Eloquent saute les requêtes de chargement groupé
     * quand la collection parente est vide, si bien qu'un terme cherché dans du texte aléatoire
     * faisait varier le compte selon les tirages — la mesure devenait instable sans que rien ne
     * soit cassé.
     */
    private const TERME = 'arquanzien';

    /** Arborescence à trois niveaux : c'est la profondeur qui déclenchait les requêtes en trop. */
    private function ajouterDuContenu(int $n): void
    {
        $book = Book::factory()->create(['description_md' => 'Un récit '.self::TERME.'.']);
        Chapter::factory()->for($book)->count($n)->create(['content_md' => 'Un passage '.self::TERME.'.']);

        $racine = EncyclopediaNode::factory()->category()->create();
        $intermediaire = EncyclopediaNode::factory()->category()->create(['parent_id' => $racine->id]);
        EncyclopediaNode::factory()->count($n)->create([
            'parent_id' => $intermediaire->id,
            'teaser_md' => 'Une entrée '.self::TERME.'.',
        ]);

        FragmentNode::factory()->count($n)->create(['description_md' => 'Un fragment '.self::TERME.'.']);
        Post::factory()->count($n)->create(['content_full' => 'Un billet '.self::TERME.'.']);
    }

    private function assertNeCroitPas(callable $action, string $surface): void
    {
        $this->ajouterDuContenu(3);
        $petit = $this->compter($action);

        $this->ajouterDuContenu(12);
        $grand = $this->compter($action);

        $this->assertSame(
            $petit,
            $grand,
            "Le nombre de requêtes de {$surface} croît avec le contenu : {$petit} puis {$grand}.",
        );
    }

    public function test_le_flux_ne_demande_pas_plus_de_requetes_quand_le_contenu_croit(): void
    {
        $this->assertNeCroitPas(
            fn () => app(FeedBuilder::class)->entries(
                FeedSelection::fromRequest(Request::create('/flux.xml'))
            ),
            'du flux',
        );
    }

    public function test_la_recherche_ne_demande_pas_plus_de_requetes_quand_le_contenu_croit(): void
    {
        $this->assertNeCroitPas(
            fn () => app(SearchService::class)->search(self::TERME),
            'de la recherche',
        );
    }

    public function test_le_sitemap_ne_demande_pas_plus_de_requetes_quand_le_contenu_croit(): void
    {
        $this->assertNeCroitPas(
            fn () => $this->get('/sitemap.xml'),
            'du sitemap',
        );
    }

    /** Un parent circulaire ferait boucler la résolution de chemin sans le garde-fou. */
    public function test_une_arborescence_circulaire_ne_fait_pas_boucler(): void
    {
        $a = EncyclopediaNode::factory()->category()->create();
        $b = EncyclopediaNode::factory()->category()->create(['parent_id' => $a->id]);
        $a->updateQuietly(['parent_id' => $b->id]);

        $this->assertIsString($b->fresh()->getFullPath());
    }
}
