<x-layouts.app :title="$post->title . ' - Arquanzia'">
    @php $unlocked = $post->isAccessibleBy($viewer); @endphp

    <a href="{{ route('feed') }}?viewer={{ $viewer }}" class="text-arq-forest hover:text-arq-forest-light mb-4 inline-block">← Retour aux chroniques</a>

    <article class="card-arq overflow-hidden">
        @if($post->media->count() > 0)
            <div class="grid gap-2 p-4 {{ $post->media->count() > 1 ? 'grid-cols-2' : '' }}">
                @foreach($post->media as $media)
                    <div class="relative aspect-video bg-arq-parchment-dark rounded-organic-sm overflow-hidden">
                        <img 
                            src="{{ route('media.show', ['media' => $media->id, 'unlocked' => $unlocked ? 1 : 0]) }}"
                            alt=""
                            class="w-full h-full object-cover"
                        >
                        @unless($unlocked)
                            <div class="absolute inset-0 locked-overlay flex items-center justify-center">
                                <span class="bg-arq-forest/80 text-arq-parchment px-3 py-1 rounded-organic-sm text-xs">🔮</span>
                            </div>
                        @endunless
                    </div>
                @endforeach
            </div>
        @endif

        <div class="p-6">
            <div class="flex items-center space-x-2 mb-3">
                <span class="text-xs font-medium px-2 py-1 rounded-organic-sm 
                    {{ $post->audience === 'public' ? 'bg-arq-mint/30 text-arq-forest' : '' }}
                    {{ $post->audience === 'connected' ? 'bg-arq-forest/10 text-arq-forest' : '' }}
                    {{ $post->audience === 'vip' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : '' }}
                    {{ $post->audience === 'reader' ? 'bg-arq-amber/30 text-arq-bark' : '' }}
                ">
                    {{ $post->audience === 'public' ? 'Public' : ($post->audience === 'reader' ? config('tiers.labels.reader') : ($post->audience === 'vip' ? config('tiers.labels.vip') : 'Connecté')) }}
                </span>
                <span class="text-arq-bark/50 text-sm">{{ $post->created_at->format('d M Y à H:i') }}</span>
            </div>

            <h1 class="font-serif text-2xl font-bold text-arq-forest mb-3">{{ $post->title }}</h1>
            <p class="text-arq-bark mb-4 text-lg">{{ $post->preview_text }}</p>

            @if($unlocked)
                @if($post->content_full)
                    <div class="text-arq-ink prose max-w-none leading-relaxed">
                        {!! nl2br(e($post->content_full)) !!}
                    </div>
                @endif
            @else
                <div class="card-arq p-8 text-center">
                    <div class="text-5xl mb-3">🔮</div>
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
        </div>
    </article>

    @if($unlocked)
        <section class="mt-8">
            <h2 class="font-serif text-xl font-bold text-arq-forest mb-4">Commentaires ({{ $post->comments->count() }})</h2>
            
            @if(session('user_id'))
                <form action="{{ route('comments.store', $post) }}" method="POST" class="mb-6">
                    @csrf
                    <div class="bg-arq-parchment rounded-lg border border-arq-amber/20 p-4">
                        <textarea name="body" rows="3" placeholder="Partagez votre pensée..." required
                            class="w-full bg-transparent border-0 focus:ring-0 resize-none text-arq-ink placeholder-arq-bark/50"></textarea>
                        <div class="flex justify-end mt-2">
                            <button type="submit" class="btn-arq btn-arq-primary text-sm">Publier</button>
                        </div>
                    </div>
                </form>
            @else
                <div class="mb-6 text-center py-4 text-arq-bark/60">
                    <a href="{{ route('login') }}" class="text-arq-forest hover:text-arq-forest-light font-medium">Connectez-vous</a> pour laisser un commentaire
                </div>
            @endif

            @if($post->comments->count() > 0)
                <div class="space-y-4">
                    @foreach($post->comments as $comment)
                        <div class="bg-arq-parchment rounded-lg shadow-parchment border border-arq-amber/20 p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-arq-ink">{{ $comment->user->handle ?? 'Anonyme' }}</span>
                                <span class="text-arq-bark/50 text-sm">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-arq-ink">{{ $comment->body }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center py-4 text-arq-bark/60">Aucun commentaire pour le moment</p>
            @endif
        </section>
    @endif
</x-layouts.app>
