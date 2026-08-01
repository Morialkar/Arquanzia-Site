<x-layouts.admin title="Modifier post">
    <div class="max-w-3xl">
        <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">Modifier: {{ $post->title }}</h1>

        <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data" class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}" required
                    class="w-full px-4 py-2 border border-arq-amber/40 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="mb-4">
                <label for="preview_text" class="block text-sm font-medium text-gray-700 mb-1">
                    Preview <span class="text-red-500">*</span>
                </label>
                <textarea name="preview_text" id="preview_text" rows="2" required maxlength="500"
                    class="w-full px-4 py-2 border border-arq-amber/40 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('preview_text', $post->preview_text) }}</textarea>
            </div>

            <div class="mb-4">
                <label for="content_full" class="block text-sm font-medium text-gray-700 mb-1">Contenu complet</label>
                <textarea name="content_full" id="content_full" rows="10"
                    class="w-full px-4 py-2 border border-arq-amber/40 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('content_full', $post->content_full) }}</textarea>
            </div>

            @if($post->media->count() > 0)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Images existantes</label>
                    <div class="grid grid-cols-3 gap-4">
                        @foreach($post->media as $media)
                            <div class="relative">
                                <img src="{{ route('media.show', ['media' => $media->id, 'unlocked' => 1]) }}" 
                                     class="w-full h-32 object-cover rounded-lg">
                                <label class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs cursor-pointer">
                                    <input type="checkbox" name="delete_media[]" value="{{ $media->id }}" class="mr-1">
                                    Suppr.
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mb-4">
                <label for="images" class="block text-sm font-medium text-gray-700 mb-1">Ajouter des images</label>
                <input type="file" name="images[]" id="images" multiple accept="image/*"
                    class="w-full px-4 py-2 border border-arq-amber/40 rounded-lg">
            </div>

            <div class="mb-4 p-4 bg-arq-parchment-dark rounded-lg">
                <h3 class="font-medium text-gray-700 mb-3">Options avancées</h3>
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_announcement" value="1" {{ $post->is_announcement ? 'checked' : '' }}
                            class="rounded border-arq-amber/40 text-arq-forest focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">📢 Annonce système</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_pinned" value="1" {{ $post->is_pinned ? 'checked' : '' }}
                            class="rounded border-arq-amber/40 text-arq-forest focus:ring-indigo-500"
                            data-bascule="#pinned-section">
                        <span class="text-sm text-gray-700">📌 Épingler</span>
                    </label>
                    <select name="pinned_section" id="pinned-section" class="{{ $post->is_pinned ? '' : 'hidden' }} px-3 py-1 border border-arq-amber/40 rounded text-sm">
                        <option value="feed" {{ $post->pinned_section === 'feed' ? 'selected' : '' }}>Feed</option>
                        <option value="library" {{ $post->pinned_section === 'library' ? 'selected' : '' }}>Bibliothèque</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <button type="submit" class="bg-arq-forest text-arq-parchment px-6 py-2 rounded-lg hover:bg-arq-forest-light font-medium">
                    Enregistrer
                </button>
                <a href="{{ route('admin.posts.index') }}" class="text-arq-bark hover:text-gray-700">Annuler</a>
            </div>
        </form>
    </div>
</x-layouts.admin>
