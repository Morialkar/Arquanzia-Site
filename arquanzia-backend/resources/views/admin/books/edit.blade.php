<x-layouts.admin title="Éditer {{ $book->title }}">
    <div class="mb-6">
        <a href="{{ route('admin.books.index') }}" class="text-arq-forest hover:underline">← Livres</a>
    </div>

    <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">Éditer: {{ $book->title }}</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <form action="{{ route('admin.books.update', $book) }}" method="POST" enctype="multipart/form-data" class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                    <input type="text" name="title" value="{{ old('title', $book->title) }}" required class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Auteur</label>
                    <input type="text" name="author" value="{{ old('author', $book->author) }}" placeholder="Créations Sortilege" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $book->slug) }}" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg font-mono">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description (Markdown)</label>
                    <textarea name="description_md" rows="5" class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg font-mono text-sm">{{ old('description_md', $book->description_md) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Couverture</label>
                    @if($book->cover)
                        <img src="{{ route('media.show', ['media' => $book->cover->id, 'unlocked' => 1]) }}" class="w-24 h-32 object-cover rounded mb-2">
                    @endif
                    <input type="file" name="cover" accept="image/*" class="w-full">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_published" value="1" id="is_published" {{ old('is_published', $book->is_published) ? 'checked' : '' }}>
                    <label for="is_published" class="text-sm text-gray-700">Publié</label>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <button type="submit" class="bg-arq-forest text-arq-parchment px-6 py-2 rounded-lg hover:bg-arq-forest-light">Enregistrer</button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6">
                <h2 class="font-bold text-arq-ink mb-4">📥 Exporter le livre</h2>
                
                @if(session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 p-3 rounded-lg text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if($errors->has('export'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg text-sm">
                        {{ $errors->first('export') }}
                    </div>
                @endif

                <p class="text-arq-bark text-sm mb-4">Génère automatiquement les fichiers EPUB et PDF à partir des chapitres publiés.</p>
                
                <div class="flex gap-2">
                    <form action="{{ route('admin.books.export', $book) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="format" value="epub">
                        <button type="submit" class="px-4 py-2 bg-arq-forest text-arq-parchment rounded-lg hover:bg-arq-forest-light text-sm">
                            Générer EPUB
                        </button>
                    </form>
                    <form action="{{ route('admin.books.export', $book) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="format" value="pdf">
                        <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm">
                            Générer PDF
                        </button>
                    </form>
                </div>
                
                @php
                    $exportService = new \App\Services\BookExportService();
                    $hasEpub = $exportService->exportExists($book, 'epub');
                    $hasPdf = $exportService->exportExists($book, 'pdf');
                @endphp
                
                @if($hasEpub || $hasPdf)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs text-arq-bark mb-2">Fichiers générés:</p>
                        <div class="flex gap-2">
                            @if($hasEpub)
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded">✓ EPUB</span>
                            @endif
                            @if($hasPdf)
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded">✓ PDF</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-arq-ink">Chapitres</h2>
                    <a href="{{ route('admin.chapters.create', $book) }}" class="text-arq-forest hover:underline text-sm">+ Ajouter</a>
                </div>

                @if($book->chapters->isEmpty())
                    <p class="text-arq-bark text-sm">Aucun chapitre</p>
                @else
                    <div class="space-y-2">
                        @foreach($book->chapters as $chapter)
                            <div class="flex items-center justify-between p-2 bg-arq-parchment-dark rounded">
                                <div>
                                    <span class="text-arq-bark/40 text-xs font-mono mr-2">{{ str_pad($chapter->order_index, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="text-sm">{{ $chapter->title }}</span>
                                    @if($chapter->isComingSoon())
                                        <span class="ml-1 text-xs text-arq-bark/40">Bientôt</span>
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
