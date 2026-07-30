<x-layouts.admin title="Nouveau post">
    <div class="max-w-3xl">
        <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">Nouveau post</h1>

        <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6">
            @csrf

            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                    class="w-full px-4 py-2 border border-arq-amber/40 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="mb-4">
                <label for="preview_text" class="block text-sm font-medium text-gray-700 mb-1">
                    Preview (visible même si verrouillé) <span class="text-red-500">*</span>
                </label>
                <textarea name="preview_text" id="preview_text" rows="2" required maxlength="500"
                    class="w-full px-4 py-2 border border-arq-amber/40 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('preview_text') }}</textarea>
                <p class="text-sm text-arq-bark mt-1">Max 500 caractères. Ce texte est toujours visible.</p>
            </div>

            <div class="mb-4">
                <label for="content_full" class="block text-sm font-medium text-gray-700 mb-1">Contenu complet</label>
                <textarea name="content_full" id="content_full" rows="10"
                    class="w-full px-4 py-2 border border-arq-amber/40 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('content_full') }}</textarea>
                <p class="text-sm text-arq-bark mt-1">Visible uniquement si l'utilisateur a accès.</p>
            </div>

            <div class="mb-6">
                <label for="images" class="block text-sm font-medium text-gray-700 mb-1">Images</label>
                <input type="file" name="images[]" id="images" multiple accept="image/*"
                    class="w-full px-4 py-2 border border-arq-amber/40 rounded-lg">
                <p class="text-sm text-arq-bark mt-1">Max 10 MB par image. Les versions floutées seront générées automatiquement.</p>
            </div>

            <div class="flex items-center space-x-4">
                <button type="submit" class="bg-arq-forest text-arq-parchment px-6 py-2 rounded-lg hover:bg-arq-forest-light font-medium">
                    Créer le post
                </button>
                <a href="{{ route('admin.posts.index') }}" class="text-arq-bark hover:text-gray-700">Annuler</a>
            </div>
        </form>
    </div>
</x-layouts.admin>
