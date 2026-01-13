@props(['history'])

<div class="border-t border-gray-200 pt-4 mt-4">
    <h3 class="text-sm font-medium text-gray-700 mb-3">Historique des envois</h3>
    <div class="space-y-2 max-h-48 overflow-y-auto">
        @foreach($history as $job)
            <div class="flex items-center justify-between text-sm p-2 bg-gray-50 rounded">
                <div>
                    <span class="text-gray-800">{{ $job->chapter->title }}</span>
                    <span class="text-gray-500 text-xs ml-1">({{ strtoupper($job->format_sent) }})</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-gray-400 text-xs">{{ $job->created_at->format('d/m H:i') }}</span>
                    @if($job->status === 'sent')
                        <span class="text-green-600 text-xs">✓ Envoyé</span>
                    @elseif($job->status === 'failed')
                        <span class="text-red-600 text-xs">✗ Échoué</span>
                    @else
                        <span class="text-gray-500 text-xs">⏳ En attente</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
