<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>تصاميم جاهزة - جوماكيك</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --pink: #D4537E; --pink-dark: #993556; --pink-soft: #FBEAF0; --ink: #4B1528; --muted: #8A6B76; --tile: #F6F0F2; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #fff; color: var(--ink); font-family: 'Tajawal', -apple-system, 'Segoe UI', Tahoma, sans-serif; }
        header { display: flex; align-items: center; justify-content: center; position: relative; padding: 16px; }
        header h1 { margin: 0; font-size: 21px; font-weight: 800; }
        header img { position: absolute; right: 16px; height: 26px; }
        .grid {
            display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px;
            padding: 4px 16px 32px; max-width: 1000px; margin: 0 auto;
        }
        @media (min-width: 760px) { .grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        a.card { display: block; text-decoration: none; color: inherit; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 10px rgba(75,21,40,.07); transition: transform .12s; }
        a.card:active { transform: scale(.98); }
        .card .img { background: var(--tile); aspect-ratio: 1 / 1; display: flex; align-items: center; justify-content: center; }
        .card .img img { width: 100%; height: 100%; object-fit: cover; }
        .card .body { padding: 10px 12px 12px; }
        .card .name { font-size: 16px; font-weight: 800; }
        .card .price { font-size: 14px; font-weight: 800; color: var(--pink-dark); margin-top: 2px; }
        .card.scratch .img { background: var(--pink-soft); flex-direction: column; gap: 8px; color: var(--pink); font-weight: 800; }
        .card.scratch svg { width: 54px; height: 54px; }
    </style>
</head>
<body>
    <header>
        <img src="{{ asset('images/icons/logo.png') }}" alt="JOMACAKE">
        <h1>تصاميم جاهزة</h1>
    </header>

    <main class="grid">
        @foreach ($designs as $design)
            <a class="card" href="{{ route('cake.customize.show', ['slug' => $cake->slug, 'design' => $design['key']]) }}">
                <div class="img"><img src="{{ asset($design['image']) }}" alt="{{ $design['name'] }}" loading="lazy"></div>
                <div class="body">
                    <div class="name">{{ $design['name'] }}</div>
                    <div class="price">{{ $design['price'] }} ر.س</div>
                </div>
            </a>
        @endforeach

        <a class="card scratch" href="{{ route('cake.customize.show', $cake->slug) }}">
            <div class="img"><x-mdi-cake-variant-outline /> صمّم من الصفر</div>
            <div class="body">
                <div class="name">كيكتك على ذوقك</div>
                <div class="price">ابتداءً من {{ collect(config('cake_customizer.sizes'))->min('price') }} ر.س</div>
            </div>
        </a>
    </main>
</body>
</html>
