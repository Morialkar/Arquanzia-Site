<?php

namespace App\Jobs;

use App\Models\Chapter;
use App\Models\DeliveryEmail;
use App\Models\DeliveryJob;
use App\Services\BookExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendChapterDeliveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 300; // 5 minutes

    public function __construct(
        public DeliveryJob $deliveryJob
    ) {}

    public function handle(): void
    {
        $deliveryJob = $this->deliveryJob;
        $deliveryEmail = $deliveryJob->deliveryEmail;
        $chapter = $deliveryJob->chapter;
        $book = $deliveryJob->book;

        if (!$deliveryEmail || !$deliveryEmail->is_active) {
            $deliveryJob->update(['status' => 'failed', 'error_message' => 'Adresse désactivée']);
            return;
        }

        $deliveryJob->increment('attempts');

        try {
            $exportService = new BookExportService();
            $exportPath = $exportService->getChapterExportPath($chapter, $deliveryJob->format_sent);
            $filePath = Storage::disk('local')->path($exportPath);

            if (!file_exists($filePath)) {
                throw new \Exception("Fichier généré introuvable: {$exportPath}");
            }

            Mail::send([], [], function ($message) use ($deliveryEmail, $chapter, $book, $filePath, $deliveryJob) {
                $message->to($deliveryEmail->email)
                    ->subject("📖 {$book->title} - {$chapter->title}")
                    ->text("Nouveau chapitre disponible: {$chapter->title}\n\nBonne lecture!")
                    ->attach($filePath, [
                        'as' => "{$book->title} - {$chapter->title}." . $deliveryJob->format_sent,
                    ]);
            });

            $deliveryJob->update(['status' => 'sent']);
            $deliveryEmail->resetFailCount();

        } catch (\Exception $e) {
            $deliveryJob->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            $deliveryEmail->incrementFailCount();

            if ($this->attempts() < $this->tries) {
                throw $e; // Retry
            }
        }
    }
}
