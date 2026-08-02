<x-layouts.app :title="$ogTitle" :description="$ogDescription" :ogImage="$ogImage">
    <div class="mb-6 flex items-center gap-2 text-sm">
        <a href="{{ route('encyclopedia.index') }}" class="text-arq-forest hover:text-arq-forest-light">Encyclopédie</a>
        @foreach($ancestors as $ancestor)
            <span class="arq-faint dark:text-arq-mint/30">›</span>
            <a href="{{ route('encyclopedia.show', $ancestor->getFullPath()) }}" class="text-arq-forest hover:text-arq-forest-light">{{ $ancestor->title }}</a>
        @endforeach
        <span class="arq-faint">›</span>
        <span class="arq-dim dark:text-arq-mint/50">{{ $node->title }}</span>
    </div>

    <article class="card-arq overflow-hidden">
        <div class="p-6 md:p-10 lg:px-16">
            <header class="mb-8">
                <h1 class="font-serif text-3xl font-bold text-arq-forest dark:text-arq-mint">{{ $node->title }}</h1>
                @if(!$node->readingTime()->isEmpty())
                    <p class="arq-dim text-sm mt-2">{{ $node->readingTime()->label() }}</p>
                @endif
            </header>

            <div class="space-y-6 lg:flex lg:gap-8 lg:space-y-0">
                <figure class="relative flex-shrink-0 w-full max-w-sm lg:max-w-xs mx-auto lg:mx-0 lg:float-left lg:mr-8 lg:mb-4 rounded-2xl shadow-arq-heavy border border-arq-amber/30 bg-arq-parchment-dark dark:bg-arq-night overflow-hidden">
                    <div class="aspect-[3/4]">
                        @if($node->article?->cover)
                            <img src="{{ route('media.show', $node->article->cover->id) }}" alt="{{ $node->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <div class="text-center">
                                    <svg class="w-20 h-20 mx-auto text-arq-bark/20 dark:text-arq-mint/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="arq-faint dark:text-arq-mint/40 mt-2 text-sm">Aucune illustration</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <figcaption class="absolute -top-4 -right-4 bg-arq-parchment dark:bg-arq-night px-3 py-1 rounded-full text-xs arq-dim dark:text-arq-mint/70 shadow-arq-subtle">
                        @if($node->isCategory())
                            Catégorie
                        @else
                            Article
                        @endif
                    </figcaption>
                </figure>

                <div class="flex-1">
                    <div class="prose prose-lg max-w-none prose-headings:font-serif prose-headings:text-arq-forest prose-p:text-arq-ink prose-p:leading-relaxed">
                        {!! $node->article?->content_html !!}
                    </div>
                </div>
            </div>
            <div class="clear-both"></div>

            @if($node->article?->gallery && $node->article->gallery->count() > 0)
                <div class="mt-12 pt-8 border-t border-arq-amber/20">
                    <h2 class="font-serif text-xl font-bold text-arq-forest dark:text-arq-mint mb-4">Illustrations</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach($node->article->gallery as $img)
                            <div class="relative aspect-square bg-arq-parchment-dark rounded-organic-sm overflow-hidden group">
                                <a href="{{ route('media.show', $img->media_id) }}"
                                   class="lightbox-image block w-full h-full hover:opacity-90 transition-opacity"
                                   data-caption="{{ $img->caption ?? '' }}"
                                   data-downloadable="{{ $img->downloadable ? '1' : '0' }}"
                                   data-image-id="{{ $img->id }}">
                                    <img src="{{ route('media.show', $img->media_id) }}"
                                         alt="{{ $img->caption ?? $node->title }}"
                                         class="w-full h-full object-cover">
                                </a>
                                @if($img->caption)
                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-2">
                                        <p class="text-white text-xs leading-tight line-clamp-2">{{ $img->caption }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </article>

    <div id="lightbox" class="fixed inset-0 bg-black/90 z-50 hidden items-center justify-center p-4">
        <button id="lightbox-close" class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300">&times;</button>
        <button id="lightbox-prev" class="absolute left-4 top-1/2 -translate-y-1/2 text-white text-4xl hover:text-gray-300 px-2">&lsaquo;</button>
        <button id="lightbox-next" class="absolute right-4 top-1/2 -translate-y-1/2 text-white text-4xl hover:text-gray-300 px-2">&rsaquo;</button>
        <button id="lightbox-download" class="absolute top-4 right-16 text-white text-xl hover:text-gray-300 hidden" title="Télécharger l'image">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
        </button>
        <img id="lightbox-img" src="" alt="" class="max-h-[90vh] max-w-[90vw] object-contain">
        <p id="lightbox-caption" class="absolute bottom-4 left-0 right-0 text-center text-white text-sm px-4"></p>
    </div>

</x-layouts.app>
