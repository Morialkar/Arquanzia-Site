<x-layouts.app title="Fil - Arquanzia">
    <div class="text-center mb-8">
        <h1 class="font-serif text-3xl font-bold text-arq-forest">Chroniques d'Arquanzia</h1>
        <div class="divider-elven mt-4 max-w-xs mx-auto">❧</div>
    </div>

    @if($pinnedPost)
        @php $unlocked = $pinnedPost->isAccessibleBy($viewer); @endphp
        <article class="card-arq card-arq-flourish border-2 border-arq-copper/40 mb-8 overflow-hidden relative">
            <div class="absolute top-4 right-4 z-10 flex items-center gap-2">
                <span class="px-3 py-1 bg-arq-forest text-arq-parchment text-xs rounded-organic-sm font-medium">📌 Épinglé</span>
                @if($pinnedPost->is_announcement)
                    <span class="px-3 py-1 bg-arq-copper text-white text-xs rounded-organic-sm font-medium">📢 Annonce</span>
                @endif
            </div>
            @if($pinnedPost->media->count() > 0)
                <div class="relative aspect-video bg-arq-parchment-dark">
                    @php $firstMedia = $pinnedPost->media->first(); @endphp
                    <img src="{{ route('media.show', ['media' => $firstMedia->id, 'unlocked' => $unlocked ? 1 : 0]) }}" alt="" class="w-full h-full object-cover">
                    @unless($unlocked)
                        <div class="absolute inset-0 locked-overlay flex items-center justify-center">
                            <span class="bg-arq-forest/80 text-arq-parchment px-4 py-2 rounded-organic-sm text-sm font-medium">Ce savoir est scellé</span>
                        </div>
                    @endunless
                </div>
            @endif
            <div class="p-6">
                <a href="{{ route('post.show', $pinnedPost) }}" class="font-serif text-2xl font-bold text-arq-forest mb-2 block hover:text-arq-forest-light">{{ $pinnedPost->title }}</a>
                <p class="text-arq-bark">{{ $pinnedPost->preview_text }}</p>
            </div>
        </article>
    @endif

    @forelse($posts as $post)
        @php $unlocked = $post->isAccessibleBy($viewer); @endphp
        <article class="card-arq mb-6 overflow-hidden">
            @if($post->media->count() > 0)
                <div class="relative aspect-video bg-arq-parchment-dark">
                    @php $firstMedia = $post->media->first(); @endphp
                    <img src="{{ route('media.show', ['media' => $firstMedia->id, 'unlocked' => $unlocked ? 1 : 0]) }}" alt="" class="w-full h-full object-cover">
                    @unless($unlocked)
                        <div class="absolute inset-0 locked-overlay flex items-center justify-center">
                            <span class="bg-arq-forest/80 text-arq-parchment px-4 py-2 rounded-organic-sm text-sm font-medium">Ce savoir est scellé</span>
                        </div>
                    @endunless
                </div>
            @endif

            <div class="p-6">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-xs font-medium px-2 py-1 rounded-organic-sm 
                        {{ $post->audience === 'public' ? 'bg-arq-mint/30 text-arq-forest' : '' }}
                        {{ $post->audience === 'connected' ? 'bg-arq-forest/10 text-arq-forest' : '' }}
                        {{ $post->audience === 'vip' ? 'bg-purple-100 text-purple-700' : '' }}
                        {{ $post->audience === 'reader' ? 'bg-arq-amber/30 text-arq-bark' : '' }}
                    ">
                        {{ $post->audience === 'public' ? 'Public' : ($post->audience === 'reader' ? config('tiers.labels.reader') : ($post->audience === 'vip' ? config('tiers.labels.vip') : 'Connecté')) }}
                    </span>
                    <span class="text-arq-bark/50 text-sm">{{ $post->created_at->diffForHumans() }}</span>
                </div>

                <a href="{{ route('post.show', $post) }}" class="font-serif text-xl font-bold text-arq-forest mb-2 block hover:text-arq-forest-light">{{ $post->title }}</a>
                <p class="text-arq-bark mb-4">{{ $post->preview_text }}</p>

                @if($unlocked)
                    @if($post->content_full)
                        <div class="text-arq-ink/80 mb-4 leading-relaxed" x-data="{ expanded: false }">
                            <div x-show="!expanded">
                                {{ Str::limit($post->content_full, 450) }}
                                @if(strlen($post->content_full) > 450)
                                    <button @click="expanded = true" class="text-arq-forest hover:underline ml-1 font-medium">Lire la suite</button>
                                @endif
                            </div>
                            <div x-show="expanded" x-cloak>
                                {!! nl2br(e($post->content_full)) !!}
                                <button @click="expanded = false" class="text-arq-forest hover:underline ml-1 block mt-2 font-medium">Réduire</button>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="bg-arq-parchment-dark border border-arq-amber/30 rounded-organic p-6 text-center">
                        <div class="text-3xl mb-3">🔮</div>
                        @if($post->audience === 'connected')
                            <p class="text-arq-bark font-serif text-lg">Entre dans les archives pour découvrir ce contenu</p>
                            <a href="{{ route('login') }}" class="btn-arq btn-arq-primary mt-4 inline-block">Entrer</a>
                        @elseif($post->audience === 'vip')
                            <p class="text-arq-bark font-serif text-lg">{{ config('tiers.cta.vip.locked_message') }}</p>
                            <div class="mt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                                <a href="{{ route('account.access') }}" class="btn-arq btn-arq-secondary">{{ config('tiers.cta.vip.button') }}</a>
                                <a href="{{ route('login') }}" class="text-arq-forest hover:text-arq-forest-light text-sm font-medium">Déjà membre ? Se connecter →</a>
                            </div>
                        @elseif($post->audience === 'reader')
                            <p class="text-arq-bark font-serif text-lg">{{ config('tiers.cta.reader.locked_message') }}</p>
                            <div class="mt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                                <a href="{{ route('account.access') }}" class="btn-arq btn-arq-secondary">{{ config('tiers.cta.reader.button') }}</a>
                                <a href="{{ route('login') }}" class="text-arq-forest hover:text-arq-forest-light text-sm font-medium">Déjà membre ? Se connecter →</a>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="flex items-center justify-between mt-4 pt-4 border-t border-arq-amber/20">
                    <div class="flex items-center gap-3 text-arq-bark/60 text-sm">
                        @php $reactions = $post->reactions()->selectRaw('type, count(*) as count')->groupBy('type')->pluck('count', 'type')->toArray(); @endphp
                        @if(!empty($reactions))
                            @foreach($reactions as $type => $count)
                                <span>{{ $type === 'sparkle' ? '✨' : ($type === 'heart' ? '❤️' : '🔥') }} {{ $count }}</span>
                            @endforeach
                        @endif
                    </div>
                    <a href="{{ route('post.show', $post) }}?viewer={{ $viewer }}" class="text-arq-forest hover:text-arq-forest-light text-sm font-medium">
                        Voir les échanges →
                    </a>
                </div>
            </div>
        </article>
    @empty
        <div class="bg-arq-parchment rounded-organic p-12 text-center">
            <div class="text-4xl mb-4">📜</div>
            <p class="text-arq-bark font-serif text-lg">Les chroniques sont vides pour le moment...</p>
        </div>
    @endforelse

    <div class="mt-8">
        {{ $posts->appends(['viewer' => $viewer])->links() }}
    </div>

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</x-layouts.app>
