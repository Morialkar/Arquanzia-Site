<x-layouts.admin title="Importer - Encyclopédie">
    <div class="mb-6">
        <a href="{{ route('admin.encyclopedia.index') }}" class="text-arq-forest hover:text-arq-forest-light">← Encyclopédie</a>
    </div>

    <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">Importer depuis un ZIP</h1>

    <div class="bg-arq-parchment rounded-lg shadow-sm border border-arq-amber/20 p-6 max-w-2xl">
        <div class="mb-6">
            <h2 class="font-bold text-arq-ink mb-2">Format attendu</h2>
            <p class="text-arq-bark text-sm mb-3">Le ZIP doit contenir une arborescence de dossiers et fichiers Markdown (.md):</p>
            <pre class="bg-arq-parchment-dark p-4 rounded text-sm text-arq-ink overflow-x-auto">
encyclopedie.zip
├── Lieux/
│   ├── Capitales/
│   │   ├── Arquanzia.md
│   │   └── Valdoria.md
│   └── Villages/
│       └── Brindille.md
├── Personnages/
│   ├── Héros.md
│   └── Antagonistes.md
└── Histoire.md
            </pre>
            <ul class="text-arq-bark text-sm mt-3 space-y-1">
                <li>• Les <strong>dossiers</strong> deviennent des catégories</li>
                <li>• Les <strong>fichiers .md</strong> deviennent des articles</li>
                <li>• Le <strong>titre H1</strong> du fichier devient le titre de l'article</li>
                <li>• Le <strong>premier paragraphe</strong> devient le teaser</li>
            </ul>
        </div>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('admin.encyclopedia.import.analyze') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-arq-bark mb-2">Fichier ZIP (max 50 Mo)</label>
                <input type="file" name="zip_file" accept=".zip" required
                    class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg bg-arq-parchment">
            </div>
            <button type="submit" class="bg-arq-forest text-arq-parchment px-6 py-2 rounded-lg hover:bg-arq-forest-light">
                Analyser le fichier
            </button>
        </form>
    </div>
</x-layouts.admin>
