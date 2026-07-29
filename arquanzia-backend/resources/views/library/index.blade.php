<x-layouts.app title="Bibliothèque - Arquanzia">
    <div class="text-center mb-8">
        <h1 class="font-serif text-4xl font-bold text-arq-forest dark:text-arq-mint italic">La Grande Bibliothèque</h1>
        <div class="divider-elven mt-4 max-w-xs mx-auto">❧</div>
        <p class="arq-dim font-serif italic text-lg sm:text-xl mt-3 max-w-sm mx-auto">
            La Bibliothèque grandit. De nouveaux chapitres arrivent bientôt.
        </p>
    </div>

    @if($books->isEmpty())
        <div class="text-center py-16">
            <p class="font-serif arq-dim text-lg italic">Les premiers ouvrages arrivent bientôt...</p>
            <p class="arq-faint text-sm mt-2">L'atelier est en pleine création.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($books as $book)
                <a href="{{ route('library.book', $book->slug) }}" class="group block card-arq overflow-hidden">
                    @if($book->cover)
                        <div class="aspect-[3/4] bg-arq-parchment-dark dark:bg-arq-night overflow-hidden">
                            <img src="{{ route('media.show', $book->cover->id) }}" alt="{{ $book->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                    @else
                        <div class="aspect-[3/4] bg-gradient-to-br from-arq-forest/10 to-arq-amber/20 flex items-center justify-center">
                            <span class="text-6xl">📖</span>
                        </div>
                    @endif
                    <div class="p-4">
                        <h2 class="font-serif font-bold text-lg text-arq-forest dark:text-arq-mint group-hover:text-arq-forest-light dark:group-hover:text-arq-mint/80">{{ $book->title }}</h2>
                        @if($book->description_md)
                            <p class="arq-dim text-sm mt-2 line-clamp-2">{!! Str::limit(strip_tags($book->description_html), 100) !!}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.app>
