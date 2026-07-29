<x-layouts.app title="Page introuvable — Arquanzia" description="Cette page n'existe pas ou a été déplacée.">
    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="card-arq p-12 text-center max-w-lg w-full">
            <div class="font-serif text-8xl text-arq-amber/60 font-bold mb-4">404</div>
            <div class="divider-elven my-6 max-w-xs mx-auto">❧</div>
            <h1 class="font-serif text-2xl font-bold text-arq-forest mb-3">Parchemin introuvable</h1>
            <p class="arq-dim leading-relaxed mb-8">
                Ce savoir a été perdu, ou n'a jamais existé dans nos archives.
                Le chemin que tu cherches ne mène nulle part.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('home') }}" class="btn-arq btn-arq-primary inline-flex items-center justify-center gap-2">
                    <span>Retour à l'accueil</span>
                    <span aria-hidden="true">→</span>
                </a>
                <a href="{{ route('encyclopedia.index') }}" class="btn-arq btn-arq-secondary inline-flex items-center justify-center gap-2">
                    <span>Encyclopédie</span>
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
