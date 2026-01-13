<?php

namespace App\Console\Commands;

use App\Models\Chapter;
use App\Services\ChapterDeliveryService;
use Illuminate\Console\Command;

class DispatchChapterDeliveries extends Command
{
    protected $signature = 'chapters:deliver {chapter_id}';
    protected $description = 'Dispatch delivery jobs for a published chapter';

    public function handle(ChapterDeliveryService $service): int
    {
        $chapter = Chapter::find($this->argument('chapter_id'));

        if (!$chapter) {
            $this->error('Chapter not found');
            return 1;
        }

        if (!$chapter->is_published) {
            $this->error('Chapter is not published');
            return 1;
        }

        $jobsCreated = $service->dispatchForChapter($chapter);

        $this->info("Created {$jobsCreated} delivery jobs for chapter: {$chapter->title}");

        return 0;
    }
}
