@props(['compact' => false, 'type' => 'reader', 'showLogin' => true])

@php
    $isVip = $type === 'vip';
    $icon = $isVip ? '✨' : '🔮';
    $title = config('tiers.cta.' . $type . '.title');
    $description = config('tiers.cta.' . $type . '.description');
    $buttonText = config('tiers.cta.' . $type . '.button');
    $borderColor = $isVip ? 'border-purple-400/30' : 'border-arq-copper/30';
@endphp

<div class="bg-arq-parchment rounded-organic shadow-parchment {{ $borderColor }} border {{ $compact ? 'p-6' : 'p-8' }} text-center">
    <div class="text-3xl mb-3">{{ $icon }}</div>
    <h3 class="font-serif font-bold text-arq-forest {{ $compact ? 'text-lg' : 'text-xl' }}">{{ $title }}</h3>
    <p class="text-arq-bark mt-2 {{ $compact ? 'text-sm' : '' }} max-w-md mx-auto">{{ $description }}</p>
    <div class="mt-5 flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="{{ route('account.access') }}" class="btn-arq btn-arq-primary">{{ $buttonText }}</a>
        @if($showLogin)
            <a href="{{ route('login') }}" class="text-arq-forest hover:text-arq-forest-light text-sm font-medium">Déjà membre ? Se connecter →</a>
        @endif
    </div>
</div>
