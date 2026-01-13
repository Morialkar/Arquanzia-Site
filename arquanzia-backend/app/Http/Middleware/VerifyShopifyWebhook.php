<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyShopifyWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        
        if (!$hmacHeader) {
            return response()->json(['error' => 'Missing HMAC header'], 401);
        }

        $secret = config('services.shopify.webhook_secret');
        
        if (!$secret) {
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        $data = $request->getContent();
        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, $secret, true));

        if (!hash_equals($calculatedHmac, $hmacHeader)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
