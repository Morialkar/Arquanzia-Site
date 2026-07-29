<?php

namespace App\Services;

use App\Models\AdminAllowlist;
use App\Models\User;
use Illuminate\Http\Request;

class ViewerResolver
{
    public function resolve(Request $request): array
    {
        $userId = session('user_id');

        if (!$userId) {
            return $this->publicContext();
        }

        $user = User::find($userId);

        if (!$user) {
            session()->forget('user_id');
            return $this->publicContext();
        }

        return [
            'is_logged_in' => true,
            'user' => $user,
            'viewer_tier' => 'public',
            'is_banned' => false,
            'is_readonly' => false,
            'can_interact' => false,
        ];
    }

    protected function publicContext(): array
    {
        return [
            'is_logged_in' => false,
            'user' => null,
            'viewer_tier' => 'public',
            'is_banned' => false,
            'is_readonly' => false,
            'can_interact' => false,
        ];
    }
}
