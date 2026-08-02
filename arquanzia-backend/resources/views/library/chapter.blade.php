<x-layouts.app :title="$ogTitle" :description="$ogDescription" :ogImage="$ogImage">
    <div class="mb-6 flex items-center gap-2 text-sm">
        <a href="{{ route('library.index') }}" class="text-arq-forest hover:text-arq-forest-light">Bibliothèque</a>
        <span class="arq-faint">›</span>
        <a href="{{ route('library.book', $book->slug) }}" class="text-arq-forest hover:text-arq-forest-light">{{ $book->title }}</a>
        <span class="arq-faint">›</span>
        <span class="arq-dim">{{ $chapter->title }}</span>
    </div>

    <article class="card-arq">
        <div class="p-6 md:p-12 lg:px-20">
            <header class="mb-10 pt-6 -mt-6 pb-6 border-b border-arq-amber/20 text-center relative sticky top-0 z-50 bg-arq-parchment">
                <p class="arq-dim text-sm mb-2">{{ $book->title }}</p>
                <h1 class="font-serif text-3xl md:text-4xl font-bold text-arq-forest">{{ $chapter->title }}</h1>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-arq-parchment-dark">
                    <div id="reading-progress" data-reader-controls class="h-full bg-arq-forest transition-all duration-100" ></div>
                </div>
            </header>

            {{-- Hors de l'en-tête collant : la durée renseigne avant la lecture, elle n'a pas
                 à occuper l'écran pendant. --}}
            @if(!$chapter->readingTime()->isEmpty())
                <p class="arq-dim text-sm text-center -mt-6 mb-8">{{ $chapter->readingTime()->label() }}</p>
            @endif

            <section data-reader-controls class="mb-8 bg-arq-parchment-dark/70 dark:bg-arq-night-card border border-arq-amber/30 dark:border-arq-emerald/20 rounded-3xl p-5 md:p-6 shadow-parchment dark:shadow-night-soft">
                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.2em] arq-dim dark:text-gray-300">
                    <span class="mr-1">Confort</span>
                    <div class="flex items-center gap-2 text-base tracking-normal normal-case">
                        <button type="button" id="reader-font-decrease"
                            class="p-2 rounded-full border border-arq-amber/40 dark:border-arq-emerald/40 text-arq-forest dark:text-arq-mint hover:bg-arq-parchment dark:hover:bg-arq-night-lift transition"
                            aria-label="Réduire la taille du texte" title="Réduire la taille du texte">
                            <span aria-hidden="true" class="font-serif text-lg leading-none">A−</span>
                        </button>
                        <span id="reader-font-size-value" class="text-sm font-normal arq-body dark:text-gray-200" aria-live="polite">100%</span>
                        <button type="button" id="reader-font-increase"
                            class="p-2 rounded-full border border-arq-amber/40 dark:border-arq-emerald/40 text-arq-forest dark:text-arq-mint hover:bg-arq-parchment dark:hover:bg-arq-night-lift transition"
                            aria-label="Augmenter la taille du texte" title="Augmenter la taille du texte">
                            <span aria-hidden="true" class="font-serif text-lg leading-none">A+</span>
                        </button>
                    </div>
                    <div class="flex items-center gap-2 ml-auto">
                        <button type="button" id="reader-dyslexic-toggle"
                            class="reader-font-option px-3 py-2 inline-flex items-center justify-center rounded-full"
                            data-state="inactive"
                            aria-pressed="false"
                            aria-label="Basculer vers la police OpenDyslexic" title="Police OpenDyslexic">
                            <span aria-hidden="true" class="reader-font-option__label font-semibold tracking-wider">OD</span>
                        </button>
                    </div>
                </div>
            </section>

            <div id="chapter-content" class="prose prose-lg max-w-none prose-headings:font-serif prose-headings:text-arq-forest prose-p:text-arq-ink prose-p:leading-relaxed prose-a:text-arq-forest prose-a:no-underline hover:prose-a:underline transition-[font-size] duration-150 ease-out">
                {!! \App\Support\ParagraphAnchors::apply(\App\Helpers\MarkdownHelper::render($chapter->content_md)) !!}
            </div>

            <footer class="mt-12 pt-6 border-t border-arq-amber/20">
                <h3 class="font-serif font-bold text-arq-forest dark:text-arq-mint mb-3">Télécharger ce chapitre</h3>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('download.chapter', ['book' => $book->slug, 'chapter' => $chapter->slug, 'format' => 'epub']) }}"
                        class="btn-arq btn-arq-secondary inline-flex items-center gap-2">
                        <span>📗</span> EPUB
                    </a>
                    <a href="{{ route('download.chapter', ['book' => $book->slug, 'chapter' => $chapter->slug, 'format' => 'pdf']) }}"
                        class="btn-arq btn-arq-primary inline-flex items-center gap-2">
                        <span>📄</span> PDF
                    </a>
                </div>
            </footer>

            @if($chapter->promo_banner_enabled && $chapter->promo_banner_text)
                <aside class="mt-10 rounded-2xl border border-arq-amber/30 dark:border-arq-mint/20 bg-arq-parchment-dark/60 dark:bg-arq-night-card p-6 md:p-8 text-center">
                    <p class="arq-body leading-relaxed">{{ $chapter->promo_banner_text }}</p>
                    @if($chapter->promo_banner_button_label && $chapter->promo_banner_button_url)
                        <a href="{{ $chapter->promo_banner_button_url }}" target="_blank" rel="noopener"
                           class="btn-arq btn-arq-primary mt-5 inline-block">{{ $chapter->promo_banner_button_label }}</a>
                    @endif
                </aside>
            @endif

            @if($prevChapter || $nextChapter)
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
                    <span></span>
                @endif
            </nav>
            @endif
        </div>
    </article>

</x-layouts.app>
