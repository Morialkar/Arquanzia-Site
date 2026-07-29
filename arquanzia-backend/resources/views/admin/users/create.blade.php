<x-layouts.admin title="Créer un compte">
    <div class="mb-8">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-arq-forest dark:text-arq-mint hover:text-arq-emerald transition-colors">
            <span class="text-lg">←</span>
            Retour aux utilisateurs
        </a>
    </div>

    <div class="max-w-2xl space-y-6">
        <div>
            <h1 class="font-serif text-3xl font-bold text-arq-forest dark:text-arq-mint mb-2">Créer un compte</h1>
            <p class="text-sm text-arq-bark/70 dark:text-gray-400 leading-relaxed">
                Ajoutez un membre manuellement pour lui accorder un accès immédiat aux archives. Un pseudo peut être généré automatiquement si vous laissez le champ vide.
            </p>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST"
            class="card-arq card-arq-flourish card-border-amber border border-arq-amber/30 dark:border-arq-emerald/30 shadow-parchment dark:shadow-night-soft p-6 md:p-8 space-y-5">
            @csrf

            <div class="space-y-2">
                <label class="block text-sm font-semibold text-arq-bark dark:text-gray-100">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required 
                    class="w-full px-4 py-2.5 bg-white/90 dark:bg-arq-night-card text-arq-ink dark:text-gray-100 placeholder-arq-bark/40 dark:placeholder-gray-500 border border-arq-amber/40 dark:border-arq-emerald/40 rounded-organic-sm focus:ring-2 focus:ring-arq-forest/40 dark:focus:ring-arq-mint/40 focus:border-arq-forest dark:focus:border-arq-mint transition"
                    placeholder="utilisateur@example.com">
                @error('email')
                    <p class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-semibold text-arq-bark dark:text-gray-100">Pseudo (optionnel)</label>
                <input type="text" name="handle" value="{{ old('handle') }}" 
                    class="w-full px-4 py-2.5 bg-white/90 dark:bg-arq-night-card text-arq-ink dark:text-gray-100 placeholder-arq-bark/40 dark:placeholder-gray-500 border border-arq-amber/40 dark:border-arq-emerald/40 rounded-organic-sm focus:ring-2 focus:ring-arq-forest/40 dark:focus:ring-arq-mint/40 focus:border-arq-forest dark:focus:border-arq-mint transition"
                    placeholder="Laissez vide pour générer automatiquement">
                @error('handle')
                    <p class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 bg-arq-forest text-arq-parchment dark:bg-arq-mint dark:text-arq-night font-semibold px-5 py-3 rounded-organic-md shadow-lg shadow-arq-forest/20 dark:shadow-arq-mint/20 hover:translate-y-0.5 transition-transform">
                    Créer le compte
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
