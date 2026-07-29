<x-layouts.app :title="$ogTitle" :description="$ogDescription" :ogImage="$ogImage">
    <div class="mb-6">
        <a href="{{ route('library.index') }}" class="text-arq-forest hover:text-arq-forest-light font-medium">← Retour à la Bibliothèque</a>
    </div>

    <div class="card-arq overflow-hidden">
        <div class="md:flex">
            @if($book->cover)
                <div class="md:w-1/3 bg-arq-parchment-dark">
                    <img src="{{ route('media.show', $book->cover->id) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                </div>
            @endif
            <div class="p-6 md:w-full">
                <h1 class="font-serif text-3xl font-bold text-arq-forest">{{ $book->title }}</h1>

                @if($book->description_md)
                    <div class="prose prose-sm mt-4 arq-body leading-relaxed">
                        {!! $book->description_html !!}
                    </div>
                @endif

                @if($book->publishedChapters()->count() > 0)
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('download.book', ['slug' => $book->slug, 'format' => 'epub']) }}" class="btn-arq btn-arq-secondary inline-flex items-center">
                            <span class="mr-2">📥</span> EPUB
                        </a>
                        <a href="{{ route('download.book', ['slug' => $book->slug, 'format' => 'pdf']) }}" class="btn-arq btn-arq-secondary inline-flex items-center">
                            <span class="mr-2">📥</span> PDF
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-10">
        <h2 class="font-serif text-2xl font-bold text-arq-forest mb-4">Chapitres</h2>

        @if($book->chapters->isEmpty())
            <p class="arq-dim">Aucun chapitre disponible pour le moment.</p>
        @else
            <div class="bg-arq-parchment rounded-lg shadow-parchment border border-arq-amber/20 divide-y divide-arq-amber/20">
                @foreach($book->chapters->where('is_published', true) as $chapter)
                    @php $comingSoon = $chapter->isComingSoon(); @endphp
                    <div class="p-4 flex items-center justify-between {{ $comingSoon ? 'opacity-50' : '' }}">
                        <div class="flex items-center gap-4">
                            <span class="arq-faint font-serif text-lg w-8">{{ str_pad($chapter->order_index, 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <span class="font-medium text-arq-ink">{{ $chapter->title }}</span>
                                @if($comingSoon)
                                    <span class="ml-2 px-2 py-0.5 bg-arq-amber/20 arq-body text-xs rounded-organic-sm">Bientôt</span>
                                @endif
                            </div>
                        </div>
                        @if(!$comingSoon)
                            <a href="{{ route('library.chapter', ['book' => $book->slug, 'chapter' => $chapter->slug]) }}" class="text-arq-forest hover:text-arq-forest-light text-sm font-medium">
                                Lire →
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
