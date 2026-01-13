<?php

namespace App\Services;

use App\Jobs\SendChapterDeliveryJob;
use App\Models\Chapter;
use App\Models\DeliveryEmail;
use App\Models\DeliveryJob;
use App\Models\UserEntitlement;

class ChapterDeliveryService
{
    public function dispatchForChapter(Chapter $chapter): int
    {
        $book = $chapter->book;
        $jobsCreated = 0;

        // Get all active reader users with delivery emails
        $activeReaderUserIds = UserEntitlement::where('type', 'reader')
            ->where('ends_at', '>', now())
            ->pluck('user_id')
            ->unique();

        foreach ($activeReaderUserIds as $userId) {
            $deliveryEmails = DeliveryEmail::getActiveForUser($userId);

            foreach ($deliveryEmails as $deliveryEmail) {
                $formats = $this->getFormatsToSend($deliveryEmail->format);

                foreach ($formats as $format) {
                    // Check if job already exists
                    $exists = DeliveryJob::where('delivery_email_id', $deliveryEmail->id)
                        ->where('chapter_id', $chapter->id)
                        ->where('format_sent', $format)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $job = DeliveryJob::create([
                        'user_id' => $userId,
                        'delivery_email_id' => $deliveryEmail->id,
                        'book_id' => $book->id,
                        'chapter_id' => $chapter->id,
                        'format_sent' => $format,
                        'status' => 'pending',
                    ]);

                    SendChapterDeliveryJob::dispatch($job);
                    $jobsCreated++;
                }
            }
        }

        return $jobsCreated;
    }

    protected function getFormatsToSend(string $format): array
    {
        return match ($format) {
            'both' => ['epub', 'pdf'],
            default => [$format],
        };
    }
}
