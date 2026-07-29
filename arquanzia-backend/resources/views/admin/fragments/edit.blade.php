<x-layouts.admin :title="'Éditer ' . $node->title">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.fragments.index') }}" class="text-arq-forest dark:text-arq-mint hover:underline">← Fragments</a>
        @if($node->is_published)
            <a href="{{ route('fragments.show', $node->getFullPath()) }}" target="_blank"
               class="btn-arq btn-arq-secondary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Voir
            </a>
        @endif
    </div>

    <h1 class="font-serif text-2xl font-bold text-arq-forest dark:text-white mb-6">
        Éditer: {{ $node->title }}
        <span class="text-arq-bark dark:text-arq-mint/80 font-normal text-base ml-2">{{ $node->isCategory() ? '📁 Catégorie' : '🖼️ Item' }}</span>
    </h1>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 rounded-lg">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.fragments.update', $node) }}" method="POST" enctype="multipart/form-data"
          class="bg-arq-parchment dark:bg-arq-night-card rounded-xl shadow-parchment dark:shadow-none border border-arq-amber/20 dark:border-white/10 p-6 space-y-6">
        @csrf
        @method('PUT')

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
                <input type="text" name="title" value="{{ old('title', $node->title) }}" required
                       class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $node->slug) }}"
                       class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg font-mono bg-white dark:bg-black/20 text-arq-ink dark:text-white">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Parent</label>
                <select name="parent_id"
                        class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
                    <option value="">Racine</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('parent_id', $node->parent_id) == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Ordre</label>
                <input type="number" name="order_index" value="{{ old('order_index', $node->order_index) }}" min="0"
                       class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Description (Markdown)</label>
            <textarea name="description_md" rows="3"
                      class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg font-mono text-sm bg-white dark:bg-black/20 text-arq-ink dark:text-white">{{ old('description_md', $node->description_md) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Thumbnail</label>
            @if($node->thumbnail)
                <img src="{{ route('media.show', $node->thumbnail->id) }}" class="w-48 h-32 object-cover rounded mb-2">
            @endif
            <input type="file" name="thumbnail" accept="image/*" class="w-full">
        </div>

        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', $node->is_published) ? 'checked' : '' }}
                       class="rounded border-arq-amber/40 dark:border-white/20">
                <span class="text-sm font-medium text-gray-700 dark:text-white/70">Publié</span>
            </label>
        </div>

        @if($node->isItem())
            <div class="space-y-6 border-t border-gray-200 dark:border-white/10 pt-6">
                <h3 class="font-medium text-arq-ink dark:text-white">Contenu media</h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Type de media</label>
                    <select name="media_type"
                            class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
                        <option value="image" {{ old('media_type', $node->item?->media_type) === 'image' ? 'selected' : '' }}>🖼️ Image</option>
                        <option value="video" {{ old('media_type', $node->item?->media_type) === 'video' ? 'selected' : '' }}>🎬 Vidéo</option>
                        <option value="pdf" {{ old('media_type', $node->item?->media_type) === 'pdf' ? 'selected' : '' }}>📄 PDF</option>
                        <option value="coloring" {{ old('media_type', $node->item?->media_type) === 'coloring' ? 'selected' : '' }}>🎨 Page à colorier</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Fichier media</label>
                    @if($node->item?->media)
                        <p class="text-xs text-arq-bark/60 dark:text-arq-mint/60 mb-2">Fichier actuel : {{ $node->item->media->filename }}</p>
                    @endif
                    <input type="file" name="media_file" class="w-full">
                    <p class="text-xs text-arq-bark/60 dark:text-arq-mint/60 mt-1">Laisser vide pour conserver le fichier existant</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">URL vidéo (YouTube / Vimeo)</label>
                    <input type="url" name="video_url" value="{{ old('video_url', $node->item?->video_url) }}"
                           placeholder="https://www.youtube.com/watch?v=..."
                           class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_downloadable" value="1"
                               {{ old('is_downloadable', $node->item?->is_downloadable) ? 'checked' : '' }}
                               class="rounded border-arq-amber/40 dark:border-white/20">
                        <span class="text-sm font-medium text-gray-700 dark:text-white/70">Téléchargeable</span>
                    </label>
                </div>
            </div>
        @endif

        <div class="pt-4 border-t border-gray-200 dark:border-white/10">
            <button type="submit" class="bg-arq-forest text-arq-parchment px-6 py-2 rounded-lg hover:bg-arq-forest-light dark:bg-arq-mint dark:text-arq-night dark:hover:bg-arq-mint-light">Enregistrer</button>
        </div>
    </form>
</x-layouts.admin>
