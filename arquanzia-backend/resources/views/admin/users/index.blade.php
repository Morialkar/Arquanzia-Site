<x-layouts.admin title="Utilisateurs">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <p class="text-sm uppercase tracking-[0.2em] text-arq-bark/60 dark:text-arq-mint/60">Communauté</p>
            <h1 class="font-serif text-3xl font-semibold text-arq-forest dark:text-arq-mint">Utilisateurs</h1>
        </div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition
            bg-arq-forest text-arq-parchment hover:bg-arq-forest-light
            dark:bg-arq-mint/90 dark:text-arq-night hover:dark:bg-arq-mint">
            + Créer un compte
        </a>
    </div>

    <form action="" method="GET" class="mb-6">
        <label class="sr-only" for="admin-user-search">Recherche</label>
        <div class="flex flex-col gap-2 sm:flex-row">
            <input 
                type="text" 
                name="search" 
                id="admin-user-search"
                value="{{ $search ?? '' }}"
                placeholder="Rechercher par handle ou email..."
                class="flex-1 px-4 py-3 text-sm rounded-lg border transition
                    border-arq-amber/40 bg-arq-parchment text-arq-ink placeholder:text-arq-bark/50
                    focus:ring-2 focus:ring-arq-forest focus:border-arq-forest/60
                    dark:bg-arq-night-card dark:border-arq-mint/30 dark:text-arq-mint dark:placeholder:text-arq-mint/50 dark:focus:ring-arq-mint"
            >
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-semibold transition shadow-sm
                bg-arq-forest text-arq-parchment hover:bg-arq-forest-light
                dark:bg-arq-mint/90 dark:text-arq-night hover:dark:bg-arq-mint">
                Rechercher
            </button>
        </div>
    </form>

    <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/80 shadow-parchment p-4 dark:bg-arq-night-card dark:border-arq-mint/20 dark:shadow-none">
        <div class="space-y-4">
            @forelse($users as $user)
                <article class="rounded-xl border border-arq-amber/30 bg-white/95 p-4 md:p-6 shadow-sm transition hover:shadow-md
                    dark:bg-arq-night dark:border-arq-mint/20 dark:shadow-lg/10">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs uppercase tracking-wide text-arq-bark/50 dark:text-arq-mint/60 mb-1">Handle</p>
                            <h2 class="font-serif text-2xl text-arq-forest dark:text-arq-mint break-words">{{ $user->handle }}</h2>
                            <p class="text-xs uppercase tracking-wide text-arq-bark/50 dark:text-arq-mint/60 mt-4 mb-1">Email</p>
                            <p class="text-sm text-arq-bark/80 dark:text-arq-mint/80 break-words">{{ $user->email ?? '-' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 sm:gap-6 text-sm text-arq-bark/80 dark:text-arq-mint/80">
                            <div class="space-y-1">
                                <p class="text-xs uppercase tracking-wide text-arq-bark/50 dark:text-arq-mint/60">{{ config('tiers.labels.vip') }}</p>
                                @if($user->entitlements['vip'])
                                    <span class="inline-flex items-center rounded-full bg-purple-100 text-purple-800 px-2 py-0.5 text-xs font-semibold dark:bg-purple-900/40 dark:text-purple-200">
                                        ✓ {{ $user->entitlements['vip_ends_at']?->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-arq-bark/40 dark:text-arq-mint/30">—</span>
                                @endif
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs uppercase tracking-wide text-arq-bark/50 dark:text-arq-mint/60">{{ config('tiers.labels.reader') }}</p>
                                @if($user->entitlements['reader'])
                                    <span class="inline-flex items-center rounded-full bg-arq-copper/10 text-arq-copper px-2 py-0.5 text-xs font-semibold dark:bg-arq-copper/30 dark:text-arq-parchment">
                                        ✓ {{ $user->entitlements['reader_ends_at']?->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-arq-bark/40 dark:text-arq-mint/30">—</span>
                                @endif
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <p class="text-xs uppercase tracking-wide text-arq-bark/50 dark:text-arq-mint/60 mb-1">Statut</p>
                                @if($user->accessControl?->is_banned)
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold dark:bg-red-900/40 dark:text-red-200">Banni</span>
                                @elseif($user->accessControl?->is_readonly)
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold dark:bg-yellow-900/30 dark:text-yellow-200">Readonly</span>
                                @else
                                    <span class="px-2 py-1 bg-arq-mint/30 text-arq-forest rounded-full text-xs font-semibold dark:bg-arq-mint/20 dark:text-arq-mint">Actif</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end">
                        <a href="{{ route('admin.users.show', $user) }}" class="text-arq-forest hover:text-arq-forest-light font-semibold tracking-wide flex items-center gap-1
                            dark:text-arq-mint dark:hover:text-arq-mint/80">
                            Détails →
                        </a>
                    </div>
                </article>
            @empty
                <div class="px-6 py-8 text-center text-arq-bark/60 dark:text-arq-mint/60">
                    Aucun utilisateur trouvé
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        {{ $users->appends(['search' => $search])->links() }}
    </div>
</x-layouts.admin>
