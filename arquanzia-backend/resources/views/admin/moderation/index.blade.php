<x-layouts.admin title="Modération">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-arq-bark/50 dark:text-arq-mint/60">Communauté</p>
            <h1 class="font-serif text-3xl font-semibold text-arq-forest dark:text-arq-mint">Modération</h1>
        </div>
        <p class="text-sm text-arq-bark/70 dark:text-arq-mint/70">Surveillez les commentaires et actions disciplinaires.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment p-6 dark:bg-arq-night-card dark:border-arq-mint/20">
                <h2 class="text-lg font-bold text-arq-ink dark:text-arq-mint mb-4">Commentaires récents</h2>

                @if($recentComments->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentComments as $comment)
                            <div class="rounded-xl border border-arq-amber/25 bg-white/90 p-4 dark:bg-arq-night dark:border-arq-mint/25">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <span class="font-medium text-arq-ink dark:text-arq-mint">{{ $comment->user->handle ?? 'User #'.substr($comment->user_id, 0, 8) }}</span>
                                            <span class="text-arq-bark/50 dark:text-arq-mint/50 text-xs">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-sm text-arq-bark/80 dark:text-arq-mint/80 leading-relaxed break-words">{{ $comment->body }}</p>
                                        <p class="text-arq-bark/50 dark:text-arq-mint/50 text-xs mt-2">
                                            Sur : <a href="{{ route('post.show', $comment->post_id) }}" class="font-semibold text-arq-forest hover:text-arq-forest-light dark:text-arq-mint dark:hover:text-arq-mint/80">{{ $comment->post->title ?? 'Post supprimé' }}</a>
                                        </p>
                                    </div>
                                    <div class="flex flex-col gap-2 ml-4 min-w-[110px]">
                                        <form action="{{ route('admin.users.ban-handle', $comment->user_id) }}" method="POST" onsubmit="return confirm('Bannir le pseudo de cet utilisateur ?')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition
                                                bg-orange-100 text-orange-800 hover:bg-orange-200
                                                dark:bg-orange-900/30 dark:text-orange-100 dark:hover:bg-orange-900/50" title="Bannir le pseudo">
                                                🚫 Pseudo
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.users.readonly', $comment->user_id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition
                                                bg-amber-100 text-amber-800 hover:bg-amber-200
                                                dark:bg-amber-900/30 dark:text-amber-100 dark:hover:bg-amber-900/50" title="Basculer lecture seule">
                                                {{ $comment->user->accessControl?->is_readonly ? '🔇 Lecture seule' : '🔊 Autoriser' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('Supprimer ce commentaire ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition
                                                bg-red-100 text-red-800 hover:bg-red-200
                                                dark:bg-red-900/30 dark:text-red-100 dark:hover:bg-red-900/50">
                                                🗑️ Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $recentComments->links() }}
                    </div>
                @else
                    <p class="text-arq-bark/70 dark:text-arq-mint/60 text-center py-4">Aucun commentaire</p>
                @endif
            </div>
        </div>

        <div>
            <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment p-6 dark:bg-arq-night-card dark:border-arq-mint/20">
                <h2 class="text-lg font-bold text-arq-ink dark:text-arq-mint mb-4">Utilisateurs sanctionnés</h2>

                @if($flaggedUsers->count() > 0)
                    <div class="space-y-3">
                        @foreach($flaggedUsers as $user)
                            <div class="rounded-xl border border-arq-amber/25 bg-white/90 p-4 dark:bg-arq-night dark:border-arq-mint/25">
                                <div class="font-medium text-arq-ink dark:text-arq-mint">{{ $user->handle ?? 'User #'.substr($user->id, 0, 8) }}</div>
                                <div class="flex items-center space-x-2 mt-1">
                                    @if($user->accessControl->is_readonly)
                                        <span class="text-xs font-semibold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full dark:bg-amber-900/30 dark:text-amber-100">Lecture seule</span>
                                    @endif
                                    @if($user->accessControl->is_banned)
                                        <span class="text-xs font-semibold bg-red-100 text-red-700 px-2 py-0.5 rounded-full dark:bg-red-900/30 dark:text-red-100">Banni</span>
                                    @endif
                                </div>
                                @if($user->accessControl->ban_reason)
                                    <p class="text-arq-bark/70 dark:text-arq-mint/70 text-xs mt-2">{{ $user->accessControl->ban_reason }}</p>
                                @endif
                                <div class="flex flex-wrap gap-2 mt-3">
                                    <form action="{{ route('admin.users.readonly', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition
                                            bg-amber-100 text-amber-800 hover:bg-amber-200
                                            dark:bg-amber-900/30 dark:text-amber-100 dark:hover:bg-amber-900/50">
                                            {{ $user->accessControl->is_readonly ? 'Autoriser' : 'Lecture seule' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.ban', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition
                                            bg-red-100 text-red-800 hover:bg-red-200
                                            dark:bg-red-900/30 dark:text-red-100 dark:hover:bg-red-900/50">
                                            {{ $user->accessControl->is_banned ? 'Débannir' : 'Bannir' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-arq-bark/70 dark:text-arq-mint/60 text-center py-4">Aucun utilisateur sanctionné</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
