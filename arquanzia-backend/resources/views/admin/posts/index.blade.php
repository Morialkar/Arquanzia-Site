<x-layouts.admin title="Posts">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
        <h1 class="font-serif text-2xl font-bold text-arq-forest dark:text-arq-mint">Posts</h1>
        <a href="{{ route('admin.posts.create') }}" class="bg-arq-forest text-arq-parchment px-4 py-2 rounded-lg hover:bg-arq-forest-light">
            + Nouveau post
        </a>
    </div>

    <div class="bg-arq-parchment dark:bg-gray-900 rounded-xl shadow-parchment border border-arq-amber/20 dark:border-gray-700 p-4">
        <div class="space-y-4">
            @forelse($posts as $post)
                <article class="bg-white/80 dark:bg-gray-800 rounded-xl border border-arq-amber/20 dark:border-gray-700 p-4 md:p-6 shadow-sm hover:shadow-md transition">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div class="flex-1">
                            <p class="text-xs uppercase tracking-wide text-arq-bark/50 dark:text-gray-400 mb-1">Titre</p>
                            <h2 class="font-serif text-xl text-arq-forest dark:text-gray-100 break-words">{{ $post->title }}</h2>
                            <p class="text-sm text-arq-bark/70 dark:text-gray-300 mt-1">{{ Str::limit($post->preview_text, 120) }}</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-arq-bark/50 dark:text-gray-400 mb-1">Date</p>
                                <p class="text-sm text-arq-bark/70 dark:text-gray-300">{{ $post->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center justify-end gap-3">
                        <a href="{{ route('admin.posts.edit', $post) }}" class="text-arq-forest dark:text-arq-mint hover:text-arq-forest-light dark:hover:text-arq-mint/80 font-medium">Modifier</a>
                        <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="inline" data-confirmer="Supprimer ce post ?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 dark:text-red-300 hover:underline font-medium">Supprimer</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="px-6 py-8 text-center text-arq-bark/60 dark:text-gray-400">Aucun post</div>
            @endforelse
        </div>
    </div>

    <div class="mt-6">
        {{ $posts->links() }}
    </div>
</x-layouts.admin>
