<x-layouts.admin title="Modération">
    <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">Modération</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6">
                <h2 class="text-lg font-bold text-arq-ink mb-4">Commentaires récents</h2>

                @if($recentComments->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentComments as $comment)
                            <div class="border border-arq-amber/20 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <span class="font-medium text-arq-ink">{{ $comment->user->handle ?? 'User #'.substr($comment->user_id, 0, 8) }}</span>
                                            <span class="text-arq-bark/40 text-sm">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-gray-700 text-sm">{{ $comment->body }}</p>
                                        <p class="text-arq-bark/40 text-xs mt-1">
                                            Sur: <a href="{{ route('post.show', $comment->post_id) }}" class="text-arq-forest hover:underline">{{ $comment->post->title ?? 'Post supprimé' }}</a>
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2 ml-4">
                                        <form action="{{ route('admin.users.ban-handle', $comment->user_id) }}" method="POST" onsubmit="return confirm('Bannir le pseudo de cet utilisateur ?')">
                                            @csrf
                                            <button type="submit" class="text-orange-600 hover:underline text-sm" title="Bannir le pseudo">🚫</button>
                                        </form>
                                        <form action="{{ route('admin.users.readonly', $comment->user_id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-amber-600 hover:underline text-sm" title="Toggle lecture seule">
                                                {{ $comment->user->accessControl?->is_readonly ? '🔇' : '🔊' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('Supprimer ce commentaire ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline text-sm">🗑️</button>
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
                    <p class="text-arq-bark text-center py-4">Aucun commentaire</p>
                @endif
            </div>
        </div>

        <div>
            <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6">
                <h2 class="text-lg font-bold text-arq-ink mb-4">Utilisateurs sanctionnés</h2>

                @if($flaggedUsers->count() > 0)
                    <div class="space-y-3">
                        @foreach($flaggedUsers as $user)
                            <div class="border border-arq-amber/20 rounded-lg p-3">
                                <div class="font-medium text-arq-ink">{{ $user->handle ?? 'User #'.substr($user->id, 0, 8) }}</div>
                                <div class="flex items-center space-x-2 mt-1">
                                    @if($user->accessControl->is_readonly)
                                        <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded">Lecture seule</span>
                                    @endif
                                    @if($user->accessControl->is_banned)
                                        <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">Banni</span>
                                    @endif
                                </div>
                                @if($user->accessControl->ban_reason)
                                    <p class="text-arq-bark text-xs mt-1">{{ $user->accessControl->ban_reason }}</p>
                                @endif
                                <div class="flex items-center space-x-2 mt-2">
                                    <form action="{{ route('admin.users.readonly', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-amber-600 hover:underline">
                                            {{ $user->accessControl->is_readonly ? 'Autoriser' : 'Lecture seule' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.ban', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-red-600 hover:underline">
                                            {{ $user->accessControl->is_banned ? 'Débannir' : 'Bannir' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-arq-bark text-center py-4">Aucun utilisateur sanctionné</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
