<x-layouts.admin title="Nouveau wikilink">
    <div class="mb-6">
        <a href="{{ route('admin.wikilinks.index') }}" class="text-arq-forest hover:text-arq-forest-light">← Retour aux wikilinks</a>
    </div>

    <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">Nouveau wikilink</h1>

    <form action="{{ route('admin.wikilinks.store') }}" method="POST" class="bg-arq-parchment rounded-xl shadow-sm border border-arq-amber/20 p-6 max-w-2xl">
        @csrf

        <div class="mb-4">
            <label for="term" class="block text-arq-bark font-medium mb-1">Terme <span class="text-red-500">*</span></label>
            <input type="text" name="term" id="term" value="{{ old('term') }}" required
                class="w-full px-4 py-2 border border-arq-amber/30 rounded-lg bg-white focus:ring-2 focus:ring-arq-forest/20 focus:border-arq-forest"
                placeholder="Ex: Arquanzia">
            <p class="text-arq-bark/60 text-sm mt-1">Le texte entre [[crochets]] qui déclenchera le lien</p>
            @error('term')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="encyclopedia_node_id" class="block text-arq-bark font-medium mb-1">Article d'encyclopédie</label>
            <select name="encyclopedia_node_id" id="encyclopedia_node_id"
                class="w-full px-4 py-2 border border-arq-amber/30 rounded-lg bg-white focus:ring-2 focus:ring-arq-forest/20 focus:border-arq-forest">
                <option value="">— Aucun —</option>
                @foreach($nodes as $node)
                    <option value="{{ $node->id }}" {{ old('encyclopedia_node_id') == $node->id ? 'selected' : '' }}>
                        {{ $node->title }}
                    </option>
                @endforeach
            </select>
            <p class="text-arq-bark/60 text-sm mt-1">Lier vers un article existant de l'encyclopédie</p>
        </div>

        <div class="mb-6">
            <label for="custom_url" class="block text-arq-bark font-medium mb-1">URL personnalisée</label>
            <input type="url" name="custom_url" id="custom_url" value="{{ old('custom_url') }}"
                class="w-full px-4 py-2 border border-arq-amber/30 rounded-lg bg-white focus:ring-2 focus:ring-arq-forest/20 focus:border-arq-forest"
                placeholder="https://...">
            <p class="text-arq-bark/60 text-sm mt-1">Alternative: lier vers une URL externe (prioritaire si article aussi défini)</p>
            @error('custom_url')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-arq-forest text-arq-parchment px-6 py-2 rounded-lg hover:bg-arq-forest-light">
                Créer
            </button>
            <a href="{{ route('admin.wikilinks.index') }}" class="px-6 py-2 border border-arq-amber/40 rounded-lg text-arq-bark hover:bg-arq-parchment-dark">
                Annuler
            </a>
        </div>
    </form>
</x-layouts.admin>
