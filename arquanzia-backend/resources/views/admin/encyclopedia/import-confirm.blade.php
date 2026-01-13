<x-layouts.admin title="Confirmer l'import - Encyclopédie">
    <div class="mb-6">
        <a href="{{ route('admin.encyclopedia.import') }}" class="text-arq-forest hover:text-arq-forest-light">← Retour</a>
    </div>

    <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">Confirmer l'import</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-arq-parchment rounded-lg shadow-sm border border-arq-amber/20 p-6">
                <div class="flex items-center gap-6 mb-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-arq-forest">{{ $analysis['total_categories'] }}</div>
                        <div class="text-sm text-arq-bark">Catégories</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-arq-forest">{{ $analysis['total_articles'] }}</div>
                        <div class="text-sm text-arq-bark">Articles</div>
                    </div>
                    @if(count($analysis['conflicts']) > 0)
                        <div class="text-center">
                            <div class="text-3xl font-bold text-amber-600">{{ count($analysis['conflicts']) }}</div>
                            <div class="text-sm text-arq-bark">Conflits</div>
                        </div>
                    @endif
                </div>

                <form action="{{ route('admin.encyclopedia.import.execute') }}" method="POST" id="import-form">
                    @csrf

                    @if(count($analysis['conflicts']) > 0)
                        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                            <h3 class="font-bold text-amber-800 mb-3">⚠️ Éléments existants détectés</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-amber-800 mb-2">Action par défaut:</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="conflict_mode" value="overwrite" checked class="text-arq-forest">
                                        <span class="text-sm">Écraser tout</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="conflict_mode" value="skip" class="text-arq-forest">
                                        <span class="text-sm">Ignorer tout</span>
                                    </label>
                                </div>
                            </div>

                            <div class="space-y-2 max-h-60 overflow-y-auto">
                                @foreach($analysis['conflicts'] as $conflict)
                                    <div class="flex items-center justify-between p-2 bg-arq-parchment rounded">
                                        <div>
                                            <span class="text-xs px-2 py-0.5 rounded {{ $conflict['type'] === 'category' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                                {{ $conflict['type'] === 'category' ? 'Catégorie' : 'Article' }}
                                            </span>
                                            <span class="text-sm text-gray-700 ml-2">{{ $conflict['path'] }}</span>
                                        </div>
                                        <label class="flex items-center gap-1 text-xs">
                                            <input type="checkbox" name="skip[]" value="{{ $conflict['path'] }}" class="text-arq-forest">
                                            <span>Ignorer</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <h3 class="font-bold text-arq-ink mb-3">Structure à importer</h3>
                    <div class="bg-arq-parchment-dark p-4 rounded-lg max-h-80 overflow-y-auto">
                        <ul class="space-y-1 text-sm">
                            @foreach($analysis['structure'] as $item)
                                <li class="flex items-center gap-2">
                                    @if($item['type'] === 'category')
                                        <span class="text-blue-600">📁</span>
                                    @else
                                        <span class="text-green-600">📄</span>
                                    @endif
                                    <span class="text-arq-ink">{{ $item['path'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="bg-arq-forest text-arq-parchment px-6 py-2 rounded-lg hover:bg-arq-forest-light">
                            Lancer l'import
                        </button>
                        <a href="{{ route('admin.encyclopedia.import') }}" class="px-6 py-2 border border-arq-amber/40 rounded-lg text-arq-bark hover:bg-arq-parchment-dark">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div>
            <div class="bg-arq-parchment rounded-lg shadow-sm border border-arq-amber/20 p-6">
                <h3 class="font-bold text-arq-ink mb-3">Légende</h3>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center gap-2">
                        <span class="text-blue-600">📁</span>
                        <span class="text-arq-bark">Catégorie (dossier)</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-green-600">📄</span>
                        <span class="text-arq-bark">Article (.md)</span>
                    </li>
                </ul>

                <div class="mt-4 pt-4 border-t border-arq-amber/20">
                    <h4 class="font-medium text-arq-ink mb-2">Gestion des conflits</h4>
                    <p class="text-xs text-arq-bark">
                        <strong>Écraser:</strong> Le contenu existant sera remplacé par le nouveau.<br>
                        <strong>Ignorer:</strong> L'élément existant sera conservé tel quel.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
