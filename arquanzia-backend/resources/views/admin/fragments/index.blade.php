<x-layouts.admin title="Fragments">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
        <h1 class="font-serif text-2xl font-bold text-arq-forest dark:text-arq-mint">Fragments</h1>
        <a href="{{ route('admin.fragments.create') }}" class="bg-arq-forest text-arq-parchment px-4 py-2 rounded-lg hover:bg-arq-forest-light">
            + Nouveau fragment
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-arq-parchment dark:bg-gray-900 rounded-2xl shadow-lg border border-arq-amber/20 dark:border-gray-700 p-4 md:p-6">
        @if($nodes->isEmpty())
            <div class="text-center py-10">
                <p class="text-arq-bark dark:text-gray-300">Aucun fragment pour le moment.</p>
            </div>
        @else
            @include('admin.fragments._tree', ['nodes' => $nodes, 'level' => 0])
        @endif
    </div>
</x-layouts.admin>
