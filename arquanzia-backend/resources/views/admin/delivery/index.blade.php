<x-layouts.admin title="Livraison - Admin">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-serif text-2xl font-bold text-arq-forest">📧 Livraison automatique</h1>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-arq-parchment rounded-lg shadow-sm border p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $stats['total_sent'] }}</div>
            <div class="text-arq-bark text-sm">Envoyés</div>
        </div>
        <div class="bg-arq-parchment rounded-lg shadow-sm border p-4 text-center">
            <div class="text-2xl font-bold text-red-600">{{ $stats['total_failed'] }}</div>
            <div class="text-arq-bark text-sm">Échoués</div>
        </div>
        <div class="bg-arq-parchment rounded-lg shadow-sm border p-4 text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['total_pending'] }}</div>
            <div class="text-arq-bark text-sm">En attente</div>
        </div>
        <div class="bg-arq-parchment rounded-lg shadow-sm border p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $stats['active_emails'] }}</div>
            <div class="text-arq-bark text-sm">Adresses actives</div>
        </div>
        <div class="bg-arq-parchment rounded-lg shadow-sm border p-4 text-center">
            <div class="text-2xl font-bold text-arq-bark/60">{{ $stats['disabled_emails'] }}</div>
            <div class="text-arq-bark text-sm">Désactivées</div>
        </div>
    </div>

    @if($failedEmails->count() > 0)
        <div class="mb-8 bg-arq-parchment rounded-lg shadow-sm border">
            <div class="p-4 border-b">
                <h2 class="font-bold text-arq-ink">Adresses avec erreurs</h2>
            </div>
            <div class="divide-y">
                @foreach($failedEmails as $email)
                    <div class="p-4 flex items-center justify-between">
                        <div>
                            <span class="text-arq-ink">{{ $email->email }}</span>
                            <span class="text-arq-bark text-sm ml-2">({{ $email->user->handle }})</span>
                            <span class="text-red-500 text-sm ml-2">{{ $email->fail_count }} échec(s)</span>
                        </div>
                        <form action="{{ route('admin.delivery.disable', $email) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:underline">Désactiver</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-arq-parchment rounded-lg shadow-sm border">
        <div class="p-4 border-b">
            <h2 class="font-bold text-arq-ink">Derniers envois</h2>
        </div>
        <div class="divide-y max-h-96 overflow-y-auto">
            @forelse($recentJobs as $job)
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <span class="text-arq-ink">{{ $job->chapter->title }}</span>
                        <span class="text-arq-bark text-xs ml-2">→ {{ $job->deliveryEmail->email }}</span>
                        <span class="text-arq-bark/40 text-xs ml-2">({{ strtoupper($job->format_sent) }})</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-arq-bark/40 text-xs">{{ $job->created_at->format('d/m H:i') }}</span>
                        @if($job->status === 'sent')
                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded">Envoyé</span>
                        @elseif($job->status === 'failed')
                            <span class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded" title="{{ $job->error_message }}">Échoué</span>
                            <form action="{{ route('admin.delivery.retry', $job) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-arq-forest hover:underline">Relancer</button>
                            </form>
                        @else
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded">En attente</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-arq-bark">Aucun envoi</div>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
