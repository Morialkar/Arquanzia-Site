<x-layouts.app title="Bibliothèque - Arquanzia">
    @if(!$context['is_logged_in'])
        <div class="mb-8">
            <x-cta-reader />
        </div>
    @endif

    <div class="text-center mb-8">
        <h1 class="font-serif text-3xl font-bold text-arq-forest">La Grande Bibliothèque</h1>
        <div class="divider-elven mt-4 max-w-xs mx-auto">❧</div>
    </div>

    @if($books->isEmpty())
        <div class="card-arq p-12 text-center">
            <div class="text-4xl mb-4">📚</div>
            <p class="text-arq-bark font-serif text-lg">Les rayons sont vides pour le moment...</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($books as $book)
                <a href="{{ route('library.book', $book->slug) }}" class="group block card-arq overflow-hidden">
                    @if($book->cover)
                        <div class="aspect-[3/4] bg-arq-parchment-dark overflow-hidden">
                            <img src="{{ route('media.show', ['media' => $book->cover->id, 'unlocked' => 1]) }}" alt="{{ $book->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                    @else
                        <div class="aspect-[3/4] bg-gradient-to-br from-arq-forest/10 to-arq-amber/20 flex items-center justify-center">
                            <span class="text-6xl">📖</span>
                        </div>
                    @endif
                    <div class="p-4">
                        <h2 class="font-serif font-bold text-lg text-arq-forest group-hover:text-arq-forest-light">{{ $book->title }}</h2>
                        @if($book->description_md)
                            <p class="text-arq-bark/70 text-sm mt-2 line-clamp-2">{!! Str::limit(strip_tags($book->description_html), 100) !!}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    @if($context['is_logged_in'] && !in_array($context['viewer_tier'], ['reader', 'vip_reader']))
        <div class="mt-10">
            <x-cta-reader />
        </div>
    @endif
</x-layouts.app>
