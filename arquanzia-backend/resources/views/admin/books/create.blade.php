<x-layouts.admin title="Nouveau livre">
    <div class="mb-6">
        <a href="{{ route('admin.books.index') }}" class="text-arq-forest hover:underline">← Livres</a>
    </div>

    <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">Nouveau livre</h1>

    <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data" class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6 space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
            <input type="text" name="title" value="{{ old('title') }}" required class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Auteur</label>
            <input type="text" name="author" value="{{ old('author') }}" placeholder="Créations Sortilege" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
            <input type="text" name="slug" value="{{ old('slug') }}" placeholder="auto-généré si vide" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg font-mono">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description (Markdown)</label>
            <textarea name="description_md" rows="5" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg font-mono text-sm">{{ old('description_md') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Couverture</label>
            <input type="file" name="cover" accept="image/*" class="w-full">
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_published" value="1" id="is_published" {{ old('is_published') ? 'checked' : '' }}>
            <label for="is_published" class="text-sm text-gray-700">Publié</label>
        </div>

        <div class="pt-4 border-t border-gray-200">
            <button type="submit" class="bg-arq-forest text-arq-parchment px-6 py-2 rounded-lg hover:bg-arq-forest-light">Créer</button>
        </div>
    </form>
</x-layouts.admin>
