<x-layouts.admin title="Utilisateur {{ $user->handle }}">
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-arq-forest hover:underline">← Retour aux utilisateurs</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6">
            <h2 class="text-lg font-bold text-arq-ink mb-4">Informations</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm text-arq-bark">Handle</dt>
                    <dd class="font-medium">{{ $user->handle }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-arq-bark">Email</dt>
                    <dd class="font-medium">{{ $user->email ?? 'Non renseigné' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-arq-bark">Inscrit le</dt>
                    <dd>{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-arq-bark">Status</dt>
                    <dd>
                        @if($user->accessControl?->is_banned)
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">Banni</span>
                        @elseif($user->accessControl?->is_readonly)
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">Readonly</span>
                        @else
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Actif</span>
                        @endif
                    </dd>
                </div>
            </dl>

            <div class="mt-6 pt-6 border-t border-gray-200 flex flex-wrap gap-4">
                <form action="{{ route('admin.users.readonly', $user) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-yellow-600 hover:underline">
                        {{ $user->accessControl?->is_readonly ? 'Retirer readonly' : 'Mettre en readonly' }}
                    </button>
                </form>
                <form action="{{ route('admin.users.ban', $user) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 hover:underline">
                        {{ $user->accessControl?->is_banned ? 'Débannir' : 'Bannir' }}
                    </button>
                </form>
                <form action="{{ route('admin.users.ban-handle', $user) }}" method="POST" class="inline" onsubmit="return confirm('Bannir le pseudo &quot;{{ $user->handle }}&quot; ? ({{ $user->handle_ban_count ?? 0 }}/5 bans)')">
                    @csrf
                    <button type="submit" class="text-sm text-orange-600 hover:underline">
                        Bannir le pseudo ({{ $user->handle_ban_count ?? 0 }}/5)
                    </button>
                </form>
                @if(($user->handle_ban_count ?? 0) > 0)
                    <form action="{{ route('admin.users.reset-handle-bans', $user) }}" method="POST" class="inline" onsubmit="return confirm('Remettre le compteur de bans pseudo à 0 ?')">
                        @csrf
                        <button type="submit" class="text-sm text-green-600 hover:underline">
                            Reset bans pseudo
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6">
            <h2 class="text-lg font-bold text-arq-ink mb-4">Accès VIP</h2>
            
            <div class="mb-4">
                @if($entitlements['vip'])
                    <p class="text-green-600">✓ Actif jusqu'au {{ $entitlements['vip_ends_at']->format('d/m/Y') }}</p>
                @else
                    <p class="text-arq-bark">Pas d'accès VIP</p>
                @endif
            </div>

            <form action="{{ route('admin.users.entitlement', $user) }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="type" value="vip">
                <div>
                    <label class="block text-sm text-arq-bark/60 mb-1">Date de fin</label>
                    <input 
                        type="date" 
                        name="ends_at" 
                        value="{{ $entitlements['vip_ends_at']?->format('Y-m-d') }}"
                        max="2037-12-31"
                        class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg"
                    >
                </div>
                <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm">
                    Mettre à jour VIP
                </button>
            </form>
        </div>

        <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6">
            <h2 class="text-lg font-bold text-arq-ink mb-4">Accès Reader</h2>
            
            <div class="mb-4">
                @if($entitlements['reader'])
                    <p class="text-green-600">✓ Actif jusqu'au {{ $entitlements['reader_ends_at']->format('d/m/Y') }}</p>
                @else
                    <p class="text-arq-bark">Pas d'accès Reader</p>
                @endif
            </div>

            <form action="{{ route('admin.users.entitlement', $user) }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="type" value="reader">
                <div>
                    <label class="block text-sm text-arq-bark/60 mb-1">Date de fin</label>
                    <input 
                        type="date" 
                        name="ends_at" 
                        value="{{ $entitlements['reader_ends_at']?->format('Y-m-d') }}"
                        max="2037-12-31"
                        class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg"
                    >
                </div>
                <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 text-sm">
                    Mettre à jour Reader
                </button>
            </form>
        </div>

        <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6">
            <h2 class="text-lg font-bold text-arq-ink mb-4">Sync Commande</h2>
            <p class="text-sm text-arq-bark mb-4">Réappliquer les entitlements d'une commande Shopify spécifique.</p>
            
            <form action="{{ route('admin.users.sync-order', $user) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm text-arq-bark/60 mb-1">Order ID Shopify</label>
                    <input 
                        type="text" 
                        name="order_id" 
                        placeholder="123456789"
                        class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg"
                    >
                </div>
                <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 text-sm">
                    Synchroniser
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
