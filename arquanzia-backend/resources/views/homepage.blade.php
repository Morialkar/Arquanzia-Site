<x-layouts.app title="Arquanzia">
    {{-- Section A : Dernier chapitre --}}
    @if($latestChapter)
    <section class="mb-12">
        <h2 class="font-serif text-2xl font-bold text-arq-forest mb-1">Dernière parution</h2>
        <div class="divider-elven mb-5 max-w-xs">❧</div>
        <a href="{{ route('library.chapter', [$latestChapter->book->slug, $latestChapter->slug]) }}"
           class="group block card-arq overflow-hidden md:flex">
            @if($latestChapter->book->cover_media_id)
                <div class="md:w-44 shrink-0 aspect-[2/3] md:aspect-auto overflow-hidden bg-arq-parchment-dark">
                    <img src="{{ route('media.show', $latestChapter->book->cover_media_id) }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="">
                </div>
            @endif
            <div class="p-6 flex flex-col justify-center">
                <p class="arq-dim text-xs uppercase tracking-widest mb-2">{{ $latestChapter->book->title }}</p>
                <h3 class="font-serif text-xl font-bold text-arq-forest group-hover:text-arq-forest-light">{{ $latestChapter->title }}</h3>
                <p class="arq-dim text-sm mt-3 inline-flex items-center gap-1">
                    Lire ce chapitre
                    <span aria-hidden="true">→</span>
                </p>
            </div>
        </a>
    </section>
    @endif

    {{-- Section B : Les Archives (encyclopédie) --}}
    @if($encyclopediaNodes->isNotEmpty())
    <section class="mb-12">
        <div class="flex items-baseline justify-between mb-1">
            <h2 class="font-serif text-2xl font-bold text-arq-forest">Encyclopédie</h2>
            <a href="{{ route('encyclopedia.index') }}" class="text-arq-forest/60 text-sm hover:underline">Tout voir →</a>
        </div>
        <div class="divider-elven mb-5 max-w-xs">❧</div>
        @php $accentPalette = ['#9B6FD4', '#40C8E0', '#5FFEB0', '#D4709A', '#B87333']; @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($encyclopediaNodes as $node)
                <a href="{{ route('encyclopedia.show', $node->getFullPath()) }}"
                   class="group block card-arq card-arq-colored overflow-hidden"
                   style="border-left: 4px solid {{ $accentPalette[$loop->index % 5] }}">
                    <div class="aspect-video bg-arq-parchment-dark dark:bg-arq-night overflow-hidden">
                        @if($node->thumbnail_media_id)
                            <img src="{{ route('media.show', $node->thumbnail_media_id) }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="">
                        @elseif($node->article?->cover_media_id)
                            <img src="{{ route('media.show', $node->article->cover_media_id) }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="">
                        @endif
                    </div>
                    <div class="p-4">
                        @php $ancestors = $node->ancestors(); @endphp
                        @if(count($ancestors) > 0)
                            <p class="text-[10px] uppercase tracking-widest text-arq-bark/50 dark:text-arq-mint/50 mb-1 truncate">{{ collect($ancestors)->pluck('title')->implode(' › ') }}</p>
                        @endif
                        <h3 class="font-serif font-bold text-arq-forest group-hover:text-arq-forest-light text-sm">{{ $node->title }}</h3>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Section C : La Bibliothèque --}}
    @if($books->isNotEmpty())
    <section class="mb-12">
        <div class="flex items-baseline justify-between mb-1">
            <h2 class="font-serif text-2xl font-bold text-arq-forest">La Bibliothèque</h2>
            <a href="{{ route('library.index') }}" class="text-arq-forest/60 text-sm hover:underline">Tout voir →</a>
        </div>
        <div class="divider-elven mb-5 max-w-xs">❧</div>
        @php $accentPaletteReversed = array_reverse($accentPalette); @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($books as $book)
                <a href="{{ route('library.book', $book->slug) }}"
                   class="group block card-arq card-arq-colored overflow-hidden"
                   style="border-left: 4px solid {{ $accentPaletteReversed[$loop->index % 5] }}">
                    <div class="aspect-[2/3] bg-arq-parchment-dark dark:bg-arq-night overflow-hidden">
                        @if($book->cover)
                            <img src="{{ route('media.show', $book->cover->id) }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 alt="{{ $book->title }}">
                        @endif
                    </div>
                    <p class="font-serif text-sm font-bold text-arq-forest px-3 py-2 group-hover:text-arq-forest-light line-clamp-2">{{ $book->title }}</p>
                </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Section D : Fragments (conditionnel) --}}
    @if($fragmentItems->isNotEmpty())
    <section class="mb-12">
        <div class="flex items-baseline justify-between mb-1">
            <h2 class="font-serif text-2xl font-bold text-arq-forest">Fragments</h2>
            <a href="{{ route('fragments.index') }}" class="text-arq-forest/60 text-sm hover:underline">Tout voir →</a>
        </div>
        <div class="divider-elven mb-5 max-w-xs">❧</div>
        <div class="columns-2 md:columns-3 gap-4 space-y-4">
            @foreach($fragmentItems as $node)
                @if($node->thumbnail_media_id)
                    <a href="{{ route('fragments.show', $node->getFullPath()) }}" class="block break-inside-avoid mb-4 group">
                        <img src="{{ route('media.show', $node->thumbnail_media_id) }}"
                             class="w-full rounded-lg group-hover:opacity-90 transition-opacity duration-200"
                             alt="{{ $node->title }}">
                    </a>
                @endif
            @endforeach
        </div>
    </section>
    @endif

    {{-- Page vide --}}
    @if($books->isEmpty() && $encyclopediaNodes->isEmpty() && !$latestChapter)
    <div class="text-center py-20">
        <h1 class="font-serif text-4xl md:text-5xl font-bold text-arq-forest italic mb-4">Arquanzia</h1>
        <p class="arq-body font-serif text-lg italic max-w-xl mx-auto leading-relaxed">
            Ici vivent les Arkas, les légendes et les îles qui refusent d'être trouvées.
        </p>
        <div class="divider-elven mt-6 max-w-xs mx-auto">❧</div>
        <p class="arq-faint text-sm mt-6">L'univers se construit. De nouvelles histoires arrivent bientôt.</p>
    </div>
    @endif
</x-layouts.app>
