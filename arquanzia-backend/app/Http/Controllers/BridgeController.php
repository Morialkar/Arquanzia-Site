<?php

namespace App\Http\Controllers;

use App\Models\BridgeToken;
use App\Models\User;
use App\Models\UserIdentity;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class BridgeController extends Controller
{
    public function consume(Request $request): RedirectResponse
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('feed')->with('error', 'Token manquant');
        }

        $bridgeToken = BridgeToken::consume($token);

        if (!$bridgeToken) {
            return redirect()->route('feed')->with('error', 'Token invalide ou expiré');
        }

        $user = $this->findOrCreateUserByShopifyCustomer($bridgeToken->shopify_customer_id);

        session(['user_id' => $user->id]);

        return redirect()->route('feed');
    }

    protected function findOrCreateUserByShopifyCustomer(string $shopifyCustomerId): User
    {
        $identity = UserIdentity::where('provider', 'shopify_customer')
            ->where('provider_user_id', $shopifyCustomerId)
            ->first();

        if ($identity) {
            return $identity->user;
        }

        $user = User::create([
            'handle' => 'shopify_' . substr($shopifyCustomerId, 0, 8),
        ]);

        UserIdentity::create([
            'user_id' => $user->id,
            'provider' => 'shopify_customer',
            'provider_user_id' => $shopifyCustomerId,
        ]);

        return $user;
    }
}
