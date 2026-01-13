<x-layouts.app title="Mes accès - Arquanzia">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('account.index') }}" class="text-indigo-600 hover:underline">← Mon compte</a>
        </div>

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Mes accès</h1>
            <form action="{{ route('account.refresh') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                    🔄 Rafraîchir mon accès
                </button>
            </form>
        </div>

        @if(session('refreshed'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 text-green-700">
                ✓ Vos accès ont été vérifiés et mis à jour.
            </div>
        @endif

        <div class="space-y-4">
            <x-access.entitlement-card type="vip" :active="$entitlements['vip']" :endsAt="$entitlements['vip_ends_at']" />
            <x-access.entitlement-card type="reader" :active="$entitlements['reader']" :endsAt="$entitlements['reader_ends_at']" />
        </div>

        <x-access.delivery-section 
            :deliveryEmails="$deliveryEmails" 
            :deliveryHistory="$deliveryHistory" 
            :hasReaderAccess="$entitlements['reader']"
            :userEmail="$user->email" 
        />

        @if(!$entitlements['vip'] && !$entitlements['reader'])
            <x-access.no-access-message />
        @endif
    </div>
</x-layouts.app>
