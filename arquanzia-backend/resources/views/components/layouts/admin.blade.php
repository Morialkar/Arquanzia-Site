<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin' }} - Arquanzia</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-arq-mint dark:bg-gray-900 min-h-screen font-sans text-arq-ink dark:text-gray-100">
    @php
        $adminLogoPath = \App\Models\SiteSetting::getLogo();
        $adminLogoVersion = \App\Models\SiteSetting::getLogoVersion();
        $adminSiteName = \App\Models\SiteSetting::getSiteName();
    @endphp
    <nav class="bg-arq-forest text-arq-parchment shadow-lg relative z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex flex-col gap-3">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 flex-1">
                    <a href="{{ route('admin.dashboard') }}" class="mr-2">
                        @if($adminLogoPath)
                            <img src="{{ asset('storage/' . $adminLogoPath) }}?v={{ $adminLogoVersion }}" alt="{{ $adminSiteName }}" class="h-auto w-24">
                        @else
                            <span class="font-serif font-bold text-xl tracking-wide">{{ $adminSiteName }}</span>
                        @endif
                    </a>
                    @if(session('admin_email'))
                        <div class="hidden lg:flex items-center space-x-1">
                            <div class="relative group">
                                <button class="px-3 py-2 text-sm hover:bg-arq-forest-light rounded transition-colors flex items-center gap-1">
                                    Contenu <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div class="absolute left-0 top-full pt-1 hidden group-hover:block">
                                    <div class="bg-arq-parchment dark:bg-gray-800 text-arq-ink dark:text-gray-100 rounded-lg shadow-lg border border-arq-amber/20 dark:border-arq-mint/20 py-1 min-w-[160px]">
                                        <a href="{{ route('admin.posts.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark dark:hover:bg-gray-700">📝 Posts</a>
                                        <a href="{{ route('admin.books.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark dark:hover:bg-gray-700">📚 Livres</a>
                                        <a href="{{ route('admin.encyclopedia.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark dark:hover:bg-gray-700">📖 Encyclopédie</a>
                                        <a href="{{ route('admin.fragments.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark dark:hover:bg-gray-700">🖼️ Fragments</a>
                                        <a href="{{ route('admin.wikilinks.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark dark:hover:bg-gray-700">🔗 Wikilinks</a>
                                    </div>
                                </div>
                            </div>
                            <div class="relative group">
                                <button class="px-3 py-2 text-sm hover:bg-arq-forest-light rounded transition-colors flex items-center gap-1">
                                    Communauté <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div class="absolute left-0 top-full pt-1 hidden group-hover:block">
                                    <div class="bg-arq-parchment dark:bg-gray-800 text-arq-ink dark:text-gray-100 rounded-lg shadow-lg border border-arq-amber/20 dark:border-arq-mint/20 py-1 min-w-[160px]">
                                        <a href="{{ route('admin.admins.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark dark:hover:bg-gray-700">🔑 Admins</a>
                                    </div>
                                </div>
                            </div>
                            <div class="relative group">
                                <button class="px-3 py-2 text-sm hover:bg-arq-forest-light rounded transition-colors flex items-center gap-1">
                                    Système <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div class="absolute left-0 top-full pt-1 hidden group-hover:block">
                                    <div class="bg-arq-parchment dark:bg-gray-800 text-arq-ink dark:text-gray-100 rounded-lg shadow-lg border border-arq-amber/20 dark:border-arq-mint/20 py-1 min-w-[160px]">
                                        <a href="{{ route('admin.analytics.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark dark:hover:bg-gray-700">📊 Analytics</a>
                                        <a href="{{ route('admin.audit.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark dark:hover:bg-gray-700">📋 Audit</a>
                                        <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 text-sm hover:bg-arq-parchment-dark dark:hover:bg-gray-700">⚙️ Paramètres</a>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('home') }}" class="px-3 py-2 text-sm hover:bg-arq-forest-light rounded transition-colors ml-2">← Site</a>
                        </div>
                    @endif
                </div>
                @if(session('admin_email'))
                    <div class="flex items-center gap-2">
                        <button id="theme-toggle" class="px-3 py-2 text-sm hover:bg-arq-forest-light rounded transition-colors">
                            <span id="theme-icon">🌙</span>
                        </button>
                        <span class="hidden lg:inline text-arq-parchment/70 text-sm">{{ session('admin_email') }}</span>
                        <form action="{{ route('admin.logout') }}" method="POST" class="hidden lg:inline">
                            @csrf
                            <button type="submit" class="text-arq-parchment/70 hover:text-arq-mint text-sm transition-colors">Déconnexion</button>
                        </form>
                        <button id="admin-nav-toggle" class="lg:hidden px-3 py-2 border border-arq-parchment/30 rounded-lg text-sm flex items-center gap-2" aria-expanded="false" aria-controls="admin-mobile-nav">
                            ☰ <span class="sr-only">Menu</span>
                        </button>
                    </div>
                @endif
            </div>

            @if(session('admin_email'))
                <div id="admin-mobile-nav" class="lg:hidden hidden border-t border-arq-parchment/20 pt-3 space-y-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-arq-parchment/60">Contenu</p>
                        <div class="mt-2 grid gap-1">
                            <a href="{{ route('admin.posts.index') }}" class="px-3 py-2 rounded-lg bg-white/5 hover:bg-white/10 flex items-center gap-2">📝 Posts</a>
                            <a href="{{ route('admin.books.index') }}" class="px-3 py-2 rounded-lg bg-white/5 hover:bg-white/10 flex items-center gap-2">📚 Livres</a>
                            <a href="{{ route('admin.encyclopedia.index') }}" class="px-3 py-2 rounded-lg bg-white/5 hover:bg-white/10 flex items-center gap-2">📖 Encyclopédie</a>
                            <a href="{{ route('admin.fragments.index') }}" class="px-3 py-2 rounded-lg bg-white/5 hover:bg-white/10 flex items-center gap-2">🖼️ Fragments</a>
                            <a href="{{ route('admin.wikilinks.index') }}" class="px-3 py-2 rounded-lg bg-white/5 hover:bg-white/10 flex items-center gap-2">🔗 Wikilinks</a>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-wide text-arq-parchment/60">Communauté</p>
                        <div class="mt-2 grid gap-1">
                            <a href="{{ route('admin.admins.index') }}" class="px-3 py-2 rounded-lg bg-white/5 hover:bg-white/10 flex items-center gap-2">🔑 Admins</a>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-wide text-arq-parchment/60">Système</p>
                        <div class="mt-2 grid gap-1">
                            <a href="{{ route('admin.analytics.index') }}" class="px-3 py-2 rounded-lg bg-white/5 hover:bg-white/10 flex items-center gap-2">📊 Analytics</a>
                            <a href="{{ route('admin.audit.index') }}" class="px-3 py-2 rounded-lg bg-white/5 hover:bg-white/10 flex items-center gap-2">📋 Audit</a>
                            <a href="{{ route('admin.settings.index') }}" class="px-3 py-2 rounded-lg bg-white/5 hover:bg-white/10 flex items-center gap-2">⚙️ Paramètres</a>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-arq-parchment/10">
                        <a href="{{ route('home') }}" class="px-3 py-2 rounded-lg bg-white/5 hover:bg-white/10 flex items-center gap-2">← Retour au site</a>
                    </div>

                    <div class="pt-2 border-t border-arq-parchment/10 flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-arq-parchment/60">Connecté</p>
                            <p class="text-sm text-arq-parchment">{{ session('admin_email') }}</p>
                        </div>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-2 text-sm bg-white/10 hover:bg-white/20 rounded-lg">Déconnexion</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-8">
        @if(session('success'))
            <div class="mb-4 p-4 bg-arq-mint/30 dark:bg-arq-mint/20 text-arq-forest dark:text-arq-mint rounded-lg border border-arq-mint dark:border-arq-mint/40">{{ session('success') }}</div>
        @endif
        @if(session('status'))
            <div class="mb-4 p-4 bg-arq-parchment dark:bg-gray-800 text-arq-forest dark:text-gray-100 rounded-lg border border-arq-amber/30 dark:border-arq-mint/20">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg border border-red-200 dark:border-red-700/50">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{ $slot }}
    </main>

    <script nonce="{{ csp_nonce() }}">
        // Admin theme toggle - uses same preference as public site
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const html = document.documentElement;
        const mobileNavToggle = document.getElementById('admin-nav-toggle');
        const mobileNav = document.getElementById('admin-mobile-nav');

        function applyTheme(theme) {
            if (theme === 'dark') {
                html.classList.add('dark');
                themeIcon.textContent = '☀️';
            } else {
                html.classList.remove('dark');
                themeIcon.textContent = '🌙';
            }
        }

        // Load saved theme or default to system preference
        const savedTheme = localStorage.getItem('theme_pref') || 'system';
        const effectiveTheme = savedTheme === 'system' 
            ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
            : savedTheme;
        applyTheme(effectiveTheme);

        themeToggle?.addEventListener('click', () => {
            const currentTheme = html.classList.contains('dark') ? 'dark' : 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme_pref', newTheme);
            applyTheme(newTheme);
        });

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            const pref = localStorage.getItem('theme_pref') || 'system';
            if (pref === 'system') {
                const theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                applyTheme(theme);
            }
        });

        // Mobile admin nav toggle
        if (mobileNavToggle && mobileNav) {
            mobileNavToggle.addEventListener('click', () => {
                const expanded = mobileNavToggle.getAttribute('aria-expanded') === 'true';
                mobileNavToggle.setAttribute('aria-expanded', (!expanded).toString());
                mobileNav.classList.toggle('hidden');
            });
        }
    </script>
</body>
</html>
