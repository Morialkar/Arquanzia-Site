<x-layouts.admin title="Encyclopédie">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
        <h1 class="font-serif text-2xl font-bold text-arq-forest dark:text-arq-mint">Encyclopédie</h1>
        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('admin.encyclopedia.import') }}" class="bg-arq-parchment dark:bg-gray-800 text-arq-forest dark:text-arq-mint border border-arq-amber/40 dark:border-gray-700 px-4 py-2 rounded-lg hover:bg-arq-parchment-dark dark:hover:bg-gray-700">
                📥 Importer ZIP
            </a>
            <a href="{{ route('admin.encyclopedia.create') }}" class="bg-arq-forest text-arq-parchment px-4 py-2 rounded-lg hover:bg-arq-forest-light">
                + Nouvel élément
            </a>
        </div>
    </div>

    <div class="bg-arq-parchment dark:bg-gray-900 rounded-2xl shadow-lg border border-arq-amber/20 dark:border-gray-700 p-4 md:p-6">
        @if($nodes->isEmpty())
            <div class="text-center py-10">
                <p class="text-arq-bark dark:text-gray-300">Aucun élément dans l'encyclopédie.</p>
            </div>
        @else
            @include('admin.encyclopedia._tree', ['nodes' => $nodes, 'level' => 0])
        @endif
    </div>
</x-layouts.admin>
