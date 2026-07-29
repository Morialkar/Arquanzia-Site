<x-layouts.app title="Erreur — Arquanzia" description="Une erreur inattendue s'est produite.">
    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="card-arq p-12 text-center max-w-lg w-full">
            <div class="font-serif text-8xl text-arq-copper/60 font-bold mb-4">500</div>
            <div class="divider-elven my-6 max-w-xs mx-auto">❧</div>
            <h1 class="font-serif text-2xl font-bold text-arq-forest mb-3">Les arcanes se sont brouillées</h1>
            <p class="arq-dim leading-relaxed mb-8">
                Une erreur inattendue s'est produite dans l'atelier.
                Les artisans ont été alertés — réessaie dans quelques instants.
            </p>
            <a href="{{ route('home') }}" class="btn-arq btn-arq-primary inline-flex items-center justify-center gap-2">
                <span>Retour à l'accueil</span>
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>
</x-layouts.app>
