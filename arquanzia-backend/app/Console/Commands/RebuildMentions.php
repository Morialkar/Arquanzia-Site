<?php

namespace App\Console\Commands;

use App\Services\MentionIndexer;
use Illuminate\Console\Command;

class RebuildMentions extends Command
{
    protected $signature = 'mentions:rebuild';

    protected $description = 'Reconstruit l’index des mentions entre textes et entrées d’encyclopédie';

    public function handle(MentionIndexer $indexer): int
    {
        $this->info('Reconstruction de l’index des mentions…');

        $total = $indexer->rebuild();

        $this->info("Terminé : {$total} texte(s) parcouru(s).");

        return self::SUCCESS;
    }
}
