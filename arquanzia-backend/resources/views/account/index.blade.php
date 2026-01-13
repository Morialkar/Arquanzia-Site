<x-layouts.app title="Mon compte - Arquanzia">
    <div class="max-w-2xl mx-auto">
        <h1 class="font-serif text-3xl font-bold text-arq-forest mb-6">Mon Grimoire Personnel</h1>

        <div class="bg-arq-parchment rounded-organic shadow-parchment border border-arq-amber/20 p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-arq-forest to-arq-forest-light rounded-full flex items-center justify-center text-arq-parchment text-2xl font-serif font-bold">
                    {{ strtoupper(substr($user->handle, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <form action="{{ route('account.update') }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        @method('PUT')
                        <input type="text" name="handle" value="{{ old('handle', $user->handle) }}" 
                            class="font-serif text-xl font-bold text-arq-forest bg-arq-parchment border border-arq-amber/40 rounded-organic-sm px-3 py-1 w-48 focus:ring-2 focus:ring-arq-forest/30 focus:border-arq-forest"
                            required maxlength="30">
                        <button type="submit" class="text-sm text-arq-forest hover:text-arq-forest-light font-medium">Enregistrer</button>
                    </form>
                    @error('handle')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    @if($user->email)
                        <p class="text-arq-bark/60 text-sm mt-1">{{ $user->email }}</p>
                    @endif
                </div>
            </div>

            <div class="border-t border-arq-amber/20 pt-6 space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-arq-bark/70">Inscrit depuis</span>
                    <span class="font-medium text-arq-ink">{{ $user->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-arq-bark/70">Rang</span>
                    <span class="px-3 py-1 rounded-organic-sm text-xs font-medium
                        {{ $context['viewer_tier'] === 'vip_reader' ? 'bg-gradient-to-r from-purple-600 to-arq-copper text-white' : '' }}
                        {{ $context['viewer_tier'] === 'vip' ? 'bg-purple-100 text-purple-700' : '' }}
                        {{ $context['viewer_tier'] === 'reader' ? 'bg-arq-amber/30 text-arq-bark' : '' }}
                        {{ $context['viewer_tier'] === 'connected' ? 'bg-arq-forest/10 text-arq-forest' : '' }}
                    ">{{ $context['viewer_tier'] === 'reader' ? 'Lecteur' : ($context['viewer_tier'] === 'vip' ? 'VIP' : ($context['viewer_tier'] === 'vip_reader' ? 'VIP Lecteur' : 'Membre')) }}</span>
                </div>
            </div>

            <div class="border-t border-arq-amber/20 pt-6 mt-6">
                <a href="{{ route('account.access') }}" class="btn-arq btn-arq-primary block text-center">
                    Gérer mes accès
                </a>
            </div>
        </div>

        <div class="bg-arq-parchment rounded-organic shadow-parchment border border-arq-amber/20 p-6 mt-6">
            <h2 class="font-serif text-lg font-bold text-arq-forest mb-4">🔔 Préférences de notifications</h2>
            @if(session('notif_success'))
                <div class="mb-4 bg-arq-mint/30 border border-arq-mint rounded-organic-sm p-3 text-arq-forest text-sm">
                    {{ session('notif_success') }}
                </div>
            @endif
            <form action="{{ route('account.notifications') }}" method="POST" class="space-y-3">
                @csrf
                @php
                    $prefs = $user->notification_prefs ?? \App\Models\User::DEFAULT_NOTIFICATION_PREFS;
                @endphp
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="new_chapters" value="1" {{ ($prefs['new_chapters'] ?? true) ? 'checked' : '' }}
                        class="rounded border-arq-amber/40 text-arq-forest focus:ring-arq-forest">
                    <span class="text-arq-ink">Nouveaux chapitres publiés</span>
                </label>
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="announcements" value="1" {{ ($prefs['announcements'] ?? true) ? 'checked' : '' }}
                        class="rounded border-arq-amber/40 text-arq-forest focus:ring-arq-forest">
                    <span class="text-arq-ink">Annonces importantes</span>
                </label>
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="encyclopedia" value="1" {{ ($prefs['encyclopedia'] ?? true) ? 'checked' : '' }}
                        class="rounded border-arq-amber/40 text-arq-forest focus:ring-arq-forest">
                    <span class="text-arq-ink">Nouveaux articles encyclopédie</span>
                </label>
                <button type="submit" class="mt-4 text-sm text-arq-forest hover:text-arq-forest-light font-medium">
                    Enregistrer les préférences
                </button>
            </form>
        </div>

        <div class="bg-arq-parchment rounded-organic shadow-parchment border border-arq-amber/20 p-6 mt-6">
            <h2 class="font-serif text-lg font-bold text-arq-forest mb-4">🌙 Apparence</h2>
            @if(session('theme_success'))
                <div class="mb-4 bg-arq-mint/30 border border-arq-mint rounded-organic-sm p-3 text-arq-forest text-sm">
                    {{ session('theme_success') }}
                </div>
            @endif
            <form action="{{ route('account.theme') }}" method="POST">
                @csrf
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-3 rounded-organic-sm cursor-pointer hover:bg-arq-parchment-dark transition-colors {{ ($user->theme_pref ?? 'system') === 'system' ? 'bg-arq-parchment-dark' : '' }}">
                        <input type="radio" name="theme_pref" value="system" {{ ($user->theme_pref ?? 'system') === 'system' ? 'checked' : '' }}
                            class="text-arq-forest focus:ring-arq-forest">
                        <div>
                            <span class="text-arq-ink font-medium">Automatique</span>
                            <p class="text-arq-bark/60 text-xs">Suit les préférences de ton appareil</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-organic-sm cursor-pointer hover:bg-arq-parchment-dark transition-colors {{ ($user->theme_pref ?? 'system') === 'light' ? 'bg-arq-parchment-dark' : '' }}">
                        <input type="radio" name="theme_pref" value="light" {{ ($user->theme_pref ?? 'system') === 'light' ? 'checked' : '' }}
                            class="text-arq-forest focus:ring-arq-forest">
                        <div>
                            <span class="text-arq-ink font-medium">☀️ Mode jour</span>
                            <p class="text-arq-bark/60 text-xs">Parchemin lumineux</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-organic-sm cursor-pointer hover:bg-arq-parchment-dark transition-colors {{ ($user->theme_pref ?? 'system') === 'dark' ? 'bg-arq-parchment-dark' : '' }}">
                        <input type="radio" name="theme_pref" value="dark" {{ ($user->theme_pref ?? 'system') === 'dark' ? 'checked' : '' }}
                            class="text-arq-forest focus:ring-arq-forest">
                        <div>
                            <span class="text-arq-ink font-medium">🌙 Mode nuit</span>
                            <p class="text-arq-bark/60 text-xs">Lecture nocturne</p>
                        </div>
                    </label>
                </div>
                <button type="submit" class="mt-4 text-sm text-arq-forest hover:text-arq-forest-light font-medium">
                    Enregistrer
                </button>
            </form>
        </div>

        <div class="bg-arq-parchment rounded-organic shadow-parchment border border-arq-amber/20 p-6 mt-6">
            <h2 class="font-serif text-lg font-bold text-arq-forest mb-4">🔑 Tokens API (avancé)</h2>
            <p class="text-arq-bark/60 text-sm mb-4">Génère un token pour accéder à l'API depuis une app externe.</p>
            
            @if(session('new_token'))
                <div class="mb-4 bg-arq-mint/30 border border-arq-mint rounded-organic-sm p-4">
                    <p class="text-arq-forest text-sm font-medium mb-2">Token créé! Copie-le maintenant:</p>
                    <code class="block bg-arq-parchment-dark p-2 rounded-organic-sm text-xs break-all text-arq-ink">{{ session('new_token') }}</code>
                </div>
            @endif

            @if($tokens->count() > 0)
                <div class="mb-4 space-y-2">
                    @foreach($tokens as $token)
                        <div class="flex items-center justify-between p-3 bg-arq-parchment-dark rounded-organic-sm">
                            <div>
                                <span class="text-arq-ink font-medium">{{ $token->name }}</span>
                                <span class="text-arq-bark/50 text-xs ml-2">
                                    @if($token->last_used_at)
                                        Dernière utilisation: {{ $token->last_used_at->diffForHumans() }}
                                    @else
                                        Jamais utilisé
                                    @endif
                                </span>
                            </div>
                            <form action="{{ route('account.token.revoke', $token) }}" method="POST" onsubmit="return confirm('Révoquer ce token?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:underline">Révoquer</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($tokens->count() < 3)
                <form action="{{ route('account.token.create') }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="name" placeholder="Nom du token" required maxlength="50"
                        class="flex-1 px-3 py-2 bg-arq-parchment border border-arq-amber/40 rounded-organic-sm text-sm focus:ring-2 focus:ring-arq-forest/30 focus:border-arq-forest">
                    <button type="submit" class="btn-arq btn-arq-secondary text-sm">
                        Générer
                    </button>
                </form>
            @else
                <p class="text-arq-bark/60 text-sm">Maximum 3 tokens atteint.</p>
            @endif
        </div>

        @if($context['is_banned'])
            <div class="mt-6 bg-red-50 border border-red-200 rounded-organic p-4 text-center">
                <p class="text-red-700">Votre compte est actuellement restreint.</p>
            </div>
        @elseif($context['is_readonly'])
            <div class="mt-6 bg-arq-amber/20 border border-arq-amber/40 rounded-organic p-4 text-center">
                <p class="text-arq-bark">Votre compte est en mode lecture seule.</p>
            </div>
        @endif

        <div class="mt-8 text-center">
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-arq-bark/60 hover:text-arq-bark text-sm">Se déconnecter</button>
            </form>
        </div>
    </div>
</x-layouts.app>
