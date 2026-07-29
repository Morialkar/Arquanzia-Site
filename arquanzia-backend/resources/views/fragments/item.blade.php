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

    <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">{{ $node->title }}</h1>

    @if($item)
        @if($item->media_type === 'image' && $item->media)
            <div x-data="{ open: false }" class="mb-6">
                <img src="{{ route('media.show', $item->media_id) }}" alt="{{ $node->title }}"
                     class="w-full rounded-lg cursor-zoom-in shadow-md"
                     @click="open=true">
                <div x-show="open" x-cloak @keydown.escape.window="open=false"
                     class="fixed inset-0 z-50 bg-black/85 flex items-center justify-center p-4"
                     @click.self="open=false">
                    <button @click="open=false" class="absolute top-4 right-4 text-white/70 hover:text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <img src="{{ route('media.show', $item->media_id) }}" class="max-h-[90vh] max-w-full rounded-lg shadow-2xl" alt="{{ $node->title }}">
                </div>
            </div>
            @if($item->is_downloadable)
                <a href="{{ route('media.show', $item->media_id) }}" download class="btn-arq btn-arq-secondary inline-flex items-center gap-2 mb-6">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Télécharger
                </a>
            @endif

        @elseif($item->media_type === 'video')
            <div class="mb-6">
                @if($item->media_id && $item->media)
                    <video controls class="w-full rounded-lg shadow-md" style="max-height: 70vh;">
                        <source src="{{ route('media.show', $item->media_id) }}" type="{{ $item->media->mime }}">
                    </video>
                @elseif($item->embed_url)
                    <div class="aspect-video rounded-lg overflow-hidden shadow-md">
                        <iframe src="{{ $item->embed_url }}" class="w-full h-full" allowfullscreen></iframe>
                    </div>
                @endif
            </div>

        @elseif(in_array($item->media_type, ['pdf', 'coloring']) && $item->media)
            <div class="mb-6">
                <div class="aspect-[3/4] rounded-lg overflow-hidden shadow-md border border-arq-amber/20">
                    <iframe src="{{ route('media.show', $item->media_id) }}" class="w-full h-full"></iframe>
                </div>
                @if($item->is_downloadable)
                    <a href="{{ route('media.show', $item->media_id) }}" download class="btn-arq btn-arq-secondary inline-flex items-center gap-2 mt-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        {{ $item->media_type === 'coloring' ? 'Télécharger la page à colorier' : 'Télécharger le PDF' }}
                    </a>
                @endif
            </div>
        @endif

        @if($node->description_md)
            <div class="prose prose-sm max-w-none arq-body mt-4">{!! $node->description_html !!}</div>
        @endif
    @endif

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</x-layouts.app>
