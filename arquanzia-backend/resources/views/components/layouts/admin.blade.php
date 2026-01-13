<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin' }} - Arquanzia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'arq': {
                            'mint': '#5FFEB0',
                            'forest': '#0E3B2E',
                            'forest-light': '#1A5742',
                            'ink': '#0E0E0E',
                            'parchment': '#F7F4EC',
                            'parchment-dark': '#EDE8DC',
                            'bark': '#5A4632',
                            'copper': '#B87333',
                            'amber': '#D4A574',
                        }
                    },
                    fontFamily: {
                        'serif': ['Cormorant Garamond', 'Georgia', 'serif'],
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        .admin-card {
            background: #F7F4EC;
            border: 1px solid rgba(212, 165, 116, 0.2);
            border-radius: 0.75rem 0.5rem 0.75rem 0.5rem;
        }
    </style>
</head>
<body class="bg-arq-mint min-h-screen font-sans text-arq-ink">
    @php
        $adminLogoPath = \App\Models\SiteSetting::getLogo();
        $adminLogoVersion = \App\Models\SiteSetting::getLogoVersion();
        $adminSiteName = \App\Models\SiteSetting::getSiteName();
    @endphp
    <nav class="bg-arq-forest text-arq-parchment shadow-lg relative z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-1">
                <a href="{{ route('admin.dashboard') }}" class="mr-4">
                    @if($adminLogoPath)
                        <img src="{{ asset('storage/' . $adminLogoPath) }}?v={{ $adminLogoVersion }}" alt="{{ $adminSiteName }}" class="h-auto w-24">
                    @else
                        <span class="font-serif font-bold text-xl tracking-wide">{{ $adminSiteName }}</span>
                    @endif
                </a>
                @if(session('admin_email'))
                    <div class="flex items-center">
                        <!-- Contenu -->
                        <div class="relative group">
                            <button class="px-3 py-2 text-sm hover:bg-arq-forest-light rounded transition-colors flex items-center gap-1">
                                Contenu <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div class="absolute left-0 top-full pt-1 hidden group-hover:block">
                                <div class="bg-arq-parchment text-arq-ink rounded-lg shadow-lg border border-arq-amber/20 py-1 min-w-[160px]">
                                    <a href="{{ route('admin.posts.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark">📝 Posts</a>
                                    <a href="{{ route('admin.books.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark">📚 Livres</a>
                                    <a href="{{ route('admin.encyclopedia.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark">📖 Encyclopédie</a>
                                </div>
                            </div>
                        </div>

                        <!-- Communauté -->
                        <div class="relative group">
                            <button class="px-3 py-2 text-sm hover:bg-arq-forest-light rounded transition-colors flex items-center gap-1">
                                Communauté <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div class="absolute left-0 top-full pt-1 hidden group-hover:block">
                                <div class="bg-arq-parchment text-arq-ink rounded-lg shadow-lg border border-arq-amber/20 py-1 min-w-[160px]">
                                    <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark">👥 Utilisateurs</a>
                                    <a href="{{ route('admin.admins.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark">🔑 Admins</a>
                                    <a href="{{ route('admin.moderation.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark">🛡️ Modération</a>
                                </div>
                            </div>
                        </div>

                        <!-- Système -->
                        <div class="relative group">
                            <button class="px-3 py-2 text-sm hover:bg-arq-forest-light rounded transition-colors flex items-center gap-1">
                                Système <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div class="absolute left-0 top-full pt-1 hidden group-hover:block">
                                <div class="bg-arq-parchment text-arq-ink rounded-lg shadow-lg border border-arq-amber/20 py-1 min-w-[160px]">
                                    <a href="{{ route('admin.delivery.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark">📬 Livraison</a>
                                    <a href="{{ route('admin.analytics.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark">📊 Analytics</a>
                                    <a href="{{ route('admin.audit.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark">📋 Audit</a>
                                    <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark">⚙️ Paramètres</a>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('feed') }}" class="px-3 py-2 text-sm hover:bg-arq-forest-light rounded transition-colors ml-2">← Site</a>
                    </div>
                @endif
            </div>
            @if(session('admin_email'))
                <div class="flex items-center space-x-4">
                    <span class="text-arq-parchment/70 text-sm">{{ session('admin_email') }}</span>
                    <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-arq-parchment/70 hover:text-arq-mint text-sm transition-colors">Déconnexion</button>
                    </form>
                </div>
            @endif
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-8">
        @if(session('success'))
            <div class="mb-4 p-4 bg-arq-mint/30 text-arq-forest rounded-lg border border-arq-mint">{{ session('success') }}</div>
        @endif
        @if(session('status'))
            <div class="mb-4 p-4 bg-arq-parchment text-arq-forest rounded-lg border border-arq-amber/30">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
