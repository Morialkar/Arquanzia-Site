<?php

namespace App\Http\Controllers;

use App\Models\DeliveryEmail;
use App\Models\DeliveryJob;
use App\Services\EntitlementService;
use App\Services\ViewerResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function __construct(
        protected ViewerResolver $viewerResolver,
        protected EntitlementService $entitlementService
    ) {}

    public function addEmail(Request $request): RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in']) {
            return redirect()->route('login');
        }

        $request->validate([
            'email' => 'required|email',
            'format' => 'required|in:epub,pdf,both',
        ]);

        $userId = $context['user']->id;

        // Max 3 addresses per user
        if (DeliveryEmail::countForUser($userId) >= 3) {
            return back()->withErrors(['email' => 'Maximum 3 adresses autorisées.']);
        }

        // Check if email already exists for user
        $existing = DeliveryEmail::where('user_id', $userId)
            ->where('email', $request->input('email'))
            ->first();

        if ($existing) {
            return back()->withErrors(['email' => 'Cette adresse est déjà enregistrée.']);
        }

        DeliveryEmail::create([
            'user_id' => $userId,
            'email' => $request->input('email'),
            'format' => $request->input('format'),
        ]);

        $fromEmail = config('mail.from.address', 'noreply@arquanzia.com');

        return back()->with('delivery_success', "Adresse ajoutée. Assure-toi qu'elle peut recevoir des fichiers depuis {$fromEmail}");
    }

    public function removeEmail(Request $request, DeliveryEmail $deliveryEmail): RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in'] || $deliveryEmail->user_id !== $context['user']->id) {
            abort(403);
        }

        $deliveryEmail->delete();

        return back()->with('delivery_success', 'Adresse supprimée.');
    }

    public function toggleEmail(Request $request, DeliveryEmail $deliveryEmail): RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in'] || $deliveryEmail->user_id !== $context['user']->id) {
            abort(403);
        }

        $deliveryEmail->is_active = !$deliveryEmail->is_active;
        $deliveryEmail->save();

        $status = $deliveryEmail->is_active ? 'activée' : 'désactivée';

        return back()->with('delivery_success', "Livraison {$status} pour cette adresse.");
    }

    public function useAccountEmail(Request $request): RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in']) {
            return redirect()->route('login');
        }

        $request->merge(['email' => $context['user']->email]);
        $request->merge(['format' => $request->input('format', 'epub')]);

        return $this->addEmail($request);
    }

    public function testSend(Request $request, DeliveryEmail $deliveryEmail): RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in'] || $deliveryEmail->user_id !== $context['user']->id) {
            abort(403);
        }

        // Check if user has reader access
        $entitlements = $this->entitlementService->getUserEntitlements($context['user']);
        if (!$entitlements['reader']) {
            return back()->withErrors(['delivery' => 'Accès Lecteur requis pour tester l\'envoi.']);
        }

        // TODO: Implement actual test send
        return back()->with('delivery_success', 'Test d\'envoi planifié. Vérifiez votre boîte de réception.');
    }
}
