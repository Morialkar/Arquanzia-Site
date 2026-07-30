<x-layouts.admin title="Analytique - Admin">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-arq-bark/50 dark:text-arq-mint/60">Système</p>
            <h1 class="font-serif text-3xl font-semibold text-arq-forest dark:text-arq-mint">📊 Analytique</h1>
        </div>
        <p class="text-sm text-arq-bark/70 dark:text-arq-mint/70">Vue d’ensemble du trafic et des contenus.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @php
            $summaryCards = [
                ['label' => 'Utilisateurs', 'value' => $stats['total_users'], 'color' => 'text-arq-forest dark:text-arq-mint'],
                ['label' => 'Lecteurs actifs', 'value' => $stats['active_readers'], 'color' => 'text-amber-600 dark:text-amber-300'],
                ['label' => 'VIPs actifs', 'value' => $stats['active_vips'], 'color' => 'text-purple-600 dark:text-purple-300'],
                ['label' => 'Taux reprise lecture', 'value' => "{$stats['resume_rate']}%", 'color' => 'text-green-600 dark:text-green-300'],
            ];
        @endphp
        @foreach($summaryCards as $card)
            <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 p-4 text-center shadow-sm dark:bg-arq-night-card dark:border-arq-mint/20">
                <div class="font-serif text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
                <div class="text-arq-bark/70 dark:text-arq-mint/70 text-xs uppercase tracking-wide">{{ $card['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment dark:bg-arq-night-card dark:border-arq-mint/20">
            <div class="p-4 border-b border-arq-amber/20 dark:border-arq-mint/20">
                <h2 class="font-serif text-lg font-semibold text-arq-forest dark:text-arq-mint">📚 Livres les plus consultés</h2>
                <p class="text-arq-bark/60 dark:text-arq-mint/60 text-xs">30 derniers jours</p>
            </div>
            <div class="divide-y divide-arq-amber/20 dark:divide-arq-mint/20 max-h-64 overflow-y-auto">
                @forelse($topBooks as $item)
                    <div class="p-3 flex items-center justify-between">
                        <span class="text-arq-ink dark:text-arq-mint text-sm truncate">{{ $item['book']->title }}</span>
                        <span class="text-arq-bark/70 dark:text-arq-mint/70 text-xs font-semibold">{{ $item['views'] }} vues</span>
                    </div>
                @empty
                    <div class="p-4 text-arq-bark/60 dark:text-arq-mint/60 text-sm text-center">Aucune donnée</div>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment dark:bg-arq-night-card dark:border-arq-mint/20">
            <div class="p-4 border-b border-arq-amber/20 dark:border-arq-mint/20">
                <h2 class="font-serif text-lg font-semibold text-arq-forest dark:text-arq-mint">📖 Chapitres les plus lus</h2>
                <p class="text-arq-bark/60 dark:text-arq-mint/60 text-xs">30 derniers jours</p>
            </div>
            <div class="divide-y divide-arq-amber/20 dark:divide-arq-mint/20 max-h-64 overflow-y-auto">
                @forelse($topChapters as $item)
                    <div class="p-3 flex items-center justify-between gap-3">
                        <div class="truncate">
                            <span class="text-arq-ink dark:text-arq-mint text-sm block">{{ $item['chapter']->title }}</span>
                            <span class="text-arq-bark/50 dark:text-arq-mint/50 text-xs">{{ $item['chapter']->book->title }}</span>
                        </div>
                        <span class="text-arq-bark/70 dark:text-arq-mint/70 text-xs font-semibold">{{ $item['views'] }}</span>
                    </div>
                @empty
                    <div class="p-4 text-arq-bark/60 dark:text-arq-mint/60 text-sm text-center">Aucune donnée</div>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment dark:bg-arq-night-card dark:border-arq-mint/20">
            <div class="p-4 border-b border-arq-amber/20 dark:border-arq-mint/20">
                <h2 class="font-serif text-lg font-semibold text-arq-forest dark:text-arq-mint">📜 Encyclopédie (public)</h2>
                <p class="text-arq-bark/60 dark:text-arq-mint/60 text-xs">30 derniers jours</p>
            </div>
            <div class="divide-y divide-arq-amber/20 dark:divide-arq-mint/20 max-h-64 overflow-y-auto">
                @forelse($topEncyclopedia as $item)
                    <div class="p-3 flex items-center justify-between">
                        <span class="text-arq-ink dark:text-arq-mint text-sm truncate">{{ $item['node']->title }}</span>
                        <span class="text-arq-bark/70 dark:text-arq-mint/70 text-xs font-semibold">{{ $item['views'] }}</span>
                    </div>
                @empty
                    <div class="p-4 text-arq-bark/60 dark:text-arq-mint/60 text-sm text-center">Aucune donnée</div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.admin>
