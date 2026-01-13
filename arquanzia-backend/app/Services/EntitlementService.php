<?php

namespace App\Services;

use App\Models\EntitlementEvent;
use App\Models\EntitlementState;
use App\Models\User;

class EntitlementService
{
    public function grantEntitlement(User $user, string $type, int $months, string $sourceRef, ?string $adminEmail = null): void
    {
        if (EntitlementEvent::hasBeenProcessed($sourceRef)) {
            return;
        }

        $source = $adminEmail ? 'admin_manual' : 'shopify_order';

        $state = EntitlementState::findOrCreateForUser($user->id, $type);
        $state->extendByMonths($months, $source, $adminEmail);

        EntitlementEvent::create([
            'user_id' => $user->id,
            'type' => $type,
            'source_ref' => $sourceRef,
            'months_added' => $months,
        ]);
    }

    public function setEntitlementEndDate(User $user, string $type, ?\DateTimeInterface $endsAt, string $adminEmail): void
    {
        $state = EntitlementState::findOrCreateForUser($user->id, $type);
        
        $state->update([
            'ends_at' => $endsAt,
            'updated_source' => 'admin_manual',
            'updated_by_admin_email' => $adminEmail,
        ]);
    }

    public function getUserEntitlements(User $user): array
    {
        $vip = EntitlementState::where('user_id', $user->id)->where('type', 'vip')->first();
        $reader = EntitlementState::where('user_id', $user->id)->where('type', 'reader')->first();

        return [
            'vip' => $vip?->isActive() ?? false,
            'vip_ends_at' => $vip?->ends_at,
            'reader' => $reader?->isActive() ?? false,
            'reader_ends_at' => $reader?->ends_at,
        ];
    }
}
