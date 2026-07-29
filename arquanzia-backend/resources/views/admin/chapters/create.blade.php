<x-layouts.admin title="Nouveau chapitre - {{ $book->title }}">
    <div class="mb-6">
        <a href="{{ route('admin.books.edit', $book) }}" class="text-arq-forest hover:text-arq-forest-light dark:text-arq-mint/80 dark:hover:text-arq-mint">← {{ $book->title }}</a>
    </div>

    <h1 class="font-serif text-2xl font-bold text-arq-forest dark:text-arq-mint mb-6">Nouveau chapitre</h1>

    <form action="{{ route('admin.chapters.store', $book) }}" method="POST" class="bg-arq-parchment dark:bg-arq-night-card rounded-xl shadow-parchment dark:shadow-none border border-arq-amber/20 dark:border-arq-mint/20 p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-arq-bark dark:text-arq-mint/80 mb-1">Titre *</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="w-full px-3 py-2 border border-arq-amber/40 dark:border-arq-mint/30 rounded-lg bg-white dark:bg-arq-night text-arq-ink dark:text-arq-mint focus:outline-none focus:ring-2 focus:ring-arq-forest dark:focus:ring-arq-mint/70">
            </div>
            <div>
                <label class="block text-sm font-medium text-arq-bark dark:text-arq-mint/80 mb-1">Ordre</label>
                <input type="number" name="order_index" value="{{ old('order_index', $nextOrder) }}" min="0" class="w-full px-3 py-2 border border-arq-amber/40 dark:border-arq-mint/30 rounded-lg bg-white dark:bg-arq-night text-arq-ink dark:text-arq-mint focus:outline-none focus:ring-2 focus:ring-arq-forest dark:focus:ring-arq-mint/70">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-arq-bark dark:text-arq-mint/80 mb-1">Slug</label>
            <input type="text" name="slug" value="{{ old('slug') }}" placeholder="auto-généré si vide" class="w-full px-3 py-2 border border-arq-amber/40 dark:border-arq-mint/30 rounded-lg font-mono bg-white dark:bg-arq-night text-arq-ink dark:text-arq-mint focus:outline-none focus:ring-2 focus:ring-arq-forest dark:focus:ring-arq-mint/70">
        </div>

        <div>
            <label class="block text-sm font-medium text-arq-bark dark:text-arq-mint/80 mb-1">Contenu (Markdown)</label>
            <textarea name="content_md" rows="20" class="w-full px-3 py-2 border border-arq-amber/40 dark:border-arq-mint/30 rounded-lg font-mono text-sm bg-white dark:bg-arq-night text-arq-ink dark:text-arq-mint focus:outline-none focus:ring-2 focus:ring-arq-forest dark:focus:ring-arq-mint/70">{{ old('content_md') }}</textarea>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_published" value="1" id="is_published" class="h-4 w-4 rounded border-arq-amber/60 dark:border-arq-mint/40 text-arq-forest focus:ring-arq-forest dark:focus:ring-arq-mint" {{ old('is_published', true) ? 'checked' : '' }}>
            <label for="is_published" class="text-sm text-arq-bark dark:text-arq-mint/80">Publié</label>
        </div>
        <div>
            <label class="block text-sm font-medium text-arq-bark dark:text-arq-mint/80 mb-1">Date de publication</label>
            <input type="datetime-local" name="published_at" value="{{ old('published_at') }}" class="w-full px-3 py-2 border border-arq-amber/40 dark:border-arq-mint/30 rounded-lg bg-white dark:bg-arq-night text-arq-ink dark:text-arq-mint focus:outline-none focus:ring-2 focus:ring-arq-forest dark:focus:ring-arq-mint/70">
            <p class="text-xs text-arq-bark dark:text-arq-mint/70 mt-1">Laisser vide = immédiat. Futur = "Bientôt"</p>
        </div>

        <div class="pt-4 border-t border-gray-200 dark:border-arq-mint/20">
            <button type="submit" class="bg-arq-forest text-arq-parchment px-6 py-2 rounded-lg hover:bg-arq-forest-light focus:outline-none focus:ring-2 focus:ring-arq-forest dark:focus:ring-arq-mint/70 dark:bg-arq-mint dark:text-arq-night dark:hover:bg-arq-mint/90">Créer</button>
        </div>
    </form>
</x-layouts.admin>
