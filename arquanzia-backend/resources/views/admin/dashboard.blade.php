<x-layouts.admin title="Dashboard">
    <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">Dashboard</h1>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('admin.posts.index') }}" class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-5 hover:border-arq-forest/40 transition-colors group">
            <div class="text-3xl font-bold text-arq-forest group-hover:text-arq-forest-light">{{ $postsCount }}</div>
            <div class="text-arq-bark text-sm">Posts</div>
        </a>
        <a href="{{ route('admin.users.index') }}" class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-5 hover:border-arq-forest/40 transition-colors group">
            <div class="text-3xl font-bold text-arq-forest group-hover:text-arq-forest-light">{{ $usersCount }}</div>
            <div class="text-arq-bark text-sm">Utilisateurs</div>
        </a>
        <a href="{{ route('admin.books.index') }}" class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-5 hover:border-arq-forest/40 transition-colors group">
            <div class="text-3xl font-bold text-arq-forest group-hover:text-arq-forest-light">{{ $booksCount }}</div>
            <div class="text-arq-bark text-sm">Livres</div>
            <div class="text-arq-bark/50 text-xs">{{ $chaptersCount }} chapitres</div>
        </a>
        <a href="{{ route('admin.encyclopedia.index') }}" class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-5 hover:border-arq-forest/40 transition-colors group">
            <div class="text-3xl font-bold text-arq-forest group-hover:text-arq-forest-light">{{ $encyclopediaCount }}</div>
            <div class="text-arq-bark text-sm">Articles</div>
            <div class="text-arq-bark/50 text-xs">{{ $categoriesCount }} catégories</div>
        </a>
    </div>

    <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-serif text-lg font-bold text-arq-forest">Posts récents</h2>
            <a href="{{ route('admin.posts.create') }}" class="bg-arq-forest text-arq-parchment px-4 py-2 rounded-lg hover:bg-arq-forest-light text-sm">
                + Nouveau post
            </a>
        </div>

        @if($recentPosts->count() > 0)
            <div class="space-y-3">
                @foreach($recentPosts as $post)
                    <div class="flex items-center justify-between py-3 border-b border-arq-amber/20 last:border-0">
                        <div>
                            <a href="{{ route('admin.posts.edit', $post) }}" class="font-medium text-arq-ink hover:text-arq-forest">
                                {{ $post->title }}
                            </a>
                            <div class="text-sm text-arq-bark/60">
                                <span class="px-2 py-0.5 rounded text-xs 
                                    {{ $post->audience === 'public' ? 'bg-arq-mint/30 text-arq-forest' : '' }}
                                    {{ $post->audience === 'connected' ? 'bg-arq-forest/10 text-arq-forest' : '' }}
                                    {{ $post->audience === 'vip' ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $post->audience === 'reader' ? 'bg-arq-amber/30 text-arq-bark' : '' }}
                                ">{{ $post->audience }}</span>
                                · {{ $post->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <a href="{{ route('admin.posts.edit', $post) }}" class="text-arq-forest hover:text-arq-forest-light text-sm">Modifier</a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-arq-bark/60 text-center py-4">Aucun post pour le moment</p>
        @endif
    </div>
</x-layouts.admin>
