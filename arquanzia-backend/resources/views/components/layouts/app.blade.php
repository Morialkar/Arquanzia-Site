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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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
        @font-face {
            font-family: 'OpenDyslexic';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('{{ asset('fonts/opendyslexic/OpenDyslexic-Regular.otf') }}') format('opentype');
        }

        @font-face {
            font-family: 'OpenDyslexic';
            font-style: italic;
            font-weight: 400;
            font-display: swap;
            src: url('{{ asset('fonts/opendyslexic/OpenDyslexic-Italic.otf') }}') format('opentype');
        }

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
            --card-border-color: rgba(255, 255, 255, 0.6);
            --card-border-width: 0.5px;
            --card-bg: rgba(247, 244, 236, 0.85);
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: var(--card-border-width) solid var(--card-border-color);
            border-radius: 2rem 0 0 0.125rem;
            clip-path: polygon(0 0, calc(100% - 2rem) 0, 100% 2rem, 100% 100%, 0 100%);
            overflow: visible;
            box-shadow:
                0 4px 24px rgba(90, 70, 50, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        /* Variante avec bordure colorée gauche (encyclopédie) */
        .card-arq-colored {
            clip-path: none !important;
            border-radius: 0 12px 12px 0 !important;
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
                0 4px 12px rgba(90, 70, 50, 0.12),
                0 12px 32px rgba(90, 70, 50, 0.08);
        }
        .dark .card-arq {
            --card-bg: rgba(31, 26, 22, 0.80);
            --card-border-color: rgba(255, 255, 255, 0.08);
            box-shadow:
                0 4px 24px rgba(0, 0, 0, 0.35),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }
        .dark .card-arq:hover {
            box-shadow:
                0 6px 12px rgba(0, 0, 0, 0.5),
                0 16px 40px rgba(0, 0, 0, 0.6);
        }
        .dark .card-arq::before {
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='3'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.06' fill='%23000000'/%3E%3C/svg%3E");
        }
        .card-border-amber {
            --card-border-color: rgba(212, 165, 116, 0.4);
        }
        .card-border-copper {
            --card-border-color: rgba(184, 115, 51, 0.5);
        }
        .card-border-width-2 {
            --card-border-width: 2px;
        }
        @media (max-width: 768px) {
            .card-arq {
                clip-path: none;
                border-radius: 1.5rem;
            }
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
        .dark .card-arq-flourish::after {
            color: rgba(95, 254, 176, 0.25);
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

        .reader-font-option {
            position: relative;
            border-radius: 999px;
            border: 1px solid rgba(14, 59, 46, 0.3);
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #5A4632;
            background: #F7F4EC;
            transition: all 0.2s ease;
        }
        .reader-font-option[data-state="active"] {
            background: #0E3B2E;
            color: #F7F4EC;
            border-color: rgba(212, 165, 116, 0.8);
            box-shadow: 0 4px 12px rgba(14, 59, 46, 0.25);
        }
        .reader-font-option:hover {
            border-color: rgba(14, 59, 46, 0.5);
        }
        .reader-font-option__label {
            font-family: 'OpenDyslexic', 'Inter', system-ui, sans-serif;
            letter-spacing: 0.08em;
            font-size: 0.85rem;
        }
        .dark .reader-font-option {
            background: #2A2420;
            color: #E8E4DF;
            border-color: rgba(95, 254, 176, 0.2);
        }
        .dark .reader-font-option[data-state="active"] {
            background: #5FFEB0;
            color: #1C1714;
            border-color: rgba(95, 254, 176, 0.8);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
        }

        /* Dyslexia-friendly reading */
        .font-reader-dyslexic,
        .font-reader-dyslexic p,
        .font-reader-dyslexic li,
        .font-reader-dyslexic span,
        .font-reader-dyslexic a,
        .font-reader-dyslexic strong,
        .font-reader-dyslexic em {
            font-family: 'OpenDyslexic', 'Inter', system-ui, sans-serif !important;
            letter-spacing: 0.02em;
        }
        .font-reader-dyslexic em,
        .font-reader-dyslexic i {
            font-family: 'OpenDyslexic', 'Inter', system-ui, sans-serif !important;
            font-style: italic;
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
        .dark .bg-arq-mint\/30 { background-color: rgba(95, 254, 176, 0.12); }
        .dark .bg-arq-amber\/30 { background-color: rgba(212, 165, 116, 0.12); }
        .dark .bg-arq-forest\/10 { background-color: rgba(95, 254, 176, 0.08); }
        .dark .text-arq-ink { color: #E8E4DF; }
        .dark .text-arq-ink\/80 { color: rgba(232, 228, 223, 0.8); }
        .dark .text-arq-forest { color: #5FFEB0; }
        .dark .text-arq-forest\/60 { color: rgba(95, 254, 176, 0.7); }
        .dark .text-arq-forest\/70 { color: rgba(95, 254, 176, 0.75); }
        .dark .text-arq-forest\/30 { color: rgba(95, 254, 176, 0.4); }
        .dark .text-arq-forest-light { color: #7AFFC4; }
        /* CSS variables — réactives au thème, aucune dépendance Tailwind */
        /* Valeurs solides (pas de rgba) pour garantir le contraste WCAG AA */
        html {
            --t-dim:   rgb(108, 86, 66);   /* ≈6.1:1 sur parchemin */
            --t-faint: rgb(134, 112, 92);  /* texte secondaire/métadonnées */
            --t-body:  rgb(90, 68, 50);    /* ≈7.5:1 sur parchemin */
            --t-nav:   rgb(90, 70, 50);
        }
        html.dark {
            --t-dim:   rgb(195, 186, 175); /* ≈9.2:1 sur nuit */
            --t-faint: rgb(165, 157, 148); /* ≈6.8:1 sur nuit */
            --t-body:  rgb(220, 212, 203); /* ≈12:1 sur nuit */
            --t-nav:   rgb(210, 202, 192);
        }
        .arq-dim   { color: var(--t-dim)   !important; }
        .arq-faint { color: var(--t-faint) !important; }
        .arq-body  { color: var(--t-body)  !important; }
        .text-arq-bark, [class~="text-arq-bark"] { color: var(--t-nav); }
        .dark .prose { color: #C4B8A8; }
        .dark .prose p, .dark .prose li, .dark .prose blockquote { color: #C4B8A8; }
        .dark .prose h1, .dark .prose h2, .dark .prose h3 { color: #5FFEB0; }
        .dark .prose a { color: #5FFEB0; }
        .dark .text-arq-copper { color: #D4A574; }
        .dark .border-arq-amber\/20 { border-color: rgba(74, 63, 55, 0.6); }
        .dark .border-arq-amber\/30 { border-color: rgba(74, 63, 55, 0.8); }
        .dark .border-arq-amber\/40 { border-color: rgba(74, 63, 55, 1); }
        .dark .border-arq-copper\/30 { border-color: rgba(74, 63, 55, 0.8); }
        .dark .border-arq-copper\/40 { border-color: rgba(74, 63, 55, 1); }
        .dark .shadow-parchment { box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4); }
        header {
            position: sticky !important;
            top: 0 !important;
            z-index: 40 !important;
            background: rgba(255, 255, 255, 0.14) !important;
            backdrop-filter: blur(24px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(24px) saturate(180%) !important;
            border-bottom: 0.5px solid rgba(255, 255, 255, 0.45) !important;
            box-shadow: 0 2px 20px rgba(90, 70, 50, 0.04) !important;
        }
        .dark header {
            background: rgba(5, 4, 3, 0.28) !important;
            backdrop-filter: blur(24px) saturate(160%) !important;
            -webkit-backdrop-filter: blur(24px) saturate(160%) !important;
            border-bottom: 0.5px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.2) !important;
        }
        
        /* Dark mode cards */
        .dark .card-arq {
            --card-border-color: rgba(74, 63, 55, 0.6);
            --card-bg: #2A2420;
            background: var(--card-bg);
            border-color: var(--card-border-color);
            box-shadow: 
                0 2px 4px rgba(0, 0, 0, 0.2),
                0 4px 12px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.03);
        }
        .dark .card-border-amber,
        .dark .card-border-copper {
            --card-border-color: rgba(74, 63, 55, 0.6);
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

        .wikilink-resolved {
            color: #0E3B2E;
            font-weight: 600;
            position: relative;
            text-decoration: none;
            border-bottom: 1px dotted rgba(14, 59, 46, 0.4);
            transition: color 0.2s ease, border-color 0.2s ease;
        }

        .wikilink-resolved::after {
            content: '↗';
            font-size: 0.65em;
            margin-left: 0.2em;
            color: rgba(14, 59, 46, 0.4);
        }

        .wikilink-resolved:hover {
            color: #B87333;
            border-color: rgba(184, 115, 51, 0.7);
        }

        .dark .wikilink-resolved {
            color: #5FFEB0;
            border-color: rgba(95, 254, 176, 0.4);
        }

        .dark .wikilink-resolved::after {
            color: rgba(95, 254, 176, 0.4);
        }

        .dark .wikilink-resolved:hover {
            color: #D4A574;
            border-color: rgba(212, 165, 116, 0.7);
        }

        .wikilink-popover {
            position: absolute;
            z-index: 1000;
            max-width: 280px;
            background: #F7F4EC;
            border: 1px solid rgba(212, 165, 116, 0.4);
            border-radius: 1rem 0 0.75rem 0.25rem;
            box-shadow: 0 10px 35px rgba(14, 59, 46, 0.25);
            padding: 1rem;
            font-size: 0.85rem;
            color: #5A4632;
            pointer-events: none;
            opacity: 0;
            transform: translateY(6px);
            transition: opacity 0.15s ease, transform 0.15s ease;
        }

        .dark .wikilink-popover {
            background: #2A2420;
            border-color: rgba(95, 254, 176, 0.2);
            color: #E8E4DF;
        }

        /* ============================================
           ARQ BACKGROUND ATMOSPHERE
           ============================================ */

        body {
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)' opacity='0.055'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }

        .arq-bg-lights {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }

        #arq-parallax-blobs,
        #arq-parallax-stars {
            position: absolute;
            inset: 0;
            pointer-events: none;
            will-change: transform;
        }

        .arq-bg-lights::before {
            content: '';
            position: absolute;
            top: -100px;
            left: -80px;
            width: 560px;
            height: 560px;
            background: radial-gradient(
                circle,
                rgba(95, 254, 176, 0.45) 0%,
                rgba(95, 254, 176, 0.20) 40%,
                transparent 70%
            );
        }

        .arq-bg-lights::after {
            content: '';
            position: absolute;
            bottom: -80px;
            right: -60px;
            width: 480px;
            height: 480px;
            background: radial-gradient(
                circle,
                rgba(95, 254, 176, 0.38) 0%,
                rgba(95, 254, 176, 0.15) 42%,
                transparent 70%
            );
        }

        .arq-bg-light-mid {
            position: absolute;
            top: 35%;
            right: 6%;
            width: 320px;
            height: 320px;
            background: radial-gradient(
                circle,
                rgba(212, 165, 116, 0.28) 0%,
                rgba(212, 165, 116, 0.10) 45%,
                transparent 70%
            );
            display: block;
        }

        .arq-star {
            position: absolute;
            pointer-events: none;
        }

        .arq-bg-light-warm {
            position: absolute;
            bottom: -60px;
            left: 8%;
            width: 420px;
            height: 420px;
            background: radial-gradient(
                circle,
                rgba(184, 115, 51, 0.18) 0%,
                rgba(212, 165, 116, 0.09) 45%,
                transparent 70%
            );
            display: block;
        }

        .arq-bg-light-mauve {
            position: absolute;
            bottom: -40px;
            left: 20%;
            width: 350px;
            height: 350px;
            background: radial-gradient(
                circle,
                rgba(155, 111, 212, 0.22) 0%,
                transparent 65%
            );
            display: block;
        }

        .arq-bg-light-rose {
            position: absolute;
            top: -40px;
            right: 15%;
            width: 300px;
            height: 300px;
            background: radial-gradient(
                circle,
                rgba(212, 112, 154, 0.18) 0%,
                transparent 65%
            );
            display: block;
        }

        .arq-bg-light-cyan {
            position: absolute;
            top: 45%;
            left: -60px;
            width: 300px;
            height: 300px;
            background: radial-gradient(
                circle,
                rgba(64, 200, 224, 0.22) 0%,
                transparent 65%
            );
            display: block;
        }

        .arq-bg-light-mauve-top {
            position: absolute;
            top: -60px;
            left: 40%;
            width: 350px;
            height: 350px;
            background: radial-gradient(
                circle,
                rgba(155, 111, 212, 0.20) 0%,
                transparent 65%
            );
            display: block;
        }

        .arq-bg-light-rose-bot {
            position: absolute;
            bottom: -60px;
            left: 35%;
            width: 320px;
            height: 320px;
            background: radial-gradient(
                circle,
                rgba(212, 112, 154, 0.20) 0%,
                transparent 65%
            );
            display: block;
        }

        body > *:not(.arq-bg-lights):not(header) {
            position: relative;
            z-index: 1;
        }

        /* ============================================
           DARK MODE — ambiance visuelle
           ============================================ */

        .dark body::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)' opacity='0.08'/%3E%3C/svg%3E");
        }

        .dark .arq-bg-lights::before {
            background: radial-gradient(
                circle,
                rgba(95, 254, 176, 0.30) 0%,
                rgba(95, 254, 176, 0.12) 40%,
                transparent 70%
            );
        }

        .dark .arq-bg-lights::after {
            background: radial-gradient(
                circle,
                rgba(95, 254, 176, 0.24) 0%,
                rgba(95, 254, 176, 0.09) 42%,
                transparent 70%
            );
        }

        .dark .arq-bg-light-mid {
            background: radial-gradient(
                circle,
                rgba(212, 165, 116, 0.22) 0%,
                rgba(212, 165, 116, 0.08) 45%,
                transparent 70%
            );
        }

        .dark .arq-bg-light-warm {
            background: radial-gradient(
                circle,
                rgba(184, 115, 51, 0.20) 0%,
                rgba(212, 165, 116, 0.07) 45%,
                transparent 70%
            );
        }

        .dark .arq-bg-light-mauve {
            background: radial-gradient(circle, rgba(155, 111, 212, 0.28) 0%, transparent 65%);
        }

        .dark .arq-bg-light-rose {
            background: radial-gradient(circle, rgba(212, 112, 154, 0.24) 0%, transparent 65%);
        }

        .dark .arq-bg-light-cyan {
            background: radial-gradient(circle, rgba(64, 200, 224, 0.26) 0%, transparent 65%);
        }
        .dark .arq-bg-light-mauve-top {
            background: radial-gradient(circle, rgba(155, 111, 212, 0.24) 0%, transparent 65%);
        }
        .dark .arq-bg-light-rose-bot {
            background: radial-gradient(circle, rgba(212, 112, 154, 0.22) 0%, transparent 65%);
        }
    </style>
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
