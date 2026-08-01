@props([
    'title' => 'Arquanzia',
    'description' => null,
    'ogImage' => null,
])
@php
    $siteLogoPath = \App\Models\SiteSetting::getLogo();
    $siteLogoVersion = \App\Models\SiteSetting::getLogoVersion();
    $siteName = \App\Models\SiteSetting::getSiteName();
    $resolvedOgImage = $ogImage ?? ($siteLogoPath ? asset('storage/' . $siteLogoPath) : null);
    $resolvedDescription = $description ?? 'L\'univers de Créations Sortilège — encyclopédie, bibliothèque et atelier.';
@endphp
<!DOCTYPE html>
<html lang="fr" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $resolvedDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">
    {{-- Découverte automatique : les lecteurs de flux proposent l'abonnement dès la page d'accueil. --}}
    <link rel="alternate" type="application/atom+xml" title="Parutions d’Arquanzia" href="{{ route('feeds.atom') }}">

    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $resolvedDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:locale" content="fr_FR">
    @if($resolvedOgImage)
    <meta property="og:image" content="{{ $resolvedOgImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    @endif

    <meta name="twitter:card" content="{{ $resolvedOgImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $resolvedDescription }}">
    @if($resolvedOgImage)
    <meta name="twitter:image" content="{{ $resolvedOgImage }}">
    @endif

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <script>
        (function() {
            const pref = localStorage.getItem('theme_pref') || 'system';
            let theme = pref;
            if (pref === 'system') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.classList.remove('light', 'dark');
            document.documentElement.classList.add(theme);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-arq-parchment dark:bg-arq-night min-h-screen font-sans text-arq-ink dark:text-[#E8E4DF]">
    <div class="arq-bg-lights" aria-hidden="true">
        <div id="arq-parallax-blobs">
            <span class="arq-bg-light-mid"></span>
            <span class="arq-bg-light-warm"></span>
            <span class="arq-bg-light-mauve"></span>
            <span class="arq-bg-light-rose"></span>
            <span class="arq-bg-light-cyan"></span>
            <span class="arq-bg-light-mauve-top"></span>
            <span class="arq-bg-light-rose-bot"></span>
        </div>
        <div id="arq-parallax-stars"></div>
    </div>
    <header class="bg-arq-parchment/95 backdrop-blur-sm border-b border-arq-amber/30 shadow-parchment sticky top-0 z-40">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center">
                @if($siteLogoPath)
                    <img src="{{ asset('storage/' . $siteLogoPath) }}?v={{ $siteLogoVersion }}" alt="{{ $siteName }}" class="w-28 md:w-36 h-auto">
                @else
                    <span class="font-serif text-2xl md:text-3xl font-bold text-arq-forest tracking-wide">{{ $siteName }}</span>
                @endif
            </a>

            <!-- Desktop nav -->
            <nav class="hidden md:flex items-center gap-1">
                <div class="relative">
                    <button type="button" id="search-toggle" class="p-2 text-arq-bark hover:text-arq-forest transition-colors" title="Rechercher dans les archives">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                    <div id="search-container" class="hidden absolute right-0 top-full mt-2 z-50">
                        <form action="{{ route('search') }}" method="GET" id="search-form">
                            <input type="text" name="q" placeholder="Explorer les archives..." autocomplete="off"
                                class="w-64 px-4 py-2 text-sm bg-arq-parchment border border-arq-amber/40 rounded-organic-sm shadow-parchment focus:ring-2 focus:ring-arq-forest/30 focus:border-arq-forest"
                                id="search-input">
                        </form>
                        <div id="search-results" class="hidden mt-1 bg-arq-parchment border border-arq-amber/30 rounded-organic-sm shadow-parchment max-h-64 overflow-y-auto"></div>
                    </div>
                </div>

                <a href="{{ route('library.index') }}" class="px-3 py-2 text-sm font-medium rounded-organic-sm transition-colors {{ request()->routeIs('library.*') ? 'bg-arq-forest text-arq-parchment' : 'text-arq-bark hover:bg-arq-parchment-dark' }}">Bibliothèque</a>
                <a href="{{ route('encyclopedia.index') }}" class="px-3 py-2 text-sm font-medium rounded-organic-sm transition-colors {{ request()->routeIs('encyclopedia.*') ? 'bg-arq-forest text-arq-parchment' : 'text-arq-bark hover:bg-arq-parchment-dark' }}">Encyclopédie</a>
                <a href="{{ route('fragments.index') }}" class="px-3 py-2 text-sm font-medium rounded-organic-sm transition-colors {{ request()->routeIs('fragments.*') ? 'bg-arq-forest text-arq-parchment' : 'text-arq-bark hover:bg-arq-parchment-dark' }}">Fragments</a>
                <a href="https://creations-sortilege.com" target="_blank" class="px-3 py-2 text-sm font-medium rounded-organic-sm text-arq-bark hover:bg-arq-parchment-dark transition-colors">Boutique ↗</a>

                <div class="flex gap-1 ml-2">
                    <button onclick="setTheme('light')" class="theme-btn p-1.5 rounded text-arq-bark hover:bg-arq-parchment-dark transition-colors" data-theme="light" title="Jour">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>
                    <button onclick="setTheme('dark')" class="theme-btn p-1.5 rounded text-arq-bark hover:bg-arq-parchment-dark transition-colors" data-theme="dark" title="Nuit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    </button>
                    <button onclick="setTheme('system')" class="theme-btn p-1.5 rounded text-arq-bark hover:bg-arq-parchment-dark transition-colors" data-theme="system" title="Auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </button>
                </div>
            </nav>

            <!-- Mobile hamburger -->
            <button type="button" id="mobile-menu-toggle" class="md:hidden p-2 text-arq-bark hover:text-arq-forest transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <!-- Mobile menu -->
        <nav id="mobile-menu" class="hidden md:hidden border-t border-arq-amber/20 bg-arq-parchment">
            <div class="px-4 py-3 space-y-1">
                <form action="{{ route('search') }}" method="GET" class="mb-3">
                    <input type="text" name="q" placeholder="Rechercher..." class="w-full px-4 py-2 text-sm bg-arq-parchment-dark border border-arq-amber/40 rounded-organic-sm">
                </form>
                <a href="{{ route('library.index') }}" class="block px-3 py-2 text-sm font-medium rounded-organic-sm {{ request()->routeIs('library.*') ? 'bg-arq-forest text-arq-parchment' : 'text-arq-bark' }}">📚 Bibliothèque</a>
                <a href="{{ route('encyclopedia.index') }}" class="block px-3 py-2 text-sm font-medium rounded-organic-sm {{ request()->routeIs('encyclopedia.*') ? 'bg-arq-forest text-arq-parchment' : 'text-arq-bark' }}">📖 Encyclopédie</a>
                <a href="{{ route('fragments.index') }}" class="block px-3 py-2 text-sm font-medium rounded-organic-sm {{ request()->routeIs('fragments.*') ? 'bg-arq-forest text-arq-parchment' : 'text-arq-bark' }}">🖼️ Fragments</a>
                <a href="https://creations-sortilege.com" target="_blank" class="block px-3 py-2 text-sm font-medium text-arq-bark">🛒 Boutique ↗</a>
                <div class="border-t border-arq-amber/20 my-2 pt-2 flex gap-2 px-3">
                    <button onclick="setTheme('light')" class="theme-btn flex-1 py-1.5 text-xs rounded text-arq-bark transition-colors" data-theme="light">Jour</button>
                    <button onclick="setTheme('dark')" class="theme-btn flex-1 py-1.5 text-xs rounded text-arq-bark transition-colors" data-theme="dark">Nuit</button>
                    <button onclick="setTheme('system')" class="theme-btn flex-1 py-1.5 text-xs rounded text-arq-bark transition-colors" data-theme="system">Auto</button>
                </div>
            </div>
        </nav>
    </header>
    
    <script>
        document.getElementById('mobile-menu-toggle')?.addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
    </script>

    <main class="max-w-5xl mx-auto px-3 sm:px-6 py-8 relative z-10">
        {{ $slot }}
    </main>

    <footer class="mt-16 py-8 text-center">
        <div class="separator-organic max-w-xs mx-auto mb-6"></div>
        <p class="text-arq-forest/60 text-sm font-serif">© {{ date('Y') }} Créations Sortilege</p>
    </footer>

    <script>
        const searchToggle = document.getElementById('search-toggle');
        const searchContainer = document.getElementById('search-container');
        const searchInput = document.getElementById('search-input');
        const searchResults = document.getElementById('search-results');
        let searchTimeout;
        
        searchToggle?.addEventListener('click', (e) => {
            e.stopPropagation();
            searchContainer.classList.toggle('hidden');
            if (!searchContainer.classList.contains('hidden')) {
                searchInput.focus();
            }
        });
        
        searchInput?.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const q = e.target.value.trim();
            
            if (q.length < 2) {
                searchResults.classList.add('hidden');
                return;
            }
            
            searchTimeout = setTimeout(async () => {
                const res = await fetch(`/api/recherche?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                
                if (data.length === 0) {
                    searchResults.innerHTML = '<div class="p-3 text-arq-bark/60 text-sm">Aucun savoir trouvé...</div>';
                } else {
                    searchResults.innerHTML = data.map(r => `
                        <a href="${r.url}" class="flex items-center gap-3 px-3 py-2 hover:bg-arq-parchment-dark border-b border-arq-amber/20 last:border-0">
                            ${r.thumbnail 
                                ? `<div class="w-10 h-10 rounded-organic-sm flex-shrink-0 overflow-hidden bg-arq-parchment-dark"><img src="${r.thumbnail}" class="w-full h-full object-cover scale-150" alt=""></div>` 
                                : `<span class="w-10 h-10 flex items-center justify-center bg-arq-parchment-dark rounded-organic-sm flex-shrink-0 text-sm text-arq-bark">${r.type === 'book' ? '📚' : '📜'}</span>`}
                            <span class="text-sm text-arq-ink truncate">${r.title}</span>
                        </a>
                    `).join('');
                }
                searchResults.classList.remove('hidden');
            }, 200);
        });
        
        document.addEventListener('click', (e) => {
            if (!searchContainer?.contains(e.target) && e.target !== searchToggle) {
                searchContainer?.classList.add('hidden');
                searchResults?.classList.add('hidden');
            }
        });
        
        // Theme switcher
        function setTheme(pref) {
            localStorage.setItem('theme_pref', pref);
            applyTheme(pref);
            updateThemeButtons(pref);
        }
        
        function applyTheme(pref) {
            let theme = pref;
            if (pref === 'system') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.classList.remove('light', 'dark');
            document.documentElement.classList.add(theme);
        }
        
        function updateThemeButtons(pref) {
            document.querySelectorAll('.theme-btn').forEach(btn => {
                const isActive = btn.dataset.theme === pref;
                btn.classList.toggle('bg-arq-forest', isActive);
                btn.classList.toggle('text-arq-parchment', isActive);
                btn.classList.toggle('text-arq-bark', !isActive);
                btn.classList.toggle('hover:bg-arq-parchment-dark', !isActive);
            });
        }
        
        // Init theme buttons on load
        const savedPref = localStorage.getItem('theme_pref') || 'system';
        updateThemeButtons(savedPref);
        
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            const pref = localStorage.getItem('theme_pref') || 'system';
            if (pref === 'system') applyTheme('system');
        });
    </script>

    <script>
        // Parallax starfield — génération et recyclage infinis
        (function () {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            const blobs   = document.getElementById('arq-parallax-blobs');
            const container = document.getElementById('arq-parallax-stars');
            if (!blobs || !container) return;

            const COLORS = ['#9B6FD4','#5FFEB0','#40C8E0','#D4709A','#B87333'];
            const SIZES  = [10, 12, 14, 16, 18, 20, 22, 24, 28];
            const COUNT  = 40;

            const speedMap = {10:0.08,12:0.14,14:0.20,16:0.28,18:0.36,20:0.43,22:0.50,24:0.56,28:0.64};
            const rand = (a, b) => a + Math.random() * (b - a);

            function randomSide() {
                const vw = window.innerWidth;
                // Marge gauche (0–10%) ou droite (88–98%)
                return Math.random() > 0.5
                    ? rand(0, vw * 0.10)
                    : rand(vw * 0.88, vw * 0.97);
            }

            function spawnStar(topPx) {
                const size  = SIZES[Math.floor(Math.random() * SIZES.length)];
                const color = COLORS[Math.floor(Math.random() * COLORS.length)];
                const opa   = rand(0.38, 0.68);
                const leftPx = randomSide();

                const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                svg.setAttribute('viewBox', '0 0 20 20');
                svg.style.cssText = `position:absolute;pointer-events:none;width:${size}px;height:${size}px;opacity:${opa};left:${leftPx}px;top:${topPx}px;`;
                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', 'M10,2 L11.5,7.5 L17,9 L11.5,10.5 L10,16 L8.5,10.5 L3,9 L8.5,7.5Z');
                path.setAttribute('fill', color);
                svg.appendChild(path);
                container.appendChild(svg);

                return { el: svg, top: topPx, speed: speedMap[size] };
            }

            const vh = window.innerHeight;
            const stars = [];

            // Distribution uniforme sur toute la zone (réservoir -600px → bas viewport)
            // Un slot par étoile = espacement garanti, pas de clusters
            const RESERVOIR = 600;
            const totalRange = vh + RESERVOIR;
            const slotSize = totalRange / COUNT;
            // Mélanger l'ordre des slots pour que vitesses et couleurs ne soient pas corrélées à la position
            const slots = Array.from({length: COUNT}, (_, i) => i).sort(() => Math.random() - 0.5);
            for (let i = 0; i < COUNT; i++) {
                const slotTop = -RESERVOIR + slots[i] * slotSize + rand(0, slotSize * 0.85);
                stars.push(spawnStar(slotTop));
            }

            let ticking = false;
            window.addEventListener('scroll', () => {
                if (!ticking) {
                    requestAnimationFrame(() => {
                        const scrollY = window.scrollY;
                        const currentVh = window.innerHeight;

                        blobs.style.transform = `translateY(${scrollY * 0.15}px)`;

                        stars.forEach(star => {
                            const screenY = star.top + scrollY * star.speed;
                            star.el.style.transform = `translateY(${scrollY * star.speed}px)`;

                            // Recyclage : dès que l'étoile passe sous le viewport
                            if (screenY > currentVh + 60) {
                                star.top = rand(-350, -30);
                                star.el.style.top = `${star.top}px`;
                                // Nouvelle position horizontale pour la variété
                                star.el.style.left = `${randomSide()}px`;
                            }
                        });

                        ticking = false;
                    });
                    ticking = true;
                }
            }, { passive: true });
        })();
    </script>

    <script>
        // Wikilink popover
        document.addEventListener('DOMContentLoaded', () => {
            const popover = document.createElement('div');
            popover.className = 'wikilink-popover';
            document.body.appendChild(popover);

            let activeLink = null;

            const hidePopover = () => {
                popover.style.opacity = '0';
                popover.style.transform = 'translateY(6px)';
                activeLink = null;
            };

            const showPopover = (link) => {
                const term = link.dataset.wikilinkTerm || link.textContent.trim();
                const teaser = link.dataset.wikilinkTeaser || '';
                
                popover.innerHTML = `
                    <div class="font-serif text-arq-forest dark:text-arq-mint text-base mb-1 font-semibold">${term}</div>
                    <p class="text-sm leading-relaxed">${teaser || 'Parchemin consultable dans l\'encyclopédie.'}</p>
                `;

                const rect = link.getBoundingClientRect();
                const top = window.scrollY + rect.bottom + 10;
                let left = window.scrollX + rect.left;
                
                // Ensure popover width is calculated
                popover.style.opacity = '0';
                popover.style.display = 'block';
                const popoverWidth = popover.offsetWidth;
                
                const maxLeft = window.scrollX + window.innerWidth - popoverWidth - 20;
                if (left > maxLeft) left = maxLeft;
                if (left < window.scrollX + 20) left = window.scrollX + 20;

                popover.style.left = `${left}px`;
                popover.style.top = `${top}px`;
                popover.style.opacity = '1';
                popover.style.transform = 'translateY(0)';
                activeLink = link;
            };

            document.addEventListener('mouseover', (e) => {
                const link = e.target.closest('.wikilink-resolved');
                if (link) {
                    showPopover(link);
                } else if (!popover.contains(e.target)) {
                    hidePopover();
                }
            });

            document.addEventListener('mouseout', (e) => {
                if (e.target.closest('.wikilink-resolved') && !e.relatedTarget?.closest('.wikilink-popover')) {
                    setTimeout(hidePopover, 100);
                }
            });
        });
    </script>
</body>
</html>
