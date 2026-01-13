@props(['userEmail'])

<div class="border-t border-gray-200 pt-4 mt-4">
    <p class="text-sm text-gray-600 mb-3">Ajouter une adresse (max 3)</p>
    <form action="{{ route('delivery.add') }}" method="POST" class="flex flex-col sm:flex-row gap-2">
        @csrf
        <input type="email" name="email" id="delivery-email-input" placeholder="adresse@liseuse.com" required
            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        <select name="format" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="epub">EPUB</option>
            <option value="pdf">PDF</option>
            <option value="both">Les deux</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
            Ajouter
        </button>
    </form>
    <button type="button" onclick="document.getElementById('delivery-email-input').value = '{{ $userEmail }}'" class="mt-2 text-sm text-indigo-600 hover:underline">
        Utiliser l'adresse de mon compte ({{ $userEmail }})
    </button>
</div>
