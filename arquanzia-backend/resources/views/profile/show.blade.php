<x-layouts.app title="{{ $profileUser->handle }} - Arquanzia">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-20 h-20 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-3xl font-bold">
                    {{ strtoupper(substr($profileUser->handle, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $profileUser->handle }}</h1>
                    <div class="flex items-center gap-2 mt-2">
                        @foreach($badges as $badge)
                            <span class="px-2 py-1 bg-{{ $badge['color'] }}-100 text-{{ $badge['color'] }}-700 rounded-full text-xs font-medium">
                                {{ $badge['label'] }}
                            </span>
                        @endforeach
                        @if(empty($badges))
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">Membre</span>
                        @endif
                    </div>
                    <p class="text-gray-500 text-sm mt-2">Membre depuis {{ $profileUser->created_at->format('F Y') }}</p>
                </div>
            </div>
        </div>

        @if($recentComments->count() > 0)
            <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">💬 Commentaires récents</h2>
                <div class="space-y-4">
                    @foreach($recentComments as $comment)
                        <div class="border-l-2 border-gray-200 pl-4">
                            <a href="{{ route('post.show', $comment->post) }}" class="text-sm text-indigo-600 hover:underline">
                                {{ $comment->post->title }}
                            </a>
                            <p class="text-gray-700 mt-1">{{ Str::limit($comment->content, 150) }}</p>
                            <span class="text-gray-400 text-xs">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="mt-6 bg-gray-50 rounded-xl p-6 text-center">
                <p class="text-gray-500">Aucune activité publique récente.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
