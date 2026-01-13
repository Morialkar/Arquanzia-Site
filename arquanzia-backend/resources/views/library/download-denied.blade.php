<x-layouts.app title="Accès refusé - Arquanzia">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center max-w-lg mx-auto mt-12">
        <span class="text-6xl">🔒</span>
        <h1 class="text-2xl font-bold text-gray-800 mt-4">Téléchargement non autorisé</h1>
        <p class="text-gray-600 mt-2">
            @if($type === 'book')
                Ce livre est réservé aux {{ config('tiers.labels.reader_plural') }}.
            @else
                Ce chapitre est réservé aux {{ config('tiers.labels.reader_plural') }}.
            @endif
        </p>
        
        @if($context['is_banned'])
            <p class="text-red-600 text-sm mt-4">Votre compte est actuellement restreint.</p>
        @else
            <a href="#" class="inline-block mt-6 bg-amber-600 text-white px-6 py-3 rounded-lg hover:bg-amber-700">
                Devenir {{ config('tiers.labels.reader') }}
            </a>
        @endif
        
        <div class="mt-6">
            <a href="{{ route('library.index') }}" class="text-indigo-600 hover:underline">← Retour à la bibliothèque</a>
        </div>
    </div>
</x-layouts.app>
