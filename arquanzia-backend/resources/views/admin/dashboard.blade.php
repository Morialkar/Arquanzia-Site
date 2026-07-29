<x-layouts.admin title="Dashboard">
    <h1 class="font-serif text-2xl font-bold text-arq-forest dark:text-arq-mint mb-6">Dashboard</h1>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('admin.posts.index') }}" class="bg-arq-parchment dark:bg-gray-900 rounded-2xl border border-arq-amber/20 dark:border-gray-700 p-5 hover:border-arq-forest/40 dark:hover:border-arq-mint/50 transition-colors group shadow-sm">
            <div class="text-3xl font-bold text-arq-forest dark:text-arq-mint group-hover:text-arq-forest-light dark:group-hover:text-arq-mint">{{ $postsCount }}</div>
            <div class="text-arq-bark dark:text-gray-400 text-sm">Posts</div>
        </a>
        <a href="{{ route('admin.users.index') }}" class="bg-arq-parchment dark:bg-gray-900 rounded-2xl border border-arq-amber/20 dark:border-gray-700 p-5 hover:border-arq-forest/40 dark:hover:border-arq-mint/50 transition-colors group shadow-sm">
            <div class="text-3xl font-bold text-arq-forest dark:text-arq-mint group-hover:text-arq-forest-light dark:group-hover:text-arq-mint">{{ $usersCount }}</div>
            <div class="text-arq-bark dark:text-gray-400 text-sm">Utilisateurs</div>
        </a>
        <a href="{{ route('admin.books.index') }}" class="bg-arq-parchment dark:bg-gray-900 rounded-2xl border border-arq-amber/20 dark:border-gray-700 p-5 hover:border-arq-forest/40 dark:hover:border-arq-mint/50 transition-colors group shadow-sm">
            <div class="text-3xl font-bold text-arq-forest dark:text-arq-mint group-hover:text-arq-forest-light dark:group-hover:text-arq-mint">{{ $booksCount }}</div>
            <div class="text-arq-bark dark:text-gray-400 text-sm">Livres</div>
            <div class="text-arq-bark/50 dark:text-gray-500 text-xs">{{ $chaptersCount }} chapitres</div>
        </a>
        <a href="{{ route('admin.encyclopedia.index') }}" class="bg-arq-parchment dark:bg-gray-900 rounded-2xl border border-arq-amber/20 dark:border-gray-700 p-5 hover:border-arq-forest/40 dark:hover:border-arq-mint/50 transition-colors group shadow-sm">
            <div class="text-3xl font-bold text-arq-forest dark:text-arq-mint group-hover:text-arq-forest-light dark:group-hover:text-arq-mint">{{ $encyclopediaCount }}</div>
            <div class="text-arq-bark dark:text-gray-400 text-sm">Articles</div>
            <div class="text-arq-bark/50 dark:text-gray-500 text-xs">{{ $categoriesCount }} catégories</div>
        </a>
    </div>

    <div class="bg-arq-parchment dark:bg-gray-900 rounded-2xl shadow-lg border border-arq-amber/20 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-serif text-lg font-bold text-arq-forest dark:text-arq-mint">Posts récents</h2>
            <a href="{{ route('admin.posts.create') }}" class="bg-arq-forest text-arq-parchment px-4 py-2 rounded-lg hover:bg-arq-forest-light text-sm">
                + Nouveau post
            </a>
        </div>

        @if($recentPosts->count() > 0)
            <div class="space-y-3">
                @foreach($recentPosts as $post)
                    <div class="flex items-center justify-between py-3 border-b border-arq-amber/20 dark:border-gray-800 last:border-0">
                        <div>
                            <a href="{{ route('admin.posts.edit', $post) }}" class="font-medium text-arq-ink dark:text-gray-100 hover:text-arq-forest dark:hover:text-arq-mint">
                                {{ $post->title }}
                            </a>
                            <div class="text-sm text-arq-bark/60 dark:text-gray-400">
                                <span class="px-2 py-0.5 rounded text-xs 
                                    {{ $post->audience === 'public' ? 'bg-arq-mint/30 text-arq-forest' : '' }}
                                    {{ $post->audience === 'connected' ? 'bg-arq-forest/10 text-arq-forest' : '' }}
                                    {{ $post->audience === 'vip' ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $post->audience === 'reader' ? 'bg-arq-amber/30 text-arq-bark' : '' }}
                                ">{{ $post->audience }}</span>
                                · {{ $post->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <a href="{{ route('admin.posts.edit', $post) }}" class="text-arq-forest dark:text-arq-mint hover:text-arq-forest-light dark:hover:text-arq-mint/80 text-sm">Modifier</a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-arq-bark/60 dark:text-gray-400 text-center py-4">Aucun post pour le moment</p>
        @endif
    </div>
</x-layouts.admin>
