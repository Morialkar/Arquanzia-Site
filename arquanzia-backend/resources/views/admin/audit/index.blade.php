<x-layouts.admin title="Audit - Admin">
    <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">📋 Journal d'audit</h1>

    <div class="bg-arq-parchment rounded-lg shadow-sm border">
        <div class="divide-y">
            @forelse($logs as $log)
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-medium text-arq-ink">{{ $log->action }}</span>
                            <span class="text-arq-bark text-sm ml-2">par {{ $log->actor_email ?? 'Système' }}</span>
                        </div>
                        <span class="text-arq-bark/40 text-xs">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($log->meta)
                        <div class="mt-2 text-xs text-arq-bark bg-arq-parchment-dark rounded p-2">
                            @foreach($log->meta as $key => $value)
                                <span class="inline-block mr-3"><strong>{{ $key }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-arq-bark">Aucun log</div>
            @endforelse
        </div>
    </div>

    <div class="mt-6">
        {{ $logs->links() }}
    </div>
</x-layouts.admin>
