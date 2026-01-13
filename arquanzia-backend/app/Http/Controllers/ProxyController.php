<?php

namespace App\Http\Controllers;

use App\Models\BridgeToken;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProxyController extends Controller
{
    public function arquanzia(Request $request): RedirectResponse|View
    {
        $shopifyCustomerId = $request->query('logged_in_customer_id');

        if ($shopifyCustomerId) {
            $result = BridgeToken::createForCustomer($shopifyCustomerId);
            $bridgeUrl = route('bridge') . '?token=' . $result['token'];
            
            return redirect($bridgeUrl);
        }

        return view('proxy.login-prompt');
    }
}
