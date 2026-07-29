<x-layouts.app :title="$node->title . ' - Fragments · Arquanzia'">
    {{-- Breadcrumb --}}
    <nav class="text-sm arq-dim mb-6 flex flex-wrap items-center gap-1">
        <a href="{{ route('fragments.index') }}" class="hover:text-arq-forest">Fragments</a>
        @foreach($ancestors as $ancestor)
            <span>/</span>
            <a href="{{ route('fragments.show', $ancestor->getFullPath()) }}" class="hover:text-arq-forest">{{ $ancestor->title }}</a>
        @endforeach
        <span>/</span>
        <span class="text-arq-forest font-medium">{{ $node->title }}</span>
    </nav>

    <div class="mb-8">
        <h1 class="font-serif text-3xl font-bold text-arq-forest">{{ $node->title }}</h1>
        @if($node->description_md)
            <div class="arq-body mt-2 prose prose-sm max-w-none">{!! $node->description_html !!}</div>
        @endif
        <div class="divider-elven mt-4 max-w-xs">❧</div>
    </div>

    @if($children->isEmpty())
        <div class="text-center py-16">
            <p class="font-serif arq-dim text-lg italic">Cette catégorie est vide pour le moment.</p>
        </div>
    @else
        <div x-data="{ lightboxOpen: false, lightboxSrc: '', lightboxType: '' }">
            <div class="columns-2 md:columns-3 gap-4 space-y-4">
                @foreach($children as $child)
                    @if($child->isItem() && $child->item)
                        @php $item = $child->item; @endphp
                        <div class="break-inside-avoid mb-4">
                            @if($item->media_type === 'image' && $item->media)
                                <div class="group relative cursor-pointer rounded-lg overflow-hidden"
                                     @click="lightboxOpen=true; lightboxSrc='{{ route('media.show', $item->media_id) }}'; lightboxType='image'">
                                    <img src="{{ route('media.show', $item->media_id) }}" alt="{{ $child->title }}" class="w-full group-hover:scale-105 transition-transform duration-300">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-200 flex items-center justify-center">
                                        <svg class="w-10 h-10 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                        </svg>
                                    </div>
                                </div>
                            @elseif(($item->media_type === 'video') && $item->embed_url)
                                <div class="group relative cursor-pointer rounded-lg overflow-hidden aspect-video bg-arq-night"
                                     @click="lightboxOpen=true; lightboxSrc='{{ $item->embed_url }}'; lightboxType='video'">
                                    @if($child->thumbnail_media_id)
                                        <img src="{{ route('media.show', $child->thumbnail_media_id) }}" alt="{{ $child->title }}" class="w-full h-full object-cover">
                                    @endif
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/40 transition-colors duration-200">
                                        <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </div>
                                </div>
                            @elseif(in_array($item->media_type, ['pdf', 'coloring']) && $item->media)
                                <div class="card-arq p-4 rounded-lg flex items-center gap-3">
                                    <svg class="w-8 h-8 text-arq-copper shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div>
                                        <p class="font-serif font-bold text-arq-forest text-sm">{{ $child->title }}</p>
                                        <p class="arq-dim text-xs mt-0.5">{{ $item->media_type === 'coloring' ? 'Page à colorier' : 'PDF' }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="card-arq p-4 rounded-lg">
                                    <p class="font-serif font-bold text-arq-forest text-sm">{{ $child->title }}</p>
                                </div>
                            @endif

                            <div class="mt-2 px-1 flex items-center justify-between">
                                <p class="arq-dim text-xs font-medium">{{ $child->title }}</p>
                                @if($item->is_downloadable && $item->media)
                                    <a href="{{ route('media.show', $item->media_id) }}" download class="text-arq-forest hover:text-arq-forest-light">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @elseif($child->isCategory())
                        <div class="break-inside-avoid mb-4">
                            <a href="{{ route('fragments.show', $child->getFullPath()) }}" class="group block card-arq overflow-hidden rounded-lg">
                                @if($child->thumbnail_media_id)
                                    <div class="aspect-video overflow-hidden">
                                        <img src="{{ route('media.show', $child->thumbnail_media_id) }}" alt="{{ $child->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    </div>
                                @endif
                                <div class="p-3">
                                    <p class="font-serif font-bold text-arq-forest group-hover:text-arq-forest-light text-sm">{{ $child->title }}</p>
                                </div>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Lightbox --}}
            <div x-show="lightboxOpen" x-cloak
                 @keydown.escape.window="lightboxOpen=false"
                 class="fixed inset-0 z-50 bg-black/85 flex items-center justify-center p-4"
                 @click.self="lightboxOpen=false">
                <button @click="lightboxOpen=false" class="absolute top-4 right-4 text-white/70 hover:text-white">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <template x-if="lightboxType==='image'">
                    <img :src="lightboxSrc" class="max-h-[90vh] max-w-full rounded-lg shadow-2xl">
                </template>
                <template x-if="lightboxType==='video'">
                    <iframe :src="lightboxSrc" class="w-full max-w-3xl aspect-video rounded-lg shadow-2xl" allowfullscreen></iframe>
                </template>
            </div>
        </div>
    @endif

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</x-layouts.app>
