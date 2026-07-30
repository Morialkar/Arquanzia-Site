<x-layouts.admin title="Éditer {{ $book->title }}">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.books.index') }}" class="text-arq-forest dark:text-arq-mint hover:underline">← Livres</a>
        <a href="{{ route('library.book', ['slug' => $book->slug]) }}" 
           target="_blank"
           class="btn-arq btn-arq-secondary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            Voir le livre
        </a>
    </div>

    <h1 class="font-serif text-2xl font-bold text-arq-forest dark:text-white mb-6">Éditer: {{ $book->title }}</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <form action="{{ route('admin.books.update', $book) }}" method="POST" enctype="multipart/form-data" class="bg-arq-parchment dark:bg-[#1c160f] rounded-xl shadow-parchment border border-arq-amber/20 dark:border-white/10 p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Titre *</label>
                    <input type="text" name="title" value="{{ old('title', $book->title) }}" required class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Auteur</label>
                    <input type="text" name="author" value="{{ old('author', $book->author) }}" placeholder="Créations Sortilege" class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg bg-white dark:bg-black/20 text-arq-ink dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $book->slug) }}"
                        @readonly($book->isSlugLocked())
                        class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg font-mono bg-white dark:bg-black/20 text-arq-ink dark:text-white @if($book->isSlugLocked()) opacity-60 cursor-not-allowed @endif">
                    @if($book->isSlugLocked())
                        <p class="mt-1 text-xs text-arq-bark/60 dark:text-arq-mint/60">
                            🔒 Figé depuis la première publication. L’adresse du livre et de ses chapitres en dépend,
                            ainsi que les flux RSS auxquels des gens sont abonnés : les modifier les casserait sans
                            que personne ne le signale. Le titre, lui, reste modifiable.
                        </p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Description (Markdown)</label>
                    <textarea name="description_md" rows="5" class="w-full px-3 py-2 border border-arq-amber/40 dark:border-white/20 rounded-lg font-mono text-sm bg-white dark:bg-black/20 text-arq-ink dark:text-white">{{ old('description_md', $book->description_md) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-1">Couverture</label>
                    @if($book->cover)
                        <img src="{{ route('media.show', ['media' => $book->cover->id, 'unlocked' => 1]) }}" class="w-24 h-32 object-cover rounded mb-2">
                    @endif
                    <input type="file" name="cover" accept="image/*" class="w-full text-sm text-arq-bark dark:text-white">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_published" value="1" id="is_published" {{ old('is_published', $book->is_published) ? 'checked' : '' }} class="rounded border-arq-amber/40 dark:border-white/30 text-arq-forest focus:ring-arq-forest">
                    <label for="is_published" class="text-sm text-gray-700 dark:text-white/70">Publié</label>
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-white/10">
                    <button type="submit" class="bg-arq-forest text-arq-parchment px-6 py-2 rounded-lg hover:bg-arq-forest-light transition-colors">Enregistrer</button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="bg-arq-parchment dark:bg-[#1c160f] rounded-xl shadow-parchment border border-arq-amber/20 dark:border-white/10 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-arq-ink dark:text-white">Chapitres</h2>
                    <a href="{{ route('admin.chapters.create', $book) }}" class="text-arq-forest hover:underline text-sm">+ Ajouter</a>
                </div>

                @if($book->chapters->isEmpty())
                    <p class="text-arq-bark dark:text-white/70 text-sm">Aucun chapitre</p>
                @else
                    <div class="space-y-2">
                        @foreach($book->chapters as $chapter)
                            <div class="flex items-center justify-between p-2 bg-arq-parchment-dark dark:bg-black/20 rounded">
                                <div class="text-arq-ink dark:text-white">
                                    <span class="text-arq-bark/40 dark:text-white/40 text-xs font-mono mr-2">{{ str_pad($chapter->order_index, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="text-sm">{{ $chapter->title }}</span>
                                    @if($chapter->isComingSoon())
                                        <span class="ml-1 text-xs text-arq-bark/40 dark:text-white/60">Bientôt</span>
                                    @endif
                                </div>
                                <a href="{{ route('admin.chapters.edit', [$book, $chapter]) }}" class="text-arq-forest hover:underline text-xs">Éditer</a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
