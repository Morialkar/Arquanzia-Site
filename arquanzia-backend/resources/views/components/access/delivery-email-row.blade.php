@props(['email'])

<div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
    <div class="flex items-center gap-3">
        <span class="{{ $email->is_active ? 'text-green-600' : 'text-gray-400' }}">
            {{ $email->is_active ? '✓' : '○' }}
        </span>
        <div>
            <span class="text-gray-800">{{ $email->email }}</span>
            <span class="text-gray-500 text-xs ml-2">({{ strtoupper($email->format) }})</span>
            @if($email->fail_count > 0)
                <span class="text-red-500 text-xs ml-2">{{ $email->fail_count }} échec(s)</span>
            @endif
        </div>
    </div>
    <div class="flex items-center gap-2">
        <form action="{{ route('delivery.toggle', $email) }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="text-xs text-indigo-600 hover:underline">
                {{ $email->is_active ? 'Désactiver' : 'Activer' }}
            </button>
        </form>
        <form action="{{ route('delivery.remove', $email) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette adresse?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs text-red-600 hover:underline">Supprimer</button>
        </form>
    </div>
</div>
