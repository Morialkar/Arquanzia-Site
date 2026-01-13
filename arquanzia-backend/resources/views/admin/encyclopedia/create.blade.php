<x-layouts.admin title="Nouvel élément encyclopédie">
    <div class="mb-6">
        <a href="{{ route('admin.encyclopedia.index') }}" class="text-arq-forest hover:underline">← Encyclopédie</a>
    </div>

    <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">
        Nouvel élément
        @if($parent)
            <span class="text-arq-bark font-normal">dans {{ $parent->title }}</span>
        @endif
    </h1>

    <form action="{{ route('admin.encyclopedia.store') }}" method="POST" enctype="multipart/form-data" class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6 space-y-6">
        @csrf

        @if($parent)
            <input type="hidden" name="parent_id" value="{{ $parent->id }}">
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                <select name="type" required class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg">
                    <option value="category" {{ old('type') === 'category' ? 'selected' : '' }}>📁 Catégorie</option>
                    <option value="article" {{ old('type') === 'article' ? 'selected' : '' }}>📄 Article</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug') }}" placeholder="auto-généré si vide" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg font-mono">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Visibilité *</label>
                <select name="visibility" required class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg">
                    <option value="public" {{ old('visibility') === 'public' ? 'selected' : '' }}>Public</option>
                    <option value="reader" {{ old('visibility') === 'reader' ? 'selected' : '' }}>Lecteur uniquement</option>
                </select>
            </div>
        </div>

        @if(!$parent)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Parent</label>
                <select name="parent_id" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg">
                    <option value="">Racine</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->title }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ordre</label>
            <input type="number" name="order_index" value="{{ old('order_index', 0) }}" min="0" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Teaser (Markdown)</label>
            <textarea name="teaser_md" rows="3" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg font-mono text-sm">{{ old('teaser_md') }}</textarea>
        </div>

        <div id="article-fields" class="space-y-6 border-t border-gray-200 pt-6">
            <h3 class="font-medium text-arq-ink">Contenu article</h3>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Couverture</label>
                <input type="file" name="cover" accept="image/*" class="w-full">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contenu (Markdown)</label>
                <textarea name="content_md" rows="15" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg font-mono text-sm">{{ old('content_md') }}</textarea>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-200">
            <button type="submit" class="bg-arq-forest text-arq-parchment px-6 py-2 rounded-lg hover:bg-arq-forest-light">Créer</button>
        </div>
    </form>

    <script>
        document.querySelector('select[name="type"]').addEventListener('change', function() {
            document.getElementById('article-fields').style.display = this.value === 'article' ? 'block' : 'none';
        });
        document.querySelector('select[name="type"]').dispatchEvent(new Event('change'));
    </script>
</x-layouts.admin>
