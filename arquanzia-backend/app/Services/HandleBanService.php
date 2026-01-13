<?php

namespace App\Services;

use App\Models\AccessControl;
use App\Models\BannedHandle;
use App\Models\User;
use Illuminate\Support\Str;
use Snipe\BanBuilder\CensorWords;

class HandleBanService
{
    const BAN_THRESHOLD = 5;

    public function banHandle(User $user, string $adminEmail): array
    {
        $oldHandle = $user->handle;

        // Ban the current handle
        BannedHandle::banHandle($oldHandle, $user->id, $adminEmail);

        // Generate new random handle
        $newHandle = $this->generateRandomHandle();
        $user->handle = $newHandle;
        $user->handle_ban_count = $user->handle_ban_count + 1;
        $user->save();

        // Check if user should be fully banned
        $fullyBanned = false;
        if ($user->handle_ban_count >= self::BAN_THRESHOLD) {
            $this->fullyBanUser($user);
            $fullyBanned = true;
        }

        return [
            'old_handle' => $oldHandle,
            'new_handle' => $newHandle,
            'ban_count' => $user->handle_ban_count,
            'fully_banned' => $fullyBanned,
        ];
    }

    protected function generateRandomHandle(): string
    {
        do {
            $handle = 'user_' . Str::random(8);
        } while (User::where('handle', $handle)->exists() || BannedHandle::isBanned($handle));

        return $handle;
    }

    protected function fullyBanUser(User $user): void
    {
        AccessControl::updateOrCreate(
            ['user_id' => $user->id],
            ['is_banned' => true]
        );
    }

    public static function isHandleBanned(string $handle): bool
    {
        return BannedHandle::isBanned($handle);
    }

    public static function containsBannedWord(string $handle): bool
    {
        $normalized = strtolower($handle);
        
        // Check with BanBuilder (handles leetspeak like n4z1, h1tl3r, etc.)
        $censor = new CensorWords();
        $censor->setDictionary(['en-us', 'fr']);
        
        // Add custom banned words (impersonation prevention)
        $customBanned = [
            'admin', 'administrator', 'moderator',
            'arquanzia', 'official', 'staff', 'support',
            'system', 'root', 'superuser',
        ];
        $censor->addDictionary($customBanned);
        
        $result = $censor->censorString($normalized, true);
        if (!empty($result['matched'])) {
            return true;
        }
        
        // Also check DB banned handles (from admin panel bans)
        $bannedHandles = BannedHandle::pluck('handle')->toArray();
        
        foreach ($bannedHandles as $banned) {
            if (str_contains($normalized, $banned)) {
                return true;
            }
        }
        
        return false;
    }
}
