<x-layouts.admin title="Connexion">
    <div class="max-w-md mx-auto">
        <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-8">
            <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6 text-center">Connexion Admin</h1>

            <form action="{{ route('admin.login.send') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        required
                        class="w-full px-4 py-2 border border-arq-amber/40 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="votre@email.com"
                    >
                </div>

                <button type="submit" class="w-full bg-arq-forest text-arq-parchment py-2 px-4 rounded-lg hover:bg-arq-forest-light font-medium">
                    Envoyer le lien de connexion
                </button>
            </form>

            <p class="mt-4 text-sm text-arq-bark text-center">
                Un lien de connexion sera envoyé à votre adresse email si elle est autorisée.
            </p>
        </div>
    </div>
</x-layouts.admin>
