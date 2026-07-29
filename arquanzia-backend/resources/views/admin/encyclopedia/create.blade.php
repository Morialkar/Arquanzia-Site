<x-layouts.admin title="Nouvel élément encyclopédie">
    <div class="mb-6">
        <a href="{{ route('admin.encyclopedia.index') }}" class="text-arq-forest dark:text-arq-mint hover:underline">← Encyclopédie</a>
    </div>

    <h1 class="font-serif text-2xl font-bold text-arq-forest dark:text-white mb-6">
        Nouvel élément
        @if($parent)
            <span class="text-arq-bark dark:text-arq-mint/80 font-normal">dans {{ $parent->title }}</span>
        @endif
    </h1>

    <x-encyclopedia-form 
        :action="route('admin.encyclopedia.store')"
        :parent="$parent"
        :categories="$categories"
        submitLabel="Créer" />
</x-layouts.admin>
