<x-layouts.admin title="Créer un compte">
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-arq-forest hover:underline">← Retour aux utilisateurs</a>
    </div>

    <div class="max-w-xl">
        <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">Créer un compte</h1>

        <form action="{{ route('admin.users.store') }}" method="POST" class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required 
                    class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="utilisateur@example.com">
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pseudo (optionnel)</label>
                <input type="text" name="handle" value="{{ old('handle') }}" 
                    class="w-full px-3 py-2 border border-arq-amber/40 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Laissez vide pour générer automatiquement">
                @error('handle')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-arq-forest text-arq-parchment px-4 py-2 rounded-lg hover:bg-arq-forest-light">
                    Créer le compte
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
