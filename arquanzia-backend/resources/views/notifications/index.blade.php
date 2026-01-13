<x-layouts.app title="Notifications - Arquanzia">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="font-serif text-3xl font-bold text-arq-forest">🔔 Notifications</h1>
            @if($notifications->where('is_read', false)->count() > 0)
                <form action="{{ route('notifications.markAllRead') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm text-arq-forest hover:text-arq-forest-light font-medium">
                        Tout marquer comme lu
                    </button>
                </form>
            @endif
        </div>

        @if($notifications->isEmpty())
            <div class="bg-arq-parchment rounded-organic p-12 text-center border border-arq-amber/20">
                <span class="text-4xl">🔕</span>
                <p class="text-arq-bark mt-4">Aucune notification pour le moment.</p>
            </div>
        @else
            <div class="bg-arq-parchment rounded-organic shadow-parchment border border-arq-amber/20 divide-y divide-arq-amber/20">
                @foreach($notifications as $notification)
                    <a href="{{ route('notifications.read', $notification) }}" 
                       class="block p-4 hover:bg-arq-parchment-dark transition-colors {{ !$notification->is_read ? 'bg-arq-mint/20' : '' }}">
                        <div class="flex items-start gap-3">
                            <div class="flex-1">
                                <div class="font-medium text-arq-ink">
                                    {{ $notification->getTitle() }}
                                </div>
                                @if($description = $notification->getDescription())
                                    <p class="text-arq-bark text-sm mt-1">{{ $description }}</p>
                                @endif
                                <span class="text-arq-bark/50 text-xs mt-2 block">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>
                            @if(!$notification->is_read)
                                <span class="w-2 h-2 bg-arq-mint rounded-full mt-2"></span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
