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
                    <dt class="text-sm text-arq-bark/70 dark:text-arq-mint/70">Pseudo</dt>
                    <dd class="font-medium text-arq-forest dark:text-arq-mint">{{ $user->handle }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-arq-bark/70 dark:text-arq-mint/70">Courriel</dt>
                    <dd class="font-medium text-arq-forest dark:text-arq-mint">{{ $user->email ?? 'Non renseigné' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-arq-bark/70 dark:text-arq-mint/70">Créé le</dt>
                    <dd class="text-arq-bark/80 dark:text-arq-mint/80">{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-arq-bark/70 dark:text-arq-mint/70">Accès au back-office</dt>
                    <dd>
                        @if($adminRole)
                            <span class="px-2 py-1 bg-arq-mint/25 text-arq-forest rounded-full text-xs font-semibold dark:bg-arq-mint/20 dark:text-arq-mint">
                                Autorisé — {{ $adminRole }}
                            </span>
                        @else
                            <span class="px-2 py-1 bg-arq-bark/10 text-arq-bark/80 rounded-full text-xs font-semibold dark:bg-white/10 dark:text-arq-mint/70">
                                Aucun
                            </span>
                        @endif
                    </dd>
                </div>
            </dl>

            <p class="mt-6 pt-6 border-t border-arq-amber/20 dark:border-arq-mint/20 text-sm text-arq-bark/70 dark:text-arq-mint/70">
                Le site n'a pas de compte lecteur : tout le contenu est en lecture publique, et
                seul le statut de publication décide de ce qui est visible. Les comptes listés ici
                servent uniquement à l'accès au back-office, géré depuis
                <a href="{{ route('admin.admins.index') }}" class="font-semibold text-arq-forest underline hover:no-underline dark:text-arq-mint">la liste des administrateurs</a>.
            </p>
        </div>

        <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment p-6 dark:bg-arq-night-card dark:border-arq-mint/20">
            <h2 class="text-lg font-bold text-arq-ink dark:text-arq-mint mb-4">
                Publications <span class="text-arq-bark/60 dark:text-arq-mint/60 font-normal">({{ $posts->count() }})</span>
            </h2>

            @if($posts->isEmpty())
                <p class="text-arq-bark/70 dark:text-arq-mint/70">Aucune publication.</p>
            @else
                <ul class="space-y-3">
                    @foreach($posts as $post)
                        <li class="flex items-baseline justify-between gap-3">
                            <a href="{{ route('admin.posts.edit', $post) }}" class="font-medium text-arq-forest hover:underline dark:text-arq-mint">
                                {{ $post->title ?: 'Sans titre' }}
                            </a>
                            <span class="shrink-0 text-xs text-arq-bark/60 dark:text-arq-mint/60">
                                {{ $post->created_at->format('d/m/Y') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-layouts.admin>
