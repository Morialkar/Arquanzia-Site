<x-layouts.admin title="Éditer {{ $chapter->title }}">
    <div class="mb-6">
        <a href="{{ route('admin.books.edit', $book) }}" class="text-arq-forest hover:underline">← {{ $book->title }}</a>
    </div>

    <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">Éditer: {{ $chapter->title }}</h1>

    <form action="{{ route('admin.chapters.update', [$book, $chapter]) }}" method="POST" class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                <input type="text" name="title" value="{{ old('title', $chapter->title) }}" required class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ordre</label>
                <input type="number" name="order_index" value="{{ old('order_index', $chapter->order_index) }}" min="0" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $chapter->slug) }}" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg font-mono">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contenu (Markdown)</label>
            <textarea name="content_md" rows="20" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg font-mono text-sm">{{ old('content_md', $chapter->content_md) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_published" value="1" id="is_published" {{ old('is_published', $chapter->is_published) ? 'checked' : '' }}>
                <label for="is_published" class="text-sm text-gray-700">Publié</label>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date de publication</label>
                <input type="datetime-local" name="published_at" value="{{ old('published_at', $chapter->published_at?->format('Y-m-d\TH:i')) }}" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg">
            </div>
        </div>

        <div class="pt-4 border-t border-gray-200">
            <button type="submit" class="bg-arq-forest text-arq-parchment px-6 py-2 rounded-lg hover:bg-arq-forest-light">Enregistrer</button>
        </div>
    </form>

    <form action="{{ route('admin.chapters.destroy', [$book, $chapter]) }}" method="POST" onsubmit="return confirm('Supprimer ce chapitre ?')" class="mt-4">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-600 hover:underline">Supprimer ce chapitre</button>
    </form>
</x-layouts.admin>
