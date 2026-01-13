<x-layouts.app title="{{ $node->title }} - Encyclopédie - Arquanzia">
    <div class="mb-6 flex items-center gap-2 text-sm">
        <a href="{{ route('encyclopedia.index') }}" class="text-arq-forest hover:text-arq-forest-light">Encyclopédie</a>
        @foreach($ancestors as $ancestor)
            <span class="text-arq-bark/40">›</span>
            <a href="{{ route('encyclopedia.show', $ancestor->getFullPath()) }}" class="text-arq-forest hover:text-arq-forest-light">{{ $ancestor->title }}</a>
        @endforeach
        <span class="text-arq-bark/40">›</span>
        <span class="text-arq-bark/60">{{ $node->title }}</span>
    </div>

    <div class="mb-8">
        <h1 class="font-serif text-3xl font-bold text-arq-forest">{{ $node->title }}</h1>
        @if($node->teaser_md)
            <div class="text-arq-bark/70 mt-2">{!! $node->teaser_html !!}</div>
        @endif
    </div>

    @if($children->isEmpty())
        <div class="card-arq p-12 text-center">
            <div class="text-4xl mb-4">📁</div>
            <p class="text-arq-bark font-serif">Cette section est vide pour le moment...</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($children as $child)
                <a href="{{ route('encyclopedia.show', $child->getFullPath()) }}" class="group block card-arq overflow-hidden">
                    @if($child->isArticle() && $child->article?->cover_media_id)
                        <div class="aspect-video bg-arq-parchment-dark overflow-hidden">
                            <img src="{{ route('media.show', ['media' => $child->article->cover_media_id, 'unlocked' => 1]) }}" alt="{{ $child->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                    @endif
                    <div class="p-5">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ $child->isCategory() ? '📁' : '📜' }}</span>
                            <div>
                                <h2 class="font-serif font-bold text-arq-forest group-hover:text-arq-forest-light">{{ $child->title }}</h2>
                                @if($child->isReaderOnly())
                                    <span class="text-xs text-arq-copper">🔮 Lecteur</span>
                                @endif
                            </div>
                        </div>
                        @if($child->teaser_md)
                            <p class="text-arq-bark/70 text-sm mt-3 line-clamp-2">{!! Str::limit(strip_tags($child->teaser_html), 80) !!}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.app>
