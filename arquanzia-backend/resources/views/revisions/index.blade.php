<x-layouts.app title="Révisions récentes — Arquanzia" description="Les textes d’Arquanzia repris ou étoffés récemment.">
    <div class="max-w-3xl mx-auto">
        <h1 class="font-serif text-4xl font-bold text-arq-forest dark:text-arq-mint">Révisions récentes</h1>
        <p class="arq-dim mt-3 mb-8 leading-relaxed">
            Les parutions sont annoncées par le <a href="{{ route('feeds.builder') }}" class="text-arq-forest dark:text-arq-mint underline hover:no-underline">flux</a>.
            Cette page recense l’autre moitié du travail : les textes déjà publiés qui ont été
            repris, corrigés ou étoffés.
        </p>

        @if($revisions->isEmpty())
            <div class="card-arq p-10 text-center">
                <p class="arq-body font-serif text-lg">Aucun texte n’a encore été révisé.</p>
            </div>
        @else
            <ul class="space-y-3">
                @foreach($revisions as $revision)
                    <li class="card-arq p-4">
                        <a href="{{ $revision['url'] }}" class="group flex flex-wrap items-baseline gap-x-3 gap-y-1">
                            <span class="text-xs uppercase tracking-[0.15em] text-arq-bark/50 dark:text-arq-mint/50">{{ $revision['section'] }}</span>
                            <span class="font-serif text-lg text-arq-forest dark:text-arq-mint group-hover:underline">{{ $revision['titre'] }}</span>
                            @if($revision['contexte'])
                                <span class="arq-dim text-sm">{{ $revision['contexte'] }}</span>
                            @endif
                            <time datetime="{{ $revision['date']->toDateString() }}" class="arq-dim text-sm ml-auto">
                                {{ $revision['date']->translatedFormat('j F Y') }}
                            </time>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts.app>
