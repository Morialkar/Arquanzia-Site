<x-layouts.admin title="Éditer {{ $chapter->title }}">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.books.edit', $book) }}" class="text-arq-forest hover:text-arq-forest-light dark:text-arq-mint/80 dark:hover:text-arq-mint">← {{ $book->title }}</a>
        <a href="{{ route('library.chapter', ['book' => $book->slug, 'chapter' => $chapter->slug]) }}" 
           target="_blank"
           class="btn-arq btn-arq-secondary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            Voir le chapitre
        </a>
    </div>

    <h1 class="font-serif text-2xl font-bold text-arq-forest dark:text-arq-mint mb-6">Éditer: {{ $chapter->title }}</h1>

    <form action="{{ route('admin.chapters.update', [$book, $chapter]) }}" method="POST"
        @if($chapter->is_published)
            data-slug-initial="{{ $chapter->slug }}"
        @endif
        class="bg-arq-parchment dark:bg-arq-night-card rounded-xl shadow-parchment dark:shadow-none border border-arq-amber/20 dark:border-arq-mint/20 p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-arq-bark dark:text-arq-mint/80 mb-1">Titre *</label>
                <input type="text" name="title" value="{{ old('title', $chapter->title) }}" required class="w-full px-3 py-2 border border-arq-amber/40 dark:border-arq-mint/30 rounded-lg bg-white dark:bg-arq-night text-arq-ink dark:text-arq-mint focus:outline-none focus:ring-2 focus:ring-arq-forest dark:focus:ring-arq-mint/70">
            </div>
            <div>
                <label class="block text-sm font-medium text-arq-bark dark:text-arq-mint/80 mb-1">Ordre</label>
                <input type="number" name="order_index" value="{{ old('order_index', $chapter->order_index) }}" min="0" class="w-full px-3 py-2 border border-arq-amber/40 dark:border-arq-mint/30 rounded-lg bg-white dark:bg-arq-night text-arq-ink dark:text-arq-mint focus:outline-none focus:ring-2 focus:ring-arq-forest dark:focus:ring-arq-mint/70">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-arq-bark dark:text-arq-mint/80 mb-1">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $chapter->slug) }}" class="w-full px-3 py-2 border border-arq-amber/40 dark:border-arq-mint/30 rounded-lg font-mono bg-white dark:bg-arq-night text-arq-ink dark:text-arq-mint focus:outline-none focus:ring-2 focus:ring-arq-forest dark:focus:ring-arq-mint/70">
            @if($chapter->is_published)
                <p class="mt-1 text-xs text-arq-bark/60 dark:text-arq-mint/60">
                    Modifier le slug change l’adresse du chapitre : les anciens liens cesseront de fonctionner et
                    le chapitre reparaîtra comme une nouveauté dans les flux RSS.
                </p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-arq-bark dark:text-arq-mint/80 mb-1">Contenu (Markdown)</label>
            <textarea name="content_md" rows="20" class="w-full px-3 py-2 border border-arq-amber/40 dark:border-arq-mint/30 rounded-lg font-mono text-sm bg-white dark:bg-arq-night text-arq-ink dark:text-arq-mint focus:outline-none focus:ring-2 focus:ring-arq-forest dark:focus:ring-arq-mint/70">{{ old('content_md', $chapter->content_md) }}</textarea>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_published" value="1" id="is_published" class="h-4 w-4 rounded border-arq-amber/60 dark:border-arq-mint/40 text-arq-forest focus:ring-arq-forest dark:focus:ring-arq-mint" {{ old('is_published', $chapter->is_published) ? 'checked' : '' }}>
            <label for="is_published" class="text-sm text-arq-bark dark:text-arq-mint/80">Publié</label>
        </div>
        <div>
            <label class="block text-sm font-medium text-arq-bark dark:text-arq-mint/80 mb-1">Date de publication</label>
            <input type="datetime-local" name="published_at" value="{{ old('published_at', $chapter->published_at?->format('Y-m-d\TH:i')) }}" class="w-full px-3 py-2 border border-arq-amber/40 dark:border-arq-mint/30 rounded-lg bg-white dark:bg-arq-night text-arq-ink dark:text-arq-mint focus:outline-none focus:ring-2 focus:ring-arq-forest dark:focus:ring-arq-mint/70">
        </div>

        <div class="border-t border-gray-200 dark:border-arq-mint/20 pt-5 space-y-4">
            <h3 class="font-serif font-bold text-arq-forest dark:text-arq-mint">Bannière promotionnelle</h3>
            <p class="text-xs text-arq-bark/60 dark:text-arq-mint/50 -mt-2">Affichée au bas du lecteur, au-dessus de la navigation prev/next.</p>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="promo_banner_enabled" value="1" id="promo_banner_enabled"
                    class="h-4 w-4 rounded border-arq-amber/60 dark:border-arq-mint/40 text-arq-forest focus:ring-arq-forest dark:focus:ring-arq-mint"
                    {{ old('promo_banner_enabled', $chapter->promo_banner_enabled) ? 'checked' : '' }}>
                <label for="promo_banner_enabled" class="text-sm text-arq-bark dark:text-arq-mint/80">Activer la bannière</label>
            </div>

            <div>
                <label class="block text-sm font-medium text-arq-bark dark:text-arq-mint/80 mb-1">Texte</label>
                <textarea name="promo_banner_text" rows="2"
                    class="w-full px-3 py-2 border border-arq-amber/40 dark:border-arq-mint/30 rounded-lg bg-white dark:bg-arq-night text-arq-ink dark:text-arq-mint focus:outline-none focus:ring-2 focus:ring-arq-forest dark:focus:ring-arq-mint/70">{{ old('promo_banner_text', $chapter->promo_banner_text) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-arq-bark dark:text-arq-mint/80 mb-1">Libellé du bouton</label>
                    <input type="text" name="promo_banner_button_label"
                        value="{{ old('promo_banner_button_label', $chapter->promo_banner_button_label) }}"
                        placeholder="ex: Voir la boutique"
                        class="w-full px-3 py-2 border border-arq-amber/40 dark:border-arq-mint/30 rounded-lg bg-white dark:bg-arq-night text-arq-ink dark:text-arq-mint focus:outline-none focus:ring-2 focus:ring-arq-forest dark:focus:ring-arq-mint/70">
                </div>
                <div>
                    <label class="block text-sm font-medium text-arq-bark dark:text-arq-mint/80 mb-1">URL du bouton</label>
                    <input type="url" name="promo_banner_button_url"
                        value="{{ old('promo_banner_button_url', $chapter->promo_banner_button_url) }}"
                        placeholder="https://..."
                        class="w-full px-3 py-2 border border-arq-amber/40 dark:border-arq-mint/30 rounded-lg bg-white dark:bg-arq-night text-arq-ink dark:text-arq-mint focus:outline-none focus:ring-2 focus:ring-arq-forest dark:focus:ring-arq-mint/70">
                    <p class="text-xs text-arq-bark/50 dark:text-arq-mint/50 mt-1">Laisser vide pour afficher le texte seul.</p>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-200 dark:border-arq-mint/20">
            <button type="submit" class="bg-arq-forest text-arq-parchment px-6 py-2 rounded-lg hover:bg-arq-forest-light focus:outline-none focus:ring-2 focus:ring-arq-forest dark:focus:ring-arq-mint/70 dark:bg-arq-mint dark:text-arq-night dark:hover:bg-arq-mint/90">Enregistrer</button>
        </div>
    </form>

    <form action="{{ route('admin.chapters.destroy', [$book, $chapter]) }}" method="POST" data-confirmer="Supprimer ce chapitre ?" class="mt-4">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Supprimer ce chapitre</button>
    </form>
</x-layouts.admin>
