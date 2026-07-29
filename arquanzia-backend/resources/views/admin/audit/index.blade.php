<x-layouts.admin title="Audit - Admin">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-arq-bark/50 dark:text-arq-mint/60">Système</p>
            <h1 class="font-serif text-3xl font-semibold text-arq-forest dark:text-arq-mint">📋 Journal d'audit</h1>
        </div>
        <p class="text-sm text-arq-bark/70 dark:text-arq-mint/70">Historique des actions administrateur et systèmes.</p>
    </div>

    <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment dark:bg-arq-night-card dark:border-arq-mint/20">
        <div class="divide-y divide-arq-amber/20 dark:divide-arq-mint/20">
            @forelse($logs as $log)
                <div class="p-4">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <span class="font-semibold text-arq-ink dark:text-arq-mint">{{ $log->action }}</span>
                            <span class="text-arq-bark/70 dark:text-arq-mint/70 text-sm ml-2">par {{ $log->actor_email ?? 'Système' }}</span>
                        </div>
                        <span class="text-arq-bark/50 dark:text-arq-mint/50 text-xs">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($log->meta)
                        <div class="mt-3 text-xs text-arq-bark/80 dark:text-arq-mint/80 bg-arq-parchment-dark/80 dark:bg-arq-night rounded-xl p-3 border border-arq-amber/20 dark:border-arq-mint/20">
                            <div class="flex flex-wrap gap-3">
                                @foreach($log->meta as $key => $value)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-white/60 dark:bg-white/5 px-3 py-1">
                                        <strong class="uppercase tracking-wide text-[0.65rem] text-arq-bark/70 dark:text-arq-mint/70">{{ $key }}</strong>
                                        <span class="text-arq-ink dark:text-arq-mint">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-arq-bark/70 dark:text-arq-mint/70">Aucun log</div>
            @endforelse
        </div>
    </div>

    <div class="mt-6">
        {{ $logs->links() }}
    </div>
</x-layouts.admin>
