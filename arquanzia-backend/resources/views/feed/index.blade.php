<x-layouts.app title="Fil - Arquanzia">
    <style>
        .card-cut {
            position: relative;
            overflow: hidden;
            --notch-size: 3.4rem;
            --notch-offset: 1.9rem;
            --notch-gap: calc(0.35rem - 1px);
            --notch-border: var(--card-border-color, rgba(212, 165, 116, 0.5));
            --notch-fill: var(--card-bg, #F7F4EC);
        }
        .card-notch {
            position: absolute;
            width: var(--notch-size);
            height: var(--notch-size);
            top: calc(var(--notch-offset) * -1);
            right: calc(var(--notch-offset) * -1);
            transform: rotate(45deg);
            background: var(--notch-border);
            border-radius: 0.35rem;
            pointer-events: none;
            z-index: 1;
        }
        .card-notch::after {
            content: '';
            position: absolute;
            inset: var(--notch-gap);
            background: var(--notch-fill);
            border-radius: 0.3rem;
        }
        .dark .card-cut,
        .dark .card-notch {
            --notch-border: var(--card-border-color, rgba(74, 63, 55, 0.6));
            --notch-fill: var(--card-bg, #2A2420);
        }
    </style>
    <div class="text-center mb-8">
        <h1 class="font-serif text-3xl font-bold text-arq-forest">Le Fil</h1>
        <div class="divider-elven mt-4 max-w-xs mx-auto">❧</div>
    </div>

    @if($pinnedPost)
        <article class="card-arq card-arq-flourish card-border-copper card-border-width-2 mb-8 overflow-hidden card-cut">
            <span class="card-notch" aria-hidden="true"></span>
            <div class="absolute top-3 right-3 sm:top-4 sm:right-4 pr-3 sm:pr-4 z-10 flex items-center gap-2">
                <span class="px-3 py-1 bg-arq-forest text-arq-parchment text-xs rounded-organic-sm font-medium shadow-md shadow-black/10">📌 Épinglé</span>
                @if($pinnedPost->is_announcement)
                    <span class="px-3 py-1 bg-arq-copper text-white text-xs rounded-organic-sm font-medium shadow-md shadow-black/10">📢 Annonce</span>
                @endif
            </div>
            @if($pinnedPost->media->count() > 0)
                <div class="relative aspect-video bg-arq-parchment-dark">
                    @php $firstMedia = $pinnedPost->media->first(); @endphp
                    <img src="{{ route('media.show', $firstMedia->id) }}" alt="" class="w-full h-full object-cover">
                </div>
            @endif
            <div class="p-6">
                <a href="{{ route('post.show', $pinnedPost) }}" class="font-serif text-2xl font-bold text-arq-forest mb-2 block hover:text-arq-forest-light">{{ $pinnedPost->title }}</a>
                <p class="arq-body mb-4">{{ $pinnedPost->preview_text }}</p>
                @if($pinnedPost->content_full)
                    <div class="text-arq-ink/80 mb-4 leading-relaxed prose prose-sm max-w-none">
                        {!! $pinnedPost->content_full_html !!}
                    </div>
                @endif
            </div>
        </article>
    @endif

    @forelse($posts as $post)
        <article class="card-arq card-border-amber mb-6 overflow-hidden card-cut">
            <span class="card-notch" aria-hidden="true"></span>
            @if($post->media->count() > 0)
                <div class="relative aspect-video bg-arq-parchment-dark">
                    @php $firstMedia = $post->media->first(); @endphp
                    <img src="{{ route('media.show', $firstMedia->id) }}" alt="" class="w-full h-full object-cover">
                </div>
            @endif

            <div class="p-6">
                <div class="flex items-center gap-3 mb-3">
                    <span class="arq-faint text-sm">{{ $post->created_at->diffForHumans() }}</span>
                </div>

                <a href="{{ route('post.show', $post) }}" class="font-serif text-xl font-bold text-arq-forest mb-2 block hover:text-arq-forest-light">{{ $post->title }}</a>
                <p class="arq-body mb-4">{{ $post->preview_text }}</p>

                @if($post->content_full)
                    <div class="text-arq-ink/80 mb-4 leading-relaxed" x-data="{ expanded: false }">
                        <div x-show="!expanded">
                            {{ Str::limit($post->content_full, 450) }}
                            @if(strlen($post->content_full) > 450)
                                <button @click="expanded = true" class="text-arq-forest hover:underline ml-1 font-medium">Lire la suite</button>
                            @endif
                        </div>
                        <div x-show="expanded" x-cloak>
                            {!! nl2br(e($post->content_full)) !!}
                            <button @click="expanded = false" class="text-arq-forest hover:underline ml-1 block mt-2 font-medium">Réduire</button>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end mt-4 pt-4 border-t border-arq-amber/20">
                    <a href="{{ route('post.show', $post) }}" class="text-arq-forest hover:text-arq-forest-light text-sm font-medium">
                        Voir l'article →
                    </a>
                </div>
            </div>
        </article>
    @empty
        <div class="text-center py-16">
            <p class="font-serif arq-dim text-lg italic">Les chroniques arrivent bientôt...</p>
            <p class="arq-faint text-sm mt-2">De nouvelles histoires sont en préparation dans l'atelier.</p>
        </div>
    @endforelse

    <div class="mt-8">
        {{ $posts->links() }}
    </div>

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</x-layouts.app>
