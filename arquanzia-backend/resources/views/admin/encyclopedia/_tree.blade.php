<ul class="space-y-1" style="margin-left: {{ $level * 1.5 }}rem">
    @foreach($nodes as $node)
        <li class="p-2 rounded hover:bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span>{{ $node->isCategory() ? '📁' : '📄' }}</span>
                    <span class="font-medium">{{ $node->title }}</span>
                    @if($node->isReaderOnly())
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs rounded-full">Lecteur</span>
                    @endif
                    <span class="text-gray-400 text-xs font-mono">/{{ $node->slug }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    @if($node->isCategory())
                        <a href="{{ route('admin.encyclopedia.create', ['parent' => $node->id]) }}" class="text-green-600 hover:underline">+ Enfant</a>
                    @endif
                    <a href="{{ route('admin.encyclopedia.edit', ['encyclopedium' => $node]) }}" class="text-indigo-600 hover:underline">Éditer</a>
                    <form action="{{ route('admin.encyclopedia.destroy', ['encyclopedium' => $node]) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cet élément et ses enfants ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Supprimer</button>
                    </form>
                </div>
            </div>
            @if($node->children && $node->children->count() > 0)
                @include('admin.encyclopedia._tree', ['nodes' => $node->children, 'level' => $level + 1])
            @endif
        </li>
    @endforeach
</ul>
