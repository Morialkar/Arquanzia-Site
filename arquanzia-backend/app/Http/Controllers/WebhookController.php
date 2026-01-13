<?php

namespace App\Http\Controllers;

use App\Models\EntitlementEvent;
use App\Models\EntitlementState;
use App\Models\User;
use App\Services\EntitlementService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WebhookController extends Controller
{
    public function ordersPaid(Request $request): JsonResponse
    {
        $order = $request->all();
        
        $email = strtolower(trim($order['email'] ?? ''));
        if (!$email) {
            return response()->json(['status' => 'skipped', 'reason' => 'no_email']);
        }

        $orderId = $order['id'] ?? null;
        $lineItems = $order['line_items'] ?? [];

        $vipVariantIds = config('services.shopify.vip_variant_ids', []);
        $readerVariantIds = config('services.shopify.reader_variant_ids', []);

        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'email' => $email,
                'handle' => 'shopify_' . substr(md5($email), 0, 8),
            ]);
        }

        if ($user->accessControl?->is_banned) {
            return response()->json(['status' => 'skipped', 'reason' => 'user_banned']);
        }

        $service = new EntitlementService();

        foreach ($lineItems as $item) {
            $variantId = (string) ($item['variant_id'] ?? '');
            $lineItemId = $item['id'] ?? '';
            $quantity = (int) ($item['quantity'] ?? 1);
            $sourceRef = "{$orderId}:{$lineItemId}";

            if (EntitlementEvent::hasBeenProcessed($sourceRef)) {
                continue;
            }

            if (in_array($variantId, $vipVariantIds)) {
                $service->grantEntitlement($user, 'vip', $quantity, $sourceRef);
            }

            if (in_array($variantId, $readerVariantIds)) {
                $service->grantEntitlement($user, 'reader', $quantity, $sourceRef);
            }
        }

        return response()->json(['status' => 'processed']);
    }
}
