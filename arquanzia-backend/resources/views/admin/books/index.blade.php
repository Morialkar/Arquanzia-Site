<x-layouts.admin title="Livres">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-serif text-2xl font-bold text-arq-forest">Livres</h1>
        <a href="{{ route('admin.books.create') }}" class="bg-arq-forest text-arq-parchment px-4 py-2 rounded-lg hover:bg-arq-forest-light">
            + Nouveau livre
        </a>
    </div>

    <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 overflow-hidden">
        <table class="min-w-full divide-y divide-arq-amber/20">
            <thead class="bg-arq-parchment-dark">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-arq-bark uppercase">Couverture</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-arq-bark uppercase">Titre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-arq-bark uppercase">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-arq-bark uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-arq-bark uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-arq-amber/20">
                @forelse($books as $book)
                    <tr class="hover:bg-arq-parchment-dark">
                        <td class="px-6 py-4">
                            @if($book->cover)
                                <img src="{{ route('media.show', ['media' => $book->cover->id, 'unlocked' => 1]) }}" class="w-12 h-16 object-cover rounded">
                            @else
                                <div class="w-12 h-16 bg-arq-parchment-dark rounded flex items-center justify-center text-arq-bark/40">📚</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-medium text-arq-ink">{{ $book->title }}</td>
                        <td class="px-6 py-4 text-sm text-arq-bark/60 font-mono">{{ $book->slug }}</td>
                        <td class="px-6 py-4">
                            @if($book->is_published)
                                <span class="px-2 py-1 bg-arq-mint/30 text-arq-forest rounded-full text-xs">Publié</span>
                            @else
                                <span class="px-2 py-1 bg-arq-parchment-dark text-arq-bark/60 rounded-full text-xs">Brouillon</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="{{ route('admin.books.edit', $book) }}" class="text-arq-forest hover:text-arq-forest-light">Éditer</a>
                            <form action="{{ route('admin.books.destroy', $book) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce livre ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-arq-bark/60">Aucun livre</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $books->links() }}</div>
</x-layouts.admin>
