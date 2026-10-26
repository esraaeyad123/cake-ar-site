<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $product['name'] ?? 'S.N: '.$product['id'] }} - جوماكيك</title>

    <script type="module" src="https://unpkg.com/@google/model-viewer@4.0.0/dist/model-viewer.min.js"></script>
    <script type="importmap">
    {
        "imports": {
            "three": "https://unpkg.com/three@0.172.0/build/three.module.js",
            "three/addons/": "https://unpkg.com/three@0.172.0/examples/jsm/"
        }
    }
    </script>

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
        body { margin: 0; font-family: -apple-system, 'Segoe UI', Tahoma, sans-serif; background: #FFFFFF; color: var(--ink); }
        button { font-family: inherit; }

        /* هيدر الموقع الرئيسي */
        .site-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 16px; border-bottom: 1px solid #F3E3E9; background: #fff;
        }
        /* الجزء الثابت فوق (الهيدر، المجسم، الملخص): الخيارات تتحرك تحته والكيكة تبقى قدام الزبون */
        .top-fixed { position: sticky; top: 0; z-index: 20; background: #fff; box-shadow: 0 4px 8px -6px rgba(75,21,40,0.18); }
        .site-header .logo { height: 34px; }
        .site-header nav { display: flex; gap: 16px; font-size: 13px; font-weight: 700; }
        .site-header nav a { color: var(--ink); text-decoration: none; }
        .site-header nav a.active { color: var(--pink); }
        .site-header .cart { color: var(--ink); display: flex; }
        .sample-tag {
            text-align: center; font-size: 11px; font-weight: 700; color: var(--pink-dark);
            background: var(--pink-soft); padding: 4px 10px;
        }

        /* المجسم */
        .viewer-wrap { position: relative; background: #fff; }
        model-viewer { width: 100%; height: 30vh; min-height: 200px; max-height: 300px; background-color: #FFFFFF; }
        .viewer-status {
            position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: var(--pink-dark); pointer-events: none;
        }
        .viewer-status[hidden] { display: none; }
        .float-card {
            position: absolute; background: #fff; border: 1.5px solid var(--pink-line);
            border-radius: 14px; padding: 7px 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); text-align: center;
        }
        .price-card { top: 12px; right: 12px; }
        .price-card .amount { font-size: 18px; font-weight: 800; white-space: nowrap; }
        .float-card .caption { font-size: 10px; font-weight: 700; color: var(--pink-dark); }
        .riyal { width: 0.85em; height: 0.85em; vertical-align: -0.08em; margin-inline-start: 2px; }

        /* ملخص الاختيار تحت المجسم + زر AR */
        .spec-bar { display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-top: 1px solid #F3E3E9; }
        .specs { flex: 1; min-width: 0; display: flex; justify-content: space-between; gap: 4px; background: #FAF5F7; border-radius: 14px; padding: 8px; }
        .spec { display: flex; align-items: center; gap: 3px; font-size: 11px; font-weight: 800; white-space: nowrap; min-width: 0; }
        .spec svg { width: 18px; height: 18px; color: var(--pink); flex-shrink: 0; }
        .ar-btn {
            background: var(--pink); color: #fff; border: none; border-radius: 22px; padding: 9px 13px;
            font-size: 14px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; flex-shrink: 0;
        }
        .ar-btn svg { width: 18px; height: 18px; }

        /* شريط التبويبات */
        .tabs {
            display: grid; grid-template-columns: repeat(5, 1fr); background: #fff;
            border-top: 1px solid #F3E3E9; border-bottom: 1px solid #F3E3E9;
        }
        .tab {
            border: none; background: none; padding: 9px 2px 8px; cursor: pointer; color: var(--muted);
            font-size: 11px; font-weight: 700; line-height: 1.35; display: flex; flex-direction: column;
            align-items: center; gap: 4px; border-bottom: 3px solid transparent;
        }
        .tab svg { width: 30px; height: 30px; color: var(--pink); }
        .tab.active { color: var(--ink); border-bottom-color: var(--pink); background: var(--pink-soft); }

        main { max-width: 520px; margin: 0 auto; padding: 4px 16px 40px; }
        .panel { display: none; }
        .panel.active { display: block; }
        .section-title { font-size: 14px; font-weight: 800; margin: 18px 0 10px; }
        .section-title small { font-weight: 600; color: var(--muted); font-size: 12px; }
        .hint { font-size: 12px; color: var(--muted); margin-top: 6px; line-height: 1.6; }

        /* كروت الأشكال: صورة المجسم + المعلومات، تتحرك يمين ويسار */
        .scroll-row { display: flex; gap: 10px; overflow-x: auto; padding: 2px 2px 8px; scroll-snap-type: x proximity; scrollbar-width: none; }
        .scroll-row::-webkit-scrollbar { display: none; }
        .shape-card {
            flex: 0 0 132px; scroll-snap-align: start; border: 1.5px solid transparent; background: #fff; border-radius: 16px;
            padding: 8px 6px 10px; cursor: pointer; text-align: center; color: var(--ink);
        }
        .shape-card img { width: 100%; aspect-ratio: 1; object-fit: contain; display: block; }
        .shape-card .name { font-size: 14px; font-weight: 800; margin-top: 2px; }
        .shape-card .meta { font-size: 11px; font-weight: 600; color: var(--muted); line-height: 1.5; }
        .shape-card .from { font-size: 12px; font-weight: 800; color: var(--pink-dark); margin-top: 2px; }
        .shape-card.active { background: var(--pink-soft); border-color: var(--pink-line); }

        /* المقاسات والطبقات: رسمة تكبر مع المقاس، وطبقات ملونة */
        .size-card, .layer-card {
            flex: 0 0 auto; min-width: 86px; border: 1.5px solid #EFDDE4; background: #fff; border-radius: 14px;
            padding: 10px 10px 9px; cursor: pointer; text-align: center; color: var(--ink);
            display: flex; flex-direction: column; align-items: center; gap: 3px;
        }
        .size-card .pic { height: 64px; display: flex; align-items: flex-end; justify-content: center; color: var(--pink); }
        .size-card .num, .layer-card .num { font-size: 13px; font-weight: 800; }
        .size-card small, .layer-card small { font-size: 11px; font-weight: 700; color: var(--muted); }
        .size-card .price, .layer-card .price { font-size: 11px; font-weight: 800; color: var(--pink-dark); }
        .layer-card .bars { width: 34px; display: flex; flex-direction: column-reverse; gap: 2px; height: 40px; justify-content: flex-start; }
        .layer-card .bars div { border-radius: 3px; }
        .size-card.active, .layer-card.active { border-color: var(--pink); background: var(--pink-soft); }

        .item-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .item-card {
            border: 1.5px solid #EFDDE4; background: #fff; border-radius: 14px; padding: 6px 6px 9px;
            cursor: pointer; text-align: center; font-size: 12px; font-weight: 800; color: var(--ink);
        }
        .item-card .pic {
            width: 100%; aspect-ratio: 1; border-radius: 10px; background: #FAF3F5 center / cover no-repeat;
            margin-bottom: 6px; display: flex; align-items: center; justify-content: center; font-size: 26px;
        }
        .item-card .extra { display: block; font-size: 11px; font-weight: 700; color: var(--pink-dark); margin-top: 3px; }
        .item-card.active { border-color: var(--pink); background: var(--pink-soft); }

        .ai-box {
            border: 1.5px solid var(--pink-line); border-radius: 16px; padding: 14px; margin-top: 16px;
            background: linear-gradient(180deg, #FFF8FA, #fff);
        }
        .ai-box h3 { margin: 0 0 4px; font-size: 15px; }
        .ai-box .badge { font-size: 11px; font-weight: 700; color: var(--pink-dark); background: #fff; border: 1px solid var(--pink-line); border-radius: 10px; padding: 2px 8px; }
        .upload {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            border: 1.5px dashed #ED93B1; border-radius: 12px; padding: 13px;
            font-size: 13px; font-weight: 700; color: var(--pink-dark); cursor: pointer; background: #fff;
        }
        .upload input { display: none; }
        .thumbs { display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
        .thumbs img { width: 58px; height: 58px; object-fit: cover; border-radius: 10px; border: 1px solid var(--pink-line); display: block; }
        .thumb { position: relative; }
        .thumb .remove {
            position: absolute; top: -7px; left: -7px; width: 24px; height: 24px; border-radius: 50%; border: 2px solid #fff;
            background: var(--ink); color: #fff; font-size: 13px; line-height: 1; cursor: pointer; padding: 0;
        }
        /* الصور اللي طلعت قبل: تختار منها وحدة */
        .ai-history { margin-top: 10px; }
        .ai-history .thumbs img { width: 64px; height: 64px; cursor: pointer; }
        .ai-history .thumbs img.current { outline: 3px solid var(--pink); outline-offset: 1px; }
        textarea, input[type="text"], input[type="date"] {
            width: 100%; border: 1.5px solid #EFDDE4; border-radius: 12px; padding: 11px 12px;
            font-size: 15px; font-family: inherit; color: var(--ink); background: #fff;
        }
        textarea { min-height: 84px; resize: vertical; }
        textarea:focus, input:focus { outline: 2px solid var(--pink); border-color: transparent; }

        .btn {
            width: 100%; border: none; border-radius: 30px; padding: 14px; font-size: 15px; font-weight: 800;
            cursor: pointer; background: var(--pink); color: #fff; margin-top: 12px;
        }
        .btn:disabled { opacity: 0.5; cursor: wait; }
        .btn.light { background: #fff; color: var(--pink-dark); border: 1.5px solid var(--pink-line); }
        .row { display: flex; gap: 8px; }
        .row .btn { margin-top: 8px; }
        .message { margin-top: 10px; font-size: 13px; font-weight: 700; line-height: 1.6; }
        .message.error { color: #B3261E; }
        .message.info { color: var(--pink-dark); }

        .ai-result { display: none; margin-top: 12px; }
        .ai-result.show { display: block; }
        .ai-result img { width: 100%; border-radius: 14px; border: 1px solid var(--pink-line); }
        .download-btn {
            display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 8px; text-decoration: none;
            border: 1.5px solid var(--pink-line); border-radius: 30px; padding: 11px; font-size: 14px; font-weight: 800;
            color: var(--pink-dark); background: #fff;
        }
        .ai-result .approved { display: none; text-align: center; font-size: 13px; font-weight: 800; color: #2E7D32; margin-top: 8px; }
        .ai-result.is-approved .approved { display: block; }
        .ai-result.is-approved .row { display: none; }

        .voice { display: flex; align-items: center; gap: 10px; margin-top: 8px; }
        .voice button {
            border: 1.5px solid var(--pink-line); background: #fff; color: var(--pink-dark); border-radius: 20px;
            padding: 8px 14px; font-size: 13px; font-weight: 800; cursor: pointer; white-space: nowrap;
        }
        .voice button.recording { background: #B3261E; border-color: #B3261E; color: #fff; }
        .voice audio { height: 34px; flex: 1; min-width: 0; }

        .nav-buttons { display: flex; gap: 8px; margin-top: 22px; }
        .nav-buttons .btn { margin-top: 0; }

        .summary { border: 1.5px solid #EFDDE4; border-radius: 16px; padding: 4px 14px; margin-top: 16px; }
        .summary dl { margin: 0; }
        .summary .line { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-bottom: 1px solid #F5EBEF; font-size: 13px; }
        .summary .line:last-child { border-bottom: none; }
        .summary dt { color: var(--muted); font-weight: 700; }
        .summary dd { margin: 0; font-weight: 800; text-align: left; }
        .summary .total dd { font-size: 17px; color: var(--pink); }
        .summary-images { display: flex; gap: 8px; flex-wrap: wrap; padding: 6px 0 12px; }
        .summary-images img { width: 76px; height: 76px; object-fit: cover; border-radius: 10px; border: 1px solid var(--pink-line); }
        .summary-images figure { margin: 0; text-align: center; font-size: 10px; font-weight: 700; color: var(--pink-dark); }

        .modal { position: fixed; inset: 0; background: rgba(75,21,40,0.45); display: none; align-items: flex-end; justify-content: center; z-index: 50; }
        .modal.show { display: flex; }
        .modal .sheet { background: #fff; width: 100%; max-width: 520px; max-height: 88vh; overflow: auto; border-radius: 20px 20px 0 0; padding: 18px 16px 26px; }
        .modal h2 { margin: 0 0 4px; font-size: 18px; }
        /* شاشة الانتظار وقت التوليد: في نص الشاشة، والزبون ما يقدر يغيّر شي إلا بعد الإلغاء */
        .modal.center { align-items: center; padding: 16px; }
        .modal.center .sheet { border-radius: 20px; text-align: center; padding: 26px 18px 18px; }
        .wait-icon { font-size: 40px; animation: tilt 1.6s ease-in-out infinite; display: inline-block; }
        @keyframes tilt { 50% { transform: rotate(180deg); } }
        .wait-time { font-size: 26px; font-weight: 800; color: var(--pink); margin: 8px 0 2px; }
    
        /* صورة المنتج فوق المجسم: تنضغط وتكبر، والمجسم 3D بالشكل والمقاس اللي اختاره الزبون */
        .photo-card { bottom: 12px; right: 12px; padding: 4px; cursor: pointer; }
        .photo-card img { width: 58px; height: 58px; object-fit: cover; border-radius: 10px; display: block; }
        .price-card .old { font-size: 11px; color: var(--muted); text-decoration: line-through; font-weight: 700; }
        .price-card .off { display: inline-block; background: var(--pink); color: #fff; border-radius: 8px; padding: 1px 6px; font-size: 10px; font-weight: 800; margin-top: 2px; }
        .photo-view { position: absolute; inset: 0; background: #fff; display: none; z-index: 2; }
        .photo-view.show { display: block; }
        .photo-view img { width: 100%; height: 100%; object-fit: contain; }
        .photo-view button { position: absolute; top: 10px; left: 10px; border: none; background: var(--ink); color: #fff; border-radius: 16px; padding: 6px 12px; font-size: 12px; font-weight: 800; cursor: pointer; }
        .product-head { max-width: 520px; margin: 0 auto; padding: 12px 16px 2px; }
        .product-head h1 { font-size: 20px; margin: 0 0 2px; }
        .product-head .sn { font-size: 13px; color: var(--pink-dark); }
        @media (min-width: 860px) {
            .order-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; max-width: 1200px; margin: 0 auto; padding: 18px 24px 40px; align-items: start; }
            .visual { top: 14px; border: 1px solid #F3E3E9; border-radius: 18px; overflow: hidden; box-shadow: none; }
            .visual model-viewer { height: 62vh; max-height: 560px; min-height: 380px; }
            .steps main, .steps .product-head { max-width: none; padding-left: 0; padding-right: 0; }
            .steps .tabs { border-radius: 14px; border: 1px solid #F3E3E9; overflow: hidden; }
            .product-head h1 { font-size: 26px; }
        }
        .ar-btn { font-size: 11px; line-height: 1.25; padding: 7px 11px; text-align: center; }
        .spec-bar .spec { font-size: 10px; }
        .spec-bar .spec svg { width: 15px; height: 15px; }
        .writing-count { font-size: 11px; color: var(--muted); text-align: left; margin-top: 4px; }
        .summary .design img { width: 64px; height: 64px; object-fit: cover; border-radius: 10px; border: 1px solid var(--pink-line); }
        .summary .old { color: var(--muted); text-decoration: line-through; }
    </style>
</head>
<body>
    @php
        $first = $product['sizes'][0];
        $title = $product['name'] ?? 'S.N: '.$product['id'];
    @endphp

    <div class="site-top">
    <header class="site-header">
        <a href="https://jomacake.com"><img src="{{ asset('images/icons/logo.png') }}" alt="JOMACAKE" class="logo"></a>
        <nav>
            <a href="https://jomacake.com">الرئيسية</a>
            <a href="{{ route('shop.index') }}" class="active">المتجر</a>
            <a href="{{ route('special-order.show') }}">طلب خاص</a>
        </nav>
        <a href="https://jomacake.com/cart" class="cart" aria-label="السلة">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 5 3H2"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/></svg>
        </a>
    </header>
    <div class="sample-tag">عينة تجريبية — الطلب ما يُرسل لموقع جوماكيك</div>

    </div>

    {{-- على الكمبيوتر: نص للكيكة (المجسم والصورة) ونص لخطوات الطلب، وعلى الجوال الكيكة ثابتة فوق --}}
    <div class="order-layout">
    <div class="top-fixed visual" id="top-fixed">
    <div class="viewer-wrap">
        <model-viewer
            id="viewer"
            alt="{{ $title }}"
            ar
            ar-modes="webxr scene-viewer quick-look"
            ar-scale="fixed"
            ar-placement="floor"
            camera-controls
            auto-rotate
            camera-orbit="0deg 66deg 88%"
            min-camera-orbit="auto 20deg auto"
            max-camera-orbit="auto 85deg auto"
            shadow-intensity="0.1"
            shadow-softness="1"
            exposure="1"
            environment-image="neutral"
        >
            <button slot="ar-button" hidden></button>
        </model-viewer>
        <div class="viewer-status" id="viewer-status">جاري تجهيز الكيكة…</div>
        <div class="float-card price-card">
            <div class="amount" id="price-amount">—</div>
            <div class="old" id="old-price"></div>
            <div class="off" id="price-off" hidden></div>
            <div class="caption">شامل الضريبة</div>
        </div>
        <div class="float-card photo-card" id="photo-card" title="صورة المنتج">
            <img id="photo-thumb" src="{{ $product['image'] }}" alt="{{ $title }}">
        </div>
        <div class="photo-view" id="photo-view">
            <img id="photo-big" src="{{ $product['image'] }}" alt="{{ $title }}">
            <button type="button" id="photo-close">المجسم 3D</button>
        </div>
    </div>

    <div class="spec-bar">
        <div class="specs">
            <div class="spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="m12 4 8 4-8 4-8-4z" fill="currentColor" fill-opacity=".9"/><path d="m4 12 8 4 8-4M4 16l8 4 8-4"/></svg><span id="spec-layers"></span></div>
            <div class="spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M3 15.5 15.5 3 21 8.5 8.5 21z"/><path d="m7 12 1.5 1.5M9.5 9.5l2 2M12 7l1.5 1.5M14.5 4.5l2 2"/></svg><span id="spec-size"></span></div>
            <div class="spec"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="7.5" r="3.2"/><circle cx="5.5" cy="9" r="2.4"/><circle cx="18.5" cy="9" r="2.4"/><path d="M6.5 19c0-3.3 2.5-5.5 5.5-5.5s5.5 2.2 5.5 5.5zM1.5 18c0-2.4 1.6-4 4-4 .8 0 1.5.2 2.1.5A7 7 0 0 0 5.4 18zM22.5 18c0-2.4-1.6-4-4-4-.8 0-1.5.2-2.1.5a7 7 0 0 1 2.2 3.5z"/></svg><span id="spec-people"></span></div>
            <div class="spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"><path d="M4 20h16v-5H4zM6.5 15v-4h11v4M9 11V8h6v3M12 8V5.5"/><path d="M12 3.5c.8.6.8 1.4 0 2-.8-.6-.8-1.4 0-2z" fill="currentColor"/></svg><span>دور واحد</span></div>
        </div>
        <button type="button" class="ar-btn" id="ar-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 7.5v9L12 21l8-4.5v-9z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/></svg>
            <span>شاهد على<br>طاولتك</span>
        </button>
    </div>
    </div>

    <div class="steps">
    <div class="product-head">
        @if ($product['name'])<h1>{{ $product['name'] }}</h1>@endif
        <div class="sn">الرقم التسلسلي: <b>{{ $product['id'] }}</b></div>
    </div>

    <div class="tabs" id="tabs">
        <button type="button" class="tab active" data-tab="0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"><path d="M12 3 3.5 7.5v9L12 21l8.5-4.5v-9z"/><path d="M3.5 7.5 12 12l8.5-4.5M12 12v9"/></svg>
            الشكل</button>
        <button type="button" class="tab" data-tab="1">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><ellipse cx="9.5" cy="10" rx="6.5" ry="2.3"/><path d="M3 10v6.5c0 1.3 2.9 2.3 6.5 2.3s6.5-1 6.5-2.3V10M3 4.5h13M3 3v3M16 3v3M20.5 9v9.5M19 9h3M19 18.5h3"/></svg>
            الحجم<br>والطبقات</button>
        <button type="button" class="tab" data-tab="2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"><path d="M3 11.5 19 6.5l2 5v7H3z"/><path d="M3 11.5h18M3 15c2 1 3.5-1 5.5 0s3.5 1 5.5 0 3.5-1 7 0"/></svg>
            النكهة<br>والحشوة</button>
        <button type="button" class="tab" data-tab="3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m14.5 4.5 3 3L9 16l-4 1 1-4z"/><path d="m13 6 3 3M3 21h9"/></svg>
            الكتابة<br>والتعديل</button>
        <button type="button" class="tab" data-tab="4">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 5 3H2"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/></svg>
            الطلب</button>
    </div>

    <main>
        <section class="panel active" data-panel="0">
            <div class="section-title">شكل الكيكة</div>
            <div class="scroll-row" id="shape-cards"></div>
        </section>

        <section class="panel" data-panel="1">
            <div class="section-title">الحجم</div>
            <div class="scroll-row" id="size-options"></div>
            <div class="section-title">عدد الطبقات</div>
            <div class="scroll-row" id="layer-options"></div>
            <div class="hint" id="cone-hint" hidden>المخروطي بارتفاع ثابت.</div>
        </section>

        <section class="panel" data-panel="2">
            @if ($product['flavors'])
                <div class="section-title">النكهة</div>
                <div class="item-cards" id="flavor-cards"></div>
            @endif
            @if ($product['stuffings'])
                <div class="section-title">الحشوة</div>
                <div class="item-cards" id="filling-cards"></div>
            @endif
        </section>

        <section class="panel" data-panel="3">
            <div class="section-title">اكتب على الكعكة @unless ($product['requires_text'])<small>(اختياري)</small>@endunless</div>
            <input type="text" id="writing" maxlength="40" placeholder="مثال: عيد ميلاد سعيد">
            <div class="writing-count"><span id="writing-count">0</span>/40</div>

            <div class="ai-box">
                <h3>✨ تبين تغيّرين شي في الكيكة؟</h3>
                <div class="hint" style="margin-top:0">اكتبي التعديل (لون، إضافة…)، وارفعي صور للي تبينه إذا عندك، والذكاء الاصطناعي يوريك الكيكة بالشكل والمقاس اللي اخترتيها.</div>
                <textarea id="ai-note" maxlength="500" placeholder="مثال: خلي اللون وردي فاتح وزيدي فراولة فوق" style="margin-top:10px"></textarea>
                <div class="writing-count"><span id="ai-note-count">0</span>/500</div>
                <label class="upload" style="margin-top:8px">📷 صور للتعديل (اختياري، لين 6)
                    <input type="file" id="ai-reference" accept="image/png,image/jpeg,image/webp" multiple>
                </label>
                <div class="thumbs" id="ai-thumbs"></div>
                <button type="button" class="btn" id="ai-generate" {{ $hasKey && $aiRemaining !== 0 ? '' : 'disabled' }}>✨ ورّيني الكيكة</button>
                <div class="hint" id="ai-remaining"></div>
                <div class="message {{ $hasKey ? 'info' : 'error' }}" id="ai-message">{{ $hasKey ? '' : 'لا يوجد مفتاح OpenAI. أضيفي OPENAI_API_KEY في ملف .env ثم شغّلي php artisan config:clear' }}</div>

                <div class="ai-result" id="ai-result">
                    <img id="ai-image" alt="الكيكة بعد التعديل">
                    <a class="download-btn" id="ai-download" href="#" download>⬇️ تحميل الصورة</a>
                    <div class="row">
                        <button type="button" class="btn" id="ai-approve">👍 اعتمدي التصميم</button>
                        <button type="button" class="btn light" id="ai-reject">ما عجبني</button>
                    </div>
                    <div class="approved">✓ تم اعتماد التصميم، بيظهر في طلبك</div>
                </div>
                <div class="ai-history" id="ai-history" hidden>
                    <div class="hint">صورك اللي طلعت (اضغطي على صورة عشان تختارينها):</div>
                    <div class="thumbs" id="ai-history-thumbs"></div>
                </div>
            </div>
        </section>

        <section class="panel" data-panel="4">
            <div class="summary" id="summary"></div>
            <div class="message error" id="submit-message"></div>
            <button type="button" class="btn" id="submit">الطلب</button>
        </section>

        <div class="nav-buttons">
            <button type="button" class="btn light" id="prev">السابق</button>
            <button type="button" class="btn" id="next">التالي</button>
        </div>
    </main>
    </main>
    </div>
    </div>

    <div class="modal center" id="ai-wait-modal" role="dialog" aria-live="polite">
        <div class="sheet">
            <div class="wait-icon">⏳</div>
            <h2>الرجاء الانتظار، جاري التوليد</h2>
            <div class="wait-time" id="ai-wait-time">0 ثانية</div>
            <div class="hint">الذكاء الاصطناعي يجهّز صورة كيكتك، ممكن ياخذ دقيقة أو أكثر.</div>
            <button type="button" class="btn light" id="ai-cancel">إلغاء</button>
        </div>
    </div>

    <div class="modal" id="done-modal">
        <div class="sheet">
            <h2>تم تجهيز طلبك ✓</h2>
            <div class="hint">هذه عينة تجريبية، الطلب ما انرسل لموقع جوماكيك. هذا الملخص اللي بيوصل:</div>
            <div class="summary" id="modal-summary"></div>
            <button type="button" class="btn" id="close-modal">تمام</button>
        </div>
    </div>

    <script type="module">
        import { composeCake } from "{{ asset('js/cake-composer.js') }}?v={{ filemtime(public_path('js/cake-composer.js')) }}";

        const product = @json($product);
        const SHAPES = @json($shapes);
        const LAYER_LIMITS = @json($layerLimits);
        const AI_URL = "{{ route('shop.ai', $product['id']) }}";
        const AI_STATUS_URL = "{{ url('/products/ai') }}/";
        const HAS_KEY = @json($hasKey);
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const modelsBaseUrl = "{{ asset('storage/models') }}";
        const CAKE_COLOR = @json(config('cake_ar.cake_color'));
        const BOARD_COLORS = @json(config('cake_ar.board_colors'));
        const LAYER_HEIGHT = @json(config('cake_ar.layer_height'));
        const SHAPE_IMAGES = "{{ asset('images/shapes') }}";
        const MAX_MODEL_LAYERS = 3;
        const MAX_REFERENCES = 6;
        const RIYAL = '<svg class="riyal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-label="ريال"><path d="M8.5 2.5v14.5M14 4.5v15.5M3 11.5l18-4M3 16l18-4M14.5 20.5l6.5-1.5"/></svg>';
        const $ = id => document.getElementById(id);
        const esc = t => String(t).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

        // كل أشكال الدور الواحد (الحين كلها متاحة لكل منتج، وبعدين تتحدد من لوحة التحكم).
        // مقاسات وطبقات كل شكل = اللي يجمع بين المنتج والشكل (القبة 3 طبقات، السن ست 6 و8 إنش…)
        const ALL_SHAPES = [
            { key: 'round', name: 'دائري' },
            { key: 'heart', name: 'قلب' },
            { key: 'cone', name: 'مخروطي' },
            { key: 'sunset', name: 'سن ست' },
            { key: 'star', name: 'نجمة' },
            { key: 'dome', name: 'قبة' },
        ];
        const sizeKey = s => `${s.inch}in`;
        function sizesOf(shape) {
            const allowed = SHAPES[shape]?.sizes ?? Object.keys(LAYER_LIMITS).filter(k => !k.startsWith('tier'));
            return product.sizes.filter(s => allowed.includes(sizeKey(s)) && layersOf(shape, s).length);
        }
        function layersOf(shape, s) {
            const own = [];
            for (let n = s.min_layers; n <= s.max_layers; n++) own.push(n);
            const allowed = SHAPES[shape] ? [].concat(SHAPES[shape].layers) : (LAYER_LIMITS[sizeKey(s)] ?? own);
            return own.filter(n => allowed.includes(n));
        }
        const shapes = ALL_SHAPES.filter(c => sizesOf(c.key).length);

        const state = {
            card: shapes[0], size: null, layers: null,
            flavor: product.flavors[0] ?? null, filling: null,
            aiImage: null, aiApproved: false, aiRemaining: @json($aiRemaining), tab: 0,
        };
        const shape = () => state.card.key;

        // نفس حساب موقع جوماكيك: سعر المقاس + الطبقة الزيادة + النكهة، والخصم على المجموع والكسر ينقص
        const discounted = (price, d) => Math.floor(Math.max(0, d ? (d.type === 'percent' ? price * (1 - d.amount / 100) : price - d.amount) : price) + 1e-9);
        const offLabel = d => d ? (d.type === 'percent' ? `خصم ${d.amount}%` : `خصم ${d.amount}`) : '';
        const layerExtra = (s, n) => (n - s.min_layers) * s.layer_price;
        const before = (s, n, flavor) => s.price + layerExtra(s, n) + (flavor?.extra ?? 0);
        const money = n => n + RIYAL;
        const totals = () => {
            const b = before(state.size, state.layers, state.flavor);
            return { before: b, final: discounted(b, state.size.discount) };
        };

        function card(className, html, active, onClick) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = className + (active ? ' active' : '');
            b.innerHTML = html;
            b.addEventListener('click', () => { [...b.parentElement.children].forEach(o => o.classList.toggle('active', o === b)); onClick(); });
            return b;
        }

        // ===== الشكل =====
        function renderShapes() {
            const box = $('shape-cards');
            box.innerHTML = '';
            shapes.forEach(c => {
                const sizes = sizesOf(c.key);
                const people = sizes.map(s => s.people).join('-').split('-').map(Number).filter(Boolean);
                const from = Math.min(...sizes.map(s => discounted(before(s, layersOf(c.key, s)[0], product.flavors[0]), s.discount)));
                box.appendChild(card('shape-card', `
                    <img src="${SHAPE_IMAGES}/${c.key}.webp" alt="" loading="lazy">
                    <div class="name">${c.name}</div>
                    <div class="meta">${people.length ? `${Math.min(...people)}-${Math.max(...people)} أشخاص` : ''}<br>${sizes.length > 1 ? `من ${sizes[0].inch} إلى ${sizes[sizes.length - 1].inch} إنش` : `${sizes[0].inch} إنش`}</div>
                    <div class="from">يبدأ من ${money(from)}</div>`,
                    c === state.card,
                    () => { state.card = c; renderSizes(); refresh(); },
                ));
            });
            // شكل واحد بس: ينختار تلقائيًا
            if (shapes.length === 1) $('shape-cards').insertAdjacentHTML('afterend', '<div class="hint">هذي الكيكة تنعمل بهذا الشكل.</div>');
        }

        // ===== الحجم والطبقات =====
        const SHAPE_PATHS = {
            round: 'M5 7.5c0-1.7 3.1-3 7-3s7 1.3 7 3-3.1 3-7 3-7-1.3-7-3zM5 7.5v9c0 1.7 3.1 3 7 3s7-1.3 7-3v-9',
            heart: 'M12 20.5s-7.5-4.6-7.5-10.1A4.3 4.3 0 0 1 12 7.6a4.3 4.3 0 0 1 7.5 2.8c0 5.5-7.5 10.1-7.5 10.1z',
            cone: 'M12 3.5c-.8 0-1.3.6-1.6 1.4L5.5 19.5h13L13.6 4.9c-.3-.8-.8-1.4-1.6-1.4z',
            sunset: 'M3 18.5h18M4.5 18.5a7.5 7.5 0 0 1 15 0',
            dome: 'M4.5 17.5c0 1.4 3.4 2.5 7.5 2.5s7.5-1.1 7.5-2.5M4.5 17.5a7.5 8 0 0 1 15 0',
            star: 'M12 3.2l2.6 5.4 5.9.8-4.3 4.1 1.1 5.8L12 16.5l-5.3 2.8 1.1-5.8-4.3-4.1 5.9-.8z',
        };
        function renderSizes() {
            const list = sizesOf(shape());
            if (!list.includes(state.size)) state.size = list[0];
            const box = $('size-options');
            box.innerHTML = '';
            list.forEach(s => {
                const px = 26 + (s.inch - 4) * 6;
                box.appendChild(card('size-card', `
                    <div class="pic"><svg width="${px}" height="${px}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="${SHAPE_PATHS[shape()]}"/></svg></div>
                    <div class="num">${s.inch} إنش</div>
                    <small>${s.people} أشخاص</small>
                    <div class="price">${money(discounted(before(s, layersOf(shape(), s)[0], state.flavor), s.discount))}</div>
                    ${s.discount ? `<small style="color:var(--pink)">${offLabel(s.discount)}</small>` : ''}`,
                    s === state.size,
                    () => { state.size = s; renderLayers(); renderItems(); refresh(); },
                ));
            });
            renderLayers();
            renderItems();
        }

        const LAYER_COLORS = ['#F4C0D1', '#ED93B1', '#D4537E', '#993556'];
        function renderLayers() {
            const isCone = shape() === 'cone';
            const allowed = layersOf(shape(), state.size);
            if (!allowed.includes(state.layers)) state.layers = allowed[0];
            $('cone-hint').hidden = !isCone;
            const box = $('layer-options');
            box.innerHTML = '';
            allowed.forEach(n => box.appendChild(card('layer-card', `
                <div class="bars">${Array.from({ length: n }, (_, b) => `<div style="height:${Math.max(6, 16 - n * 2)}px;background:${LAYER_COLORS[b % LAYER_COLORS.length]}"></div>`).join('')}</div>
                <div class="num">${n} طبقات</div>
                <div class="price">${n === state.size.min_layers ? 'الأساسي' : (layerExtra(state.size, n) ? '+' + money(layerExtra(state.size, n)) : 'بدون زيادة')}</div>`,
                n === state.layers,
                () => { state.layers = n; refresh(); },
            )));
        }

        // ===== النكهة والحشوة =====
        function renderItems() {
            [['flavor-cards', 'flavors', 'flavor', '🍰'], ['filling-cards', 'stuffings', 'filling', '🍓']].forEach(([boxId, list, key, icon]) => {
                const box = $(boxId);
                if (!box) return;
                box.innerHTML = '';
                product[list].forEach(item => box.appendChild(card('item-card',
                    `<div class="pic" ${item.image ? `style="background-image:url('${item.image}')"` : ''}>${item.image ? '' : icon}</div>${esc(item.name)}${key === 'flavor' ? `<span class="extra">${item.extra ? '+' + money(item.extra) : 'بدون زيادة'}</span>` : ''}`,
                    state[key]?.id === item.id,
                    () => { state[key] = item; refresh(); },
                )));
            });
        }

        function updateBadges() {
            const { before: b, final } = totals();
            $('price-amount').innerHTML = money(final);
            $('old-price').textContent = final < b ? b : '';
            $('price-off').hidden = !state.size.discount;
            $('price-off').textContent = offLabel(state.size.discount);
            $('spec-layers').textContent = shape() === 'cone' ? '—' : `${state.layers} طبقة`;
            $('spec-size').textContent = `${state.size.inch} إنش`;
            $('spec-people').textContent = `${state.size.people} أشخاص`;
        }

        // ===== المجسم 3D و AR =====
        let token = 0, currentBlob = null, modelReady = Promise.resolve();
        async function updateModel() {
            const mine = ++token;
            const key = sizeKey(state.size);
            const modelLayers = Math.min(state.layers, MAX_MODEL_LAYERS);
            $('viewer-status').hidden = !!currentBlob;
            try {
                const url = await composeCake(`${modelsBaseUrl}/cake_${key}_layers${modelLayers}_v45.glb`, {
                    color: CAKE_COLOR,
                    boardColor: BOARD_COLORS[key] || BOARD_COLORS.default,
                    shape: shape(),
                    style: 'plain',
                    extraHeight: (state.layers - modelLayers) * LAYER_HEIGHT,
                });
                if (mine !== token) { URL.revokeObjectURL(url); return; }
                if (currentBlob) URL.revokeObjectURL(currentBlob);
                currentBlob = url;
                const loaded = new Promise(res => $('viewer').addEventListener('load', res, { once: true }));
                $('viewer').src = url;
                await loaded;
                $('viewer-status').hidden = true;
            } catch (e) {
                console.error(e);
                if (mine === token) { $('viewer-status').hidden = false; $('viewer-status').textContent = 'تعذر تحميل الكيكة، حدّثي الصفحة'; }
            }
        }

        let lastModelKey = '';
        function refresh() {
            updateBadges();
            renderSummary($('summary'));
            const k = [shape(), sizeKey(state.size), state.layers].join('|');
            if (k !== lastModelKey) { lastModelKey = k; modelReady = updateModel(); }
        }

        $('ar-btn').addEventListener('click', () => {
            const v = $('viewer');
            if (v.canActivateAR) v.activateAR();
            else alert('افتحي الصفحة من الجوال عشان تشوفين الكيكة على طاولتك.');
        });

        // صورة المنتج (أو التصميم المعتمد): تكبر فوق المجسم
        $('photo-card').addEventListener('click', () => $('photo-view').classList.add('show'));
        $('photo-close').addEventListener('click', () => $('photo-view').classList.remove('show'));
        function setPhoto(url) { $('photo-thumb').src = url; $('photo-big').src = url; }

        // ===== الخطوات =====
        const tabs = [...document.querySelectorAll('.tab')];
        function goTo(i, scroll = true) {
            state.tab = i;
            tabs.forEach((t, n) => { t.classList.toggle('active', n === i); t.classList.toggle('done', n < i); });
            document.querySelectorAll('.panel').forEach(p => p.classList.toggle('active', Number(p.dataset.panel) === i));
            $('prev').style.visibility = i === 0 ? 'hidden' : 'visible';
            $('next').style.display = i === tabs.length - 1 ? 'none' : '';
            renderSummary($('summary'));
            if (scroll) window.scrollTo({ top: $('tabs').getBoundingClientRect().top + window.scrollY - (matchMedia('(min-width: 860px)').matches ? 14 : $('top-fixed').offsetHeight), behavior: 'smooth' });
        }
        tabs.forEach((t, i) => t.addEventListener('click', () => goTo(i)));
        $('prev').addEventListener('click', () => goTo(Math.max(0, state.tab - 1)));
        $('next').addEventListener('click', () => goTo(Math.min(tabs.length - 1, state.tab + 1)));

        $('writing').addEventListener('input', () => { $('writing-count').textContent = $('writing').value.length; });
        $('ai-note').addEventListener('input', () => { $('ai-note-count').textContent = $('ai-note').value.length; });

        // ===== الذكاء الاصطناعي: صورة المنتج + شكل المجسم + تعديل الزبون =====
        let refFiles = [], aiAbort = null;
        const aiImages = [];
        const canGenerate = () => state.aiRemaining === null || state.aiRemaining > 0;
        function aiMessage(text, type = 'info') { $('ai-message').textContent = text; $('ai-message').className = 'message ' + type; }
        function renderRemaining() {
            $('ai-remaining').textContent = state.aiRemaining === null ? '' : `باقي لك ${state.aiRemaining} محاولات.`;
            $('ai-generate').disabled = !HAS_KEY || !canGenerate();
        }

        function renderRefs() {
            $('ai-thumbs').innerHTML = '';
            refFiles.forEach((f, i) => {
                const wrap = document.createElement('div');
                wrap.className = 'thumb';
                wrap.innerHTML = `<img src="${URL.createObjectURL(f)}" alt=""><button type="button" class="remove" aria-label="حذف الصورة">×</button>`;
                wrap.querySelector('button').addEventListener('click', () => { refFiles.splice(i, 1); renderRefs(); });
                $('ai-thumbs').appendChild(wrap);
            });
        }
        $('ai-reference').addEventListener('change', e => {
            const picked = [...e.target.files];
            if (refFiles.length + picked.length > MAX_REFERENCES) aiMessage(`الحد ${MAX_REFERENCES} صور.`);
            refFiles = [...refFiles, ...picked].slice(0, MAX_REFERENCES);
            e.target.value = '';
            renderRefs();
        });

        async function shrink(file) {
            try {
                const bitmap = await createImageBitmap(file);
                const scale = Math.min(1, 2048 / Math.max(bitmap.width, bitmap.height));
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(bitmap.width * scale);
                canvas.height = Math.round(bitmap.height * scale);
                canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
                const blob = await new Promise(res => canvas.toBlob(res, 'image/jpeg', 0.9));
                return blob ? new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }) : file;
            } catch (e) {
                return file;
            }
        }

        // صورة المجسم رمادي (مثل الطلب الخاص): للشكل والمقاس بس، والتصميم من صورة المنتج
        async function greyClay(blob) {
            const bitmap = await createImageBitmap(blob);
            const canvas = document.createElement('canvas');
            canvas.width = bitmap.width;
            canvas.height = bitmap.height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(bitmap, 0, 0);
            const img = ctx.getImageData(0, 0, canvas.width, canvas.height), d = img.data;
            for (let i = 0; i < d.length; i += 4) {
                if (d[i + 3] < 10 || (d[i] > 250 && d[i + 1] > 250 && d[i + 2] > 250)) continue;
                const cake = d[i] - d[i + 2] > 14 && d[i + 1] >= d[i + 2];
                const g = Math.round((0.3 * d[i] + 0.59 * d[i + 1] + 0.11 * d[i + 2]) * (cake ? 0.5 : 0.85));
                d[i] = d[i + 1] = d[i + 2] = g;
            }
            ctx.putImageData(img, 0, 0);
            return await new Promise(res => canvas.toBlob(b => res(b || blob), 'image/png'));
        }
        async function snapshotModel() {
            try {
                await Promise.race([modelReady, new Promise(res => setTimeout(res, 15000))]);
                const v = $('viewer');
                if (!v.loaded) return null;
                const orbit = v.getCameraOrbit(), auto = v.autoRotate;
                v.autoRotate = false;
                v.cameraOrbit = '0deg 55deg 105%';
                v.jumpCameraToGoal();
                await new Promise(res => requestAnimationFrame(() => requestAnimationFrame(res)));
                const blob = await v.toBlob({ mimeType: 'image/png' });
                v.cameraOrbit = `${orbit.theta}rad ${orbit.phi}rad ${orbit.radius}m`;
                v.autoRotate = auto;
                return await greyClay(blob);
            } catch (e) {
                console.error(e);
                return null;
            }
        }

        function showAiImage(url) {
            state.aiImage = url;
            state.aiApproved = false;
            $('ai-image').src = url;
            $('ai-download').href = url;
            $('ai-download').download = `jomacake-${product.id}-${aiImages.indexOf(url) + 1}.png`;
            $('ai-result').className = 'ai-result show';
            setPhoto(product.image);
            $('ai-history').hidden = aiImages.length < 2;
            $('ai-history-thumbs').innerHTML = '';
            aiImages.forEach(u => {
                const img = document.createElement('img');
                img.src = u;
                img.className = u === url ? 'current' : '';
                img.addEventListener('click', () => showAiImage(u));
                $('ai-history-thumbs').appendChild(img);
            });
        }

        async function waitForJob(job, signal) {
            const started = Date.now();
            while (Date.now() - started < 360000) {
                await new Promise(res => setTimeout(res, 4000));
                if (signal.aborted) throw new DOMException('cancelled', 'AbortError');
                try {
                    const res = await fetch(AI_STATUS_URL + job, { headers: { 'Accept': 'application/json' }, signal });
                    const data = await res.json();
                    if (data.status === 'done') return { data, ok: true };
                    if (data.status === 'failed') return { data, ok: false };
                    if (data.status === 'missing' && Date.now() - started > 20000) break;
                } catch (e) {
                    if (signal.aborted) throw e;
                }
            }
            return { data: { message: 'انقطع الاتصال قبل ما توصل الصورة، وما انحسبت المحاولة. جربي مرة ثانية.' }, ok: false };
        }

        $('ai-cancel').addEventListener('click', () => aiAbort?.abort());
        $('ai-generate').addEventListener('click', async () => {
            const abort = aiAbort = new AbortController();
            const started = Date.now();
            const tick = () => { $('ai-wait-time').textContent = `${Math.round((Date.now() - started) / 1000)} ثانية`; };
            tick();
            const timer = setInterval(tick, 1000);
            $('ai-wait-modal').classList.add('show');
            aiMessage('');
            $('ai-generate').disabled = true;

            const form = new FormData();
            form.append('shape', shape());
            form.append('size', state.size.id);
            form.append('layers', state.layers);
            form.append('note', $('ai-note').value.trim());
            form.append('writing', $('writing').value.trim());
            for (const f of await Promise.all(refFiles.map(shrink))) form.append('references[]', f);
            const job = Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
            form.append('job', job);

            try {
                const shot = await snapshotModel();
                if (shot) form.append('shape_image', shot, 'shape.png');
                if (abort.signal.aborted) throw new DOMException('cancelled', 'AbortError');
                let data, ok;
                try {
                    const res = await fetch(AI_URL, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: form,
                        signal: abort.signal,
                    });
                    data = await res.json();
                    ok = res.ok;
                } catch (cut) {
                    if (abort.signal.aborted) throw cut;
                    ({ data, ok } = await waitForJob(job, abort.signal));
                }
                if (data.remaining !== undefined) state.aiRemaining = data.remaining;
                if (!ok) {
                    const firstError = data.errors && Object.values(data.errors)[0]?.[0];
                    throw new Error(firstError || data.message || 'صار خطأ، جربي مرة ثانية.');
                }
                aiImages.push(data.image);
                showAiImage(data.image);
                $('ai-generate').textContent = '✨ جربي مرة ثانية';
                aiMessage('هذي كيكتك بالشكل والمقاس اللي اخترتيها. عجبتك؟');
            } catch (e) {
                aiMessage(abort.signal.aborted ? 'تم الإلغاء. غيّري اللي تبين وجربي من جديد.' : e.message, abort.signal.aborted ? 'info' : 'error');
            } finally {
                clearInterval(timer);
                $('ai-wait-modal').classList.remove('show');
                if (aiAbort === abort) aiAbort = null;
                renderRemaining();
                renderSummary($('summary'));
            }
        });

        $('ai-approve').addEventListener('click', () => {
            state.aiApproved = true;
            $('ai-result').classList.add('is-approved');
            setPhoto(state.aiImage);
            aiMessage('');
            renderSummary($('summary'));
        });
        $('ai-reject').addEventListener('click', () => {
            state.aiApproved = false;
            $('ai-result').className = 'ai-result';
            $('ai-history').hidden = !aiImages.length;
            setPhoto(product.image);
            aiMessage('غيّري الوصف أو الصور وجربي مرة ثانية.');
            $('ai-note').focus();
            renderSummary($('summary'));
        });

        // ===== الطلب =====
        function renderSummary(box) {
            const { before: b, final } = totals();
            const rows = [
                ['المنتج', `S.N: ${product.id}`],
                ['الشكل', state.card.name],
                ['الحجم', `${state.size.inch} إنش (${state.size.people} أشخاص)`],
                ...(shape() === 'cone' ? [] : [['الطبقات', `${state.layers} طبقات`]]),
                ['النكهة', state.flavor?.name ?? '—'],
                ['الحشوة', state.filling?.name ?? '—'],
                ['الكتابة', $('writing').value.trim() || '—'],
                ...(state.aiApproved && $('ai-note').value.trim() ? [['التعديل', $('ai-note').value.trim()]] : []),
            ];
            box.innerHTML = `<dl>${rows.map(([k, v]) => `<div class="line"><dt>${k}</dt><dd>${esc(v)}</dd></div>`).join('')}
                <div class="line"><dt>التصميم</dt><dd class="design"><img src="${state.aiApproved ? state.aiImage : product.image}" alt=""></dd></div>
                ${final < b ? `<div class="line"><dt>قبل الخصم</dt><dd class="old">${b}</dd></div>` : ''}
                <div class="line total"><dt>الإجمالي</dt><dd>${money(final)}</dd></div></dl>`;
        }

        $('submit').addEventListener('click', () => {
            const missing = [];
            if (product.stuffings.length && !state.filling) missing.push('الحشوة');
            if (product.requires_text && !$('writing').value.trim()) missing.push('الكتابة على الكعكة');
            if (missing.length) { $('submit-message').textContent = 'كمّلي: ' + missing.join('، '); return; }
            $('submit-message').textContent = '';
            renderSummary($('modal-summary'));
            $('done-modal').classList.add('show');
        });
        $('close-modal').addEventListener('click', () => $('done-modal').classList.remove('show'));

        renderShapes();
        renderSizes();
        renderRemaining();
        // شكل واحد بس: نروح للحجم على طول
        goTo(shapes.length === 1 ? 1 : 0, false);
        refresh();
    </script>
</body>
</html>
