<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title') - جوماكيك</title>
    <style>
        :root {
            --pink: #D4537E;
            --pink-dark: #993556;
            --pink-soft: #FBEAF0;
            --pink-line: #F4C0D1;
            --ink: #4B1528;
            --muted: #8A6B76;
        }
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body { margin: 0; font-family: -apple-system, 'Segoe UI', Tahoma, sans-serif; background: #fff; color: var(--ink); }
        button, input { font-family: inherit; }
        a { color: inherit; }

        .site-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 16px; border-bottom: 1px solid #F3E3E9; background: #fff;
        }
        .site-header .logo { height: 34px; }
        .site-header nav { display: flex; gap: 16px; font-size: 13px; font-weight: 700; }
        .site-header nav a { text-decoration: none; }
        .site-header nav a.active { color: var(--pink); }
        .site-header .cart { display: flex; }
        .sample-tag { text-align: center; font-size: 11px; font-weight: 700; color: var(--pink-dark); background: var(--pink-soft); padding: 4px 10px; }

        .riyal { width: 0.85em; height: 0.85em; vertical-align: -0.08em; margin-inline-start: 2px; }
        .old { color: var(--muted); text-decoration: line-through; font-size: 12px; font-weight: 600; }
        .badge { background: var(--pink); color: #fff; border-radius: 8px; padding: 2px 7px; font-size: 11px; font-weight: 800; white-space: nowrap; }
        .tax { font-size: 10px; font-weight: 700; color: var(--pink-dark); }

        /* أيقونات المواصفات: طبقة، إنش، دور، أشخاص */
        .specs { display: flex; justify-content: space-between; gap: 4px; }
        .spec { display: flex; align-items: center; gap: 3px; font-size: 11px; font-weight: 800; white-space: nowrap; min-width: 0; }
        .spec svg { width: 15px; height: 15px; color: var(--pink); flex-shrink: 0; }
        @yield('styles')
    </style>
</head>
<body>
    @yield('top')
    @yield('content')
    @stack('scripts')
</body>
</html>
