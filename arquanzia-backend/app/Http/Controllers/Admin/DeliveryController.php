<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryEmail;
use App\Models\DeliveryJob;
use App\Models\Chapter;
use App\Services\ChapterDeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_sent' => DeliveryJob::where('status', 'sent')->count(),
            'total_failed' => DeliveryJob::where('status', 'failed')->count(),
            'total_pending' => DeliveryJob::where('status', 'pending')->count(),
            'active_emails' => DeliveryEmail::where('is_active', true)->count(),
            'disabled_emails' => DeliveryEmail::where('is_active', false)->count(),
        ];

        $recentJobs = DeliveryJob::with(['user', 'deliveryEmail', 'book', 'chapter'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $failedEmails = DeliveryEmail::where('fail_count', '>', 0)
            ->with('user')
            ->orderBy('fail_count', 'desc')
            ->limit(20)
            ->get();

        return view('admin.delivery.index', [
            'stats' => $stats,
            'recentJobs' => $recentJobs,
            'failedEmails' => $failedEmails,
        ]);
    }

    public function retryJob(DeliveryJob $deliveryJob): RedirectResponse
    {
        $deliveryJob->update(['status' => 'pending', 'attempts' => 0, 'error_message' => null]);
        \App\Jobs\SendChapterDeliveryJob::dispatch($deliveryJob);

        return back()->with('success', 'Envoi relancé');
    }

    public function disableEmail(DeliveryEmail $deliveryEmail): RedirectResponse
    {
        $deliveryEmail->update(['is_active' => false]);
        return back()->with('success', 'Adresse désactivée');
    }

    public function dispatchChapter(Request $request, Chapter $chapter): RedirectResponse
    {
        $service = app(ChapterDeliveryService::class);
        $jobsCreated = $service->dispatchForChapter($chapter);

        return back()->with('success', "{$jobsCreated} livraisons programmées pour ce chapitre");
    }
}
