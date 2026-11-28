<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \App\Services\ShopProducts::localName($product) ?? 'S.N: '.$product['id'] }} - {{ __('cake_ar.common.site') }}</title>

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
            /* خلفية الصفحة: كريمي دافي بدل الأبيض الصافي (يريح العين، والكروت البيضاء تبان) */
            --bg: #FCF5F7;
            --pink: #D4537E;
            --pink-dark: #993556;
            --pink-soft: #FBEAF0;
            --pink-line: #F4C0D1;
            --ink: #4B1528;
            --muted: #8A6B76;
        }
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body { margin: 0; font-family: -apple-system, 'Segoe UI', Tahoma, sans-serif; background: var(--bg); color: var(--ink); }
        button { font-family: inherit; }

        /* هيدر الموقع الرئيسي */
        .site-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 16px; border-bottom: 1px solid #F3E3E9; background: #fff;
        }
        /* الجزء الثابت فوق (الهيدر، المجسم، الملخص): الخيارات تتحرك تحته والكيكة تبقى قدام الزبون */
        /* وقت الكتابة في المحادثة (الكيبورد مفتوح) الجزء العلوي ما يبقى ثابت، عشان يبان اللي ينكتب */
        body.typing .top-fixed { position: relative; }
        /* وقت الكتابة (الكيبورد مفتوح) نخفي السابق والتالي، وترجع لما يسكّر الكيبورد */
        body.typing .nav-buttons { display: none; }
        .top-fixed { position: sticky; top: 0; z-index: 20; background: var(--bg); box-shadow: 0 4px 8px -6px rgba(75,21,40,0.18); }
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
        .viewer-wrap { position: relative; background: var(--bg); }
        model-viewer { width: 100%; height: 30vh; min-height: 200px; max-height: 300px; background-color: var(--bg); }
        .viewer-status {
            position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: var(--pink-dark); pointer-events: none;
        }
        .viewer-status[hidden] { display: none; }
        .float-card {
            position: absolute; background: #fff; border: 1.5px solid var(--pink-line);
            border-radius: 14px; padding: 7px 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); text-align: center;
        }
        /* السعر: مربع صغير (أضيق من اليمين واليسار) */
        .price-card { top: 12px; right: 12px; padding: 6px 9px; border-radius: 12px; }
        .price-card .caption { font-size: 9px; line-height: 1.2; }
        .price-card .amount { font-size: 17px; font-weight: 800; white-space: nowrap; line-height: 1.2; }
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
            display: grid; grid-template-columns: repeat(5, 1fr); background: var(--bg);
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
            flex: 0 0 132px; scroll-snap-align: start; border: 1.5px solid #F1DDE5; background: var(--bg); border-radius: 16px; box-shadow: none;
            padding: 8px 6px 10px; cursor: pointer; text-align: center; color: var(--ink);
        }
        .shape-card img { width: 100%; aspect-ratio: 1; object-fit: contain; display: block; }
        .shape-card .name { font-size: 14px; font-weight: 800; margin-top: 2px; }
        .shape-card .meta { font-size: 11px; font-weight: 600; color: var(--muted); line-height: 1.5; }
        .shape-card .from { font-size: 12px; font-weight: 800; color: var(--pink-dark); margin-top: 2px; }
        .shape-card.active { background: var(--pink-soft); border-color: var(--pink-line); }

        /* المقاسات والطبقات: رسمة تكبر مع المقاس، وطبقات ملونة */
        .size-card, .layer-card {
            flex: 0 0 auto; min-width: 86px; border: 1.5px solid #EFDDE4; background: #fff; border-radius: 14px; box-shadow: 0 1px 6px rgba(75,21,40,.06);
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
        /* الأشكال تحت بعض (3 في السطر) عشان الزبون يشوف كل الخيارات مرة وحدة، مثل النكهات والحشوات */
        .shape-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .shape-grid .shape-card { padding: 6px 4px 8px; }
        .shape-grid .shape-card .name { font-size: 13px; }
        .item-card {
            border: 1.5px solid #EFDDE4; background: #fff; border-radius: 14px; padding: 6px 6px 9px; box-shadow: 0 1px 6px rgba(75,21,40,.06);
            cursor: pointer; text-align: center; font-size: 12px; font-weight: 800; color: var(--ink);
        }
        .item-card .pic {
            width: 100%; aspect-ratio: 1; border-radius: 10px; background: #FAF3F5 center / cover no-repeat;
            margin-bottom: 6px; display: flex; align-items: center; justify-content: center; font-size: 26px;
        }
        .item-card .extra { display: block; font-size: 11px; font-weight: 700; color: var(--pink-dark); margin-top: 3px; }
        .item-card .pic.slice { background-size: contain; background-color: #F7F2F6; }
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
        /* المعتمد هو آخر صورة في المحادثة (أو اللي يضغط عليها فيها)، فما نعرض صورة وأزرار تحت المحادثة */
        #ai-result, #ai-history { display: none !important; }
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

        .summary { border: 1.5px solid #EFDDE4; border-radius: 16px; padding: 4px 14px; margin-top: 16px; background: #fff; }
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
        .photo-view { position: absolute; inset: 0; background: var(--bg); display: none; z-index: 2; }
        .photo-view.show { display: block; }
        .photo-view { background: #F7F2F4; }
        .photo-view img { width: 100%; height: 100%; object-fit: contain; }
        .photo-view button { position: absolute; top: 12px; left: 12px; border: none; background: var(--ink); color: #fff; border-radius: 18px; padding: 8px 14px; font-size: 13px; font-weight: 800; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,.2); }
        .viewer-wrap .price-card { z-index: 3; }
        .product-head { max-width: 520px; margin: 0 auto; padding: 12px 16px 14px; }
        .product-head h1 { font-size: 20px; margin: 0 0 2px; }
        .product-head .sn { font-size: 13px; color: var(--pink-dark); }
        /* الجوال: صورة المنتج مربعة بعرض الشاشة مثل صفحة جوماكيك (مو ثابتة فوق حتى تاخذ حجمها) */
        @media (max-width: 859px) {
            /* صورة المنتج ثابتة فوق وقت النزول (مثل الكعكة الخاصة) */
            .visual { position: sticky; top: 0; box-shadow: 0 4px 8px -6px rgba(75,21,40,0.18); padding: 8px 16px 0; }
            .visual .viewer-wrap { border-radius: 16px; overflow: hidden; }
            .visual model-viewer { height: 34vh; min-height: 220px; max-height: 320px; aspect-ratio: auto; }
            .visual .photo-view img { object-fit: cover; }
            .visual .spec-bar { border-top: none; padding: 10px 0; }
        }
        @media (min-width: 860px) {
            .order-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 32px; max-width: 1240px; margin: 0 auto; padding: 18px 24px 40px; align-items: start; }
            .visual { top: 14px; border: 1px solid #F3E3E9; border-radius: 18px; overflow: hidden; box-shadow: none; }
            .visual model-viewer { height: auto; aspect-ratio: 1; max-height: 640px; }
            .visual .photo-view img { object-fit: cover; }
            .visual .spec-bar { padding: 14px 16px; }
            .visual .specs { padding: 12px 14px; }
            .visual .spec-bar .spec { font-size: 16px; gap: 8px; }
            .visual .spec-bar .spec svg { width: 28px; height: 28px; }
            .visual .ar-btn { font-size: 14px; padding: 10px 16px; }
            .visual .ar-btn svg { width: 22px; height: 22px; }
            .product-head { padding-bottom: 22px; }
            .steps main, .steps .product-head { max-width: none; padding-left: 0; padding-right: 0; }
            .steps .tabs { border-radius: 14px; border: 1px solid #F3E3E9; overflow: hidden; }
            .product-head h1 { font-size: 26px; }
            .product-head .sn { font-size: 16px; margin-top: 8px; }
            .price-line .amount { font-size: 28px; }
        }
        .ar-btn { font-size: 12px; padding: 8px 12px; }
        /* السعر تحت الصورة، و"شاهد على طاولتك" على الصورة تحت يسار */
        .price-line { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 2px 0 0; }
        .product-head .sn { margin-top: 4px; }
        .price-line .amount { font-size: 24px; font-weight: 800; white-space: nowrap; }
        .price-line .old { font-size: 14px; color: var(--muted); text-decoration: line-through; font-weight: 700; }
        .price-line .off { background: var(--pink); color: #fff; border-radius: 8px; padding: 2px 8px; font-size: 12px; font-weight: 800; }
        .price-line .off[hidden] { display: none; }
        .price-line .tax { font-size: 12px; font-weight: 700; color: var(--pink-dark); }
        /* بالإنجليزي (من اليسار لليمين): نعكس أماكن السعر وصورة المنتج وزر الطاولة وفقاعات المحادثة */
        [dir="ltr"] .price-card { right: auto; left: 12px; }
        [dir="ltr"] .photo-card { right: auto; left: 12px; }
        [dir="ltr"] .ar-float { left: auto; right: 12px; }
        /* زر AR: أيقونة أكبر وواضحة مع الكلمة */
        .ar-float { gap: 7px; }
        /* أيقونة بس بدون كلمة (طلب المدير)، وأكبر */
        .ar-float { font-size: 0 !important; padding: 6px 8px !important; border-radius: 16px !important; }
        .ar-float span { display: none; }
        .ar-float svg { width: 42px !important; height: 40px !important; stroke-width: 1.1; }
        /* مفرّغ: خلفية بيضاء بحد وردي، والكلمة والأيقونة وردي */
        .ar-float { background: var(--bg) !important; color: var(--pink-dark) !important; border: 1.8px solid var(--pink) !important; box-shadow: 0 2px 8px rgba(75,21,40,.12) !important; }
        [dir="ltr"] .bubble.user { align-self: flex-end; border-bottom-right-radius: 16px; border-bottom-left-radius: 4px; }
        [dir="ltr"] .bubble.assistant { align-self: flex-start; border-bottom-left-radius: 16px; border-bottom-right-radius: 4px; }
        .ar-float { position: absolute; left: 12px; bottom: 12px; z-index: 3; box-shadow: 0 2px 8px rgba(0,0,0,.18); }
        /* شريط المواصفات أكبر وبمسافات */
        .spec-bar .specs { justify-content: space-around; gap: 10px; padding: 12px 10px; }
        .spec-bar .spec { font-size: 14px; gap: 6px; }
        .spec-bar .spec svg { width: 24px; height: 24px; }
        /* المحادثة */
        .ai-chat { margin-top: 14px; border-top: 1px dashed var(--pink-line); padding-top: 12px; }
        .ai-chat h4 { margin: 0 0 2px; font-size: 14px; }
        /* المحادثة تنسحب مع الصفحة نفسها (بدون صندوق سحب صغير داخلها، كان يتعارض مع سحب الصفحة) */
        .chat-thread { display: flex; flex-direction: column; gap: 8px; margin: 10px 0; padding: 2px; }
        .bubble { max-width: 85%; padding: 9px 12px; border-radius: 16px; font-size: 13px; line-height: 1.6; white-space: pre-wrap; word-break: break-word; }
        .bubble.user { align-self: flex-start; background: var(--pink); color: #fff; border-bottom-right-radius: 4px; }
        .bubble.assistant { align-self: flex-end; background: #FAF3F5; color: var(--ink); border-bottom-left-radius: 4px; }
        .bubble.typing { color: var(--muted); padding: 12px 14px; }
        /* مؤشر التفكير: ثلاث نقاط تتحرك (مثل واتساب) */
        .bubble.typing .dots { display: inline-flex; gap: 5px; align-items: center; height: 10px; }
        .bubble.typing .dots i { width: 7px; height: 7px; border-radius: 50%; background: var(--pink); opacity: .35; animation: think-dot 1.2s infinite ease-in-out; }
        .bubble.typing .dots i:nth-child(2) { animation-delay: .2s; }
        .bubble.typing .dots i:nth-child(3) { animation-delay: .4s; }
        @keyframes think-dot { 0%, 80%, 100% { opacity: .35; transform: translateY(0); } 40% { opacity: 1; transform: translateY(-4px); } }
        .bubble-download { display: inline-block; margin-top: 6px; padding: 6px 12px; border-radius: 16px; border: 1.5px solid var(--pink-line); background: #fff; color: var(--pink-dark); font-size: 12px; font-weight: 800; text-decoration: none; }
        .bubble img { display: block; width: 160px; height: 160px; object-fit: cover; border-radius: 12px; margin-top: 6px; cursor: pointer; border: 1px solid var(--pink-line); }
        /* خانة الكتابة مستطيل بعرض الصندوق، وزر الإرفاق وزر الإرسال تحتها */
        /* خانة نحيفة و + جنبها، والإرسال من زر الكيبورد (Enter). الخانة تكبر لو الكتابة زادت */
        .chat-input { display: flex; gap: 8px; align-items: flex-end; }
        .chat-input textarea { flex: 1; min-width: 0; box-sizing: border-box; min-height: 46px; height: 46px; max-height: 160px; padding: 11px 14px; border-radius: 14px; font-size: 16px; line-height: 1.5; resize: none; overflow-y: auto; }
        .chat-input .btn#chat-send { display: none; }
        #next.pulse { animation: next-pulse 1s ease-in-out 3; }
        @keyframes next-pulse { 50% { transform: scale(1.06); box-shadow: 0 0 0 6px rgba(212,83,126,.25); } }
        /* زر + (إرفاق صور للذكاء الاصطناعي) على اليسار دائمًا */
        [dir="rtl"] .chat-input .chat-attach { order: 2; }
        .chat-count { font-size: 11px; font-weight: 800; color: var(--pink-dark); text-align: center; margin: 0 0 6px; }
        .chat-count:empty { display: none; }
        /* إرفاق صور للتوضيح أثناء المحادثة */
        .chat-attach { width: 46px; height: 46px; flex-shrink: 0; border-radius: 50%; border: 1.5px solid var(--pink-line); background: #fff; color: var(--pink-dark); display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .chat-attach svg { width: 22px; height: 22px; }
        .chat-attach input { display: none; }
        #chat-thumbs { margin: 0 0 8px; }
        #chat-thumbs .t { position: relative; }
        #chat-thumbs img { width: 52px; height: 52px; object-fit: cover; border-radius: 10px; border: 1px solid var(--pink-line); display: block; }
        #chat-thumbs button { position: absolute; top: -6px; left: -6px; width: 20px; height: 20px; border-radius: 50%; border: none; background: var(--ink); color: #fff; font-size: 12px; line-height: 20px; padding: 0; cursor: pointer; }
        .bubble .refs { display: flex; gap: 4px; flex-wrap: wrap; margin-top: 6px; }
        .bubble .refs img { width: 56px; height: 56px; border-radius: 8px; margin: 0; border: 1px solid rgba(255,255,255,.6); cursor: default; }
        .ai-box .ai-result { margin-top: 10px; }
        .writing-count { font-size: 11px; color: var(--muted); text-align: left; margin-top: 4px; }
        .summary .design img { width: 64px; height: 64px; object-fit: cover; border-radius: 10px; border: 1px solid var(--pink-line); }
        .summary .old { color: var(--muted); text-decoration: line-through; }
        .summary .line { align-items: center; }
        /* الأيقونات عمود واحد على اليسار، والكلام على يمينها */
        .summary .vis { display: flex; align-items: center; justify-content: flex-end; gap: 10px; }
        .summary .line dd:has(.vis) { flex: 1; }
        .summary .vis-pic { width: 44px; height: 44px; border-radius: 10px; background: #FAF3F5; display: inline-flex; align-items: center; justify-content: center; color: var(--pink); flex-shrink: 0; overflow: hidden; }
        .summary .vis-pic img { width: 100%; height: 100%; object-fit: cover; }
        .summary .vis-pic img.fit { object-fit: contain; }
        .summary .vis-icon { font-size: 22px; }
        .summary .vis-bars { display: flex; flex-direction: column-reverse; gap: 2px; width: 26px; }
        .summary .vis-bars i { display: block; height: 6px; border-radius: 2px; }
    </style>
</head>
<body>
    @php
        $first = $product['sizes'][0];
        $title = \App\Services\ShopProducts::localName($product) ?? 'S.N: '.$product['id'];
    @endphp

    <div class="site-top">
    <header class="site-header">
        <a href="https://jomacake.com"><img src="{{ asset('images/icons/logo.png') }}" alt="JOMACAKE" class="logo"></a>
        <nav>
            <a href="https://jomacake.com">{{ __('cake_ar.common.nav_home') }}</a>
            <a href="{{ route('shop.index') }}" class="active">{{ __('cake_ar.common.nav_shop') }}</a>
            <a href="{{ route('special-order.show') }}">{{ __('cake_ar.common.nav_special') }}</a>
        </nav>
        <div class="header-end">
        @include('partials.lang-switch')
        <a href="https://jomacake.com/cart" class="cart" aria-label="{{ __('cake_ar.common.cart') }}">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 5 3H2"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/></svg>
        </a>
        </div>
    </header>
    <div class="sample-tag">{{ __('cake_ar.common.demo_tag') }}</div>

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
            camera-orbit="-25deg 66deg 88%"
            min-camera-orbit="auto 20deg auto"
            max-camera-orbit="auto 85deg auto"
            shadow-intensity="0.1"
            shadow-softness="1"
            exposure="1"
            environment-image="neutral"
        >
            <button slot="ar-button" hidden></button>
        </model-viewer>
        <div class="viewer-status" id="viewer-status">{{ __('cake_ar.common.preparing') }}</div>
        {{-- صورة المنتج (أو التصميم المعتمد) دايمًا فوق، والمجسم 3D تحتها للـ AR وصورة الشكل للذكاء الاصطناعي --}}
        <div class="photo-view show" id="photo-view">
            <img id="photo-big" src="{{ $product['image'] }}" alt="{{ $title }}">
        </div>
        <button type="button" class="ar-btn ar-float" id="ar-btn" aria-label="AR" title="AR">
            <svg viewBox="0 0 36 34" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round" stroke-linecap="round" aria-hidden="true"><path d="M5.5 15.5 14 12.5l8.5 3L14 18.5z"/><path d="M5.5 15.5v10l8.5 3v-10M22.5 15.5v10l-8.5 3"/><path d="M10.5 7v4.8l4 2.2 4-2.2V7z" fill="#fff"/><path d="M14.5 9.2V14"/><path d="M10.5 7 14.5 4.8 18.5 7 14.5 9.2z" fill="#fff"/><rect x="18" y="20.5" width="12.5" height="8.5" rx="2" fill="#fff"/><path d="M21.2 20.5l1-1.7h3.4l1 1.7"/><circle cx="24.25" cy="24.8" r="2.3"/><path d="M28.5 10c.3 1.9.6 2.2 2.5 2.5-1.9.3-2.2.6-2.5 2.5-.3-1.9-.6-2.2-2.5-2.5 1.9-.3 2.2-.6 2.5-2.5z" fill="currentColor" stroke="none"/></svg>
            <span>{{ __('cake_ar.common.ar_button') }}</span>
        </button>
    </div>


    <div class="spec-bar">
        <div class="specs">
            <div class="spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="m12 4 8 4-8 4-8-4z" fill="currentColor" fill-opacity=".9"/><path d="m4 12 8 4 8-4M4 16l8 4 8-4"/></svg><span id="spec-layers"></span></div>
            <div class="spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M3 15.5 15.5 3 21 8.5 8.5 21z"/><path d="m7 12 1.5 1.5M9.5 9.5l2 2M12 7l1.5 1.5M14.5 4.5l2 2"/></svg><span id="spec-size"></span></div>
            <div class="spec"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="7.5" r="3.2"/><circle cx="5.5" cy="9" r="2.4"/><circle cx="18.5" cy="9" r="2.4"/><path d="M6.5 19c0-3.3 2.5-5.5 5.5-5.5s5.5 2.2 5.5 5.5zM1.5 18c0-2.4 1.6-4 4-4 .8 0 1.5.2 2.1.5A7 7 0 0 0 5.4 18zM22.5 18c0-2.4-1.6-4-4-4-.8 0-1.5.2-2.1.5a7 7 0 0 1 2.2 3.5z"/></svg><span id="spec-people"></span></div>
            <div class="spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"><path d="M4 20h16v-5H4zM6.5 15v-4h11v4M9 11V8h6v3M12 8V5.5"/><path d="M12 3.5c.8.6.8 1.4 0 2-.8-.6-.8-1.4 0-2z" fill="currentColor"/></svg><span>{{ __('cake_ar.common.tier1') }}</span></div>
        </div>
    </div>
    </div>

    <div class="steps">
    <div class="product-head">
        @if (\App\Services\ShopProducts::localName($product))<h1>{{ \App\Services\ShopProducts::localName($product) }}</h1>@endif
        <div class="sn">{{ __('cake_ar.shop.serial') }} <b>{{ $product['id'] }}</b></div>
        {{-- المقاس والسعر في جهة الطلب مثل صفحة جوماكيك --}}
        <div class="sn">{{ __('cake_ar.shop.price_label') }}</div>
        <div class="price-line">
            <span class="amount" id="price-amount">—</span>
            <span class="old" id="old-price"></span>
            <span class="off" id="price-off" hidden></span>
            <span class="tax">{{ __('cake_ar.shop.tax_included_paren') }}</span>
        </div>
    </div>

    <div class="tabs" id="tabs">
        <button type="button" class="tab active" data-tab="0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"><path d="M12 3 3.5 7.5v9L12 21l8.5-4.5v-9z"/><path d="M3.5 7.5 12 12l8.5-4.5M12 12v9"/></svg>
            {{ __('cake_ar.shop.tab_shape') }}</button>
        <button type="button" class="tab" data-tab="1">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><ellipse cx="9.5" cy="10" rx="6.5" ry="2.3"/><path d="M3 10v6.5c0 1.3 2.9 2.3 6.5 2.3s6.5-1 6.5-2.3V10M3 4.5h13M3 3v3M16 3v3M20.5 9v9.5M19 9h3M19 18.5h3"/></svg>
            {!! __('cake_ar.shop.tab_size') !!}</button>
        <button type="button" class="tab" data-tab="2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m14.5 4.5 3 3L9 16l-4 1 1-4z"/><path d="m13 6 3 3M3 21h9"/></svg>
            {!! __('cake_ar.shop.tab_ai') !!}</button>
        <button type="button" class="tab" data-tab="3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"><path d="M3 11.5 19 6.5l2 5v7H3z"/><path d="M3 11.5h18M3 15c2 1 3.5-1 5.5 0s3.5 1 5.5 0 3.5-1 7 0"/></svg>
            {!! __('cake_ar.shop.tab_flavor') !!}</button>
        <button type="button" class="tab" data-tab="4">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 5 3H2"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/></svg>
            {{ __('cake_ar.shop.tab_order') }}</button>
    </div>

    <main>
        <section class="panel active" data-panel="0">
            <div class="section-title">{{ __('cake_ar.shop.shape_title') }}</div>
            <div class="shape-grid" id="shape-cards"></div>
        </section>

        <section class="panel" data-panel="1">
            <div class="section-title">{{ __('cake_ar.shop.size_title') }}</div>
            <div class="scroll-row" id="size-options"></div>
            <div class="section-title">{{ __('cake_ar.shop.layers_title') }}</div>
            <div class="scroll-row" id="layer-options"></div>
            <div class="hint" id="cone-hint" hidden>{{ __('cake_ar.shop.cone_hint') }}</div>
        </section>

        <section class="panel" data-panel="2">
            <div class="ai-box">
                <div class="ai-result" id="ai-result">
                    <img id="ai-image" alt="{{ __('cake_ar.shop.ai_after_alt') }}">
                    <a class="download-btn" id="ai-download" href="#" download>⬇️ {{ __('cake_ar.common.download') }}</a>
                    <div class="row">
                        <button type="button" class="btn" id="ai-approve">{{ __('cake_ar.shop.approve') }}</button>
                        <button type="button" class="btn light" id="ai-reject">{{ __('cake_ar.shop.reject') }}</button>
                    </div>
                    <div class="approved">{{ __('cake_ar.shop.approved') }}</div>
                </div>
                <div class="ai-history" id="ai-history" hidden>
                    <div class="hint">{{ __('cake_ar.shop.history') }}</div>
                    <div class="thumbs" id="ai-history-thumbs"></div>
                </div>
                <div class="ai-chat" id="ai-chat">
                    <h4>{{ __('cake_ar.shop.chat_title') }}</h4>
                    <div class="hint" style="margin-top:0">{{ __('cake_ar.shop.chat_hint') }}</div>
                    <div class="chat-thread" id="chat-thread"></div>
                    <div class="thumbs" id="chat-thumbs"></div>
                    <div class="chat-count" id="chat-count"></div>
                    <div class="chat-input">
                        <label class="chat-attach" title="{{ __('cake_ar.common.chat_attach') }}" aria-label="{{ __('cake_ar.common.chat_attach') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                            <input type="file" id="chat-files" accept="image/png,image/jpeg,image/webp" multiple>
                        </label>
                        <textarea id="chat-input" maxlength="500" rows="1" enterkeyhint="send" placeholder="{{ __('cake_ar.shop.chat_placeholder') }}"></textarea>
                        <button type="button" class="btn" id="chat-send">{{ __('cake_ar.common.send') }}</button>
                    </div>
                    <div class="message error" id="chat-message"></div>
                </div>
                {{-- الصندوق القديم (وصف + صور + زر) مخفي: صار كل شي من المحادثة مثل الكعكة الخاصة --}}
                <div class="ai-start" id="ai-start" hidden>
                <div class="hint" style="margin-top:0">{{ __('cake_ar.shop.note_hint') }}</div>
                <textarea id="ai-note" maxlength="500" placeholder="{{ __('cake_ar.shop.note_placeholder') }}" style="margin-top:10px"></textarea>
                <div class="writing-count"><span id="ai-note-count">0</span>/500</div>
                <label class="upload" style="margin-top:8px">{{ __('cake_ar.shop.upload') }}
                    <input type="file" id="ai-reference" accept="image/png,image/jpeg,image/webp" multiple>
                </label>
                <div class="thumbs" id="ai-thumbs"></div>
                <button type="button" class="btn" id="ai-generate" {{ $hasKey && $aiRemaining !== 0 ? '' : 'disabled' }}>{{ __('cake_ar.shop.show_me') }}</button>
                <div class="hint" id="ai-remaining"></div>
                </div>
                <div class="message {{ $hasKey ? 'info' : 'error' }}" id="ai-message">{{ $hasKey ? '' : __('cake_ar.srv.no_key') }}</div>

            </div>
        </section>

        <section class="panel" data-panel="3">
            @if ($product['flavors'])
                <div class="section-title">{{ __('cake_ar.shop.flavor_title') }}</div>
                <div class="item-cards" id="flavor-cards"></div>
            @endif
            @if ($product['stuffings'])
                <div class="section-title">{{ __('cake_ar.shop.filling_title') }}</div>
                <div class="item-cards" id="filling-cards"></div>
            @endif
        </section>

        <section class="panel" data-panel="4">
            <div class="summary" id="summary"></div>
            <div class="message error" id="submit-message"></div>
            <button type="button" class="btn" id="submit">{{ __('cake_ar.shop.submit') }}</button>
        </section>

        <div class="nav-buttons">
            <button type="button" class="btn light" id="prev">{{ __('cake_ar.common.prev') }}</button>
            <button type="button" class="btn" id="next">{{ __('cake_ar.common.next') }}</button>
        </div>
    </main>
    </div>
    </div>

    <div class="modal center" id="ai-wait-modal" role="dialog" aria-live="polite">
        <div class="sheet">
            @include('partials.cake-loader')
            <h2>{{ __('cake_ar.common.wait_title') }}</h2>
            <div class="wait-time" id="ai-wait-time">{{ __('cake_ar.common.seconds', ['n' => ['high' => 129, 'medium' => 60, 'low' => 30][config('shop.image_quality') ?: config('services.openai.image_quality')] ?? 60]) }}</div>
            <div class="hint">{{ __('cake_ar.so.wait_hint') }}</div>
            <button type="button" class="btn light" id="ai-cancel">{{ __('cake_ar.common.cancel') }}</button>
        </div>
    </div>

    <div class="modal" id="done-modal">
        <div class="sheet">
            <h2>{{ __('cake_ar.shop.done_title') }}</h2>
            <div class="hint">{{ __('cake_ar.shop.done_hint') }}</div>
            <div class="summary" id="modal-summary"></div>
            <button type="button" class="btn" id="close-modal">{{ __('cake_ar.common.ok') }}</button>
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
        // رقم نسخة الصور: لما تتغير صور الأشكال يحمّلها الجوال من جديد بدل القديمة المحفوظة
        const SHAPE_V = "?v={{ max(array_map('filemtime', glob(public_path('images/shapes/*.webp')) ?: [0])) }}";
        const MAX_MODEL_LAYERS = 3;
        const MAX_REFERENCES = 6;
        // الترجمة: نصوص الصفحة من lang/{ar,en}/cake_ar.php حسب لغة الموقع
        const LOCALE = @json(app()->getLocale());
        const TR = { ...@json(__('cake_ar.common')), ...@json(__('cake_ar.shop')) };
        const t = (k, r = {}) => String(TR[k] ?? k).replace(/:(\w+)/g, (m, n) => (n in r ? r[n] : m));
        // عداد تنازلي وقت التوليد (طلب المدير) حسب جودة الصور: عالية 129 ثانية، متوسطة 60، منخفضة 30. لو طوّل يكتب "قربنا…"
        const WAIT_SECONDS = @json(['high' => 129, 'medium' => 60, 'low' => 30][config('shop.image_quality') ?: config('services.openai.image_quality')] ?? 60);
        const countdown = started => {
            const left = WAIT_SECONDS - Math.round((Date.now() - started) / 1000);
            return left > 0 ? t('seconds', { n: left }) : t('almost_ready');
        };
        // اسم الصنف (نكهة/حشوة) بلغة الموقع
        const itemName = item => (LOCALE === 'en' && item?.title_en) ? item.title_en : item?.name;
        const RIYAL = '<svg class="riyal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-label="' + t('riyal') + '"><path d="M8.5 2.5v14.5M14 4.5v15.5M3 11.5l18-4M3 16l18-4M14.5 20.5l6.5-1.5"/></svg>';
        const $ = id => document.getElementById(id);
@include('partials.flavor-pictures')
        const esc = t => String(t).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

        // كل أشكال الدور الواحد (الحين كلها متاحة لكل منتج، وبعدين تتحدد من لوحة التحكم).
        // مقاسات وطبقات كل شكل = اللي يجمع بين المنتج والشكل (القبة 3 طبقات، السن ست 6 و8 إنش…)
        const ALL_SHAPES = [
            { key: 'round', name: t('shape_round') },
            { key: 'heart', name: t('shape_heart') },
            { key: 'cone', name: t('shape_cone') },
            { key: 'sunset', name: t('shape_sunset') },
            { key: 'star', name: t('shape_star') },
            { key: 'dome', name: t('shape_dome') },
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
        const offLabel = d => d ? (d.type === 'percent' ? t('off', { n: d.amount }) : t('off_amount', { n: d.amount })) : '';
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
                    <img src="${SHAPE_IMAGES}/${c.key}.webp${SHAPE_V}" alt="" loading="lazy">
                    <div class="name">${c.name}</div>
`,
                    c === state.card,
                    () => { state.card = c; renderSizes(); refresh(); },
                ));
            });
            // شكل واحد بس: ينختار تلقائيًا
            if (shapes.length === 1) $('shape-cards').insertAdjacentHTML('afterend', `<div class="hint">${t('one_shape')}</div>`);
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
                    <div class="num">${t('inch', { n: s.inch })}</div>
                    <small>${t('people', { n: s.people })}</small>
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
                <div class="num">${t('layers_n', { n })}</div>
                <div class="price">+${money(layerExtra(state.size, n))}</div>`,
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
                plainLast(product[list], key).forEach(item => box.appendChild(card('item-card',
                    `${(pic => `<div class="pic${pic && /\/images\/(flavors|fillings)\//.test(pic) ? ' slice' : ''}" ${pic ? `style="background-image:url('${pic}')"` : ''}>${pic ? '' : icon}</div>`)(key === 'flavor' ? flavorPic(item) : fillingPic(item, state.flavor))}${esc(itemName(item))}${key === 'flavor' && item.extra ? `<span class="extra">+${money(item.extra)}</span>` : ''}`,
                    state[key]?.id === item.id,
                    () => { state[key] = item; if (key === 'flavor') renderItems(); refresh(); },
                )));
            });
        }

        function updateBadges() {
            const { before: b, final } = totals();
            $('price-amount').innerHTML = money(final);
            $('old-price').textContent = final < b ? b : '';
            $('price-off').hidden = !state.size.discount;
            $('price-off').textContent = offLabel(state.size.discount);
            $('spec-layers').textContent = shape() === 'cone' ? '—' : t('layer_n', { n: state.layers });
            $('spec-size').textContent = t('inch', { n: state.size.inch });
            $('spec-people').textContent = t('people', { n: state.size.people });
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
                if (mine === token) { $('viewer-status').hidden = false; $('viewer-status').textContent = t('viewer_failed'); }
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
            else alert(t('ar_phone'));
        });

        // صورة المنتج (أو التصميم المعتمد): تكبر فوق المجسم
        function setPhoto(url) { $('photo-big').src = url; }

        // ===== الخطوات =====
        const tabs = [...document.querySelectorAll('.tab')];
        function goTo(i, scroll = true) {
            state.tab = i;
            tabs.forEach((t, n) => { t.classList.toggle('active', n === i); t.classList.toggle('done', n < i); });
            document.querySelectorAll('.panel').forEach(p => p.classList.toggle('active', Number(p.dataset.panel) === i));
            $('prev').style.visibility = i === 0 ? 'hidden' : 'visible';
            $('next').style.display = i === tabs.length - 1 ? 'none' : '';
            renderSummary($('summary'));
            if (scroll) window.scrollTo({ top: $('tabs').getBoundingClientRect().top + window.scrollY - (matchMedia('(min-width: 860px)').matches ? 14 : 8), behavior: 'smooth' });
        }
        tabs.forEach((t, i) => t.addEventListener('click', () => goTo(i)));
        $('prev').addEventListener('click', () => goTo(Math.max(0, state.tab - 1)));
        $('next').addEventListener('click', () => goTo(Math.min(tabs.length - 1, state.tab + 1)));

        $('ai-note').addEventListener('input', () => { $('ai-note-count').textContent = $('ai-note').value.length; });

        // ===== الذكاء الاصطناعي: صورة المنتج + شكل المجسم + تعديل الزبون =====
        let refFiles = [], aiAbort = null;
        const aiImages = [];
        // كل صورة والتعديلات اللي طلعت منها (الوصف الأول + طلبات المحادثة)، ترسل مع التعديل الجاي حتى ما تضيع
        const imageEdits = {};
        const canGenerate = () => state.aiRemaining === null || state.aiRemaining > 0;
        function aiMessage(text, type = 'info') { $('ai-message').textContent = text; $('ai-message').className = 'message ' + type; }
        function renderRemaining() {
            $('ai-remaining').textContent = state.aiRemaining === null ? '' : t('remaining', { n: state.aiRemaining });
            $('ai-generate').disabled = !HAS_KEY || !canGenerate();
        }

        function renderRefs() {
            $('ai-thumbs').innerHTML = '';
            refFiles.forEach((f, i) => {
                const wrap = document.createElement('div');
                wrap.className = 'thumb';
                wrap.innerHTML = `<img src="${URL.createObjectURL(f)}" alt=""><button type="button" class="remove" aria-label="${t('remove_photo')}">×</button>`;
                wrap.querySelector('button').addEventListener('click', () => { refFiles.splice(i, 1); renderRefs(); });
                $('ai-thumbs').appendChild(wrap);
            });
        }
        $('ai-reference').addEventListener('change', e => {
            const picked = [...e.target.files];
            if (refFiles.length + picked.length > MAX_REFERENCES) aiMessage(t('max_refs', { n: MAX_REFERENCES }));
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
            state.aiApproved = true; // الصورة الحالية في المحادثة هي المعتمدة
            $('ai-image').src = url;
            $('ai-download').href = url;
            $('ai-download').download = `jomacake-${product.id}-${aiImages.indexOf(url) + 1}.png`;
            $('ai-result').className = 'ai-result show';
            // الصورة المولّدة تصير الصورة الرئيسية فوق (وتبقى في الخطوات اللي بعدها)
            setPhoto(url);
            $('ai-history').hidden = aiImages.length < 2;
            $('ai-chat').hidden = false;
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
            return { data: { message: t('conn_lost') }, ok: false };
        }

        $('ai-cancel').addEventListener('click', () => aiAbort?.abort());
        // أول صورة: من أول رسالة في المحادثة (الوصف في ai-note والصور في refFiles)
        $('ai-generate').addEventListener('click', () => generateFirst());
        async function generateFirst() {
            let done = false;
            const abort = aiAbort = new AbortController();
            const started = Date.now();
            const tick = () => { $('ai-wait-time').textContent = countdown(started); };
            tick();
            const timer = setInterval(tick, 1000);
            // التوليد بدأ: نسكّر الكيبورد عشان تبان شاشة الانتظار كاملة
            $('chat-input').blur();
            document.body.classList.remove('typing');
            $('ai-wait-modal').classList.add('show');
            aiMessage('');
            $('ai-generate').disabled = true;

            const form = new FormData();
            form.append('shape', shape());
            form.append('size', state.size.id);
            form.append('layers', state.layers);
            form.append('note', $('ai-note').value.trim());
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
                    // الاستضافة قطعت الطلب (502/503/504/524…) أو رجّعت رد بدون رسالة، والصورة ممكن تكون طلعت في السيرفر:
                    // نسأل عن النتيجة بدل ما نقول "صار خطأ"
                    if (!ok && !(data && (data.message || data.errors))) throw new Error('gateway');
                } catch (cut) {
                    if (abort.signal.aborted) throw cut;
                    ({ data, ok } = await waitForJob(job, abort.signal));
                }
                if (data.remaining !== undefined) state.aiRemaining = data.remaining;
                if (!ok) {
                    const firstError = data.errors && Object.values(data.errors)[0]?.[0];
                    throw new Error(firstError || data.message || t('error_retry'));
                }
                aiImages.push(data.image);
                imageEdits[data.image] = [$('ai-note').value.trim()].filter(Boolean);
                showAiImage(data.image);
                bubble('assistant', t('here_is_cake'), data.image);
                $('ai-generate').textContent = t('try_again');
                done = true;
            } catch (e) {
                aiMessage(abort.signal.aborted ? t('cancelled') : e.message, abort.signal.aborted ? 'info' : 'error');
            } finally {
                clearInterval(timer);
                $('ai-wait-modal').classList.remove('show');
                if (aiAbort === abort) aiAbort = null;
                renderRemaining();
                renderSummary($('summary'));
            }
            return done;
        }

        // ===== المحادثة: الزبون يكتب، الذكاء يرد أو يسأل، والتعديل على الصورة اللي فوق =====
        const chatUrl = "{{ route('shop.ai.chat', $product['id']) }}";
        const editUrl = "{{ route('shop.ai.edit', $product['id']) }}";
        const chatMessages = [];
        // الذكاء الاصطناعي "يفكر" شوي قبل ما يرد: ثلاث نقاط تتحرك، وأقل مدة ثانية ونص حتى للردود السريعة
        const THINK_MS = 1500;
        const sleep = ms => new Promise(res => setTimeout(res, ms));
        const thinkRest = started => sleep(Math.max(0, THINK_MS - (Date.now() - started)));
        async function thinking() {
            const dots = bubble('assistant typing', '');
            $('chat-send').disabled = true;
            await sleep(THINK_MS);
            dots.remove();
            $('chat-send').disabled = false;
        }
        function bubble(role, text, image) {
            const b = document.createElement('div');
            b.className = 'bubble ' + role;
            if (role.includes('typing')) b.innerHTML = '<span class="dots" aria-label="…"><i></i><i></i><i></i></span>';
            else b.textContent = text;
            if (image) {
                const img = document.createElement('img');
                img.src = image;
                img.alt = t('edited_alt');
                img.addEventListener('click', () => { showAiImage(image); $('ai-image').scrollIntoView({ behavior: 'smooth', block: 'center' }); });
                b.appendChild(img);
                // تحميل الصورة من داخل المحادثة
                const dl = document.createElement('a');
                dl.className = 'bubble-download';
                dl.href = image;
                dl.download = `jomacake-${(image.split('/').pop() || 'cake').replace(/\.\w+$/, '')}.png`;
                dl.textContent = '⬇️ ' + t('download');
                b.appendChild(dl);
            }
            $('chat-thread').appendChild(b);
            // ننزل للرسالة الجديدة (مو وقت استرجاع المسودة)
            let quiet = false;
            try { quiet = restoring; } catch (e) { /* قبل تعريف المسودة */ }
            if (!quiet) requestAnimationFrame(() => b.scrollIntoView({ behavior: 'smooth', block: 'nearest' }));
            return b;
        }
        const specs = () => ({ shape: shape(), size: state.size.id, layers: state.layers });

        async function editImage(instruction, files = []) {
            const base = state.aiImage;
            const abort = aiAbort = new AbortController();
            const started = Date.now();
            const tick = () => { $('ai-wait-time').textContent = countdown(started); };
            tick();
            const timer = setInterval(tick, 1000);
            // التوليد بدأ: نسكّر الكيبورد عشان تبان شاشة الانتظار كاملة
            $('chat-input').blur();
            document.body.classList.remove('typing');
            $('ai-wait-modal').classList.add('show');
            const job = Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
            try {
                let data, ok;
                try {
                    const form = new FormData();
                    Object.entries(specs()).forEach(([k, v]) => form.append(k, v));
                    form.append('image', base.split('/').pop());
                    form.append('instruction', instruction);
                    (imageEdits[base] || []).forEach(p => form.append('previous[]', p));
                    form.append('job', job);
                    // صور الزبون للتوضيح مع هذي الرسالة
                    for (const f of await Promise.all(files.map(shrink))) form.append('references[]', f);
                    const res = await fetch(editUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: form,
                        signal: abort.signal,
                    });
                    data = await res.json();
                    ok = res.ok;
                    // الاستضافة قطعت الطلب (502/503/504/524…) أو رجّعت رد بدون رسالة، والصورة ممكن تكون طلعت في السيرفر:
                    // نسأل عن النتيجة بدل ما نقول "صار خطأ"
                    if (!ok && !(data && (data.message || data.errors))) throw new Error('gateway');
                } catch (cut) {
                    if (abort.signal.aborted) throw cut;
                    ({ data, ok } = await waitForJob(job, abort.signal));
                }
                if (data.remaining !== undefined) state.aiRemaining = data.remaining;
                if (!ok) throw new Error((data.errors && Object.values(data.errors)[0]?.[0]) || data.message || t('edit_failed'));
                aiImages.push(data.image);
                imageEdits[data.image] = [...(imageEdits[base] || []), instruction].slice(-12);
                showAiImage(data.image);
                bubble('assistant', t('after_edit'), data.image);
            } catch (e) {
                bubble('assistant', abort.signal.aborted ? t('edit_stopped') : e.message);
            } finally {
                clearInterval(timer);
                $('ai-wait-modal').classList.remove('show');
                if (aiAbort === abort) aiAbort = null;
                renderRemaining();
                renderSummary($('summary'));
            }
        }

        // صور يرفقها الزبون مع رسالته في المحادثة (لين 6)
        let chatFiles = [];
        function renderChatFiles() {
            $('chat-thumbs').innerHTML = '';
            chatFiles.forEach((f, i) => {
                const thumb = document.createElement('div');
                thumb.className = 't';
                thumb.innerHTML = `<img src="${URL.createObjectURL(f)}" alt=""><button type="button" aria-label="${t('remove_photo')}">×</button>`;
                thumb.querySelector('button').addEventListener('click', () => { chatFiles.splice(i, 1); renderChatFiles(); });
                $('chat-thumbs').appendChild(thumb);
            });
            // عداد الصور المرفقة (مثل 5/6)
            $('chat-count').textContent = chatFiles.length ? t('photos_count', { n: chatFiles.length, max: 6 }) : '';
        }
        $('chat-files').addEventListener('change', e => {
            chatFiles = [...chatFiles, ...e.target.files].slice(0, 6);
            e.target.value = '';
            renderChatFiles();
        });

        // قبل أول صورة: وصف الزبون (يروح مع الطلب حتى لو ما ولّد صورة)، والرد على سؤال "تبين أولّد؟"
        const pendingFirst = [];
        const firstChat = [];
        const YES = /^(اي+وه?|ايوا|إي+|أيو[هة]|ايو[هة]|اي|نعم|اكيد|أكيد|يس|yes|y|ok|okay|sure|yeah|yep|please|go ahead|اوك|اوكي|أوكي|تمام|طيب|ماشي|ولدي?|ولّدي?|يلا|بدي|ابي|أبي|ابغى|أبغى)( .*)?$/i;
        const NO = /^(لا+|لأ|لاء|no|n|nope|no thanks|not now|مابدي|ما بدي|مابي|ما ابي|ما أبي|ماابغى|ما ابغى|ما أبغى|بدون|مو لازم|لا شكرا|كملي|أكمل|اكمل)( .*)?$/i;
        function keepNote(text) {
            if (!text || text === t('edit_by_refs')) return;
            const prev = $('ai-note').value.trim();
            $('ai-note').value = prev ? `${prev}\n${text}` : text;
            renderSummary($('summary'));
        }
        // يكمّل بدون صورة: نسكّر الكيبورد عشان يبان "التالي"، وننزل له ونخليه يلمع
        function showNext() {
            $('chat-input').blur();
            document.body.classList.remove('typing');
            setTimeout(() => {
                $('next').scrollIntoView({ behavior: 'smooth', block: 'center' });
                $('next').classList.remove('pulse'); void $('next').offsetWidth; $('next').classList.add('pulse');
            }, 350);
        }
        async function sendChat() {
            const files = chatFiles.slice();
            let text = $('chat-input').value.trim();
            if ((!text && !files.length) || $('chat-send').disabled) return;
            if (!text) text = t('edit_by_refs');
            $('chat-message').textContent = '';
            $('chat-input').value = '';
            chatFiles = [];
            renderChatFiles();
            const userBubble = bubble('user', text);
            if (files.length) {
                const refs = document.createElement('div');
                refs.className = 'refs';
                files.forEach(f => { const im = document.createElement('img'); im.src = URL.createObjectURL(f); im.alt = ''; refs.appendChild(im); });
                userBubble.appendChild(refs);
            }
            // الموديل النصي ما يشوف الصور: نقول له إن فيه صور مرفقة، وتنرسل مع التعديل
            const content = files.length ? `${text} ${t('attached_n', { n: files.length })}` : text;
            // ما فيه صورة لسا: نسأل الزبون يولّد صورة بالتعديل، أو يكمّل على نفس شكل المنتج والوصف اللي كتبه
            if (!aiImages.length) {
                if (files.length) { refFiles = [...refFiles, ...files].slice(0, MAX_REFERENCES); renderRefs(); }
                if (!canGenerate()) { await thinking(); bubble('assistant', t('clear') + t('no_more')); keepNote(text); showNext(); return; }
                if (!pendingFirst.length) {
                    pendingFirst.push(text === t('edit_by_refs') ? '' : text);
                    keepNote(text);
                    firstChat.push({ role: 'user', content }, { role: 'assistant', content: t('ask_generate') });
                    await thinking();
                    bubble('assistant', t('ask_generate'));
                    return;
                }
                firstChat.push({ role: 'user', content });
                const typing = bubble('assistant typing', '');
                const started = Date.now();
                $('chat-send').disabled = true;
                let answer;
                try {
                    const res = await fetch(chatUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({ ...specs(), stage: 'first', messages: firstChat.slice(-12) }),
                    });
                    answer = await res.json();
                    if (!res.ok) throw new Error((answer.errors && Object.values(answer.errors)[0]?.[0]) || answer.message || t('error_retry'));
                } catch (e) {
                    // ما رد الذكاء الاصطناعي: نفهم الردود الواضحة بنفسنا
                    const short = text.replace(/[.!؟?،,\s]+/g, ' ').trim();
                    answer = YES.test(short) ? { action: 'generate', reply: '', note: '' }
                        : NO.test(short) && short.split(' ').length <= 3 ? { action: 'skip', reply: t('no_more') }
                        : null;
                    if (!answer) {
                        typing.remove(); firstChat.pop();
                        $('chat-message').textContent = e.message;
                        $('chat-input').value = text;
                        $('chat-send').disabled = false;
                        return;
                    }
                }
                await thinkRest(started);
                typing.remove();
                if (answer.reply) { bubble('assistant', answer.reply); firstChat.push({ role: 'assistant', content: answer.reply }); }
                if (answer.action === 'ask') {
                    pendingFirst.push(text);
                    keepNote(text);
                } else if (answer.action === 'skip') {
                    showNext();
                } else if (answer.action === 'generate') {
                    $('ai-note').value = (answer.note || pendingFirst.filter(Boolean).join('\n')).trim();
                    if (await generateFirst()) {
                        chatMessages.push({ role: 'user', content: $('ai-note').value || t('made_it') }, { role: 'assistant', content: t('made_it') });
                        pendingFirst.length = 0;
                        firstChat.length = 0;
                    } else {
                        bubble('assistant', ($('ai-message').textContent || t('error_retry')) + ' ' + t('retry_yes'));
                    }
                }
                $('chat-send').disabled = false;
                return;
            }
            chatMessages.push({ role: 'user', content });
            const typing = bubble('assistant typing', '');
            const started = Date.now();
            $('chat-send').disabled = true;
            try {
                const res = await fetch(chatUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ ...specs(), messages: chatMessages.slice(-12) }),
                });
                const data = await res.json();
                await thinkRest(started);
                typing.remove();
                if (!res.ok) throw new Error((data.errors && Object.values(data.errors)[0]?.[0]) || data.message || t('error_retry'));
                bubble('assistant', data.reply);
                chatMessages.push({ role: 'assistant', content: data.reply });
                if (data.action === 'edit') {
                    if (!canGenerate()) bubble('assistant', t('no_more_tries'));
                    else await editImage(data.instruction, files);
                }
            } catch (e) {
                typing.remove();
                chatMessages.pop();
                $('chat-message').textContent = e.message;
                $('chat-input').value = text;
                chatFiles = files;
                renderChatFiles();
            } finally {
                $('chat-send').disabled = false;
            }
        }
        $('chat-send').addEventListener('click', sendChat);
        $('chat-input').addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendChat(); } });
        // بعض كيبوردات الجوال (أندرويد) ما ترسل Enter كزر: لو انضاف سطر جديد نرسل بدل ما ننزل سطر
        $('chat-input').addEventListener('beforeinput', e => { if (e.inputType === 'insertLineBreak' || e.inputType === 'insertParagraph') { e.preventDefault(); sendChat(); } });

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
            aiMessage(t('reject_msg'));
            $('chat-input').focus();
            renderSummary($('summary'));
        });

        // ===== الطلب =====
        // الملخص بدلائل بصرية: صورة الشكل، رسمة المقاس والطبقات، صورة النكهة والحشوة
        function visual(pic, text) {
            return `<span class="vis"><span>${esc(text)}</span><span class="vis-pic">${pic}</span></span>`;
        }
        const imgPic = (url, icon) => url ? `<img src="${url}" alt="">` : `<span class="vis-icon">${icon}</span>`;
        function sizePic(inch) {
            const px = 18 + (inch - 4) * 4;
            return `<svg width="${px}" height="${px}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="${SHAPE_PATHS[shape()]}"/></svg>`;
        }
        const layersPic = n => `<span class="vis-bars">${Array.from({ length: n }, (_, i) => `<i style="background:${LAYER_COLORS[i % LAYER_COLORS.length]}"></i>`).join('')}</span>`;

        function renderSummary(box) {
            const { before: b, final } = totals();
            const rows = [
                [t('sum_product'), visual(`<img src="${product.image}" alt="">`, `S.N: ${product.id}`)],
                [t('sum_shape'), visual(`<img class="fit" src="${SHAPE_IMAGES}/${shape()}.webp${SHAPE_V}" alt="">`, state.card.name)],
                [t('sum_size'), visual(sizePic(state.size.inch), t('inch', { n: state.size.inch }))],
                ...(shape() === 'cone' ? [] : [[t('sum_layers'), visual(layersPic(state.layers), t('layers_n', { n: state.layers }))]]),
                [t('sum_flavor'), state.flavor ? visual(imgPic(flavorPic(state.flavor), '🍰'), itemName(state.flavor)) : '—'],
                [t('sum_filling'), state.filling ? visual(imgPic(fillingPic(state.filling, state.flavor), '🍓'), itemName(state.filling)) : '—'],
                ...($('ai-note').value.trim() ? [[t('sum_edit'), esc($('ai-note').value.trim())]] : []),
            ];
            box.innerHTML = `<dl>${rows.map(([k, v]) => `<div class="line"><dt>${k}</dt><dd>${v}</dd></div>`).join('')}
                <div class="line"><dt>${t('sum_design')}</dt><dd class="design"><img src="${state.aiApproved ? state.aiImage : product.image}" alt=""></dd></div>
                ${final < b ? `<div class="line"><dt>${t('before_discount')}</dt><dd class="old">${b}</dd></div>` : ''}
                <div class="line total"><dt>${t('total')}</dt><dd>${money(final)}</dd></div></dl>`;
        }

        $('submit').addEventListener('click', () => {
            const missing = [];
            if (product.stuffings.length && !state.filling) missing.push(t('sum_filling'));
            if (missing.length) { $('submit-message').textContent = t('complete') + missing.join(t('list_sep')); return; }
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
<script>
    // خانة المحادثة تكبر مع الكتابة، وتطلع فوق الكيبورد في الجوال عشان الزبون يشوف وش يكتب
    (function () {
        const t = document.getElementById('chat-input');
        if (!t) return;
        const grow = () => { t.style.height = 'auto'; t.style.height = Math.min(t.scrollHeight + 2, 160) + 'px'; };
        t.addEventListener('input', grow);
        const show = () => setTimeout(() => t.scrollIntoView({ block: 'center', behavior: 'smooth' }), 350);
        t.addEventListener('focus', () => { document.body.classList.add('typing'); show(); });
        t.addEventListener('blur', () => setTimeout(() => { if (document.activeElement !== t) document.body.classList.remove('typing'); }, 200));
        // ما نرجّع الصفحة للخانة مع كل تغيير في الشاشة: كان يسحب الزبون لتحت كل ما حاول يطلع لفوق
        // لو تغيّر النص من الكود (مثل بعد الإرسال) نرجّع الحجم
        let last = t.value;
        setInterval(() => { if (t.value !== last) { last = t.value; t.value ? grow() : (t.style.height = ''); } }, 400);
    })();

    // تحميل صور الذكاء الاصطناعي بصيغة PNG، وفي الجوال تنحفظ في الصور (حفظ الصورة)
    async function asPng(url) {
        const blob = await (await fetch(url)).blob();
        if (blob.type === 'image/png') return blob;
        const bmp = await createImageBitmap(blob);
        const c = document.createElement('canvas'); c.width = bmp.width; c.height = bmp.height;
        c.getContext('2d').drawImage(bmp, 0, 0);
        return await new Promise(r => c.toBlob(r, 'image/png'));
    }
    document.addEventListener('click', async e => {
        const a = e.target.closest('a.bubble-download, a#ai-download');
        if (!a || !a.href || a.getAttribute('href') === '#') return;
        e.preventDefault();
        const name = (a.getAttribute('download') || 'jomacake.png').replace(/\.\w+$/, '') + '.png';
        try {
            const png = await asPng(a.href);
            const file = new File([png], name, { type: 'image/png' });
            const mobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
            if (mobile && navigator.canShare && navigator.canShare({ files: [file] })) {
                try { await navigator.share({ files: [file] }); } catch (err) { /* سكّر النافذة */ }
                return;
            }
            const link = document.createElement('a');
            link.href = URL.createObjectURL(file);
            link.download = name;
            document.body.appendChild(link); link.click(); link.remove();
            setTimeout(() => URL.revokeObjectURL(link.href), 4000);
        } catch (err) {
            window.open(a.href, '_blank');
        }
    });
</script>
</body>
</html>
