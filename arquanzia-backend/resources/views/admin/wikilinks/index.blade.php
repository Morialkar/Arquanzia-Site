<x-layouts.admin title="Wikilinks">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-arq-bark/50 dark:text-arq-mint/60">Système</p>
            <h1 class="font-serif text-3xl font-semibold text-arq-forest dark:text-arq-mint">Wikilinks</h1>
        </div>
        <a href="{{ route('admin.wikilinks.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition
            bg-arq-forest text-arq-parchment hover:bg-arq-forest-light
            dark:bg-arq-mint/90 dark:text-arq-night dark:hover:bg-arq-mint">
            + Nouveau wikilink
        </a>
    </div>

    <p class="text-arq-bark/70 dark:text-arq-mint/70 text-sm mb-4">
        Les wikilinks permettent de lier automatiquement les termes <code class="bg-arq-parchment-dark/80 dark:bg-arq-night px-1 rounded">[[terme]]</code> vers des articles d'encyclopédie.
    </p>

    @if($wikilinks->isEmpty())
        <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment p-8 text-center dark:bg-arq-night-card dark:border-arq-mint/20">
            <p class="text-arq-bark dark:text-arq-mint">Aucun wikilink configuré.</p>
            <p class="text-arq-bark/60 dark:text-arq-mint/60 text-sm mt-2">Les termes seront automatiquement liés aux articles ayant le même titre ou slug.</p>
        </div>
    @else
        <div class="rounded-2xl border border-arq-amber/30 bg-arq-parchment/90 shadow-parchment overflow-hidden dark:bg-arq-night-card dark:border-arq-mint/20">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead class="hidden md:table-header-group bg-arq-parchment-dark border-b border-arq-amber/20 dark:bg-arq-night dark:border-arq-mint/20">
                        <tr>
                            <th class="px-4 py-3 text-left text-arq-bark font-medium dark:text-arq-mint">Terme</th>
                            <th class="px-4 py-3 text-left text-arq-bark font-medium dark:text-arq-mint">Destination</th>
                            <th class="px-4 py-3 text-right text-arq-bark font-medium dark:text-arq-mint">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="block md:table-row-group divide-y md:divide-y divide-transparent md:divide-arq-amber/10 space-y-4 md:space-y-0 dark:md:divide-arq-mint/10">
                        @foreach($wikilinks as $wikilink)
                            <tr class="block md:table-row rounded-xl md:rounded-none shadow-sm md:shadow-none transition bg-white/80 md:bg-transparent hover:bg-arq-parchment-dark/60 dark:bg-arq-night dark:hover:bg-arq-night-border/60">
                                <td class="px-4 py-3 md:px-6 md:py-4 align-top">
                                    <span class="md:hidden text-xs uppercase text-arq-bark/50 dark:text-arq-mint/60 block mb-1">Terme</span>
                                    <code class="bg-arq-parchment-dark/80 dark:bg-arq-night px-2 py-1 rounded text-arq-forest dark:text-arq-mint">[[{{ $wikilink->term }}]]</code>
                                </td>
                                <td class="px-4 py-3 md:px-6 md:py-4 text-arq-bark align-top dark:text-arq-mint">
                                    <span class="md:hidden text-xs uppercase text-arq-bark/50 dark:text-arq-mint/60 block mb-1">Destination</span>
                                    @if($wikilink->encyclopediaNode)
                                        <span class="text-arq-forest dark:text-arq-mint">📜 {{ $wikilink->encyclopediaNode->title }}</span>
                                    @elseif($wikilink->custom_url)
                                        <span class="text-arq-bark/70 dark:text-arq-mint/70 break-words">🔗 {{ Str::limit($wikilink->custom_url, 40) }}</span>
                                    @else
                                        <span class="text-arq-bark/50 dark:text-arq-mint/50">Non défini</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 md:px-6 md:py-4 text-sm md:text-right flex md:block items-center justify-end gap-3">
                                    <span class="md:hidden text-xs uppercase text-arq-bark/50 dark:text-arq-mint/60">Actions</span>
                                    <a href="{{ route('admin.wikilinks.edit', $wikilink) }}" class="text-arq-forest hover:text-arq-forest-light md:mr-3 dark:text-arq-mint dark:hover:text-arq-mint/80">Modifier</a>
                                    <form action="{{ route('admin.wikilinks.destroy', $wikilink) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce wikilink ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold transition
                                            bg-red-100 text-red-700 hover:bg-red-200
                                            dark:bg-red-900/30 dark:text-red-100 dark:hover:bg-red-900/50">
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $wikilinks->links() }}
        </div>
    @endif
</x-layouts.admin>
