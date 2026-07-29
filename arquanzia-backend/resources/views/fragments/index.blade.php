<x-layouts.app title="Fragments - Arquanzia">
    <div class="text-center mb-8">
        <h1 class="font-serif text-4xl font-bold text-arq-forest dark:text-arq-mint italic">Fragments</h1>
        <div class="divider-elven mt-4 max-w-xs mx-auto">❧</div>
    </div>

    @if($nodes->isEmpty())
        <div class="text-center py-16">
            <p class="font-serif arq-dim text-lg italic">Les fragments arrivent bientôt...</p>
            <p class="arq-faint text-sm mt-2">Images, pages à colorier et vidéos en préparation.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($nodes as $node)
                <a href="{{ route('fragments.show', $node->slug) }}" class="group block card-arq overflow-hidden">
                    <div class="aspect-video bg-arq-parchment-dark dark:bg-arq-night overflow-hidden relative">
                        @if($node->thumbnail_media_id)
                            <img src="{{ route('media.show', $node->thumbnail_media_id) }}" alt="{{ $node->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-20 h-20 text-arq-bark/20 dark:text-arq-mint/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="p-5">
                        <div class="flex items-center gap-3">
                            @if($node->isCategory())
                                <svg class="w-5 h-5 text-arq-amber shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5 arq-dim shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            @endif
                            <h2 class="font-serif font-bold text-arq-forest dark:text-arq-mint group-hover:text-arq-forest-light">{{ $node->title }}</h2>
                        </div>
                        @if($node->description_md)
                            <p class="arq-dim text-sm mt-3 line-clamp-2">{!! Str::limit(strip_tags($node->description_html), 80) !!}</p>
                        @endif
                        @if($node->isCategory() && $node->children->count() > 0)
                            <p class="arq-faint text-xs mt-3">{{ $node->children->count() }} {{ Str::plural('élément', $node->children->count()) }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.app>
