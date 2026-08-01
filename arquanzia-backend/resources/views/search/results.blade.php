<x-layouts.app title="Recherche — Arquanzia" description="Rechercher dans l’univers d’Arquanzia.">
    <div class="max-w-3xl mx-auto">
        <h1 class="font-serif text-4xl font-bold text-arq-forest mb-6">Recherche</h1>

        <form action="{{ route('search') }}" method="GET" class="mb-8">
            <label for="q" class="sr-only">Termes recherchés</label>
            <div class="flex gap-2">
                <input type="search" name="q" id="q" value="{{ $query }}" autofocus
                       placeholder="Un nom, un lieu, un mot du texte…"
                       class="flex-1 px-4 py-2 rounded-organic-sm border border-arq-amber/40 bg-white text-arq-ink placeholder:text-arq-bark/50 focus:outline-none focus:ring-2 focus:ring-arq-forest">
                <button type="submit" class="btn-arq btn-arq-primary">Chercher</button>
            </div>
        </form>

        @if($query === '')
            <p class="arq-dim">La recherche couvre les titres <em>et</em> le texte des livres, chapitres, entrées d’encyclopédie, fragments et publications du fil.</p>
        @elseif(mb_strlen(trim($query)) < \App\Services\SearchService::MIN_LENGTH)
            <p class="arq-dim">Deux caractères au minimum.</p>
        @elseif($totalResults === 0)
            <p class="arq-dim">Aucun résultat pour « {{ $query }} ».</p>
        @else
            <p class="arq-dim text-sm mb-6">
                {{ $totalResults }} résultat{{ $totalResults > 1 ? 's' : '' }} pour « {{ $query }} »
            </p>

            <ul class="space-y-4">
                @foreach($results as $result)
                    <li class="card-arq p-4">
                        <a href="{{ $result['url'] }}" class="flex gap-4 group">
                            @if($result['thumbnail'])
                                <img src="{{ $result['thumbnail'] }}" alt=""
                                     class="w-14 h-14 rounded-organic-sm object-cover shrink-0">
                            @endif
                            <div class="min-w-0">
                                <p class="text-xs uppercase tracking-[0.2em] text-arq-bark/60">
                                    {{ $result['label'] }}@if($result['context']) · {{ $result['context'] }}@endif
                                </p>
                                <p class="font-serif text-lg text-arq-forest group-hover:underline">{{ $result['title'] }}</p>
                                @if($result['excerpt'])
                                    <p class="arq-dim text-sm mt-1">{{ $result['excerpt'] }}</p>
                                @endif
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts.app>
