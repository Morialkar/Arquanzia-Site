<x-layouts.app title="{{ $node->title }} - Encyclopédie - Arquanzia">
    <nav aria-label="Fil d’Ariane" class="mb-6 flex flex-wrap items-center gap-2 text-sm">
        <a href="{{ route('encyclopedia.index') }}" class="text-arq-forest hover:text-arq-forest-light">Encyclopédie</a>
        @foreach($ancestors as $ancestor)
            <span class="arq-faint">›</span>
            <a href="{{ route('encyclopedia.show', $ancestor->getFullPath()) }}" class="text-arq-forest hover:text-arq-forest-light">{{ $ancestor->title }}</a>
        @endforeach
        <span class="arq-faint">›</span>
        <span class="arq-dim dark:text-arq-mint/50">{{ $node->title }}</span>
    </nav>

    <div class="mb-8">
        <h1 class="font-serif text-3xl font-bold text-arq-forest dark:text-arq-mint">{{ $node->title }}</h1>
        @if($node->teaser_md)
            <div class="arq-dim mt-2">{!! $node->teaser_html !!}</div>
        @endif
    </div>

    @if($children->isEmpty())
        <div class="card-arq p-12 text-center">
            <div class="text-4xl mb-4">📁</div>
            <p class="arq-body font-serif dark:text-arq-mint/70">Cette section est vide pour le moment...</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($children as $child)
                <a href="{{ route('encyclopedia.show', $child->getFullPath()) }}" class="group block card-arq overflow-hidden">
                    @if($child->isCategory())
                        {{-- Thumbnail paysage pour les catégories --}}
                        <div class="aspect-video bg-arq-parchment-dark dark:bg-arq-night overflow-hidden relative">
                            @if($child->thumbnail_media_id)
                                <img src="{{ route('media.show', $child->thumbnail_media_id) }}" alt="{{ $child->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <div class="text-center">
                                        <svg class="w-20 h-20 mx-auto text-arq-bark/20 dark:text-arq-mint/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @elseif($child->isArticle())
                        {{-- Image portrait pour les articles --}}
                        <div class="aspect-[3/4] bg-arq-parchment-dark dark:bg-arq-night overflow-hidden relative">
                            @if($child->article?->cover_media_id)
                                <img src="{{ route('media.show', $child->article->cover_media_id) }}" alt="{{ $child->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <div class="text-center">
                                        <svg class="w-16 h-16 mx-auto text-arq-bark/20 dark:text-arq-mint/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                    <div class="p-5">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ $child->isCategory() ? '📁' : '📜' }}</span>
                            <div>
                                <h2 class="font-serif font-bold text-arq-forest dark:text-arq-mint group-hover:text-arq-forest-light">{{ $child->title }}</h2>
                            </div>
                        </div>
                        @if($child->teaser_md)
                            <p class="arq-dim text-sm mt-3 line-clamp-2">{!! Str::limit(strip_tags($child->teaser_html), 80) !!}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.app>
