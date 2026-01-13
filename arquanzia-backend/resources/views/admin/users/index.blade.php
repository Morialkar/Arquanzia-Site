<x-layouts.admin title="Utilisateurs">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-serif text-2xl font-bold text-arq-forest">Utilisateurs</h1>
        <a href="{{ route('admin.users.create') }}" class="bg-arq-forest text-arq-parchment px-4 py-2 rounded-lg hover:bg-arq-forest-light">
            + Créer un compte
        </a>
    </div>

    <form action="" method="GET" class="mb-6">
        <div class="flex gap-2">
            <input 
                type="text" 
                name="search" 
                value="{{ $search ?? '' }}"
                placeholder="Rechercher par handle ou email..."
                class="flex-1 px-4 py-2 border border-arq-amber/40 rounded-lg bg-arq-parchment"
            >
            <button type="submit" class="bg-arq-forest text-arq-parchment px-4 py-2 rounded-lg hover:bg-arq-forest-light">
                Rechercher
            </button>
        </div>
    </form>

    <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 overflow-hidden">
        <table class="min-w-full divide-y divide-arq-amber/20">
            <thead class="bg-arq-parchment-dark">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-arq-bark uppercase">Handle</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-arq-bark uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-arq-bark uppercase">{{ config('tiers.labels.vip') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-arq-bark uppercase">{{ config('tiers.labels.reader') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-arq-bark uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-arq-bark uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-arq-amber/20">
                @forelse($users as $user)
                    <tr class="hover:bg-arq-parchment-dark">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-arq-ink">
                            {{ $user->handle }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-arq-bark/60">
                            {{ $user->email ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($user->entitlements['vip'])
                                <span class="text-purple-600">✓ {{ $user->entitlements['vip_ends_at']?->format('d/m/Y') }}</span>
                            @else
                                <span class="text-arq-bark/40">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($user->entitlements['reader'])
                                <span class="text-arq-copper">✓ {{ $user->entitlements['reader_ends_at']?->format('d/m/Y') }}</span>
                            @else
                                <span class="text-arq-bark/40">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($user->accessControl?->is_banned)
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">Banni</span>
                            @elseif($user->accessControl?->is_readonly)
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">Readonly</span>
                            @else
                                <span class="px-2 py-1 bg-arq-mint/30 text-arq-forest rounded-full text-xs">Actif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-arq-forest hover:text-arq-forest-light">
                                Détails
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-arq-bark/60">
                            Aucun utilisateur trouvé
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->appends(['search' => $search])->links() }}
    </div>
</x-layouts.admin>
