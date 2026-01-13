<?php

namespace App\Services;

use App\Models\User;
use App\Models\EntitlementState;
use Illuminate\Http\Request;

class ViewerResolver
{
    public function resolve(Request $request): array
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return [
                'is_logged_in' => false,
                'user' => null,
                'viewer_tier' => 'public',
                'is_banned' => false,
                'is_readonly' => false,
                'can_interact' => false,
            ];
        }

        $user = User::with('accessControl')->find($userId);
        
        if (!$user) {
            session()->forget('user_id');
            return [
                'is_logged_in' => false,
                'user' => null,
                'viewer_tier' => 'public',
                'is_banned' => false,
                'is_readonly' => false,
                'can_interact' => false,
            ];
        }

        $accessControl = $user->accessControl;
        $isBanned = $accessControl?->is_banned ?? false;
        $isReadonly = $accessControl?->is_readonly ?? false;

        if ($isBanned) {
            return [
                'is_logged_in' => true,
                'user' => $user,
                'viewer_tier' => 'public',
                'is_banned' => true,
                'is_readonly' => true,
                'can_interact' => false,
            ];
        }

        $viewerTier = $this->calculateTier($user);

        return [
            'is_logged_in' => true,
            'user' => $user,
            'viewer_tier' => $viewerTier,
            'is_banned' => false,
            'is_readonly' => $isReadonly,
            'can_interact' => !$isReadonly,
        ];
    }

    protected function calculateTier(User $user): string
    {
        $vipState = EntitlementState::where('user_id', $user->id)->where('type', 'vip')->first();
        $readerState = EntitlementState::where('user_id', $user->id)->where('type', 'reader')->first();

        $isVip = $vipState?->isActive() ?? false;
        $isReader = $readerState?->isActive() ?? false;

        if ($isVip && $isReader) {
            return 'vip_reader';
        }
        if ($isVip) {
            return 'vip';
        }
        if ($isReader) {
            return 'reader';
        }

        return 'connected';
    }
}
