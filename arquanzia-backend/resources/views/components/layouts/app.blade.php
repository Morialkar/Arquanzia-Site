<!DOCTYPE html>
<html lang="fr" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Arquanzia' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        (function() {
            const pref = '{{ session("user_id") ? (optional(\App\Models\User::find(session("user_id")))->theme_pref ?? "system") : "system" }}';
            let theme = pref;
            if (pref === 'system') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.classList.remove('light', 'dark');
            document.documentElement.classList.add(theme);
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'arq': {
                            'mint': '#5FFEB0',
                            'mint-dark': '#4DE89A',
                            'forest': '#0E3B2E',
                            'forest-light': '#1A5742',
                            'ink': '#0E0E0E',
                            'parchment': '#F7F4EC',
                            'parchment-dark': '#EDE8DC',
                            'bark': '#5A4632',
                            'bark-light': '#7A6652',
                            'copper': '#B87333',
                            'amber': '#D4A574',
                            'night': '#1C1714',
                            'night-card': '#2A2420',
                            'night-border': '#4A3F37',
                        }
                    },
                    fontFamily: {
                        'serif': ['Cormorant Garamond', 'Georgia', 'serif'],
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    borderRadius: {
                        'organic': '0.75rem 0.5rem 0.75rem 0.5rem',
                        'organic-sm': '0.5rem 0.25rem 0.5rem 0.25rem',
                    },
                    boxShadow: {
                        'parchment': '0 2px 8px rgba(90, 70, 50, 0.08), 0 1px 2px rgba(90, 70, 50, 0.04)',
                        'parchment-hover': '0 4px 12px rgba(90, 70, 50, 0.12), 0 2px 4px rgba(90, 70, 50, 0.06)',
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        
        /* Textures & backgrounds */
        .bg-parchment-texture {
            background-color: #F7F4EC;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
        }
        
        .bg-mint-field {
            background: linear-gradient(180deg, #5FFEB0 0%, #4DE89A 100%);
        }
        
        /* Locked content overlay */
        .locked-overlay {
            background: linear-gradient(135deg, rgba(14, 59, 46, 0.15) 0%, rgba(90, 70, 50, 0.1) 100%);
            backdrop-filter: blur(2px);
        }
        
        /* Organic separators */
        .separator-organic {
            height: 2px;
            background: linear-gradient(90deg, transparent 0%, #D4A574 20%, #B87333 50%, #D4A574 80%, transparent 100%);
            opacity: 0.4;
        }
        
        /* Grimoire card - organic parchment style with cut corner */
        .card-arq {
            position: relative;
            background: #F7F4EC;
            border: 1px solid rgba(212, 165, 116, 0.4);
            border-radius: 2rem 0 0 0.125rem;
            clip-path: polygon(0 0, calc(100% - 2rem) 0, 100% 2rem, 100% 100%, 0 100%);
            box-shadow: 
                0 2px 4px rgba(90, 70, 50, 0.08),
                0 4px 12px rgba(90, 70, 50, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-arq::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='3'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.02'/%3E%3C/svg%3E");
            pointer-events: none;
        }
        .card-arq:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 4px 8px rgba(90, 70, 50, 0.12),
                0 8px 24px rgba(90, 70, 50, 0.08);
        }
        
        /* Corner flourish decoration */
        .card-arq-flourish::after {
            content: '❧';
            position: absolute;
            bottom: 0.5rem;
            right: 0.75rem;
            font-size: 0.875rem;
            color: rgba(212, 165, 116, 0.4);
            pointer-events: none;
        }
        
        /* Button styles - seal/label inspired */
        .btn-arq {
            position: relative;
            padding: 0.625rem 1.25rem;
            font-weight: 500;
            border-radius: 0.25rem 0.75rem 0.25rem 0.75rem;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }
        .btn-arq-primary {
            background: linear-gradient(135deg, #0E3B2E 0%, #1A5742 100%);
            color: #F7F4EC;
            border-color: rgba(212, 165, 116, 0.3);
            box-shadow: 0 2px 4px rgba(14, 59, 46, 0.3);
        }
        .btn-arq-primary:hover {
            background: linear-gradient(135deg, #1A5742 0%, #0E3B2E 100%);
            box-shadow: 0 3px 8px rgba(14, 59, 46, 0.4);
        }
        .btn-arq-secondary {
            background: linear-gradient(135deg, #F7F4EC 0%, #EDE8DC 100%);
            color: #0E3B2E;
            border: 1px solid #D4A574;
            box-shadow: 0 1px 3px rgba(90, 70, 50, 0.1);
        }
        .btn-arq-secondary:hover {
            background: linear-gradient(135deg, #EDE8DC 0%, #E5DFD3 100%);
            box-shadow: 0 2px 6px rgba(90, 70, 50, 0.15);
        }
        
        /* Elven divider */
        .divider-elven {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: rgba(212, 165, 116, 0.6);
        }
        .divider-elven::before,
        .divider-elven::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(212, 165, 116, 0.4), transparent);
        }
        
        /* Forest badge */
        .badge-arq {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 500;
            background: linear-gradient(135deg, rgba(14, 59, 46, 0.1) 0%, rgba(95, 254, 176, 0.15) 100%);
            color: #0E3B2E;
            border: 1px solid rgba(14, 59, 46, 0.2);
            border-radius: 0.125rem 0.5rem 0.125rem 0.5rem;
        }
        
        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            .card-arq, .btn-arq {
                transition: none;
            }
            .card-arq:hover {
                transform: none;
            }
        }
        
        /* Dark mode overrides - warm brown tones */
        .dark .bg-arq-parchment { background-color: #2A2420; }
        .dark .bg-arq-parchment-dark { background-color: #1C1714; }
        .dark .bg-arq-mint { background-color: #1C1714; }
        .dark .bg-arq-mint\/30 { background-color: rgba(95, 254, 176, 0.12); }
        .dark .bg-arq-amber\/30 { background-color: rgba(212, 165, 116, 0.12); }
        .dark .bg-arq-forest\/10 { background-color: rgba(95, 254, 176, 0.08); }
        .dark .text-arq-ink { color: #E8E4DF; }
        .dark .text-arq-ink\/80 { color: rgba(232, 228, 223, 0.8); }
        .dark .text-arq-forest { color: #5FFEB0; }
        .dark .text-arq-forest-light { color: #7AFFC4; }
        .dark .text-arq-bark { color: #C4B8A8; }
        .dark .text-arq-bark\/50 { color: rgba(196, 184, 168, 0.6); }
        .dark .text-arq-bark\/60 { color: rgba(196, 184, 168, 0.7); }
        .dark .text-arq-bark\/70 { color: rgba(196, 184, 168, 0.8); }
        .dark .text-arq-copper { color: #D4A574; }
        .dark .border-arq-amber\/20 { border-color: rgba(74, 63, 55, 0.6); }
        .dark .border-arq-amber\/30 { border-color: rgba(74, 63, 55, 0.8); }
        .dark .border-arq-amber\/40 { border-color: rgba(74, 63, 55, 1); }
        .dark .border-arq-copper\/30 { border-color: rgba(74, 63, 55, 0.8); }
        .dark .border-arq-copper\/40 { border-color: rgba(74, 63, 55, 1); }
        .dark .shadow-parchment { box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4); }
        
        /* Dark mode cards */
        .dark .card-arq {
            background: #2A2420;
            border-color: rgba(74, 63, 55, 0.6);
            box-shadow: 
                0 2px 4px rgba(0, 0, 0, 0.2),
                0 4px 12px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.03);
        }
        .dark .card-arq:hover {
            box-shadow: 
                0 4px 8px rgba(0, 0, 0, 0.25),
                0 8px 24px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }
        .dark .card-arq-flourish::after { color: rgba(212, 165, 116, 0.25); }
        
        /* Dark mode buttons */
        .dark .btn-arq-primary { 
            background: linear-gradient(135deg, #5FFEB0 0%, #4DE89A 100%); 
            color: #1C1714;
            border-color: rgba(95, 254, 176, 0.3);
        }
        .dark .btn-arq-primary:hover { 
            background: linear-gradient(135deg, #4DE89A 0%, #5FFEB0 100%); 
        }
        .dark .btn-arq-secondary { 
            background: linear-gradient(135deg, #2A2420 0%, #352E28 100%); 
            color: #E8E4DF; 
            border-color: #4A3F37; 
        }
        .dark .btn-arq-secondary:hover { 
            background: linear-gradient(135deg, #352E28 0%, #3D3530 100%); 
        }
        
        /* Dark mode badge */
        .dark .badge-arq {
            background: linear-gradient(135deg, rgba(95, 254, 176, 0.1) 0%, rgba(95, 254, 176, 0.05) 100%);
            color: #5FFEB0;
            border-color: rgba(95, 254, 176, 0.2);
        }
        
        /* Dark mode divider */
        .dark .divider-elven { color: rgba(212, 165, 116, 0.4); }
        .dark .divider-elven::before,
        .dark .divider-elven::after {
            background: linear-gradient(90deg, transparent, rgba(74, 63, 55, 0.6), transparent);
        }
        
        .dark .locked-overlay { background: linear-gradient(135deg, rgba(28,23,20,0.7) 0%, rgba(28,23,20,0.6) 100%); }
        .dark .separator-organic { background: linear-gradient(90deg, transparent 0%, #4A3F37 20%, #5FFEB0 50%, #4A3F37 80%, transparent 100%); }
        .dark .prose { color: #E8E4DF; }
        .dark .prose p { color: #E8E4DF; }
        .dark .prose h1, .dark .prose h2, .dark .prose h3 { color: #5FFEB0; }
        .dark .prose a { color: #5FFEB0; }
        .dark input, .dark textarea, .dark select { background-color: #1C1714; color: #E8E4DF; border-color: #4A3F37; }
        .dark input:focus, .dark textarea:focus, .dark select:focus { border-color: #5FFEB0; }
    </style>
</head>
<body class="bg-arq-mint dark:bg-arq-night min-h-screen font-sans text-arq-ink dark:text-gray-200">
    @php
        $siteLogoPath = \App\Models\SiteSetting::getLogo();
        $siteLogoVersion = \App\Models\SiteSetting::getLogoVersion();
        $siteName = \App\Models\SiteSetting::getSiteName();
    @endphp
    <header class="bg-arq-parchment border-b border-arq-amber/30 shadow-parchment">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('feed') }}" class="flex items-center">
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
                
                <a href="{{ route('feed') }}" class="px-3 py-2 text-sm font-medium rounded-organic-sm transition-colors {{ request()->routeIs('feed') ? 'bg-arq-forest text-arq-parchment' : 'text-arq-bark hover:bg-arq-parchment-dark' }}">Fil</a>
                <a href="{{ route('library.index') }}" class="px-3 py-2 text-sm font-medium rounded-organic-sm transition-colors {{ request()->routeIs('library.*') ? 'bg-arq-forest text-arq-parchment' : 'text-arq-bark hover:bg-arq-parchment-dark' }}">Bibliothèque</a>
                <a href="{{ route('encyclopedia.index') }}" class="px-3 py-2 text-sm font-medium rounded-organic-sm transition-colors {{ request()->routeIs('encyclopedia.*') ? 'bg-arq-forest text-arq-parchment' : 'text-arq-bark hover:bg-arq-parchment-dark' }}">Encyclopédie</a>
                <a href="https://creations-sortilege.com" target="_blank" class="px-3 py-2 text-sm font-medium rounded-organic-sm text-arq-bark hover:bg-arq-parchment-dark transition-colors">Boutique ↗</a>
                
                @if(session('user_id'))
                    @php
                        $currentUser = \App\Models\User::find(session('user_id'));
                        $isAdmin = $currentUser && \App\Models\AdminAllowlist::isAllowed($currentUser->email);
                        $unreadNotifications = $currentUser ? \App\Models\Notification::getUnreadCountForUser($currentUser->id) : 0;
                    @endphp
                    
                    <div class="w-px h-6 bg-arq-amber/30 mx-2"></div>
                    
                    <div class="relative group">
                        <button class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-arq-bark hover:bg-arq-parchment-dark rounded-organic-sm transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            @if($unreadNotifications > 0)
                                <span class="bg-arq-copper text-white text-xs rounded-full w-4 h-4 flex items-center justify-center font-medium">
                                    {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
                                </span>
                            @endif
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="absolute right-0 top-full pt-1 hidden group-hover:block z-50">
                            <div class="bg-arq-parchment border border-arq-amber/30 rounded-organic-sm shadow-parchment py-1 min-w-[160px]">
                                <a href="{{ route('favorites.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-arq-bark hover:bg-arq-parchment-dark">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                    Favoris
                                </a>
                                <a href="{{ route('notifications.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-arq-bark hover:bg-arq-parchment-dark">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    Notifications
                                    @if($unreadNotifications > 0)
                                        <span class="bg-arq-copper text-white text-xs rounded-full px-1.5 py-0.5 font-medium">{{ $unreadNotifications }}</span>
                                    @endif
                                </a>
                                <a href="{{ route('account.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-arq-bark hover:bg-arq-parchment-dark">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                    Mon accès
                                </a>
                                <div class="border-t border-arq-amber/20 my-1"></div>
                                <div class="px-4 py-2">
                                    <div class="text-xs text-arq-bark/60 mb-2">Apparence</div>
                                    <div class="flex gap-1">
                                        <button onclick="setTheme('light')" class="theme-btn flex-1 px-2 py-1 text-xs rounded transition-colors" data-theme="light" title="Jour">
                                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        </button>
                                        <button onclick="setTheme('dark')" class="theme-btn flex-1 px-2 py-1 text-xs rounded transition-colors" data-theme="dark" title="Nuit">
                                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                                        </button>
                                        <button onclick="setTheme('system')" class="theme-btn flex-1 px-2 py-1 text-xs rounded transition-colors" data-theme="system" title="Auto">
                                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        </button>
                                    </div>
                                </div>
                                @if($isAdmin)
                                    <div class="border-t border-arq-amber/20 my-1"></div>
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-arq-bark hover:bg-arq-parchment-dark">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Admin
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-arq btn-arq-primary ml-2">Entrer</a>
                @endif
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
                <a href="{{ route('feed') }}" class="block px-3 py-2 text-sm font-medium rounded-organic-sm {{ request()->routeIs('feed') ? 'bg-arq-forest text-arq-parchment' : 'text-arq-bark' }}">📜 Fil</a>
                <a href="{{ route('library.index') }}" class="block px-3 py-2 text-sm font-medium rounded-organic-sm {{ request()->routeIs('library.*') ? 'bg-arq-forest text-arq-parchment' : 'text-arq-bark' }}">📚 Bibliothèque</a>
                <a href="{{ route('encyclopedia.index') }}" class="block px-3 py-2 text-sm font-medium rounded-organic-sm {{ request()->routeIs('encyclopedia.*') ? 'bg-arq-forest text-arq-parchment' : 'text-arq-bark' }}">📖 Encyclopédie</a>
                <a href="https://creations-sortilege.com" target="_blank" class="block px-3 py-2 text-sm font-medium text-arq-bark">🛒 Boutique ↗</a>
                
                @if(session('user_id'))
                    @php
                        $currentUser = $currentUser ?? \App\Models\User::find(session('user_id'));
                        $isAdmin = $isAdmin ?? ($currentUser && \App\Models\AdminAllowlist::isAllowed($currentUser->email));
                        $unreadNotifications = $unreadNotifications ?? ($currentUser ? \App\Models\Notification::getUnreadCountForUser($currentUser->id) : 0);
                    @endphp
                    <div class="border-t border-arq-amber/20 my-2 pt-2">
                        @if($isAdmin)
                            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 text-sm text-arq-bark">⚙️ Admin</a>
                        @endif
                        <a href="{{ route('notifications.index') }}" class="block px-3 py-2 text-sm text-arq-bark">
                            🔔 Notifications @if($unreadNotifications > 0)<span class="bg-arq-copper text-white text-xs rounded-full px-2 py-0.5 ml-1">{{ $unreadNotifications }}</span>@endif
                        </a>
                        <a href="{{ route('favorites.index') }}" class="block px-3 py-2 text-sm text-arq-bark">❤️ Favoris</a>
                        <a href="{{ route('account.index') }}" class="block px-3 py-2 text-sm font-medium {{ request()->routeIs('account.*') ? 'text-arq-forest' : 'text-arq-bark' }}">👤 Mon accès</a>
                    </div>
                @else
                    <div class="border-t border-arq-amber/20 my-2 pt-2">
                        <a href="{{ route('login') }}" class="block btn-arq btn-arq-primary text-center">Entrer</a>
                    </div>
                @endif
            </div>
        </nav>
    </header>
    
    <script>
        document.getElementById('mobile-menu-toggle')?.addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
    </script>

    <main class="max-w-5xl mx-auto px-4 py-8">
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
            
            // Save to server if logged in
            fetch('/api/theme', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ theme: pref })
            }).catch(() => {});
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
</body>
</html>
