<x-layouts.admin title="Encyclopédie">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-serif text-2xl font-bold text-arq-forest">Encyclopédie</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.encyclopedia.import') }}" class="bg-arq-parchment text-arq-forest border border-arq-amber/40 px-4 py-2 rounded-lg hover:bg-arq-parchment-dark">
                📥 Importer ZIP
            </a>
            <a href="{{ route('admin.encyclopedia.create') }}" class="bg-arq-forest text-arq-parchment px-4 py-2 rounded-lg hover:bg-arq-forest-light">
                + Nouvel élément
            </a>
        </div>
    </div>

    @if($nodes->isEmpty())
        <div class="bg-arq-parchment rounded-xl shadow-sm border border-arq-amber/20 p-8 text-center">
            <p class="text-arq-bark">Aucun élément dans l'encyclopédie.</p>
        </div>
    @else
        <div class="bg-arq-parchment rounded-xl shadow-sm border border-arq-amber/20 p-4">
            @include('admin.encyclopedia._tree', ['nodes' => $nodes, 'level' => 0])
        </div>
    @endif
</x-layouts.admin>
