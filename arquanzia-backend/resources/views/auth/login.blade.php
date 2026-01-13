<x-layouts.app title="Entrer - Arquanzia">
    <div class="max-w-md mx-auto mt-8">
        <div class="bg-arq-parchment rounded-organic shadow-parchment border border-arq-amber/20 p-8">
            <div class="text-center mb-6">
                <span class="text-4xl">🔮</span>
                <h1 class="font-serif text-2xl font-bold text-arq-forest mt-3">Entrer dans les Archives</h1>
            </div>

            @if(session('status'))
                <div class="mb-4 p-4 bg-arq-mint/30 text-arq-forest rounded-organic-sm text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-organic-sm text-sm">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.send') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-arq-bark mb-1">Adresse de correspondance</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        required
                        placeholder="votre@email.com"
                        class="w-full px-4 py-3 bg-arq-parchment border border-arq-amber/40 rounded-organic-sm focus:ring-2 focus:ring-arq-forest/30 focus:border-arq-forest"
                    >
                </div>
                <button type="submit" class="btn-arq btn-arq-primary w-full">
                    Recevoir le passage secret
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-arq-bark/60">
                Un lien magique sera envoyé à votre adresse.
            </p>
        </div>
    </div>
</x-layouts.app>
