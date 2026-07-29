@props([
    'node' => null,
    'parent' => null,
    'categories' => collect(),
    'isEdit' => false
])

<form action="{{ $isEdit ? route('admin.encyclopedia.update', ['encyclopedium' => $node]) : route('admin.encyclopedia.store') }}" 
      method="POST" 
      enctype="multipart/form-data" 
      class="bg-arq-parchment dark:bg-arq-night-card rounded-xl shadow-parchment dark:shadow-none border border-arq-amber/20 dark:border-white/10 p-6 space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    @if($parent && !$isEdit)
        <input type="hidden" name="parent_id" value="{{ $parent->id }}">
    @endif

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Titre *</label>
            <input type="text" 
                   name="title" 
                   value="{{ old('title', $node?->title) }}" 
                   required 
                   class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
        </div>
        @if(!$isEdit)
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Type *</label>
                <select name="type" required class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
                    <option value="category" {{ old('type') === 'category' ? 'selected' : '' }}>📁 Catégorie</option>
                    <option value="article" {{ old('type') === 'article' ? 'selected' : '' }}>📄 Article</option>
                </select>
            </div>
        @else
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Slug</label>
                <input type="text" 
                       name="slug" 
                       value="{{ old('slug', $node?->slug) }}" 
                       class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg font-mono bg-white dark:bg-black/20 text-arq-ink dark:text-white">
            </div>
        @endif
    </div>

    @if($isEdit)
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Parent</label>
                <select name="parent_id" class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
                    <option value="">Racine</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('parent_id', $node?->parent_id) == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Visibilité *</label>
                <select name="visibility" required class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
                    <option value="public" {{ old('visibility', $node?->visibility) === 'public' ? 'selected' : '' }}>Public</option>
                    <option value="reader" {{ old('visibility', $node?->visibility) === 'reader' ? 'selected' : '' }}>Lecteur uniquement</option>
                </select>
            </div>
        </div>
    @else
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Slug</label>
                <input type="text" 
                       name="slug" 
                       value="{{ old('slug') }}" 
                       placeholder="auto-généré si vide" 
                       class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg font-mono bg-white dark:bg-black/20 text-arq-ink dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Visibilité *</label>
                <select name="visibility" required class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
                    <option value="public" {{ old('visibility') === 'public' ? 'selected' : '' }}>Public</option>
                    <option value="reader" {{ old('visibility') === 'reader' ? 'selected' : '' }}>Lecteur uniquement</option>
                </select>
            </div>
        </div>

        @if(!$parent)
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Parent</label>
                <select name="parent_id" class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
                    <option value="">Racine</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->title }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    @endif

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Ordre</label>
        <input type="number" 
               name="order_index" 
               value="{{ old('order_index', $node?->order_index ?? 0) }}" 
               min="0" 
               class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Teaser (Markdown)</label>
        <textarea name="teaser_md" 
                  rows="3" 
                  class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg font-mono text-sm bg-white dark:bg-black/20 text-arq-ink dark:text-white">{{ old('teaser_md', $node?->teaser_md) }}</textarea>
    </div>

    <div id="article-fields" class="space-y-6 border-t border-gray-200 dark:border-white/10 pt-6" @if($isEdit && !$node?->isArticle()) style="display: none;" @endif>
        <h3 class="font-medium text-arq-ink dark:text-white">Contenu article</h3>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Couverture</label>
            @if($isEdit && $node?->article?->cover)
                <img src="{{ route('media.show', ['media' => $node->article->cover->id, 'unlocked' => 1]) }}" class="w-32 h-20 object-cover rounded mb-2">
            @endif
            <input type="file" name="cover" accept="image/*" class="w-full">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Contenu (Markdown)</label>
            <textarea name="content_md" 
                      rows="15" 
                      class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg font-mono text-sm bg-white dark:bg-black/20 text-arq-ink dark:text-white">{{ old('content_md', $node?->article?->content_md) }}</textarea>
        </div>

        @if($isEdit && $node?->article?->gallery && $node->article->gallery->count() > 0)
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Galerie</label>
                <div class="space-y-4 mb-6">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-white/70">Images existantes</h4>
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($node->article->gallery as $img)
                            <div class="border border-arq-amber/30 dark:border-white/20 rounded-lg p-4 bg-arq-parchment/50 dark:bg-black/20">
                                <div class="flex gap-4">
                                    <img src="{{ route('media.show', ['media' => $img->media_id, 'unlocked' => 1]) }}" class="w-24 h-16 object-cover rounded">
                                    <div class="flex-1 space-y-3">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-white/60 mb-1">Légende</label>
                                                <input type="text" 
                                                       name="gallery_caption[{{ $img->id }}]" 
                                                       value="{{ old('gallery_caption.' . $img->id, $img->caption) }}" 
                                                       placeholder="Légende de l'image"
                                                       class="w-full px-2 py-1 text-sm border border-arq-amber/40 dark:border-white/20 rounded bg-white dark:bg-black/20 text-arq-ink dark:text-white">
                                            </div>
                                            <div class="flex items-end gap-2">
                                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-white/70">
                                                    <input type="checkbox" 
                                                           name="gallery_downloadable[{{ $img->id }}]" 
                                                           value="1"
                                                           {{ old('gallery_downloadable.' . $img->id, $img->downloadable) ? 'checked' : '' }}
                                                           class="rounded border-arq-amber/40 dark:border-white/20">
                                                    <span>Téléchargeable</span>
                                                </label>
                                                <button type="button" 
                                                        onclick="if(confirm('Supprimer cette image ?')) { 
                                                            window.location.href='{{ route('admin.encyclopedia.gallery.destroy', ['encyclopedium' => $node, 'image' => $img]) }}?_token={{ csrf_token() }}&_method=DELETE'; 
                                                        }" 
                                                        class="ml-auto bg-red-600 text-white rounded px-2 py-1 text-xs hover:bg-red-700">Supprimer</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            <input type="file" name="gallery[]" accept="image/*" multiple class="w-full">
            <p class="text-xs text-arq-bark mt-1 dark:text-arq-mint/60">Plusieurs images possibles</p>
        </div>
    </div>

    <div class="pt-4 border-t border-gray-200 dark:border-white/10">
        <button type="submit" class="bg-arq-forest text-arq-parchment px-6 py-2 rounded-lg hover:bg-arq-forest-light dark:bg-arq-mint dark:text-arq-night dark:hover:bg-arq-mint-light">
            {{ $isEdit ? 'Enregistrer' : 'Créer' }}
        </button>
    </div>
</form>

@if(!$isEdit)
<script>
    document.querySelector('select[name="type"]').addEventListener('change', function() {
        document.getElementById('article-fields').style.display = this.value === 'article' ? 'block' : 'none';
    });
    document.querySelector('select[name="type"]').dispatchEvent(new Event('change'));
</script>
@endif
