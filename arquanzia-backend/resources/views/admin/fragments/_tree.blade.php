<ul class="space-y-2" style="margin-left: {{ $level * 1.5 }}rem">
    @foreach($nodes as $node)
        <li class="p-3 rounded-xl bg-white/90 dark:bg-gray-800 border border-arq-amber/10 dark:border-gray-700 shadow-sm hover:bg-white dark:hover:bg-gray-750">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-lg">{{ $node->isCategory() ? '📁' : '🖼️' }}</span>
                    <span class="font-medium text-arq-forest dark:text-gray-100">{{ $node->title }}</span>
                    @if(!$node->is_published)
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 text-xs rounded-full">Brouillon</span>
                    @endif
                    <span class="text-gray-400 dark:text-gray-500 text-xs font-mono">/{{ $node->slug }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    @if($node->isCategory())
                        <a href="{{ route('admin.fragments.create', ['parent' => $node->id]) }}" class="text-green-700 dark:text-green-300 hover:underline">+ Enfant</a>
                    @endif
                    <a href="{{ route('admin.fragments.edit', $node) }}" class="text-indigo-600 dark:text-indigo-300 hover:underline">Éditer</a>
                    <form action="{{ route('admin.fragments.destroy', $node) }}" method="POST" class="inline" data-confirmer="Supprimer cet élément ?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-300 hover:underline">Supprimer</button>
                    </form>
                </div>
            </div>
            @if($node->children && $node->children->count() > 0)
                @include('admin.fragments._tree', ['nodes' => $node->children, 'level' => $level + 1])
            @endif
        </li>
    @endforeach
</ul>
