<x-layouts.admin title="Analytique - Admin">
    <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">📊 Analytique</h1>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-arq-parchment rounded-lg shadow-sm border p-4 text-center">
            <div class="font-serif text-2xl font-bold text-arq-forest">{{ $stats['total_users'] }}</div>
            <div class="text-arq-bark text-sm">Utilisateurs</div>
        </div>
        <div class="bg-arq-parchment rounded-lg shadow-sm border p-4 text-center">
            <div class="text-2xl font-bold text-amber-600">{{ $stats['active_readers'] }}</div>
            <div class="text-arq-bark text-sm">Lecteurs actifs</div>
        </div>
        <div class="bg-arq-parchment rounded-lg shadow-sm border p-4 text-center">
            <div class="text-2xl font-bold text-purple-600">{{ $stats['active_vips'] }}</div>
            <div class="text-arq-bark text-sm">VIPs actifs</div>
        </div>
        <div class="bg-arq-parchment rounded-lg shadow-sm border p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $stats['resume_rate'] }}%</div>
            <div class="text-arq-bark text-sm">Taux reprise lecture</div>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <div class="bg-arq-parchment rounded-lg shadow-sm border">
            <div class="p-4 border-b">
                <h2 class="font-bold text-arq-ink">📚 Livres les plus consultés</h2>
                <p class="text-arq-bark text-xs">30 derniers jours</p>
            </div>
            <div class="divide-y max-h-64 overflow-y-auto">
                @forelse($topBooks as $item)
                    <div class="p-3 flex items-center justify-between">
                        <span class="text-arq-ink text-sm truncate">{{ $item['book']->title }}</span>
                        <span class="text-arq-bark text-xs">{{ $item['views'] }} vues</span>
                    </div>
                @empty
                    <div class="p-4 text-arq-bark text-sm text-center">Aucune donnée</div>
                @endforelse
            </div>
        </div>

        <div class="bg-arq-parchment rounded-lg shadow-sm border">
            <div class="p-4 border-b">
                <h2 class="font-bold text-arq-ink">📖 Chapitres les plus lus</h2>
                <p class="text-arq-bark text-xs">30 derniers jours</p>
            </div>
            <div class="divide-y max-h-64 overflow-y-auto">
                @forelse($topChapters as $item)
                    <div class="p-3 flex items-center justify-between">
                        <div class="truncate">
                            <span class="text-arq-ink text-sm">{{ $item['chapter']->title }}</span>
                            <span class="text-arq-bark/40 text-xs block">{{ $item['chapter']->book->title }}</span>
                        </div>
                        <span class="text-arq-bark text-xs">{{ $item['views'] }}</span>
                    </div>
                @empty
                    <div class="p-4 text-arq-bark text-sm text-center">Aucune donnée</div>
                @endforelse
            </div>
        </div>

        <div class="bg-arq-parchment rounded-lg shadow-sm border">
            <div class="p-4 border-b">
                <h2 class="font-bold text-arq-ink">📜 Encyclopédie (publics)</h2>
                <p class="text-arq-bark text-xs">30 derniers jours</p>
            </div>
            <div class="divide-y max-h-64 overflow-y-auto">
                @forelse($topEncyclopedia as $item)
                    <div class="p-3 flex items-center justify-between">
                        <span class="text-arq-ink text-sm truncate">{{ $item['node']->title }}</span>
                        <span class="text-arq-bark text-xs">{{ $item['views'] }}</span>
                    </div>
                @empty
                    <div class="p-4 text-arq-bark text-sm text-center">Aucune donnée</div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.admin>
