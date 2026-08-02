<x-layouts.app title="Arquanzia">
    {{-- Les accents de couleur servent à deux sections ; les définir dans la première rendait
         la seconde tributaire de son affichage — une page sans article d'encyclopédie publié
         plantait sur une variable indéfinie. --}}
    @php $accentPalette = ['#9B6FD4', '#40C8E0', '#5FFEB0', '#D4709A', '#B87333']; @endphp

    {{-- Section A : Dernière chronique --}}
    @if($latestPost)
    <section class="mb-12">
        <h2 class="font-serif text-2xl font-bold text-arq-forest mb-1">Dernière chronique</h2>
        <div class="divider-elven mb-5 max-w-xs">❧</div>
        <a href="{{ route('post.show', $latestPost) }}" class="group block card-arq overflow-hidden">
            @if($latestPost->media->isNotEmpty())
                <div class="aspect-video overflow-hidden bg-arq-parchment-dark">
                    <img src="{{ route('media.show', $latestPost->media->first()->id) }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="">
                </div>
            @endif
            <div class="p-6">
                <p class="arq-dim text-xs uppercase tracking-widest mb-2">{{ $latestPost->created_at->diffForHumans() }}</p>
                <h3 class="font-serif text-xl font-bold text-arq-forest group-hover:text-arq-forest-light">{{ $latestPost->title ?: 'Sans titre' }}</h3>
                @php
                    // Le texte d'aperçu est écrit pour annoncer le billet ; à défaut, on prend le
                    // début du corps, débarrassé de sa syntaxe pour ne pas afficher de balisage brut.
                    $apercu = $latestPost->preview_text
                        ?: Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($latestPost->content_full ?? '')) ?? ''), 220, ' …');
                @endphp
                @if($apercu !== '')
                    <p class="arq-body mt-3 leading-relaxed">{{ $apercu }}</p>
                @endif
                <p class="arq-dim text-sm mt-3 inline-flex items-center gap-1">
                    Lire la chronique
                    <span aria-hidden="true">→</span>
                </p>
            </div>
        </a>
    </section>
    @endif

    {{-- Section B : Dernier chapitre --}}
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

    {{-- Section C : Les Archives (encyclopédie) --}}
    @if($encyclopediaNodes->isNotEmpty())
    <section class="mb-12">
        <div class="flex items-baseline justify-between mb-1">
            <h2 class="font-serif text-2xl font-bold text-arq-forest">Encyclopédie</h2>
            <a href="{{ route('encyclopedia.index') }}" class="text-arq-forest/60 text-sm hover:underline">Tout voir →</a>
        </div>
        <div class="divider-elven mb-5 max-w-xs">❧</div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($encyclopediaNodes as $node)
                <a href="{{ route('encyclopedia.show', $node->getFullPath()) }}"
                   class="group block card-arq card-arq-colored overflow-hidden accent-{{ $loop->index % 5 }}">
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

    {{-- Section D : La Bibliothèque --}}
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
                   class="group block card-arq card-arq-colored overflow-hidden accent-{{ 4 - ($loop->index % 5) }}">
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

    {{-- Section E : Fragments (conditionnel) --}}
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
    @if($books->isEmpty() && $encyclopediaNodes->isEmpty() && !$latestChapter && !$latestPost)
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
