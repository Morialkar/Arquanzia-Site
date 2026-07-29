<x-layouts.app :title="$ogTitle" :description="$ogDescription" :ogImage="$ogImage">
    <a href="{{ route('feed') }}" class="text-arq-forest hover:text-arq-forest-light mb-4 inline-block">← Retour aux chroniques</a>

    <article class="card-arq overflow-hidden">
        @if($post->media->count() > 0)
            <div class="grid gap-2 p-4 {{ $post->media->count() > 1 ? 'grid-cols-2' : '' }}">
                @foreach($post->media as $media)
                    <div class="relative aspect-video bg-arq-parchment-dark rounded-organic-sm overflow-hidden">
                        <img src="{{ route('media.show', $media->id) }}" alt="" class="w-full h-full object-cover">
                    </div>
                @endforeach
            </div>
        @endif

        <div class="p-6">
            <div class="flex items-center space-x-2 mb-3">
                <span class="arq-faint text-sm">{{ $post->created_at->format('d M Y à H:i') }}</span>
            </div>

            <h1 class="font-serif text-2xl font-bold text-arq-forest mb-3">{{ $post->title }}</h1>
            <p class="arq-body mb-4 text-lg">{{ $post->preview_text }}</p>

            @if($post->content_full)
                <div class="text-arq-ink prose max-w-none leading-relaxed">
                    {!! $post->content_full_html !!}
                </div>
            @endif
        </div>
    </article>
</x-layouts.app>
