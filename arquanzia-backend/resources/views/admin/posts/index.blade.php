<x-layouts.admin title="Posts">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-serif text-2xl font-bold text-arq-forest">Posts</h1>
        <a href="{{ route('admin.posts.create') }}" class="bg-arq-forest text-arq-parchment px-4 py-2 rounded-lg hover:bg-arq-forest-light">
            + Nouveau post
        </a>
    </div>

    <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 overflow-hidden">
        <table class="w-full">
            <thead class="bg-arq-parchment-dark border-b border-arq-amber/20">
                <tr>
                    <th class="text-left px-6 py-3 text-sm font-medium text-arq-bark">Titre</th>
                    <th class="text-left px-6 py-3 text-sm font-medium text-arq-bark">Audience</th>
                    <th class="text-left px-6 py-3 text-sm font-medium text-arq-bark">Date</th>
                    <th class="text-right px-6 py-3 text-sm font-medium text-arq-bark">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-arq-amber/20">
                @forelse($posts as $post)
                    <tr class="hover:bg-arq-parchment-dark">
                        <td class="px-6 py-4">
                            <div class="font-medium text-arq-ink">{{ $post->title }}</div>
                            <div class="text-sm text-arq-bark/60">{{ Str::limit($post->preview_text, 60) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs font-medium
                                {{ $post->audience === 'public' ? 'bg-arq-mint/30 text-arq-forest' : '' }}
                                {{ $post->audience === 'connected' ? 'bg-arq-forest/10 text-arq-forest' : '' }}
                                {{ $post->audience === 'vip' ? 'bg-purple-100 text-purple-700' : '' }}
                                {{ $post->audience === 'reader' ? 'bg-arq-amber/30 text-arq-bark' : '' }}
                            ">{{ $post->audience }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-arq-bark/60">
                            {{ $post->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.posts.edit', $post) }}" class="text-arq-forest hover:text-arq-forest-light mr-3">Modifier</a>
                            <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce post ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-arq-bark/60">Aucun post</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $posts->links() }}
    </div>
</x-layouts.admin>
