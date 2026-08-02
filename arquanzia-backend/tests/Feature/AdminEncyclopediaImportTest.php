<?php

namespace Tests\Feature;

use App\Models\EncyclopediaNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\ActsAsAdmin;
use Tests\TestCase;
use ZipArchive;

/**
 * Import d'une arborescence d'encyclopédie depuis une archive ZIP de notes Obsidian.
 *
 * C'est l'écriture la plus lourde du back-office : elle crée des dizaines de nœuds d'un coup
 * et n'était couverte par rien. L'archive est construite pour de vrai dans un fichier
 * temporaire, puis nettoyée — le contrôleur écrit sur le disque sans passer par la façade
 * Storage, donc Storage::fake() ne l'intercepterait pas.
 */
class AdminEncyclopediaImportTest extends TestCase
{
    use ActsAsAdmin, RefreshDatabase;

    /** @var list<string> */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAdmin();
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }

    /**
     * @param  array<string, string>  $entries  chemin dans l'archive => contenu
     */
    private function makeZip(array $entries): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'arqzip').'.zip';
        $this->tempFiles[] = $path;

        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($entries as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();

        return new UploadedFile($path, 'encyclopedie.zip', 'application/zip', null, true);
    }

    public function test_analyser_une_archive_affiche_la_confirmation(): void
    {
        $zip = $this->makeZip([
            'bestiaire/dragon.md' => "# Dragon\n\nUne créature ancienne.",
        ]);

        $this->actingAsAdmin()
            ->post(route('admin.encyclopedia.import.analyze', absolute: false), ['zip_file' => $zip])
            ->assertSuccessful()
            ->assertViewIs('admin.encyclopedia.import-confirm');

        // L'analyse ne doit rien écrire : elle prépare seulement la confirmation.
        $this->assertDatabaseCount('encyclopedia_nodes', 0);
    }

    public function test_executer_l_import_cree_l_arborescence(): void
    {
        $zip = $this->makeZip([
            'bestiaire/dragon.md' => "# Dragon\n\nUne créature ancienne.",
            'bestiaire/griffon.md' => "# Griffon\n\nUn gardien.",
        ]);

        $this->actingAsAdmin()
            ->post(route('admin.encyclopedia.import.analyze', absolute: false), ['zip_file' => $zip]);

        $this->actingAsAdmin()
            ->post(route('admin.encyclopedia.import.execute', absolute: false), ['conflict_mode' => 'overwrite'])
            ->assertRedirect(route('admin.encyclopedia.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('encyclopedia_nodes', ['slug' => 'bestiaire', 'type' => 'category']);
        $this->assertDatabaseHas('encyclopedia_nodes', ['slug' => 'dragon', 'type' => 'article']);
        $this->assertDatabaseHas('encyclopedia_nodes', ['slug' => 'griffon', 'type' => 'article']);

        $dragon = EncyclopediaNode::where('slug', 'dragon')->first();
        $this->assertSame('bestiaire', $dragon->parent->slug, 'L’article doit être rattaché à sa catégorie.');
    }

    /**
     * Un nœud peut en citer un autre créé plus tard dans le même import : l'indexation au fil
     * de l'eau ne pouvait alors pas le résoudre, et l'index sortait incomplet en silence.
     */
    public function test_l_import_indexe_les_mentions_croisees(): void
    {
        $zip = $this->makeZip([
            // « avant » cite « zebre », qui n'existera qu'après lui dans l'ordre alphabétique.
            'bestiaire/avant.md' => "# Avant\n\nOn y croise le [[Zebre]].",
            'bestiaire/zebre.md' => '# Zebre',
        ]);

        $this->actingAsAdmin()
            ->post(route('admin.encyclopedia.import.analyze', absolute: false), ['zip_file' => $zip]);
        $this->actingAsAdmin()
            ->post(route('admin.encyclopedia.import.execute', absolute: false), ['conflict_mode' => 'overwrite']);

        $this->assertDatabaseCount('mentions', 1);

        $cible = EncyclopediaNode::where('slug', 'zebre')->first();
        $this->assertNotNull($cible);
        $this->assertSame(1, $cible->mentionedIn()->count());
    }

    public function test_un_import_sans_analyse_prealable_est_refuse(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.encyclopedia.import.execute', absolute: false), ['conflict_mode' => 'overwrite'])
            ->assertRedirect(route('admin.encyclopedia.import'))
            ->assertSessionHasErrors('zip_file');

        $this->assertDatabaseCount('encyclopedia_nodes', 0);
    }

    public function test_un_fichier_qui_n_est_pas_une_archive_est_refuse(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.encyclopedia.import.analyze', absolute: false), [
                'zip_file' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors('zip_file');

        $this->assertDatabaseCount('encyclopedia_nodes', 0);
    }
}
