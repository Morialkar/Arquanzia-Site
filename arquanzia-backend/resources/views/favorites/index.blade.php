<x-layouts.app title="Mes favoris - Arquanzia">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">❤️ Mes favoris</h1>

        @if($favoriteBooks->count() === 0 && $favoriteArticles->count() === 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                <span class="text-4xl">🤍</span>
                <p class="text-gray-600 mt-4">Vous n'avez pas encore de favoris.</p>
                <p class="text-gray-500 text-sm mt-2">Cliquez sur le ❤️ sur un livre ou article pour l'ajouter ici.</p>
            </div>
        @else
            @if($favoriteBooks->count() > 0)
                <div class="mb-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-3">📚 Livres</h2>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 divide-y divide-gray-200">
                        @foreach($favoriteBooks as $book)
                            <div class="p-4 flex items-center justify-between">
                                <a href="{{ route('library.book', $book->slug) }}" class="flex items-center gap-3 font-medium text-gray-800 hover:text-indigo-600">
                                    @if($book->cover)
                                        <div class="w-12 h-12 rounded overflow-hidden bg-gray-100 flex-shrink-0">
                                            <img src="{{ route('media.show', ['media' => $book->cover->id, 'unlocked' => 0]) }}" class="w-full h-full object-cover scale-150" alt="">
                                        </div>
                                    @else
                                        <span class="w-12 h-12 flex items-center justify-center bg-gray-100 rounded flex-shrink-0">📚</span>
                                    @endif
                                    {{ $book->title }}
                                </a>
                                <form action="{{ route('favorites.toggle') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="book">
                                    <input type="hidden" name="target_id" value="{{ $book->id }}">
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Retirer</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($favoriteArticles->count() > 0)
                <div>
                    <h2 class="text-lg font-bold text-gray-800 mb-3">📜 Encyclopédie</h2>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 divide-y divide-gray-200">
                        @foreach($favoriteArticles as $article)
                            <div class="p-4 flex items-center justify-between">
                                <a href="{{ route('encyclopedia.show', $article->getFullPath()) }}" class="flex items-center gap-3 font-medium text-gray-800 hover:text-indigo-600">
                                    @if($article->article?->cover)
                                        <div class="w-12 h-12 rounded overflow-hidden bg-gray-100 flex-shrink-0">
                                            <img src="{{ route('media.show', ['media' => $article->article->cover->id, 'unlocked' => 0]) }}" class="w-full h-full object-cover scale-150" alt="">
                                        </div>
                                    @else
                                        <span class="w-12 h-12 flex items-center justify-center bg-gray-100 rounded flex-shrink-0">📜</span>
                                    @endif
                                    {{ $article->title }}
                                </a>
                                <form action="{{ route('favorites.toggle') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="encyclopedia">
                                    <input type="hidden" name="target_id" value="{{ $article->id }}">
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Retirer</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
