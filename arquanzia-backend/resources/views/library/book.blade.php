<x-layouts.app title="{{ $book->title }} - Bibliothèque - Arquanzia">
    <div class="mb-6">
        <a href="{{ route('library.index') }}" class="text-arq-forest hover:text-arq-forest-light font-medium">← Retour à la Bibliothèque</a>
    </div>

    @if(!$context['is_logged_in'])
        <div class="mb-8">
            <x-cta-reader />
        </div>
    @endif

    <div class="card-arq overflow-hidden">
        <div class="md:flex">
            @if($book->cover)
                <div class="md:w-1/3 bg-arq-parchment-dark">
                    <img src="{{ route('media.show', ['media' => $book->cover->id, 'unlocked' => $hasAccess ? 1 : 0]) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                </div>
            @endif
            <div class="p-6 md:w-full">
                <div class="flex items-start justify-between w-full">
                    <h1 class="font-serif text-3xl font-bold text-arq-forest">{{ $book->title }}</h1>
                    @if($context['is_logged_in'])
                        <form action="{{ route('favorites.toggle') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="book">
                            <input type="hidden" name="target_id" value="{{ $book->id }}">
                            <button type="submit" class="text-2xl hover:scale-110 transition-transform text-arq-copper" title="{{ $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' }}">
                                {{ $isFavorite ? '❤️' : '🤍' }}
                            </button>
                        </form>
                    @endif
                </div>
                
                @if($book->description_md)
                    <div class="prose prose-sm mt-4 text-arq-bark leading-relaxed">
                        {!! $book->description_html !!}
                    </div>
                @endif

                @if($hasAccess)
                    @if(isset($readingProgress) && $readingProgress && $readingProgress->chapter)
                        <div class="mt-6">
                            <a href="{{ route('library.chapter', ['book' => $book->slug, 'chapter' => $readingProgress->chapter->slug]) }}" class="btn-arq btn-arq-primary inline-flex items-center">
                                <span class="mr-2">📖</span> Reprendre la lecture
                                <span class="ml-2 opacity-70 text-sm">({{ $readingProgress->chapter->title }})</span>
                            </a>
                        </div>
                    @endif
                    
                    @if($book->publishedChapters()->count() > 0)
                        <div class="mt-4 flex gap-3">
                            <a href="{{ route('download.book', ['slug' => $book->slug, 'format' => 'epub']) }}" class="btn-arq btn-arq-secondary inline-flex items-center">
                                <span class="mr-2">📥</span> EPUB
                            </a>
                            <a href="{{ route('download.book', ['slug' => $book->slug, 'format' => 'pdf']) }}" class="btn-arq btn-arq-secondary inline-flex items-center">
                                <span class="mr-2">📥</span> PDF
                            </a>
                        </div>
                    @endif
                @else
                    <div class="mt-6 bg-arq-parchment-dark border border-arq-amber/30 rounded-organic p-4">
                        <p class="text-arq-bark font-serif">Ce grimoire attend d'être déverrouillé...</p>
                        <a href="{{ route('account.access') }}" class="inline-block mt-2 text-arq-forest hover:text-arq-forest-light font-medium">{{ config('tiers.cta.reader.button') }} →</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-10">
        <h2 class="font-serif text-2xl font-bold text-arq-forest mb-4">Chapitres</h2>
        
        @if($book->chapters->isEmpty())
            <p class="text-arq-bark/60">Aucun chapitre disponible pour le moment.</p>
        @else
            <div class="bg-arq-parchment rounded-lg shadow-parchment border border-arq-amber/20 divide-y divide-arq-amber/20">
                @foreach($book->chapters as $chapter)
                    @php 
                        $isComingSoon = !($isAdmin ?? false) && $chapter->isComingSoon();
                        $isCurrentChapter = isset($readingProgress) && $readingProgress && $readingProgress->chapter_id === $chapter->id && $readingProgress->progress < 100;
                        $isRead = isset($readingProgress) && $readingProgress && $readingProgress->chapter && (
                            $chapter->order_index < $readingProgress->chapter->order_index ||
                            ($readingProgress->chapter_id === $chapter->id && $readingProgress->progress >= 100)
                        );
                    @endphp
                    <div class="p-4 flex items-center justify-between {{ $isComingSoon ? 'opacity-50' : '' }} {{ $isCurrentChapter ? 'bg-arq-mint/20' : '' }}">
                        <div class="flex items-center gap-4">
                            <span class="text-arq-bark/40 font-serif text-lg w-8">{{ str_pad($chapter->order_index, 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <span class="font-medium text-arq-ink">{{ $chapter->title }}</span>
                                @if($isCurrentChapter)
                                    <span class="ml-2 px-2 py-0.5 bg-arq-mint/40 text-arq-forest text-xs rounded-organic-sm">En cours</span>
                                @elseif($isRead)
                                    <span class="ml-2 px-2 py-0.5 bg-arq-parchment-dark text-arq-bark/60 text-xs rounded-organic-sm">Lu</span>
                                @endif
                                @if($chapter->isComingSoon())
                                    <span class="ml-2 px-2 py-0.5 bg-arq-amber/20 text-arq-bark text-xs rounded-organic-sm">Bientôt</span>
                                @endif
                                @if(!$chapter->is_published)
                                    <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-600 text-xs rounded-organic-sm">Non publié</span>
                                @endif
                            </div>
                        </div>
                        @if(!$isComingSoon)
                            @if($hasAccess)
                                <a href="{{ route('library.chapter', ['book' => $book->slug, 'chapter' => $chapter->slug]) }}" class="text-arq-forest hover:text-arq-forest-light text-sm font-medium">
                                    {{ $isCurrentChapter ? 'Continuer →' : 'Lire →' }}
                                </a>
                            @else
                                <span class="text-arq-bark/40 text-sm">🔮</span>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if(!$hasAccess && $context['is_logged_in'])
        <div class="mt-10">
            <x-cta-reader />
        </div>
    @endif
</x-layouts.app>
