<x-layouts.admin title="Utilisateur {{ $user->handle }}">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-arq-forest hover:text-arq-forest-light dark:text-arq-mint dark:hover:text-arq-mint/80">
            ← Retour aux utilisateurs
        </a>
        <div class="text-right">
            <p class="text-xs uppercase tracking-[0.2em] text-arq-bark/50 dark:text-arq-mint/60">Fiche utilisateur</p>
            <h1 class="font-serif text-3xl font-semibold text-arq-forest dark:text-arq-mint">{{ $user->handle }}</h1>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment p-6 dark:bg-arq-night-card dark:border-arq-mint/20">
            <h2 class="text-lg font-bold text-arq-ink dark:text-arq-mint mb-4">Informations</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm text-arq-bark/70 dark:text-arq-mint/70">Handle</dt>
                    <dd class="font-medium text-arq-forest dark:text-arq-mint">{{ $user->handle }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-arq-bark/70 dark:text-arq-mint/70">Email</dt>
                    <dd class="font-medium text-arq-forest dark:text-arq-mint">{{ $user->email ?? 'Non renseigné' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-arq-bark/70 dark:text-arq-mint/70">Inscrit le</dt>
                    <dd class="text-arq-bark/80 dark:text-arq-mint/80">{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-arq-bark/70 dark:text-arq-mint/70">Status</dt>
                    <dd>
                        @if($user->accessControl?->is_banned)
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold dark:bg-red-900/40 dark:text-red-200">Banni</span>
                        @elseif($user->accessControl?->is_readonly)
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold dark:bg-yellow-900/30 dark:text-yellow-200">Readonly</span>
                        @else
                            <span class="px-2 py-1 bg-arq-mint/25 text-arq-forest rounded-full text-xs font-semibold dark:bg-arq-mint/20 dark:text-arq-mint">Actif</span>
                        @endif
                    </dd>
                </div>
            </dl>

            <div class="mt-6 pt-6 border-t border-arq-amber/20 dark:border-arq-mint/20 flex flex-wrap gap-4">
                <form action="{{ route('admin.users.readonly', $user) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-yellow-700 hover:text-yellow-600 dark:text-yellow-200 dark:hover:text-yellow-100">
                        {{ $user->accessControl?->is_readonly ? 'Retirer readonly' : 'Mettre en readonly' }}
                    </button>
                </form>
                <form action="{{ route('admin.users.ban', $user) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-red-700 hover:text-red-600 dark:text-red-300 dark:hover:text-red-200">
                        {{ $user->accessControl?->is_banned ? 'Débannir' : 'Bannir' }}
                    </button>
                </form>
                <form action="{{ route('admin.users.ban-handle', $user) }}" method="POST" class="inline" onsubmit="return confirm('Bannir le pseudo &quot;{{ $user->handle }}&quot; ? ({{ $user->handle_ban_count ?? 0 }}/5 bans)')">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-orange-700 hover:text-orange-600 dark:text-orange-200 dark:hover:text-orange-100">
                        Bannir le pseudo ({{ $user->handle_ban_count ?? 0 }}/5)
                    </button>
                </form>
                @if(($user->handle_ban_count ?? 0) > 0)
                    <form action="{{ route('admin.users.reset-handle-bans', $user) }}" method="POST" class="inline" onsubmit="return confirm('Remettre le compteur de bans pseudo à 0 ?')">
                        @csrf
                        <button type="submit" class="text-sm font-semibold text-green-700 hover:text-green-600 dark:text-green-300 dark:hover:text-green-200">
                            Reset bans pseudo
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment p-6 dark:bg-arq-night-card dark:border-arq-mint/20">
            <h2 class="text-lg font-bold text-arq-ink dark:text-arq-mint mb-4">Accès VIP</h2>
            
            <div class="mb-4">
                @if($entitlements['vip'])
                    <span class="inline-flex items-center rounded-full bg-green-100 text-green-800 px-2 py-0.5 text-xs font-semibold dark:bg-green-900/30 dark:text-green-200">
                        ✓ Actif jusqu'au {{ $entitlements['vip_ends_at']->format('d/m/Y') }}
                    </span>
                @else
                    <p class="text-arq-bark/70 dark:text-arq-mint/70">Pas d'accès VIP</p>
                @endif
            </div>

            <form action="{{ route('admin.users.entitlement', $user) }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="type" value="vip">
                <div>
                    <label class="block text-sm text-arq-bark/60 dark:text-arq-mint/70 mb-1">Date de fin</label>
                    <input 
                        type="date" 
                        name="ends_at" 
                        value="{{ $entitlements['vip_ends_at']?->format('Y-m-d') }}"
                        max="2037-12-31"
                        class="w-full px-3 py-2 rounded-lg border transition
                            border-arq-amber/40 bg-white text-arq-ink placeholder:text-arq-bark/50
                            focus:ring-2 focus:ring-purple-500 focus:border-purple-500/60
                            dark:bg-arq-night dark:border-arq-mint/30 dark:text-arq-mint dark:placeholder:text-arq-mint/40 dark:focus:ring-purple-300"
                    >
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition
                    bg-purple-600 text-white hover:bg-purple-500
                    dark:bg-purple-500 dark:text-white dark:hover:bg-purple-400">
                    Mettre à jour VIP
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment p-6 dark:bg-arq-night-card dark:border-arq-mint/20">
            <h2 class="text-lg font-bold text-arq-ink dark:text-arq-mint mb-4">Accès Reader</h2>
            
            <div class="mb-4">
                @if($entitlements['reader'])
                    <span class="inline-flex items-center rounded-full bg-green-100 text-green-800 px-2 py-0.5 text-xs font-semibold dark:bg-green-900/30 dark:text-green-200">
                        ✓ Actif jusqu'au {{ $entitlements['reader_ends_at']->format('d/m/Y') }}
                    </span>
                @else
                    <p class="text-arq-bark/70 dark:text-arq-mint/70">Pas d'accès Reader</p>
                @endif
            </div>

            <form action="{{ route('admin.users.entitlement', $user) }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="type" value="reader">
                <div>
                    <label class="block text-sm text-arq-bark/60 dark:text-arq-mint/70 mb-1">Date de fin</label>
                    <input 
                        type="date" 
                        name="ends_at" 
                        value="{{ $entitlements['reader_ends_at']?->format('Y-m-d') }}"
                        max="2037-12-31"
                        class="w-full px-3 py-2 rounded-lg border transition
                            border-arq-amber/40 bg-white text-arq-ink placeholder:text-arq-bark/50
                            focus:ring-2 focus:ring-arq-amber focus:border-arq-amber/60
                            dark:bg-arq-night dark:border-arq-mint/30 dark:text-arq-mint dark:placeholder:text-arq-mint/40 dark:focus:ring-arq-amber"
                    >
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition
                    bg-arq-copper text-white hover:bg-arq-copper/90
                    dark:bg-arq-copper/80 dark:hover:bg-arq-copper">
                    Mettre à jour Reader
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment p-6 dark:bg-arq-night-card dark:border-arq-mint/20">
            <h2 class="text-lg font-bold text-arq-ink dark:text-arq-mint mb-4">Sync Commande</h2>
            <p class="text-sm text-arq-bark/70 dark:text-arq-mint/70 mb-4">Réappliquer les entitlements d'une commande Shopify spécifique.</p>
            
            <form action="{{ route('admin.users.sync-order', $user) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm text-arq-bark/60 dark:text-arq-mint/70 mb-1">Order ID Shopify</label>
                    <input 
                        type="text" 
                        name="order_id" 
                        placeholder="123456789"
                        class="w-full px-3 py-2 rounded-lg border transition
                            border-arq-amber/40 bg-white text-arq-ink placeholder:text-arq-bark/40
                            focus:ring-2 focus:ring-arq-forest focus:border-arq-forest/60
                            dark:bg-arq-night dark:border-arq-mint/30 dark:text-arq-mint dark:placeholder:text-arq-mint/40 dark:focus:ring-arq-mint"
                    >
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition
                    bg-gray-900 text-white hover:bg-gray-700
                    dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                    Synchroniser
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
