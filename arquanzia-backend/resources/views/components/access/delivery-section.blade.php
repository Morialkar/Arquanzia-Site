@props(['deliveryEmails', 'deliveryHistory', 'hasReaderAccess', 'userEmail'])

<div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-bold text-gray-800 mb-4">📧 Livraison automatique (Liseuse)</h2>
    <p class="text-gray-600 text-sm mb-4">Recevez les nouveaux chapitres automatiquement par courriel, en pièce jointe PDF ou EPUB.</p>

    @if(session('delivery_success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-3 text-green-700 text-sm">
            {{ session('delivery_success') }}
        </div>
    @endif

    @error('email')
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-3 text-red-700 text-sm">{{ $message }}</div>
    @enderror

    @if(!$hasReaderAccess)
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-amber-700 text-sm mb-4">
            ⚠️ Accès Lecteur requis pour recevoir les livraisons automatiques.
        </div>
    @endif

    @if($deliveryEmails->count() > 0)
        <div class="mb-4 space-y-2">
            @foreach($deliveryEmails as $email)
                <x-access.delivery-email-row :email="$email" />
            @endforeach
        </div>
    @endif

    @if($deliveryEmails->count() < 3)
        <x-access.delivery-add-form :userEmail="$userEmail" />
    @endif

    @if($deliveryHistory->count() > 0)
        <x-access.delivery-history :history="$deliveryHistory" />
    @endif
</div>
