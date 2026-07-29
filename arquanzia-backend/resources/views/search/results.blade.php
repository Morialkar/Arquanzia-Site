<x-layouts.app title="Recherche - Arquanzia">
    <div class="max-w-3xl mx-auto">
        <form action="{{ route('search') }}" method="GET" class="mb-8">
            <div class="flex gap-2">
                <input type="text" name="q" value="{{ $query }}" placeholder="Rechercher..." 
                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-lg"
                    autofocus>
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Rechercher
                </button>
            </div>
        </form>

        @if($query)
            <p class="text-gray-600 mb-6">
                {{ $totalResults }} résultat{{ $totalResults > 1 ? 's' : '' }} pour "{{ $query }}"
            </p>

            @if($totalResults === 0)
                <div class="bg-gray-50 rounded-xl p-8 text-center">
                    <span class="text-4xl">🔍</span>
                    <p class="text-gray-600 mt-4">Aucun résultat trouvé.</p>
                </div>
            @else
                @if($results['books']->count() > 0)
                    <div class="mb-8">
                        <h2 class="text-lg font-bold text-gray-800 mb-3">📚 Livres</h2>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 divide-y divide-gray-200">
                            @foreach($results['books'] as $book)
                                <a href="{{ route('library.book', $book->slug) }}" class="flex items-center gap-3 p-4 hover:bg-gray-50">
                                    @if($book->cover)
                                        <div class="w-12 h-12 rounded overflow-hidden bg-gray-100 flex-shrink-0">
                                            <img src="{{ route('media.show', ['media' => $book->cover->id, 'unlocked' => 0]) }}" class="w-full h-full object-cover scale-150" alt="">
                                        </div>
                                    @else
                                        <span class="w-12 h-12 flex items-center justify-center bg-gray-100 rounded flex-shrink-0">📚</span>
                                    @endif
                                    <span class="font-medium text-gray-800">{{ $book->title }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($results['chapters']->count() > 0)
                    <div class="mb-8">
                        <h2 class="text-lg font-bold text-gray-800 mb-3">📖 Chapitres</h2>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 divide-y divide-gray-200">
                            @foreach($results['chapters'] as $chapter)
                                <a href="{{ route('library.chapter', ['book' => $chapter->book->slug, 'chapter' => $chapter->slug]) }}" class="flex items-center gap-3 p-4 hover:bg-gray-50">
                                    @if($chapter->book->cover)
                                        <div class="w-12 h-12 rounded overflow-hidden bg-gray-100 flex-shrink-0">
                                            <img src="{{ route('media.show', ['media' => $chapter->book->cover->id, 'unlocked' => 0]) }}" class="w-full h-full object-cover scale-150" alt="">
                                        </div>
                                    @else
                                        <span class="w-12 h-12 flex items-center justify-center bg-gray-100 rounded flex-shrink-0">📖</span>
                                    @endif
                                    <div>
                                        <span class="font-medium text-gray-800">{{ $chapter->title }}</span>
                                        <span class="text-gray-500 text-sm ml-2">dans {{ $chapter->book->title }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($results['encyclopedia']->count() > 0)
                    <div class="mb-8">
                        <h2 class="text-lg font-bold text-gray-800 mb-3">📜 Encyclopédie</h2>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 divide-y divide-gray-200">
                            @foreach($results['encyclopedia'] as $node)
                                <a href="{{ route('encyclopedia.show', $node->getFullPath()) }}" class="flex items-center gap-3 p-4 hover:bg-gray-50">
                                    @if($node->article?->cover)
                                        <div class="w-12 h-12 rounded overflow-hidden bg-gray-100 flex-shrink-0">
                                            <img src="{{ route('media.show', ['media' => $node->article->cover->id, 'unlocked' => 0]) }}" class="w-full h-full object-cover scale-150" alt="">
                                        </div>
                                    @else
                                        <span class="w-12 h-12 flex items-center justify-center bg-gray-100 rounded flex-shrink-0">📜</span>
                                    @endif
                                    <div>
                                        <span class="font-medium text-gray-800">{{ $node->title }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        @else
            <div class="bg-gray-50 rounded-xl p-8 text-center">
                <span class="text-4xl">🔍</span>
                <p class="text-gray-600 mt-4">Entrez un terme de recherche (minimum 2 caractères).</p>
            </div>
        @endif
    </div>
</x-layouts.app>
