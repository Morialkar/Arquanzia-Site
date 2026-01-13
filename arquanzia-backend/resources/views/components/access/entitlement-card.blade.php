@props(['type', 'active', 'endsAt' => null])

@php
    $tierLabel = config('tiers.labels.' . $type);
    $config = match($type) {
        'vip' => [
            'title' => 'Accès ' . $tierLabel,
            'color' => 'purple',
            'description' => 'Accès aux posts ' . $tierLabel . ' et contenus exclusifs du feed.',
            'link' => 'https://sortilege.ca/products/vip',
        ],
        'reader' => [
            'title' => 'Accès ' . $tierLabel,
            'color' => 'amber',
            'description' => 'Accès à la bibliothèque, chapitres et articles encyclopédie ' . $tierLabel . '.',
            'link' => 'https://sortilege.ca/products/lecteur',
        ],
    };
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-{{ $config['color'] }}-700">{{ $config['title'] }}</h2>
        @if($active)
            <span class="px-3 py-1 bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-700 rounded-full text-sm">Actif</span>
        @else
            <span class="px-3 py-1 bg-gray-100 text-gray-500 rounded-full text-sm">Inactif</span>
        @endif
    </div>
    @if($active && $endsAt)
        <p class="text-gray-600">
            Votre {{ strtolower($config['title']) }} est actif jusqu'au <strong>{{ $endsAt->format('d/m/Y') }}</strong>.
        </p>
        <p class="text-gray-500 text-sm mt-2">{{ $config['description'] }}</p>
    @else
        <p class="text-gray-500">Vous n'avez pas d'{{ strtolower($config['title']) }} actif.</p>
        <a href="{{ $config['link'] }}" target="_blank" class="inline-block mt-4 text-{{ $config['color'] }}-600 hover:underline">
            Devenir {{ $tierLabel }} →
        </a>
    @endif
</div>
