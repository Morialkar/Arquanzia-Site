<x-layouts.admin title="Paramètres">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-arq-bark/50 dark:text-arq-mint/60">Système</p>
            <h1 class="font-serif text-3xl font-semibold text-arq-forest dark:text-arq-mint">Paramètres du site</h1>
        </div>
        <p class="text-sm text-arq-bark/70 dark:text-arq-mint/70">Gestion du logo et alias du site.</p>
    </div>

    <div class="grid gap-6">
        <!-- Logo -->
        <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment p-6 dark:bg-arq-night-card dark:border-arq-mint/20">
            <h2 class="font-serif text-lg font-bold text-arq-forest dark:text-arq-mint mb-4">Logo du site</h2>
            
            <div class="flex flex-col lg:flex-row items-start gap-6">
                <div class="flex-shrink-0 w-full max-w-xs">
                    @if($logoPath)
                        <div class="bg-arq-parchment-dark/90 dark:bg-arq-night rounded-xl p-3 border border-arq-amber/30 dark:border-arq-mint/20">
                            <img src="{{ asset('storage/' . $logoPath) }}?v={{ \App\Models\SiteSetting::getLogoVersion() }}" alt="Logo" class="w-full h-auto object-contain">
                        </div>
                        <form action="{{ route('admin.settings.logo.delete') }}" method="POST" class="mt-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold text-red-600 hover:text-red-500 dark:text-red-300 dark:hover:text-red-200">
                                Supprimer le logo
                            </button>
                        </form>
                    @else
                        <div class="w-full h-20 bg-arq-parchment-dark rounded-xl flex items-center justify-center border border-dashed border-arq-amber/50 dark:border-arq-mint/30">
                            <span class="font-serif text-xl font-bold text-arq-forest dark:text-arq-mint">{{ $siteName }}</span>
                        </div>
                        <p class="text-arq-bark/60 dark:text-arq-mint/60 text-sm mt-2">Aucun logo configuré.</p>
                    @endif
                </div>

                <form action="{{ route('admin.settings.logo') }}" method="POST" enctype="multipart/form-data" class="flex-1 w-full space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-arq-bark/80 dark:text-arq-mint/80 mb-2">Nouveau logo (SVG, PNG, JPG)</label>
                        <input type="file" name="logo" accept=".svg,.png,.jpg,.jpeg,.webp" 
                            class="block w-full text-sm text-arq-bark dark:text-arq-mint file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-arq-forest file:text-arq-parchment hover:file:bg-arq-forest-light dark:file:bg-arq-mint dark:file:text-arq-night dark:hover:file:bg-arq-mint/90
                            border border-arq-amber/40 rounded-lg bg-white dark:bg-arq-night dark:border-arq-mint/30 focus:ring-2 focus:ring-arq-forest dark:focus:ring-arq-mint">
                        <p class="text-arq-bark/60 dark:text-arq-mint/60 text-xs mt-2">Recommandé : SVG (max 2MB) pour une qualité optimale.</p>
                    </div>
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition
                        bg-arq-forest text-arq-parchment hover:bg-arq-forest-light
                        dark:bg-arq-mint/90 dark:text-arq-night dark:hover:bg-arq-mint">
                        Mettre à jour le logo
                    </button>
                </form>
            </div>
        </div>

        <!-- Nom du site -->
        <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment p-6 dark:bg-arq-night-card dark:border-arq-mint/20">
            <h2 class="font-serif text-lg font-bold text-arq-forest dark:text-arq-mint mb-2">Nom du site (fallback)</h2>
            <p class="text-arq-bark/60 dark:text-arq-mint/60 text-sm mb-4">Affiché si aucun logo n’est configuré.</p>
            
            <form action="{{ route('admin.settings.name') }}" method="POST" class="flex flex-col gap-3 sm:flex-row">
                @csrf
                <input type="text" name="site_name" value="{{ $siteName }}" 
                    class="flex-1 px-4 py-3 rounded-lg border transition
                        border-arq-amber/40 bg-white text-arq-ink placeholder:text-arq-bark/50
                        focus:ring-2 focus:ring-arq-forest focus:border-arq-forest/60
                        dark:bg-arq-night dark:border-arq-mint/30 dark:text-arq-mint dark:placeholder:text-arq-mint/50 dark:focus:ring-arq-mint">
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-semibold transition
                    bg-arq-forest text-arq-parchment hover:bg-arq-forest-light
                    dark:bg-arq-mint/90 dark:text-arq-night dark:hover:bg-arq-mint">
                    Enregistrer
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
