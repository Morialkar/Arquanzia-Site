<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyShopifyProxy
{
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->query('signature');
        
        if (!$signature) {
            return response()->json(['error' => 'Missing signature'], 401);
        }

        $secret = config('services.shopify.api_secret');
        
        if (!$secret) {
            return response()->json(['error' => 'Shopify not configured'], 500);
        }

        $params = $request->query();
        unset($params['signature']);
        ksort($params);

        $queryString = http_build_query($params);
        $calculatedSignature = hash_hmac('sha256', $queryString, $secret);

        if (!hash_equals($calculatedSignature, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
