<x-layouts.admin title="Livraison - Admin">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-arq-bark/50 dark:text-arq-mint/60">Système</p>
            <h1 class="font-serif text-3xl font-semibold text-arq-forest dark:text-arq-mint">📧 Livraison automatique</h1>
        </div>
        <p class="text-sm text-arq-bark/70 dark:text-arq-mint/70">Surveille l’envoi automatique des chapitres.</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        @php
            $cards = [
                ['label' => 'Envoyés', 'value' => $stats['total_sent'], 'color' => 'text-green-600 dark:text-green-400'],
                ['label' => 'Échoués', 'value' => $stats['total_failed'], 'color' => 'text-red-600 dark:text-red-300'],
                ['label' => 'En attente', 'value' => $stats['total_pending'], 'color' => 'text-yellow-600 dark:text-yellow-300'],
                ['label' => 'Adresses actives', 'value' => $stats['active_emails'], 'color' => 'text-blue-600 dark:text-blue-300'],
                ['label' => 'Désactivées', 'value' => $stats['disabled_emails'], 'color' => 'text-arq-bark/70 dark:text-arq-mint/70'],
            ];
        @endphp

        @foreach($cards as $card)
            <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 p-4 text-center shadow-sm dark:bg-arq-night-card dark:border-arq-mint/20">
                <div class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
                <div class="text-arq-bark/70 dark:text-arq-mint/70 text-xs uppercase tracking-wide">{{ $card['label'] }}</div>
            </div>
        @endforeach
    </div>

    @if($failedEmails->count() > 0)
        <div class="mb-8 rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment dark:bg-arq-night-card dark:border-arq-mint/20">
            <div class="p-4 border-b border-arq-amber/20 dark:border-arq-mint/20">
                <h2 class="font-serif text-lg font-semibold text-arq-forest dark:text-arq-mint">Adresses avec erreurs</h2>
            </div>
            <div class="divide-y divide-arq-amber/20 dark:divide-arq-mint/20">
                @foreach($failedEmails as $email)
                    <div class="p-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div class="text-sm">
                            <span class="font-semibold text-arq-ink dark:text-arq-mint">{{ $email->email }}</span>
                            <span class="text-arq-bark/70 dark:text-arq-mint/70 ml-2">({{ $email->user->handle }})</span>
                            <span class="text-red-600 dark:text-red-300 font-medium ml-2">{{ $email->fail_count }} échec(s)</span>
                        </div>
                        <form action="{{ route('admin.delivery.disable', $email) }}" method="POST" class="self-start md:self-auto">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition
                                bg-red-100 text-red-700 hover:bg-red-200
                                dark:bg-red-900/30 dark:text-red-100 dark:hover:bg-red-900/50">
                                Désactiver
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment dark:bg-arq-night-card dark:border-arq-mint/20">
        <div class="p-4 border-b border-arq-amber/20 dark:border-arq-mint/20 flex items-center justify-between">
            <div>
                <h2 class="font-serif text-lg font-semibold text-arq-forest dark:text-arq-mint">Derniers envois</h2>
                <p class="text-xs text-arq-bark/60 dark:text-arq-mint/60">Historique des 30 derniers jobs</p>
            </div>
            <span class="text-xs text-arq-bark/50 dark:text-arq-mint/50">{{ $recentJobs->count() }} jobs</span>
        </div>
        <div class="divide-y divide-arq-amber/20 dark:divide-arq-mint/20 max-h-96 overflow-y-auto">
            @forelse($recentJobs as $job)
                <div class="p-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="font-medium text-arq-ink dark:text-arq-mint">{{ $job->chapter->title }}</p>
                        <p class="text-xs text-arq-bark/60 dark:text-arq-mint/60">
                            → {{ $job->deliveryEmail->email }} · {{ strtoupper($job->format_sent) }}
                        </p>
                    </div>
                    <div class="flex items-center flex-wrap gap-3 text-xs">
                        <span class="text-arq-bark/50 dark:text-arq-mint/50">{{ $job->created_at->format('d/m H:i') }}</span>
                        @if($job->status === 'sent')
                            <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 font-semibold dark:bg-green-900/30 dark:text-green-200">Envoyé</span>
                        @elseif($job->status === 'failed')
                            <span class="px-2 py-1 rounded-full bg-red-100 text-red-700 font-semibold dark:bg-red-900/30 dark:text-red-200" title="{{ $job->error_message }}">Échoué</span>
                            <form action="{{ route('admin.delivery.retry', $job) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition
                                    bg-arq-forest text-white hover:bg-arq-forest-light
                                    dark:bg-arq-mint/90 dark:text-arq-night dark:hover:bg-arq-mint">
                                    Relancer
                                </button>
                            </form>
                        @else
                            <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 font-semibold dark:bg-yellow-900/30 dark:text-yellow-200">En attente</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-arq-bark/70 dark:text-arq-mint/70">Aucun envoi</div>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
