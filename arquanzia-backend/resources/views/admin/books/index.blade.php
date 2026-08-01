<x-layouts.admin title="Livres">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-arq-bark/60 dark:text-white/40">Bibliothèque</p>
            <h1 class="font-serif text-2xl font-bold text-arq-forest dark:text-white mt-1">Livres</h1>
        </div>
        <a href="{{ route('admin.books.create') }}" class="inline-flex items-center justify-center gap-2 bg-arq-forest text-arq-parchment px-4 py-2 rounded-lg hover:bg-arq-forest-light transition-colors">
            <span class="text-lg leading-none">＋</span>
            + Nouveau livre
        </a>
    </div>

    <div class="hidden md:block bg-arq-parchment dark:bg-[#1c160f] rounded-xl shadow-parchment border border-arq-amber/20 overflow-hidden">
        <table class="min-w-full divide-y divide-arq-amber/20 dark:divide-white/5">
            <thead class="bg-arq-parchment-dark dark:bg-white/5">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-arq-bark uppercase tracking-wide dark:text-white/70">Couverture</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-arq-bark uppercase tracking-wide dark:text-white/70">Titre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-arq-bark uppercase tracking-wide dark:text-white/70">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-arq-bark uppercase tracking-wide dark:text-white/70">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-arq-bark uppercase tracking-wide dark:text-white/70">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-arq-amber/20 dark:divide-white/5">
                @forelse($books as $book)
                    <tr class="hover:bg-arq-parchment-dark dark:hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4">
                            @if($book->cover)
                                <img src="{{ route('media.show', ['media' => $book->cover->id, 'unlocked' => 1]) }}" class="w-12 h-16 object-cover rounded">
                            @else
                                <div class="w-12 h-16 bg-arq-parchment-dark rounded flex items-center justify-center text-arq-bark/40">📚</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-medium text-arq-ink dark:text-white">{{ $book->title }}</td>
                        <td class="px-6 py-4 text-sm text-arq-bark/60 dark:text-white/60 font-mono">{{ $book->slug }}</td>
                        <td class="px-6 py-4">
                            @if($book->is_published)
                                <span class="px-2 py-1 bg-arq-mint/30 text-arq-forest rounded-full text-xs">Publié</span>
                            @else
                                <span class="px-2 py-1 bg-arq-parchment-dark text-arq-bark/60 rounded-full text-xs">Brouillon</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="{{ route('admin.books.edit', $book) }}" class="text-arq-forest hover:text-arq-forest-light">Éditer</a>
                            <form action="{{ route('admin.books.destroy', $book) }}" method="POST" class="inline" data-confirmer="Supprimer ce livre ?">
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

    <div class="md:hidden space-y-4">
        @forelse($books as $book)
            <div class="bg-arq-parchment dark:bg-[#1c160f] border border-arq-amber/20 dark:border-white/10 rounded-2xl p-4 shadow-parchment">
                <div class="flex items-start gap-4">
                    <div class="w-20 h-28 rounded-xl overflow-hidden bg-arq-parchment-dark flex-shrink-0">
                        @if($book->cover)
                            <img src="{{ route('media.show', ['media' => $book->cover->id, 'unlocked' => 1]) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-2xl text-arq-bark/30">📚</div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="text-xs uppercase tracking-[0.2em] text-arq-bark/50 dark:text-white/30 mb-1">Livre</p>
                        <h2 class="font-serif text-xl font-semibold text-arq-forest dark:text-white">{{ $book->title }}</h2>
                        <p class="text-xs font-mono text-arq-bark/60 dark:text-white/50 mt-1">{{ $book->slug }}</p>
                        <div class="mt-3">
                            @if($book->is_published)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-arq-mint/30 text-arq-forest text-xs font-medium">
                                    <span class="text-sm">●</span> Publié
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-arq-parchment-dark text-arq-bark/70 dark:text-white/60 text-xs font-medium">
                                    <span class="text-sm">●</span> Brouillon
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex flex-col sm:flex-row sm:items-center gap-3">
                    <a href="{{ route('admin.books.edit', $book) }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-arq-forest text-arq-parchment text-sm font-medium shadow-sm hover:bg-arq-forest-light transition-colors">
                        Éditer
                    </a>
                    <form action="{{ route('admin.books.destroy', $book) }}" method="POST" data-confirmer="Supprimer ce livre ?" class="inline-flex">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg border border-red-200 text-red-600 text-sm font-medium hover:bg-red-50 dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10 transition-colors">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-arq-parchment dark:bg-[#1c160f] border border-dashed border-arq-amber/30 dark:border-white/20 rounded-2xl p-8 text-center text-arq-bark/60 dark:text-white/50">
                Aucun livre
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $books->links() }}</div>
</x-layouts.admin>
