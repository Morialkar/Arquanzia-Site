<x-layouts.app title="Encyclopédie - Arquanzia">
    <div class="text-center mb-8">
        <h1 class="font-serif text-4xl font-bold text-arq-forest dark:text-arq-mint italic">Les Archives du Monde</h1>
        <div class="divider-elven mt-4 max-w-xs mx-auto">❧</div>
    </div>

    @if($nodes->isEmpty())
        <div class="card-arq p-12 text-center">
            <div class="text-4xl mb-4">📜</div>
            <p class="arq-body font-serif text-lg">Les archives sont en cours de rédaction...</p>
        </div>
    @else
        @php $accentPalette = ['#9B6FD4', '#40C8E0', '#5FFEB0', '#D4709A', '#B87333']; @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($nodes as $node)
                <a href="{{ route('encyclopedia.show', $node->slug) }}"
                   class="group block card-arq card-arq-colored overflow-hidden"
                   style="border-left: 4px solid {{ $accentPalette[$loop->index % 5] }}">
                    @if($node->isCategory())
                        <div class="aspect-video bg-arq-parchment-dark dark:bg-arq-night overflow-hidden relative">
                            @if($node->thumbnail_media_id)
                                <img src="{{ route('media.show', $node->thumbnail_media_id) }}" alt="{{ $node->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-20 h-20 text-arq-bark/20 dark:text-arq-mint/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                    </svg>
                                </div>
                            @endif
                            @if($node->created_at->gt(now()->subDays(30)))
                                <span class="absolute top-2 left-2 bg-arq-forest text-arq-parchment text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full">Nouveau</span>
                            @endif
                        </div>
                    @else
                        <div class="aspect-[3/4] bg-arq-parchment-dark dark:bg-arq-night overflow-hidden relative">
                            @if($node->article?->cover_media_id)
                                <img src="{{ route('media.show', $node->article->cover_media_id) }}" alt="{{ $node->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-16 h-16 text-arq-bark/20 dark:text-arq-mint/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                            @endif
                            @if($node->created_at->gt(now()->subDays(30)))
                                <span class="absolute top-2 left-2 bg-arq-forest text-arq-parchment text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full">Nouveau</span>
                            @endif
                        </div>
                    @endif
                    <div class="p-5">
                        <div class="flex items-center gap-3">
                            @if($node->isCategory())
                                <svg class="w-5 h-5 text-arq-amber shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5 arq-dim shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            @endif
                            <h2 class="font-serif font-bold text-arq-forest dark:text-arq-mint group-hover:text-arq-forest-light">{{ $node->title }}</h2>
                        </div>
                        @if($node->teaser_md)
                            <p class="arq-dim text-sm mt-3 line-clamp-2">{!! Str::limit(strip_tags($node->teaser_html), 80) !!}</p>
                        @endif
                        @if($node->isCategory() && $node->children->count() > 0)
                            <p class="arq-faint text-xs mt-3">{{ $node->children->count() }} {{ Str::plural('article', $node->children->count()) }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.app>
