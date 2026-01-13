<x-layouts.admin title="Gestion des admins">
    <h1 class="font-serif text-2xl font-bold text-arq-forest mb-6">Gestion des admins</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6">
            <h2 class="text-lg font-bold text-arq-ink mb-4">Admins autorisés</h2>

            <div class="space-y-3">
                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-arq-ink">{{ $rootAdmin }}</div>
                        <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded">Root Admin</span>
                    </div>
                    <span class="text-arq-bark/40 text-sm">Non supprimable</span>
                </div>

                @foreach($admins as $admin)
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                        <div>
                            <div class="font-medium text-arq-ink">{{ $admin->email }}</div>
                            <span class="text-xs {{ $admin->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700' }} px-2 py-0.5 rounded">
                                {{ $admin->role }}
                            </span>
                        </div>
                        @if($isRoot)
                            <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" onsubmit="return confirm('Retirer cet admin ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-sm">Retirer</button>
                            </form>
                        @endif
                    </div>
                @endforeach

                @if($admins->isEmpty())
                    <p class="text-arq-bark py-2">Aucun admin supplémentaire</p>
                @endif
            </div>
        </div>

        @if($isRoot)
            <div class="bg-arq-parchment rounded-xl shadow-parchment border border-arq-amber/20 p-6">
                <h2 class="text-lg font-bold text-arq-ink mb-4">Ajouter un admin</h2>

                <form action="{{ route('admin.admins.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="email" required
                            class="w-full px-4 py-2 border border-arq-amber/40 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
                        <select name="role" id="role" class="w-full px-4 py-2 border border-arq-amber/40 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="editor">Editor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <button type="submit" class="bg-arq-forest text-arq-parchment px-4 py-2 rounded-lg hover:bg-arq-forest-light">
                        Ajouter
                    </button>
                </form>
            </div>
        @else
            <div class="bg-arq-parchment-dark rounded-xl border border-arq-amber/20 p-6 flex items-center justify-center">
                <p class="text-arq-bark">Seul le root admin peut gérer les admins</p>
            </div>
        @endif
    </div>
</x-layouts.admin>
