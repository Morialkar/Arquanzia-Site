<x-layouts.admin title="Paramètres">
    <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">Paramètres du site</h1>

    <div class="grid gap-6">
        <!-- Logo -->
        <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6">
            <h2 class="font-serif text-lg font-bold text-arq-forest mb-4">Logo du site</h2>
            
            <div class="flex items-start gap-6">
                <div class="flex-shrink-0">
                    @if($logoPath)
                        <div class="bg-arq-parchment-dark rounded-lg p-2">
                            <img src="{{ asset('storage/' . $logoPath) }}?v={{ \App\Models\SiteSetting::getLogoVersion() }}" alt="Logo" class="w-32 h-auto">
                        </div>
                        <form action="{{ route('admin.settings.logo.delete') }}" method="POST" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-sm hover:underline">Supprimer le logo</button>
                        </form>
                    @else
                        <div class="w-48 h-16 bg-arq-parchment-dark rounded-lg flex items-center justify-center">
                            <span class="font-serif text-xl font-bold text-arq-forest">{{ $siteName }}</span>
                        </div>
                        <p class="text-arq-bark/60 text-sm mt-2">Aucun logo configuré</p>
                    @endif
                </div>

                <form action="{{ route('admin.settings.logo') }}" method="POST" enctype="multipart/form-data" class="flex-1">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-arq-bark mb-2">Nouveau logo (SVG, PNG, JPG)</label>
                        <input type="file" name="logo" accept=".svg,.png,.jpg,.jpeg,.webp" 
                            class="block w-full text-sm text-arq-bark file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-arq-forest file:text-arq-parchment hover:file:bg-arq-forest-light">
                        <p class="text-arq-bark/60 text-xs mt-1">Recommandé: SVG pour une qualité optimale. Max 2MB.</p>
                    </div>
                    <button type="submit" class="bg-arq-forest text-arq-parchment px-4 py-2 rounded-lg hover:bg-arq-forest-light">
                        Mettre à jour le logo
                    </button>
                </form>
            </div>
        </div>

        <!-- Nom du site -->
        <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6">
            <h2 class="font-serif text-lg font-bold text-arq-forest mb-4">Nom du site (fallback)</h2>
            <p class="text-arq-bark/60 text-sm mb-4">Affiché si aucun logo n'est configuré.</p>
            
            <form action="{{ route('admin.settings.name') }}" method="POST" class="flex gap-4">
                @csrf
                <input type="text" name="site_name" value="{{ $siteName }}" 
                    class="flex-1 px-4 py-2 border border-arq-amber/40 rounded-lg bg-arq-parchment">
                <button type="submit" class="bg-arq-forest text-arq-parchment px-4 py-2 rounded-lg hover:bg-arq-forest-light">
                    Enregistrer
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
