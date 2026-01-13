<x-layouts.admin title="Éditer {{ $node->title }}">
    <div class="mb-6">
        <a href="{{ route('admin.encyclopedia.index') }}" class="text-arq-forest hover:underline">← Encyclopédie</a>
    </div>

    <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">
        Éditer: {{ $node->title }}
        <span class="text-arq-bark font-normal text-base ml-2">{{ $node->isCategory() ? '📁 Catégorie' : '📄 Article' }}</span>
    </h1>

    <form action="{{ route('admin.encyclopedia.update', ['encyclopedium' => $node]) }}" method="POST" enctype="multipart/form-data" class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                <input type="text" name="title" value="{{ old('title', $node->title) }}" required class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $node->slug) }}" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg font-mono">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Parent</label>
                <select name="parent_id" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg">
                    <option value="">Racine</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('parent_id', $node->parent_id) == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Visibilité *</label>
                <select name="visibility" required class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg">
                    <option value="public" {{ old('visibility', $node->visibility) === 'public' ? 'selected' : '' }}>Public</option>
                    <option value="reader" {{ old('visibility', $node->visibility) === 'reader' ? 'selected' : '' }}>Lecteur uniquement</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ordre</label>
            <input type="number" name="order_index" value="{{ old('order_index', $node->order_index) }}" min="0" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Teaser (Markdown)</label>
            <textarea name="teaser_md" rows="3" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg font-mono text-sm">{{ old('teaser_md', $node->teaser_md) }}</textarea>
        </div>

        @if($node->isArticle())
            <div class="space-y-6 border-t border-gray-200 pt-6">
                <h3 class="font-medium text-arq-ink">Contenu article</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Couverture</label>
                    @if($node->article?->cover)
                        <img src="{{ route('media.show', ['media' => $node->article->cover->id, 'unlocked' => 1]) }}" class="w-32 h-20 object-cover rounded mb-2">
                    @endif
                    <input type="file" name="cover" accept="image/*" class="w-full">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contenu (Markdown)</label>
                    <textarea name="content_md" rows="15" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg font-mono text-sm">{{ old('content_md', $node->article?->content_md) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Galerie</label>
                    @if($node->article?->gallery && $node->article->gallery->count() > 0)
                        <div class="grid grid-cols-4 gap-2 mb-3">
                            @foreach($node->article->gallery as $img)
                                <div class="relative group">
                                    <img src="{{ route('media.show', ['media' => $img->media_id, 'unlocked' => 1]) }}" class="w-full h-20 object-cover rounded">
                                    <form action="{{ route('admin.encyclopedia.gallery.destroy', ['encyclopedium' => $node, 'image' => $img]) }}" method="POST" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 text-white rounded-full w-5 h-5 text-xs hover:bg-red-700">×</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <input type="file" name="gallery[]" accept="image/*" multiple class="w-full">
                    <p class="text-xs text-arq-bark mt-1">Plusieurs images possibles</p>
                </div>
            </div>
        @endif

        <div class="pt-4 border-t border-gray-200">
            <button type="submit" class="bg-arq-forest text-arq-parchment px-6 py-2 rounded-lg hover:bg-arq-forest-light">Enregistrer</button>
        </div>
    </form>
</x-layouts.admin>
