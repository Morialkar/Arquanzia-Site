<x-layouts.admin title="Notes d’autrice">
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.2em] text-arq-bark/50 dark:text-arq-mint/60">Notes d’autrice</p>
        <h1 class="font-serif text-3xl font-semibold text-arq-forest dark:text-arq-mint">{{ $titre }}</h1>
        <p class="mt-2 text-sm text-arq-bark/70 dark:text-arq-mint/70">
            Une note s’accroche à un paragraphe. Vider le champ la supprime.
        </p>
    </div>

    @if($detachees->isNotEmpty())
        <div class="mb-8 rounded-xl border-2 border-red-500 bg-red-50 dark:bg-red-950/40 p-5">
            <h2 class="font-bold text-red-700 dark:text-red-300 mb-2">
                ⚠️ {{ $detachees->count() }} note{{ $detachees->count() > 1 ? 's' : '' }} détachée{{ $detachees->count() > 1 ? 's' : '' }}
            </h2>
            <p class="text-sm text-red-800 dark:text-red-200 mb-4">
                Le paragraphe commenté a été réécrit : son identifiant a changé et la note ne
                s’affiche plus. Elle est conservée telle quelle — recopiez-la sur le bon
                paragraphe ci-dessous, puis supprimez-la ici.
            </p>

            <ul class="space-y-3">
                @foreach($detachees as $note)
                    <li class="rounded-lg bg-white dark:bg-black/30 border border-red-300 dark:border-red-800 p-3">
                        <div class="prose prose-sm max-w-none dark:prose-invert">{!! $note->note_html !!}</div>
                        <form action="{{ route('admin.notes.destroy', [$type, $notable->getKey(), $note]) }}"
                              method="POST" class="mt-2" data-confirmer="Supprimer définitivement cette note détachée ?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-red-700 dark:text-red-300 hover:underline">
                                Supprimer cette note
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(empty($paragraphes))
        <div class="admin-card p-8 text-center">
            <p class="text-arq-bark/70 dark:text-arq-mint/70">
                Ce texte n’a aucun paragraphe à annoter.
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($paragraphes as $ancre => $texte)
                <div class="admin-card p-5">
                    <p class="text-arq-ink dark:text-gray-100 leading-relaxed">{{ \Illuminate\Support\Str::limit($texte, 320) }}</p>

                    <form action="{{ route('admin.notes.store', [$type, $notable->getKey()]) }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="paragraph_id" value="{{ $ancre }}">
                        <label class="block text-xs uppercase tracking-wide text-arq-bark/60 dark:text-arq-mint/60 mb-1">
                            Note @if($notes->has($ancre))<span class="text-arq-forest dark:text-arq-mint">— enregistrée</span>@endif
                        </label>
                        <textarea name="note_md" rows="3"
                                  placeholder="Ce que vous vouliez dire sur ce passage…"
                                  class="w-full px-3 py-2 border border-arq-amber/40 dark:border-arq-mint/30 rounded-lg font-mono text-sm bg-white dark:bg-arq-night text-arq-ink dark:text-arq-mint">{{ old('note_md', $notes->get($ancre)?->note_md) }}</textarea>
                        <button type="submit" class="mt-2 px-3 py-1.5 rounded-lg text-sm font-semibold bg-arq-forest text-arq-parchment hover:bg-arq-forest-light">
                            Enregistrer
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
