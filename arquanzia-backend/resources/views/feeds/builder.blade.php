<x-layouts.app title="Suivre les parutions — Arquanzia" description="Composez un flux RSS sur mesure pour suivre les parutions d’Arquanzia, sans inscription.">
    <div class="max-w-3xl mx-auto">
        <h1 class="font-serif text-4xl font-bold text-arq-forest mb-3">Suivre les parutions</h1>
        <p class="arq-dim leading-relaxed mb-8">
            Aucun compte, aucune adresse courriel à donner. Copiez une adresse ci-dessous dans votre
            lecteur de flux — Feedly, NetNewsWire, Thunderbird ou un autre — et les nouveautés vous
            parviendront d’elles-mêmes, texte intégral compris.
        </p>

        <div class="card-arq p-6 mb-8">
            <h2 class="font-serif text-xl font-semibold text-arq-forest mb-2">Tout le site</h2>
            <p class="arq-dim text-sm mb-4">Le plus simple : chapitres, fil, encyclopédie et fragments.</p>
            <code class="block break-all rounded-organic-sm bg-arq-parchment-dark/60 px-3 py-2 text-sm">{{ route('feeds.atom') }}</code>
        </div>

        <div class="card-arq p-6">
            <h2 class="font-serif text-xl font-semibold text-arq-forest mb-2">Sur mesure</h2>
            <p class="arq-dim text-sm mb-5">Choisissez ce que vous voulez suivre ; l’adresse se met à jour toute seule.</p>

            @if($books->isNotEmpty())
                <fieldset class="mb-5">
                    <legend class="text-xs uppercase tracking-[0.2em] text-arq-bark/60 mb-2">Livres</legend>
                    <div class="grid sm:grid-cols-2 gap-2">
                        @foreach($books as $book)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" data-flux="livres" value="{{ $book->slug }}" class="rounded border-arq-amber/50 text-arq-forest focus:ring-arq-forest">
                                <span>{{ $book->title }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endif

            <fieldset class="mb-5">
                <legend class="text-xs uppercase tracking-[0.2em] text-arq-bark/60 mb-2">Sections</legend>
                <div class="grid sm:grid-cols-2 gap-2">
                    @foreach($sections as $section)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" data-flux="sections" value="{{ $section }}" class="rounded border-arq-amber/50 text-arq-forest focus:ring-arq-forest">
                            <span>{{ ['fil' => 'Fil d’actualités', 'encyclopedie' => 'Encyclopédie', 'fragments' => 'Fragments'][$section] ?? $section }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <p class="block text-xs uppercase tracking-[0.2em] text-arq-bark/60 mb-2">Votre adresse de flux</p>
            <div class="flex flex-col sm:flex-row gap-2">
                <code id="flux-url" class="flex-1 break-all rounded-organic-sm bg-arq-parchment-dark/60 px-3 py-2 text-sm">{{ route('feeds.atom') }}</code>
                <button type="button" id="flux-copier" class="btn-arq btn-arq-primary shrink-0">Copier</button>
            </div>
            <p id="flux-etat" class="mt-2 text-sm text-arq-forest" role="status" aria-live="polite"></p>
        </div>
    </div>

    <script nonce="{{ csp_nonce() }}">
        (function () {
            var base = @json(route('feeds.atom'));
            var sortie = document.getElementById('flux-url');
            var bouton = document.getElementById('flux-copier');
            var etat = document.getElementById('flux-etat');

            function valeurs(nom) {
                return Array.prototype.slice
                    .call(document.querySelectorAll('input[data-flux="' + nom + '"]:checked'))
                    .map(function (c) { return c.value; })
                    .sort();
            }

            function recomposer() {
                // Le tri et l'ordre des paramètres reproduisent la forme canonique du serveur :
                // une adresse composée ici ne déclenche donc aucune redirection.
                var params = [];
                var livres = valeurs('livres');
                var sections = valeurs('sections');

                if (livres.length) { params.push('livres=' + livres.join(',')); }
                if (sections.length) { params.push('sections=' + sections.join(',')); }

                sortie.textContent = params.length ? base + '?' + params.join('&') : base;
            }

            Array.prototype.forEach.call(document.querySelectorAll('input[data-flux]'), function (c) {
                c.addEventListener('change', recomposer);
            });

            bouton.addEventListener('click', function () {
                var texte = sortie.textContent;

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(texte).then(
                        function () { etat.textContent = 'Adresse copiée.'; },
                        function () { etat.textContent = 'Copie impossible — sélectionnez l’adresse à la main.'; }
                    );
                } else {
                    etat.textContent = 'Copie impossible — sélectionnez l’adresse à la main.';
                }
            });

            recomposer();
        })();
    </script>
</x-layouts.app>
