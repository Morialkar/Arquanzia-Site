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

    @if(!$hasAccess)
        <div class="card-arq p-12 text-center">
            <span class="text-5xl">🔮</span>
            <h1 class="font-serif text-2xl font-bold text-arq-forest mt-4">{{ $node->title }}</h1>
            @if($node->teaser_md)
                <div class="text-arq-bark mt-4 max-w-lg mx-auto">{!! $node->teaser_html !!}</div>
            @endif
            <p class="text-arq-bark/60 mt-4 font-serif">Ce savoir ancien est scellé aux non-initiés.</p>
            <a href="{{ route('account.access') }}" class="btn-arq btn-arq-primary mt-6 inline-block">Rejoindre les {{ config('tiers.labels.reader_plural') }}</a>
        </div>
    @else
        <article class="card-arq overflow-hidden">
            @if($node->article?->cover)
                <div class="aspect-video bg-arq-parchment-dark">
                    <img src="{{ route('media.show', ['media' => $node->article->cover->id, 'unlocked' => 1]) }}" alt="{{ $node->title }}" class="w-full h-full object-cover">
                </div>
            @endif
            <div class="p-6 md:p-10 lg:px-16">
                <header class="mb-8">
                    <div class="flex items-start justify-between w-full">
                        <h1 class="font-serif text-3xl font-bold text-arq-forest">{{ $node->title }}</h1>
                        @if($context['is_logged_in'])
                            <form action="{{ route('favorites.toggle') }}" method="POST">
                                @csrf
                                <input type="hidden" name="type" value="encyclopedia">
                                <input type="hidden" name="target_id" value="{{ $node->id }}">
                                <button type="submit" class="text-2xl hover:scale-110 transition-transform text-arq-copper" title="{{ $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' }}">
                                    {{ $isFavorite ? '❤️' : '🤍' }}
                                </button>
                            </form>
                        @endif
                    </div>
                    @if($node->isReaderOnly())
                        <span class="inline-block mt-3 px-3 py-1 bg-arq-amber/20 text-arq-bark text-xs rounded-organic-sm">Savoir réservé aux {{ config('tiers.labels.reader_plural') }}</span>
                    @endif
                </header>

                <div class="prose prose-lg max-w-none prose-headings:font-serif prose-headings:text-arq-forest prose-p:text-arq-ink prose-p:leading-relaxed">
                    {!! $node->article?->content_html !!}
                </div>

                @if($node->article?->gallery && $node->article->gallery->count() > 0)
                    <div class="mt-12 pt-8 border-t border-arq-amber/20">
                        <h2 class="font-serif text-xl font-bold text-arq-forest mb-4">Illustrations</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach($node->article->gallery as $img)
                                <a href="{{ route('media.show', ['media' => $img->media_id, 'unlocked' => 1]) }}" 
                                   class="lightbox-image block aspect-square bg-arq-parchment-dark rounded-organic-sm overflow-hidden hover:opacity-90 transition-opacity"
                                   data-caption="{{ $img->caption }}">
                                    <img src="{{ route('media.show', ['media' => $img->media_id, 'unlocked' => 1]) }}" 
                                         alt="{{ $img->caption ?? $node->title }}" 
                                         class="w-full h-full object-cover">
                                </a>
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
            <img id="lightbox-img" src="" alt="" class="max-h-[90vh] max-w-[90vw] object-contain">
            <p id="lightbox-caption" class="absolute bottom-4 left-0 right-0 text-center text-white text-sm"></p>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const images = document.querySelectorAll('.lightbox-image');
                const lightbox = document.getElementById('lightbox');
                const lightboxImg = document.getElementById('lightbox-img');
                const lightboxCaption = document.getElementById('lightbox-caption');
                let currentIndex = 0;

                function showImage(index) {
                    currentIndex = index;
                    const img = images[index];
                    lightboxImg.src = img.href;
                    lightboxCaption.textContent = img.dataset.caption || '';
                    lightbox.classList.remove('hidden');
                    lightbox.classList.add('flex');
                }

                function closeLightbox() {
                    lightbox.classList.add('hidden');
                    lightbox.classList.remove('flex');
                }

                images.forEach((img, index) => {
                    img.addEventListener('click', (e) => {
                        e.preventDefault();
                        showImage(index);
                    });
                });

                document.getElementById('lightbox-close').addEventListener('click', closeLightbox);
                lightbox.addEventListener('click', (e) => { if (e.target === lightbox) closeLightbox(); });

                document.getElementById('lightbox-prev').addEventListener('click', () => {
                    showImage((currentIndex - 1 + images.length) % images.length);
                });
                document.getElementById('lightbox-next').addEventListener('click', () => {
                    showImage((currentIndex + 1) % images.length);
                });

                document.addEventListener('keydown', (e) => {
                    if (lightbox.classList.contains('hidden')) return;
                    if (e.key === 'Escape') closeLightbox();
                    if (e.key === 'ArrowLeft') showImage((currentIndex - 1 + images.length) % images.length);
                    if (e.key === 'ArrowRight') showImage((currentIndex + 1) % images.length);
                });
            });
        </script>
    @endif
</x-layouts.app>
