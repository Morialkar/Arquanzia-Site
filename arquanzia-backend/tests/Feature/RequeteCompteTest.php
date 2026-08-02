<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaNode;
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

    /** Arborescence à trois niveaux : c'est la profondeur qui déclenchait les requêtes en trop. */
    private function ajouterDuContenu(int $n): void
    {
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->count($n)->create();

        $racine = EncyclopediaNode::factory()->category()->create();
        $intermediaire = EncyclopediaNode::factory()->category()->create(['parent_id' => $racine->id]);
        EncyclopediaNode::factory()->count($n)->create(['parent_id' => $intermediaire->id]);
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
            fn () => app(SearchService::class)->search('en'),
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
