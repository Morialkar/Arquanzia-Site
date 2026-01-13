<x-layouts.app title="{{ $chapter->title }} - {{ $book->title }} - Arquanzia">
    <div class="mb-6 flex items-center gap-2 text-sm">
        <a href="{{ route('library.index') }}" class="text-arq-forest hover:text-arq-forest-light">Bibliothèque</a>
        <span class="text-arq-bark/40">›</span>
        <a href="{{ route('library.book', $book->slug) }}" class="text-arq-forest hover:text-arq-forest-light">{{ $book->title }}</a>
        <span class="text-arq-bark/40">›</span>
        <span class="text-arq-bark/60">{{ $chapter->title }}</span>
    </div>

    @if($isComingSoon)
        <div class="card-arq p-12 text-center">
            <span class="text-5xl">⏳</span>
            <h1 class="font-serif text-2xl font-bold text-arq-forest mt-4">{{ $chapter->title }}</h1>
            <p class="text-arq-bark mt-2">Ce chapitre sera bientôt dévoilé...</p>
            @if($chapter->published_at)
                <p class="text-arq-bark/60 text-sm mt-2">Disponible le {{ $chapter->published_at->format('d/m/Y') }}</p>
            @endif
        </div>
    @elseif(!$hasAccess)
        <div class="card-arq p-12 text-center">
            <span class="text-5xl">🔮</span>
            <h1 class="font-serif text-2xl font-bold text-arq-forest mt-4">{{ $chapter->title }}</h1>
            <p class="text-arq-bark mt-2 font-serif">Ce savoir est réservé aux Lecteurs initiés.</p>
            <a href="{{ route('account.access') }}" class="btn-arq btn-arq-primary mt-6 inline-block">Rejoindre les Lecteurs</a>
        </div>
    @else
        <article class="card-arq">
            <div class="p-6 md:p-12 lg:px-20">
                <header class="mb-10 pt-6 -mt-6 pb-6 border-b border-arq-amber/20 text-center relative sticky top-0 z-50 bg-arq-parchment">
                    <p class="text-arq-bark/60 text-sm mb-2">{{ $book->title }}</p>
                    <h1 class="font-serif text-3xl md:text-4xl font-bold text-arq-forest">{{ $chapter->title }}</h1>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-arq-parchment-dark">
                        <div id="reading-progress" class="h-full bg-arq-forest transition-all duration-100" style="width: 0%"></div>
                    </div>
                </header>

                <div class="prose prose-lg max-w-none prose-headings:font-serif prose-headings:text-arq-forest prose-p:text-arq-ink prose-p:leading-relaxed prose-a:text-arq-forest prose-a:no-underline hover:prose-a:underline">
                    {!! $chapter->content_html !!}
                </div>

                @if($chapter->files->count() > 0)
                    <footer class="mt-12 pt-6 border-t border-arq-amber/20">
                        <h3 class="font-serif font-bold text-arq-forest mb-3">Télécharger ce chapitre</h3>
                        <div class="flex gap-3">
                            @foreach($chapter->files as $file)
                                <a href="{{ route('download.chapter', ['book' => $book->slug, 'chapter' => $chapter->slug, 'format' => $file->format]) }}" class="btn-arq btn-arq-secondary inline-flex items-center">
                                    <span class="mr-2">📥</span> {{ strtoupper($file->format) }}
                                </a>
                            @endforeach
                        </div>
                    </footer>
                @endif

                <nav class="mt-12 pt-6 border-t border-arq-amber/20 flex items-center justify-between">
                    @if($prevChapter)
                        <a href="{{ route('library.chapter', ['book' => $book->slug, 'chapter' => $prevChapter->slug]) }}" class="text-arq-forest hover:text-arq-forest-light font-medium">
                            ← {{ Str::limit($prevChapter->title, 25) }}
                        </a>
                    @else
                        <span></span>
                    @endif

                    @if($nextChapter)
                        <a href="{{ route('library.chapter', ['book' => $book->slug, 'chapter' => $nextChapter->slug]) }}" class="btn-arq btn-arq-primary">
                            {{ Str::limit($nextChapter->title, 25) }} →
                        </a>
                    @else
                        <form action="{{ route('library.chapter.complete', ['book' => $book->slug, 'chapter' => $chapter->slug]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn-arq btn-arq-primary">
                                ✓ Chapitre terminé
                            </button>
                        </form>
                    @endif
                </nav>
            </div>
        </article>

        <script>
            const progressBar = document.getElementById('reading-progress');
            window.addEventListener('scroll', () => {
                const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                const progress = (window.scrollY / docHeight) * 100;
                progressBar.style.width = Math.min(100, Math.max(0, progress)) + '%';
            });
        </script>
    @endif
</x-layouts.app>
