<x-layouts.admin title="Nouveau fragment">
    <div class="mb-6">
        <a href="{{ route('admin.fragments.index') }}" class="text-arq-forest dark:text-arq-mint hover:underline">← Fragments</a>
    </div>

    <h1 class="font-serif text-2xl font-bold text-arq-forest dark:text-white mb-6">
        Nouveau fragment
        @if($parent)
            <span class="text-arq-bark dark:text-arq-mint/80 font-normal">dans {{ $parent->title }}</span>
        @endif
    </h1>

    <form action="{{ route('admin.fragments.store') }}" method="POST" enctype="multipart/form-data"
          class="bg-arq-parchment dark:bg-arq-night-card rounded-xl shadow-parchment dark:shadow-none border border-arq-amber/20 dark:border-white/10 p-6 space-y-6"
          x-data="{ type: '{{ old('type', 'category') }}' }">
        @csrf

        @if($parent)
            <input type="hidden" name="parent_id" value="{{ $parent->id }}">
        @endif

        @if($errors->any())
            <div class="px-4 py-3 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 rounded-lg text-sm">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Titre *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Type *</label>
                <select name="type" x-model="type" required
                        class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
                    <option value="category">📁 Catégorie</option>
                    <option value="item">🖼️ Item</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug') }}" placeholder="auto-généré si vide"
                       class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg font-mono bg-white dark:bg-black/20 text-arq-ink dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Ordre</label>
                <input type="number" name="order_index" value="{{ old('order_index', 0) }}" min="0"
                       class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
            </div>
        </div>

        @if(!$parent)
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Parent</label>
                <select name="parent_id"
                        class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
                    <option value="">Racine</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('parent_id') == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Description (Markdown)</label>
            <textarea name="description_md" rows="3"
                      class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg font-mono text-sm bg-white dark:bg-black/20 text-arq-ink dark:text-white">{{ old('description_md') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Thumbnail</label>
            <input type="file" name="thumbnail" accept="image/*" class="w-full">
            <p class="text-xs text-arq-bark/60 dark:text-arq-mint/60 mt-1">Image d'aperçu (format paysage recommandé)</p>
        </div>

        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}
                       class="rounded border-arq-amber/40 dark:border-white/20">
                <span class="text-sm font-medium text-gray-700 dark:text-white/70">Publié</span>
            </label>
        </div>

        {{-- Champs item uniquement --}}
        <div x-show="type === 'item'" class="space-y-6 border-t border-gray-200 dark:border-white/10 pt-6">
            <h3 class="font-medium text-arq-ink dark:text-white">Contenu media</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Type de media *</label>
                <select name="media_type"
                        class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
                    <option value="image" {{ old('media_type') === 'image' ? 'selected' : '' }}>🖼️ Image</option>
                    <option value="video" {{ old('media_type') === 'video' ? 'selected' : '' }}>🎬 Vidéo</option>
                    <option value="pdf" {{ old('media_type') === 'pdf' ? 'selected' : '' }}>📄 PDF</option>
                    <option value="coloring" {{ old('media_type') === 'coloring' ? 'selected' : '' }}>🎨 Page à colorier</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Fichier media</label>
                <input type="file" name="media_file" class="w-full">
                <p class="text-xs text-arq-bark/60 dark:text-arq-mint/60 mt-1">Image, PDF ou vidéo (max 20 Mo)</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">URL vidéo (YouTube / Vimeo)</label>
                <input type="url" name="video_url" value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=..."
                       class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_downloadable" value="1" {{ old('is_downloadable') ? 'checked' : '' }}
                           class="rounded border-arq-amber/40 dark:border-white/20">
                    <span class="text-sm font-medium text-gray-700 dark:text-white/70">Téléchargeable</span>
                </label>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-200 dark:border-white/10">
            <button type="submit" class="bg-arq-forest text-arq-parchment px-6 py-2 rounded-lg hover:bg-arq-forest-light dark:bg-arq-mint dark:text-arq-night dark:hover:bg-arq-mint-light">Créer</button>
        </div>
    </form>

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</x-layouts.admin>
