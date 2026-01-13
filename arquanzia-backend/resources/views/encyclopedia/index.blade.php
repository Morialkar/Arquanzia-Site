<x-layouts.app title="Encyclopédie - Arquanzia">
    <div class="text-center mb-8">
        <h1 class="font-serif text-3xl font-bold text-arq-forest">Les Archives du Monde</h1>
        <div class="divider-elven mt-4 max-w-xs mx-auto">❧</div>
    </div>

    @if($nodes->isEmpty())
        <div class="card-arq p-12 text-center">
            <div class="text-4xl mb-4">📜</div>
            <p class="text-arq-bark font-serif text-lg">Les archives sont en cours de rédaction...</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($nodes as $node)
                <a href="{{ route('encyclopedia.show', $node->slug) }}" class="group block card-arq overflow-hidden">
                    @if($node->isArticle() && $node->article?->cover_media_id)
                        <div class="aspect-video bg-arq-parchment-dark overflow-hidden">
                            <img src="{{ route('media.show', ['media' => $node->article->cover_media_id, 'unlocked' => 1]) }}" alt="{{ $node->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                    @endif
                    <div class="p-5">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ $node->isCategory() ? '📁' : '📜' }}</span>
                            <div>
                                <h2 class="font-serif font-bold text-arq-forest group-hover:text-arq-forest-light">{{ $node->title }}</h2>
                                @if($node->isReaderOnly())
                                    <span class="text-xs text-arq-copper">🔮 Lecteur</span>
                                @endif
                            </div>
                        </div>
                        @if($node->teaser_md)
                            <p class="text-arq-bark/70 text-sm mt-3 line-clamp-2">{!! Str::limit(strip_tags($node->teaser_html), 80) !!}</p>
                        @endif
                        @if($node->isCategory() && $node->children->count() > 0)
                            <p class="text-arq-bark/50 text-xs mt-3">{{ $node->children->count() }} entrée(s)</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.app>
