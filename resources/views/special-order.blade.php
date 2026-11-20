<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('cake_ar.so.title') }}</title>

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
            --bg: #FDFAF8;
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
            flex: 0 0 132px; scroll-snap-align: start; border: 1.5px solid transparent; background: #fff; border-radius: 16px; box-shadow: 0 1px 6px rgba(75,21,40,.07);
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

        /* المحادثة مع الذكاء الاصطناعي بعد أول صورة */
        .ai-chat { margin-top: 4px; }
        .ai-chat h4 { margin: 0 0 2px; font-size: 14px; }
        .chat-thread { display: flex; flex-direction: column; gap: 8px; margin: 10px 0; max-height: 360px; overflow-y: auto; padding: 2px; }
        .bubble { max-width: 85%; padding: 9px 12px; border-radius: 16px; font-size: 13px; line-height: 1.6; white-space: pre-wrap; word-break: break-word; }
        .bubble.user { align-self: flex-start; background: var(--pink); color: #fff; border-bottom-right-radius: 4px; }
        .bubble.assistant { align-self: flex-end; background: #FAF3F5; color: var(--ink); border-bottom-left-radius: 4px; }
        .bubble.typing { color: var(--muted); }
        /* بالإنجليزي: رسالة الزبون يمين والذكاء الاصطناعي يسار */
        [dir="ltr"] .bubble.user { align-self: flex-end; border-bottom-right-radius: 16px; border-bottom-left-radius: 4px; }
        [dir="ltr"] .bubble.assistant { align-self: flex-start; border-bottom-left-radius: 16px; border-bottom-right-radius: 4px; }
        .bubble-download { display: inline-block; margin-top: 6px; padding: 6px 12px; border-radius: 16px; border: 1.5px solid var(--pink-line); background: #fff; color: var(--pink-dark); font-size: 12px; font-weight: 800; text-decoration: none; }
        .bubble img { display: block; width: 160px; height: 160px; object-fit: cover; border-radius: 12px; margin-top: 6px; cursor: pointer; border: 1px solid var(--pink-line); }
        /* خانة الكتابة مستطيل بعرض الصندوق، وزر الإرفاق وزر الإرسال تحتها */
        .chat-input { display: grid; grid-template-columns: auto 1fr auto; grid-template-areas: "text text text" "attach . send"; gap: 8px; align-items: center; }
        .chat-input textarea { grid-area: text; width: 100%; box-sizing: border-box; min-height: 96px; height: 96px; max-height: 200px; border-radius: 12px; font-size: 16px; line-height: 1.5; resize: none; overflow-y: auto; }
        .chat-input .chat-attach { grid-area: attach; }
        .chat-input .btn { grid-area: send; width: auto; margin: 0; padding: 12px 26px; }
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
        /* فوق: مشبك الإرفاق والصورة المولّدة مكان المجسم، و"شاهد على طاولتك" على الصورة */
        .step-shape .price-card, .step-shape .spec-bar { display: none; }
        .top-view { position: absolute; inset: 0; z-index: 2; background: var(--bg); border: none; width: 100%; }
        .top-view[hidden] { display: none; }
        .attach-view { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 10px 12px 58px; }
        .attach-view[hidden] { display: none; }
        .attach-btn { border: none; background: none; cursor: pointer; color: #444; font-family: inherit; font-size: 18px; font-weight: 800; display: flex; flex-direction: column; align-items: center; gap: 8px; }
        .attach-btn svg { width: 110px; height: auto; transform: rotate(-35deg); }
        .attach-view.has-refs .attach-btn svg { width: 64px; }
        .attach-view.has-refs .attach-btn { font-size: 15px; }
        /* الصور المرفقة تحت المشبك، صف واحد */
        .refs-grid { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }
        .refs-grid:empty { display: none; }
        .refs-grid .ref { position: relative; width: 58px; height: 58px; border-radius: 10px; overflow: hidden; border: 1px solid var(--pink-line); background: #FAF3F5; }
        .refs-grid .ref img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .refs-grid .ref .remove { position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; border-radius: 50%; border: 1.5px solid #fff; background: var(--ink); color: #fff; font-size: 10px; line-height: 1; cursor: pointer; padding: 0; }
        /* الصورة المولّدة كاملة بدون قص ولا زوم، والجوانب بلون خلفية الصورة نفسها */
        .gen-view { background: #F4F1F0; display: flex; justify-content: center; align-items: center; overflow: hidden; }
        .gen-view[hidden] { display: none; }
        /* الصورة بطولها كاملة، وأطرافها تذوب في الخلفية عشان ما يبان خط */
        .gen-view img { height: 100%; width: auto; max-width: 100%; object-fit: contain; display: block;
            -webkit-mask-image: linear-gradient(to right, transparent 0, #000 7%, #000 93%, transparent 100%);
                    mask-image: linear-gradient(to right, transparent 0, #000 7%, #000 93%, transparent 100%); }
        .viewer-wrap .price-card { z-index: 3; }
        /* بالإنجليزي (من اليسار لليمين): السعر يسار وزر الطاولة يمين */
        [dir="ltr"] .price-card { right: auto; left: 12px; }
        [dir="ltr"] .ar-float { left: auto; right: 12px; }
        .ar-float { position: absolute; left: 12px; bottom: 12px; z-index: 3; font-size: 12px; padding: 8px 12px; box-shadow: 0 2px 8px rgba(0,0,0,.18); }
        .spec-bar .specs { justify-content: space-around; gap: 10px; padding: 12px 10px; }
        .spec-bar .spec { font-size: 13px; gap: 6px; }
        .spec-bar .spec svg { width: 22px; height: 22px; }

        .nav-buttons { display: flex; gap: 8px; margin-top: 22px; }
        .nav-buttons .btn { margin-top: 0; }

        .summary { border: 1.5px solid #EFDDE4; border-radius: 16px; padding: 4px 14px; margin-top: 16px; background: #fff; }
        .summary dl { margin: 0; }
        .summary .line { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-bottom: 1px solid #F5EBEF; font-size: 13px; }
        .summary .line:last-child { border-bottom: none; }
        .summary dt { color: var(--muted); font-weight: 700; }
        .summary dd { margin: 0; font-weight: 800; text-align: left; }
        .summary .total dd { font-size: 17px; color: var(--pink); }
        .summary .line { align-items: center; }
        /* الأيقونات عمود واحد على اليسار، والكلام على يمينها */
        .summary .vis { display: flex; align-items: center; justify-content: flex-end; gap: 10px; }
        .summary .line dd:has(.vis) { flex: 1; }
        .summary .vis-pic { width: 44px; height: 44px; border-radius: 10px; background: #FAF3F5; display: inline-flex; align-items: center; justify-content: center; color: var(--pink); flex-shrink: 0; overflow: hidden; font-size: 20px; }
        .summary .vis-pic img { width: 100%; height: 100%; object-fit: cover; }
        .summary .vis-pic img.fit { object-fit: contain; }
        .summary .vis-pic svg { max-width: 34px; max-height: 34px; }
        .summary .vis-icon { font-size: 22px; }
        .summary .vis-bars { display: flex; flex-direction: column-reverse; gap: 2px; width: 26px; }
        .summary .vis-bars i { display: block; height: 6px; border-radius: 2px; }
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
    </style>
<script>
    // خلفية مكان الصورة المولّدة بنفس لون خلفية الصورة (متوسط العمود الأيسر والأيمن من الصورة)
    function genEdgeColor(img) {
        try {
            const W = 48, H = 48, c = document.createElement('canvas'); c.width = W; c.height = H;
            const x = c.getContext('2d', { willReadFrequently: true }); x.drawImage(img, 0, 0, W, H);
            let r = 0, g = 0, b = 0, n = 0;
            [0, 1, W - 2, W - 1].forEach(col => {
                const d = x.getImageData(col, 0, 1, H).data;
                for (let i = 0; i < d.length; i += 4) { r += d[i]; g += d[i + 1]; b += d[i + 2]; n++; }
            });
            img.parentElement.style.background = `rgb(${Math.round(r / n)},${Math.round(g / n)},${Math.round(b / n)})`;
        } catch (e) { /* يبقى اللون الافتراضي */ }
    }
</script>
</head>
<body>

    <div class="top-fixed" id="top-fixed">
    <header class="site-header">
        <a href="https://jomacake.com"><img src="{{ asset('images/icons/logo.png') }}" alt="JOMACAKE" class="logo"></a>
        <nav>
            <a href="https://jomacake.com">{{ __('cake_ar.common.nav_home') }}</a>
            <a href="https://jomacake.com/shop">{{ __('cake_ar.common.nav_shop') }}</a>
            <a href="#" class="active">{{ __('cake_ar.common.nav_special') }}</a>
        </nav>
        <div class="header-end">
        @include('partials.lang-switch')
        <a href="https://jomacake.com/cart" class="cart" aria-label="{{ __('cake_ar.common.cart') }}">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 5 3H2"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/></svg>
        </a>
        </div>
    </header>
    <div class="sample-tag">{{ __('cake_ar.common.banner') }}</div>

    <div class="viewer-wrap">
        <model-viewer
            id="viewer"
            alt="{{ __('cake_ar.so.alt_cake') }}"
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
            {{-- زر AR الخاص بنا في شريط الملخص تحت، فنخفي زر model-viewer الافتراضي --}}
            <button slot="ar-button" hidden></button>
        </model-viewer>
        <div class="viewer-status" id="viewer-status">{{ __('cake_ar.common.preparing') }}</div>
        <div class="float-card price-card">
            <div class="amount" id="price-amount">—</div>
            <div class="caption">{{ __('cake_ar.common.start_price') }}</div>
        </div>
        {{-- في الشكل والحجم: المجسم 3D. من خطوة الوصف: المشبك لإرفاق الصور، والصور المرفقة صغيرة تحته (✕ لكل صورة)،
             وبعد التوليد الصورة المولّدة مكانهم --}}
        <div class="top-view attach-view" id="attach-view" hidden>
            <button type="button" class="attach-btn" id="attach-btn">
                <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"><path d="M44 22 26.5 39.5a5 5 0 0 1-7-7L38 14a9 9 0 0 1 12.7 12.7L31 46.4a13 13 0 0 1-18.4-18.4L30 10.6"/></svg>
                <span>{{ __('cake_ar.so.attach') }}</span>
            </button>
            <div class="refs-grid" id="refs-grid"></div>
        </div>
        <input type="file" id="ai-reference" accept="image/png,image/jpeg,image/webp" multiple hidden>
        <div class="top-view gen-view" id="gen-view" hidden><img id="gen-image" alt="{{ __('cake_ar.so.gen_alt') }}" onload="genEdgeColor(this)"></div>
        <button type="button" class="ar-btn ar-float" id="ar-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 7.5v9L12 21l8-4.5v-9z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/></svg>
            {{ __('cake_ar.common.ar_button') }}
        </button>
    </div>

    <div class="spec-bar">
        <div class="specs">
            <div class="spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="m12 4 8 4-8 4-8-4z" fill="currentColor" fill-opacity=".9"/><path d="m4 12 8 4 8-4M4 16l8 4 8-4"/></svg><span id="spec-layers"></span></div>
            <div class="spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M3 15.5 15.5 3 21 8.5 8.5 21z"/><path d="m7 12 1.5 1.5M9.5 9.5l2 2M12 7l1.5 1.5M14.5 4.5l2 2"/></svg><span id="spec-size"></span></div>
            <div class="spec"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="7.5" r="3.2"/><circle cx="5.5" cy="9" r="2.4"/><circle cx="18.5" cy="9" r="2.4"/><path d="M6.5 19c0-3.3 2.5-5.5 5.5-5.5s5.5 2.2 5.5 5.5zM1.5 18c0-2.4 1.6-4 4-4 .8 0 1.5.2 2.1.5A7 7 0 0 0 5.4 18zM22.5 18c0-2.4-1.6-4-4-4-.8 0-1.5.2-2.1.5a7 7 0 0 1 2.2 3.5z"/></svg><span id="spec-people"></span></div>
            <div class="spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"><path d="M4 20h16v-5H4zM6.5 15v-4h11v4M9 11V8h6v3M12 8V5.5"/><path d="M12 3.5c.8.6.8 1.4 0 2-.8-.6-.8-1.4 0-2z" fill="currentColor"/></svg><span id="spec-tiers"></span></div>
        </div>
    </div>

    </div>

    <div class="tabs" id="tabs">
        <button type="button" class="tab active" data-tab="0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"><path d="M12 3 3.5 7.5v9L12 21l8.5-4.5v-9z"/><path d="M3.5 7.5 12 12l8.5-4.5M12 12v9"/></svg>
            {!! __('cake_ar.so.tab_shape') !!}</button>
        <button type="button" class="tab" data-tab="1">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><ellipse cx="9.5" cy="10" rx="6.5" ry="2.3"/><path d="M3 10v6.5c0 1.3 2.9 2.3 6.5 2.3s6.5-1 6.5-2.3V10M3 4.5h13M3 3v3M16 3v3M20.5 9v9.5M19 9h3M19 18.5h3"/></svg>
            {!! __('cake_ar.so.tab_size') !!}</button>
        <button type="button" class="tab" data-tab="2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m14.5 4.5 3 3L9 16l-4 1 1-4z"/><path d="m13 6 3 3M3 21h9"/></svg>
            {!! __('cake_ar.so.tab_desc') !!}</button>
        <button type="button" class="tab" data-tab="3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"><path d="M3 11.5 19 6.5l2 5v7H3z"/><path d="M3 11.5h18M3 15c2 1 3.5-1 5.5 0s3.5 1 5.5 0 3.5-1 7 0"/></svg>
            {!! __('cake_ar.so.tab_flavor') !!}</button>
        <button type="button" class="tab" data-tab="4">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><rect x="3" y="5" width="15" height="14" rx="2"/><path d="M3 9.5h15M7 3v4M14 3v4M6.5 12.5h1M10 12.5h1M13.5 12.5h1M6.5 15.5h1M10 15.5h1"/><circle cx="18" cy="17.5" r="3.6" fill="#fff"/><path d="m16.4 17.6 1.1 1.1 2-2.2"/></svg>
            {!! __('cake_ar.so.tab_date') !!}</button>
    </div>

    <main>
        {{-- 1) الشكل --}}
        <section class="panel active" data-panel="0">
            <div class="section-title">{{ __('cake_ar.so.shape_title') }}</div>
            <div class="shape-grid" id="shape-cards"></div>
        </section>

        {{-- 2) الحجم والطبقات --}}
        <section class="panel" data-panel="1">
            <div class="section-title">{{ __('cake_ar.so.size_title') }}</div>
            <div class="scroll-row" id="size-options"></div>

            <div id="layers-block">
                <div class="section-title">{{ __('cake_ar.so.layers_title') }} <small>{{ __('cake_ar.so.per_tier') }}</small></div>
                <div class="scroll-row" id="layer-options"></div>
            </div>
            <div class="hint" id="cone-hint" hidden>{{ __('cake_ar.so.cone_hint') }}</div>
        </section>

        {{-- 3) النكهة والحشوة --}}
        <section class="panel" data-panel="2">
            <div class="ai-box">

                <button type="button" id="ai-generate" hidden></button>
                <div class="hint" id="ai-remaining"></div>
                <div class="message {{ $hasKey ? 'info' : 'error' }}" id="ai-message">{{ $hasKey ? '' : __('cake_ar.srv.no_key') }}</div>

                <div class="ai-result" id="ai-result">
                    <img id="ai-image" alt="{{ __('cake_ar.so.ai_alt') }}">
                    <a class="download-btn" id="ai-download" href="#" download>⬇️ {{ __('cake_ar.common.download') }}</a>
                    <div class="row">
                        <button type="button" class="btn" id="ai-approve">{{ __('cake_ar.so.approve') }}</button>
                        <button type="button" class="btn light" id="ai-reject">{{ __('cake_ar.so.reject') }}</button>
                    </div>
                    <div class="approved">{{ __('cake_ar.so.approved') }}</div>
                </div>
                <div class="ai-history" id="ai-history" hidden>
                    <div class="hint">{{ __('cake_ar.so.history') }}</div>
                    <div class="thumbs" id="ai-history-thumbs"></div>
                </div>

                <div class="ai-chat" id="ai-chat">
                    <h4>💬 {{ __('cake_ar.common.chat_title') }}</h4>
                    <div class="hint" style="margin-top:0" id="chat-hint">{{ __('cake_ar.so.chat_hint') }}</div>
                    <div class="chat-thread" id="chat-thread"></div>
                    <div class="thumbs" id="chat-thumbs"></div>
                    <div class="chat-input">
                        <label class="chat-attach" title="{{ __('cake_ar.common.chat_attach') }}" aria-label="{{ __('cake_ar.common.chat_attach') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                            <input type="file" id="chat-files" accept="image/png,image/jpeg,image/webp" multiple>
                        </label>
                        <textarea id="chat-input" maxlength="500" placeholder="{{ __('cake_ar.so.chat_placeholder') }}"></textarea>
                        <button type="button" class="btn" id="chat-send">{{ __('cake_ar.common.send') }}</button>
                    </div>
                    <div class="message error" id="chat-message"></div>
                </div>
            </div>

        </section>

        <section class="panel" data-panel="3">
            <div class="section-title">{{ __('cake_ar.so.flavor_title') }} <small>{{ __('cake_ar.so.whole_cake') }}</small></div>
            <div class="item-cards" id="flavor-cards"></div>

            <div class="section-title">{{ __('cake_ar.so.filling_title') }} <small>{{ __('cake_ar.so.whole_cake') }}</small></div>
            <div class="item-cards" id="filling-cards"></div>
        </section>

        {{-- 4) الكتابة والوصف --}}
        {{-- 5) التاريخ والتأكيد --}}
        <section class="panel" data-panel="4">
            <div class="section-title">{{ __('cake_ar.so.date_title') }}</div>
            <input type="date" id="delivery-date">
            <div class="hint">{{ __('cake_ar.so.date_hint') }}</div>

            <div class="summary" id="summary"></div>
            <div class="message error" id="submit-message"></div>
            <button type="button" class="btn" id="submit">{{ __('cake_ar.so.submit') }}</button>
        </section>

        <div class="nav-buttons">
            <button type="button" class="btn light" id="prev">{{ __('cake_ar.common.prev') }}</button>
            <button type="button" class="btn" id="next">{{ __('cake_ar.common.next') }}</button>
        </div>
    </main>

    <div class="modal center" id="ai-wait-modal" role="dialog" aria-live="polite">
        <div class="sheet">
            <div class="wait-icon">⏳</div>
            <h2>{{ __('cake_ar.common.wait_title') }}</h2>
            <div class="wait-time" id="ai-wait-time">{{ __('cake_ar.common.seconds', ['n' => 0]) }}</div>
            <div class="hint">{{ __('cake_ar.so.wait_hint') }}</div>
            <button type="button" class="btn light" id="ai-cancel">{{ __('cake_ar.common.cancel') }}</button>
        </div>
    </div>

    <div class="modal" id="done-modal">
        <div class="sheet">
            <h2>{{ __('cake_ar.so.done_title') }}</h2>
            <div class="hint">{{ __('cake_ar.so.done_hint') }}</div>
            <div class="summary" id="modal-summary"></div>
            <button type="button" class="btn" id="close-modal">{{ __('cake_ar.common.ok') }}</button>
        </div>
    </div>

    <script type="module">
        // رقم النسخة = وقت آخر تعديل للملف، حتى ما يستخدم المتصفح (أو كاش الاستضافة) نسخة قديمة بعد التحديث
        import { composeCake } from "{{ asset('js/cake-composer.js') }}?v={{ filemtime(public_path('js/cake-composer.js')) }}";

        const options = @json($options);
        const prices = @json((object) $prices);
        const layerLimits = @json($layerLimits);
        const tierPeople = @json($tierPeople);
        const aiUrl = "{{ route('special-order.ai') }}";
        const reviewUrl = "{{ route('special-order.review') }}";
        const HAS_KEY = @json($hasKey);
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const modelsBaseUrl = "{{ asset('storage/models') }}";
        const CAKE_COLOR = @json(config('cake_ar.cake_color'));
        const BOARD_COLORS = @json(config('cake_ar.board_colors'));
        const LAYER_HEIGHT = @json(config('cake_ar.layer_height'));
        const MAX_MODEL_LAYERS = 3;
        // الترجمة: نصوص الصفحة من lang/{ar,en}/cake_ar.php حسب لغة الموقع
        const LOCALE = @json(app()->getLocale());
        const TR = { ...@json(__('cake_ar.common')), ...@json(__('cake_ar.so')) };
        const t = (k, r = {}) => String(TR[k] ?? k).replace(/:(\w+)/g, (m, n) => (n in r ? r[n] : m));
        // اسم الصنف (نكهة/حشوة) بلغة الموقع
        const itemName = item => (LOCALE === 'en' && item?.title_en) ? item.title_en : item?.name;
        const RIYAL = '<svg class="riyal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-label="' + t('riyal') + '"><path d="M8.5 2.5v14.5M14 4.5v15.5M3 11.5l18-4M3 16l18-4M14.5 20.5l6.5-1.5"/></svg>';
        const TIER_LABELS = { 1: t('tier1'), 2: t('tier2'), 3: t('tier3') };
        // أشكال خاصة (السن ست، الوال كيك): مقاساتها وطبقاتها وأدوارها ثابتة، وسعرها من أسعار
        // الكعكة الخاصة لنفس المقاس مضروب في price_factor (الوال كيك نص السعر)
        const SHAPES = @json($shapes);
        const $ = id => document.getElementById(id);
@include('partials.flavor-pictures')

        const sizesByTier = { 1: [], 2: [], 3: [] };
        Object.keys(layerLimits).forEach(key => {
            const tiers = key.startsWith('tier') ? Number(key[4]) : 1;
            sizesByTier[tiers].push(key);
        });

        // كروت الشكل: كيك الأدوار شكل بحاله (دورين، 3 أدوار). model = الشكل في المركّب
        const SHAPE_CARDS = [
            { key: 'round', name: t('shape_round'), model: 'round', tiers: 1 },
            { key: 'heart', name: t('shape_heart'), model: 'heart', tiers: 1 },
            { key: 'tier2', name: t('shape_tier2'), model: 'round', tiers: 2 },
            { key: 'tier3', name: t('shape_tier3'), model: 'round', tiers: 3 },
            { key: 'cone', name: t('shape_cone'), model: 'cone', tiers: 1 },
            { key: 'sunset', name: t('shape_sunset'), model: 'sunset', tiers: SHAPES.sunset.tiers },
            { key: 'wall', name: t('shape_wall'), model: 'wall', tiers: SHAPES.wall.tiers },
            { key: 'star', name: t('shape_star'), model: 'star', tiers: SHAPES.star.tiers },
            { key: 'square', name: t('shape_square'), model: 'square', tiers: SHAPES.square.tiers },
            { key: 'rectangle', name: t('shape_rectangle'), model: 'rectangle', tiers: SHAPES.rectangle.tiers },
            { key: 'dome', name: t('shape_dome'), model: 'dome', tiers: SHAPES.dome.tiers },
        ];
        const SHAPE_IMAGES = "{{ asset('images/shapes') }}";
        // رقم نسخة الصور: لما تتغير صور الأشكال يحمّلها الجوال من جديد بدل القديمة المحفوظة
        const SHAPE_V = "?v={{ max(array_map('filemtime', glob(public_path('images/shapes/*.webp')) ?: [0])) }}";
        // الفنتج موقّف في هذي الصفحة (كيك سادة فقط)، وكوده باقي في المركّب لاستخدامات ثانية
        const STYLE = 'plain';

        const state = {
            card: SHAPE_CARDS[0], size: sizesByTier[1][0], layers: 2,
            flavor: options.flavors[0] || null, filling: options.fillings[0] || null,
            aiImage: null, aiApproved: false, aiRemaining: @json($aiRemaining), // null = بدون حد
            tab: 0,
        };
        const shape = () => state.card.model;
        const special = () => SHAPES[shape()];
        const factor = () => special()?.price_factor ?? 1;
        const sizesOf = card => SHAPES[card.model] ? SHAPES[card.model].sizes : sizesByTier[card.tiers];
        const layersOf = (card, size) => SHAPES[card.model] ? [].concat(SHAPES[card.model].layers) : layerLimits[size];

        // "tier2_8-6" => 8 (الدور السفلي يحدد السعر)، "6in" => 6
        const inches = key => key.startsWith('tier') ? key.split('_')[1].split('-').map(Number) : [Number(key.replace('in', ''))];
        const bottomInch = key => inches(key)[0];
        const sizeLabel = key => t('inch', { n: inches(key).join('/') });
        const people = key => key.startsWith('tier') ? tierPeople[key] : options.sizes[bottomInch(key)]?.people;
        const money = n => Math.round(n * 10) / 10 + RIYAL;
        const extra = item => item ? (item.extra[bottomInch(state.size)] || 0) * factor() : 0;
        const priceOf = (card, size, n) => {
            const p = prices[size]?.[n];
            return p === undefined ? undefined : p * (SHAPES[card.model]?.price_factor ?? 1);
        };
        const basePrice = (size, n) => priceOf(state.card, size, n);

        function total() {
            const base = basePrice(state.size, state.layers);
            return base === undefined ? undefined : base + extra(state.flavor) + extra(state.filling);
        }

        function selectIn(container, el) {
            [...container.children].forEach(o => o.classList.toggle('active', o === el));
        }

        function card(className, html, active, onClick) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = className + (active ? ' active' : '');
            b.innerHTML = html;
            b.addEventListener('click', () => { selectIn(b.parentElement, b); onClick(); });
            return b;
        }

        // ===== الشكل =====
        function renderShapes() {
            const box = $('shape-cards');
            box.innerHTML = '';
            // قسم الشكل للشكل بس (طلب المدير): صورة واسم، والأشخاص والأسعار في قسم الحجم والطبقات
            SHAPE_CARDS.forEach(c => {
                box.appendChild(card('shape-card', `
                    <img src="${SHAPE_IMAGES}/${c.key}.webp${SHAPE_V}" alt="" loading="lazy">
                    <div class="name">${c.name}</div>`,
                    c === state.card,
                    () => { state.card = c; renderSizes(); renderItems(); refresh(); },
                ));
            });
        }

        // ===== الحجم والطبقات =====
        const SHAPE_PATHS = {
            round: 'M5 7.5c0-1.7 3.1-3 7-3s7 1.3 7 3-3.1 3-7 3-7-1.3-7-3zM5 7.5v9c0 1.7 3.1 3 7 3s7-1.3 7-3v-9',
            heart: 'M12 20.5s-7.5-4.6-7.5-10.1A4.3 4.3 0 0 1 12 7.6a4.3 4.3 0 0 1 7.5 2.8c0 5.5-7.5 10.1-7.5 10.1z',
            cone: 'M12 3.5c-.8 0-1.3.6-1.6 1.4L5.5 19.5h13L13.6 4.9c-.3-.8-.8-1.4-1.6-1.4z',
            sunset: 'M3 18.5h18M4.5 18.5a7.5 7.5 0 0 1 15 0',
            dome: 'M4.5 17.5c0 1.4 3.4 2.5 7.5 2.5s7.5-1.1 7.5-2.5M4.5 17.5a7.5 8 0 0 1 15 0',
            star: 'M12 3.2l2.6 5.4 5.9.8-4.3 4.1 1.1 5.8L12 16.5l-5.3 2.8 1.1-5.8-4.3-4.1 5.9-.8z',
            square: 'M5 5h14v14H5z',
            rectangle: 'M3 7h18v10H3z',
        };
        // رسمة المقاس: الشكل يكبر مع الإنش، وكيك الأدوار أدوار فوق بعض بعرض كل دور
        function sizePicture(key) {
            const inch = inches(key);
            if (inch.length === 1) {
                const px = 26 + (inch[0] - 4) * 6;
                return `<svg width="${px}" height="${px}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="${SHAPE_PATHS[shape()] || SHAPE_PATHS.round}"/></svg>`;
            }
            const h = 15, w = i => 8 + i * 5;
            const rects = inch.map((i, n) => `<rect x="${(60 - w(i)) / 2}" y="${62 - (n + 1) * (h + 1)}" width="${w(i)}" height="${h}" rx="3" fill="${n % 2 ? '#F4C0D1' : '#FBEAF0'}" stroke="currentColor" stroke-width="1.5"/>`).join('');
            return `<svg width="60" height="64" viewBox="0 0 60 64">${rects}</svg>`;
        }

        function renderSizes() {
            const box = $('size-options');
            const list = sizesOf(state.card);
            if (!list.includes(state.size)) state.size = list[0];
            box.innerHTML = '';
            list.forEach(key => box.appendChild(card('size-card', `
                <div class="pic">${sizePicture(key)}</div>
                <div class="num">${sizeLabel(key)}</div>
                <small>${people(key) ? t('people', { n: people(key) }) : ''}</small>
                ${basePrice(key, layersOf(state.card, key)[0]) !== undefined ? `<div class="price">${money(basePrice(key, layersOf(state.card, key)[0]))}</div>` : ''}`,
                key === state.size,
                () => { state.size = key; renderLayers(); renderItems(); refresh(); },
            )));
            renderLayers();
        }

        const LAYER_COLORS = ['#F4C0D1', '#ED93B1', '#D4537E', '#993556'];
        function renderLayers() {
            const isCone = shape() === 'cone';
            $('layers-block').hidden = isCone;
            $('cone-hint').hidden = !isCone;
            const allowed = layersOf(state.card, state.size);
            if (isCone || !allowed.includes(state.layers)) state.layers = allowed[0];
            const box = $('layer-options');
            box.innerHTML = '';
            // مثل موقع جوماكيك: زيادة الطبقة على أقل عدد (+0، +20، +40…)، وتختلف حسب المقاس
            const base = basePrice(state.size, allowed[0]);
            allowed.forEach(n => box.appendChild(card('layer-card', `
                <div class="bars">${Array.from({ length: n }, (_, b) => `<div style="height:${Math.max(6, 16 - n * 2)}px;background:${LAYER_COLORS[b % LAYER_COLORS.length]}"></div>`).join('')}</div>
                <div class="num">${t('layers_n', { n })}</div>
                ${basePrice(state.size, n) !== undefined && base !== undefined ? `<div class="price">+${money(basePrice(state.size, n) - base)}</div>` : ''}`,
                n === state.layers,
                () => { state.layers = n; refresh(); },
            )));
        }

        function renderItems() {
            [['flavor-cards', 'flavors', 'flavor', '🍰'], ['filling-cards', 'fillings', 'filling', '🍓']].forEach(([boxId, list, key, icon]) => {
                const box = $(boxId);
                box.innerHTML = '';
                plainLast(options[list], key).forEach(item => {
                    const add = extra(item);
                    box.appendChild(card('item-card',
                        `${(pic => `<div class="pic${pic && /\/images\/(flavors|fillings)\//.test(pic) ? ' slice' : ''}" ${pic ? `style="background-image:url('${pic}')"` : ''}>${pic ? '' : icon}</div>`)(key === 'flavor' ? flavorPic(item) : fillingPic(item, state.flavor))}${escapeHtml(itemName(item))}${add ? `<span class="extra">+${money(add)}</span>` : ''}`,
                        state[key]?.id === item.id,
                        () => { state[key] = item; if (key === 'flavor') renderItems(); refresh(); },
                    ));
                });
            });
        }

        function updateBadges() {
            const price = total();
            $('price-amount').innerHTML = price === undefined ? '—' : money(price);
            $('spec-layers').textContent = shape() === 'cone' ? '—' : t('layer_n', { n: state.layers });
            $('spec-size').textContent = sizeLabel(state.size);
            $('spec-people').textContent = t('people', { n: people(state.size) || '—' });
            $('spec-tiers').textContent = TIER_LABELS[state.card.tiers];
        }

        let token = 0, currentBlob = null, modelReady = Promise.resolve();
        async function updateModel() {
            const mine = ++token;
            const modelLayers = Math.min(state.layers, MAX_MODEL_LAYERS);
            const base = `${modelsBaseUrl}/cake_${state.size}_layers${modelLayers}_v45.glb`;
            $('viewer-status').hidden = !!currentBlob;
            try {
                const url = await composeCake(base, {
                    color: CAKE_COLOR,
                    boardColor: BOARD_COLORS[state.size] || BOARD_COLORS.default,
                    shape: shape(),
                    style: STYLE,
                    extraHeight: (state.layers - modelLayers) * LAYER_HEIGHT,
                    // المربع والمستطيل: العرض والعمق بالسنتيمتر من config → متر
                    footprint: (d => d ? { w: d[0] / 100, d: d[1] / 100 } : null)(SHAPES[shape()]?.dims?.[state.size]),
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
            saveDraft();
            updateBadges();
            renderSummary($('summary'));
            const key = [shape(), state.size, state.layers].join('|');
            if (key !== lastModelKey) { lastModelKey = key; modelReady = updateModel(); }
        }

        $('ar-btn').addEventListener('click', () => {
            const v = $('viewer');
            if (v.canActivateAR) v.activateAR();
            else alert(t('ar_phone'));
        });

        // ===== التبويبات =====
        const tabs = [...document.querySelectorAll('.tab')];
        // فوق حسب الخطوة: الشكل والحجم = المجسم، بعدها = الصورة المولّدة (أو المشبك إذا ما فيه صورة)
        function updateTop() {
            // في قسم الشكل: بدون السعر وشريط البيانات عشان التركيز على الشكل
            document.body.classList.toggle('step-shape', state.tab === 0);
            const after = state.tab >= 2;
            $('gen-view').hidden = !(after && state.aiImage);
            $('attach-view').hidden = !(after && !state.aiImage);
            $('attach-view').classList.toggle('has-refs', refFiles.length > 0);
            if (state.aiImage && $('gen-image').getAttribute('src') !== state.aiImage) $('gen-image').src = state.aiImage;
        }
        $('attach-btn').addEventListener('click', () => aiRef.click());

        function goTo(i, scroll = true) {
            state.tab = i;
            updateTop();
            saveDraft();
            tabs.forEach((t, n) => { t.classList.toggle('active', n === i); t.classList.toggle('done', n < i); });
            document.querySelectorAll('.panel').forEach(p => p.classList.toggle('active', Number(p.dataset.panel) === i));
            $('prev').style.visibility = i === 0 ? 'hidden' : 'visible';
            $('next').style.display = i === tabs.length - 1 ? 'none' : '';
            renderSummary($('summary'));
            if (scroll) window.scrollTo({ top: $('tabs').offsetTop - $('top-fixed').offsetHeight, behavior: 'smooth' });
        }
        tabs.forEach((t, i) => t.addEventListener('click', () => goTo(i)));
        $('prev').addEventListener('click', () => goTo(Math.max(0, state.tab - 1)));
        $('next').addEventListener('click', () => goTo(Math.min(tabs.length - 1, state.tab + 1)));

        // ===== الذكاء الاصطناعي =====
        const aiRef = $('ai-reference');
        function showAiMessage(text, type) { $('ai-message').textContent = text; $('ai-message').className = 'message ' + type; }
        function renderRemaining() {
            // المحاولة الفاشلة ما تنحسب، فالعدد = الصور اللي باقية
            $('ai-remaining').textContent = state.aiRemaining === null ? ''
                : state.aiRemaining > 0
                ? (state.aiRemaining === 1 ? t('remaining_one') : t('remaining_n', { n: state.aiRemaining }))
                : t('no_more_images');
            $('ai-generate').disabled = !HAS_KEY || !canGenerate();
        }
        // صورة مصغّرة عليها ✕ للحذف
        function thumb(file, onRemove) {
            const box = document.createElement('div');
            box.className = 'thumb';
            box.innerHTML = `<img src="${URL.createObjectURL(file)}" alt=""><button type="button" class="remove" aria-label="${t('remove_photo')}">✕</button>`;
            box.querySelector('.remove').addEventListener('click', onRemove);
            return box;
        }

        const canGenerate = () => state.aiRemaining === null || state.aiRemaining > 0;
        const MAX_REFS = 6;
        const refFiles = [];
        function renderRef() {
            const grid = $('refs-grid');
            grid.innerHTML = '';
            refFiles.forEach((f, i) => {
                const box = thumb(f, () => { refFiles.splice(i, 1); renderRef(); });
                box.className = 'ref';
                grid.appendChild(box);
            });
            saveDraft();
            renderSummary($('summary')); // صور الكيكة تظهر في الملخص
            updateTop();
        }
        aiRef.addEventListener('change', () => {
            const picked = [...aiRef.files], room = MAX_REFS - refFiles.length;
            refFiles.push(...picked.slice(0, room));
            if (picked.length > room) showAiMessage(t('max_refs', { max: MAX_REFS, n: Math.max(room, 0) }), 'error');
            aiRef.value = ''; // حتى يقدر يختار نفس الصورة مرة ثانية بعد الحذف
            renderRef();
        });

        // نصغّر الصورة (أطول ضلع 2048 بكسل) قبل الرفع: 6 صور جوال ممكن تكون ثقيلة على السيرفر،
        // وبهذا الحجم تبقى التفاصيل (البتلات، الكتابة) واضحة للذكاء الاصطناعي
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

        // كل الصور اللي طلعت، والمعروضة الحين
        const aiImages = [];
        // كل صورة والتعديلات اللي طلعت منها (الوصف الأول + طلبات المحادثة)، ترسل مع التعديل الجاي حتى ما تضيع
        const imageEdits = {};
        function showAiImage(url) {
            state.aiImage = url;
            state.aiApproved = true; // الصورة الحالية في المحادثة هي المعتمدة
            saveDraft();
            $('ai-image').src = url;
            $('ai-download').href = url;
            $('ai-download').download = `jomacake-${aiImages.indexOf(url) + 1}.png`;
            $('ai-result').className = 'ai-result show';
            $('ai-history').hidden = aiImages.length < 2;
            updateTop();
            $('ai-history-thumbs').innerHTML = '';
            aiImages.forEach(u => {
                const img = document.createElement('img');
                img.src = u;
                img.alt = '';
                img.className = u === url ? 'current' : '';
                img.addEventListener('click', () => { showAiImage(u); renderSummary($('summary')); });
                $('ai-history-thumbs').appendChild(img);
            });
        }
        // صورة المجسم بالمواصفات المختارة من الأمام، تنرسل تلقائيًا مع صورة الزبون حتى ياخذ الذكاء الاصطناعي الشكل منها
        // نحوّل صورة المجسم لرمادي (مثل مجسم طين) حتى ما ياخذ الذكاء الاصطناعي لونها الكريمي:
        // هي للشكل بس، واللون والزينة من صور الزبون. الكيكة (الكريمي الدافي) رمادي غامق، والبورد
        // واللوجو رمادي فاتح حتى يبان محيط الكيكة، والخلفية البيضاء تبقى بيضاء
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
                v.cameraOrbit = '0deg 55deg 105%'; // من فوق شوي حتى يبان المحيط (قلب، سن ست…)
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

        // نسأل عن نتيجة التوليد كل 4 ثواني (لين 4 دقايق). إذا ما وصل الطلب للسيرفر أصلًا نوقف بعد 20 ثانية
        // نسأل لين 6 دقايق (الجودة العالية ممكن تاخذ 5)، ونوقف إذا الزبون ضغط إلغاء
        async function waitForJob(job, signal) {
            const started = Date.now();
            while (Date.now() - started < 360000) {
                await new Promise(res => setTimeout(res, 4000));
                if (signal.aborted) throw new DOMException('cancelled', 'AbortError');
                try {
                    const res = await fetch(`${aiUrl}/${job}`, { headers: { 'Accept': 'application/json' }, signal });
                    const data = await res.json();
                    if (data.status === 'done') return { data, ok: true };
                    if (data.status === 'failed') return { data, ok: false };
                    if (data.status === 'missing' && Date.now() - started > 20000) break;
                } catch (e) {
                    if (signal.aborted) throw e;
                    /* نجرب مرة ثانية */
                }
            }
            return { data: { message: t('conn_lost') }, ok: false };
        }

        // الإلغاء: نوقف الانتظار ونرجّع الزبون للخيارات (التوليد ممكن يكمّل عند OpenAI، بس نتجاهل نتيجته)
        let aiAbort = null;
        $('ai-cancel').addEventListener('click', () => aiAbort?.abort());

        $('ai-generate').addEventListener('click', () => generateFirst(''));

        // أول صورة: من صور الزبون + أول رسالة في المحادثة (إذا كتب). ترجع true إذا طلعت
        async function generateFirst(note) {
            if (!refFiles.length) {
                showAiMessage(t('attach_first'), 'error');
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return false;
            }
            const abort = aiAbort = new AbortController();
            const started = Date.now();
            const tick = () => { $('ai-wait-time').textContent = t('seconds', { n: Math.round((Date.now() - started) / 1000) }); };
            tick();
            const timer = setInterval(tick, 1000);
            $('ai-wait-modal').classList.add('show');
            showAiMessage('', 'info');
            const form = new FormData();
            form.append('shape', shape());
            form.append('size', state.size);
            form.append('layers', state.layers);
            form.append('style', STYLE);
            form.append('note', note);
            for (const f of await Promise.all(refFiles.map(shrink))) form.append('references[]', f);
            // رقم التوليد: إذا انقطع الطلب نسأل السيرفر عن نتيجته بهذا الرقم
            const job = Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
            form.append('job', job);

            $('ai-generate').disabled = true;
            let done = false;
            try {
                const shot = await snapshotModel();
                if (shot) form.append('shape_image', shot, 'shape.png');
                if (abort.signal.aborted) throw new DOMException('cancelled', 'AbortError');
                let data, ok;
                try {
                    const res = await fetch(aiUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: form,
                        signal: abort.signal,
                    });
                    data = await res.json();
                    ok = res.ok;
                } catch (cut) {
                    if (abort.signal.aborted) throw cut;
                    // الاستضافة قطعت الانتظار (أو رجعت صفحة خطأ)، والتوليد مكمّل في السيرفر: ننتظر نتيجته
                    ({ data, ok } = await waitForJob(job, abort.signal));
                }
                clearInterval(timer);
                if (data.remaining !== undefined) state.aiRemaining = data.remaining;
                if (!ok) {
                    const firstError = data.errors && Object.values(data.errors)[0]?.[0];
                    throw new Error(firstError || data.message || t('error_retry'));
                }
                aiImages.push(data.image);
                imageEdits[data.image] = [note].filter(Boolean);
                showAiImage(data.image);
                done = true;
                $('ai-generate').textContent = t('generate_again');
                showAiMessage(t('here_is_specs'), 'info');
            } catch (e) {
                showAiMessage(abort.signal.aborted ? t('cancelled') : e.message, abort.signal.aborted ? 'info' : 'error');
            } finally {
                clearInterval(timer);
                $('ai-wait-modal').classList.remove('show');
                if (aiAbort === abort) aiAbort = null;
                $('ai-generate').disabled = !HAS_KEY || !canGenerate();
                renderRemaining();
                renderSummary($('summary'));
            }
            return done;
        }
        // ===== المحادثة: الزبون يكتب، الذكاء يرد أو يسأل، والتعديل على الصورة اللي قدامه =====
        const chatUrl = "{{ route('special-order.ai.chat') }}";
        const editUrl = "{{ route('special-order.ai.edit') }}";
        const chatMessages = [];
        // طلبات الزبون بالعربي (للملخص بدل خانة الوصف)
        const userRequests = [];
        const chatLog = [];
        function bubble(role, text, image) {
            if (!role.includes('typing')) { chatLog.push({ role, text, image: image || null }); saveDraft(); }
            const b = document.createElement('div');
            b.className = 'bubble ' + role;
            b.textContent = text;
            if (image) {
                const img = document.createElement('img');
                img.src = image;
                img.alt = t('edited_alt');
                img.addEventListener('click', () => { showAiImage(image); renderSummary($('summary')); window.scrollTo({ top: 0, behavior: 'smooth' }); });
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
            $('chat-thread').scrollTop = $('chat-thread').scrollHeight;
            return b;
        }
        const specs = () => ({ shape: shape(), size: state.size, layers: state.layers, style: STYLE });

        async function editImage(instruction, files = []) {
            const base = state.aiImage;
            const abort = aiAbort = new AbortController();
            const started = Date.now();
            const tick = () => { $('ai-wait-time').textContent = t('seconds', { n: Math.round((Date.now() - started) / 1000) }); };
            tick();
            const timer = setInterval(tick, 1000);
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
        }
        $('chat-files').addEventListener('change', e => {
            chatFiles = [...chatFiles, ...e.target.files].slice(0, 6);
            e.target.value = '';
            renderChatFiles();
        });

        async function sendChat() {
            const files = chatFiles.slice();
            let text = $('chat-input').value.trim();
            const firstFromPhotos = !aiImages.length && refFiles.length;
            if ((!text && !files.length && !firstFromPhotos) || $('chat-send').disabled) return;
            if (!text) text = aiImages.length ? t('edit_by_refs') : t('like_photo');
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
            userRequests.push(text);
            // ما فيه صورة لسا: أول رسالة هي الوصف، وتولّد أول صورة من الصور المرفقة
            if (!aiImages.length) {
                if (files.length) { refFiles.push(...files.slice(0, MAX_REFS - refFiles.length)); renderRef(); }
                if (!refFiles.length) {
                    bubble('assistant', t('attach_first_chat'));
                    userRequests.pop();
                    $('chat-input').value = text;
                    return;
                }
                // ما نولّد على طول: نسأل الزبون في المحادثة، والذكاء الاصطناعي يفهم رده (يبي صورة ولا يكمّل والدعم يعالج الطلب)
                const ASK = t('ask_generate');
                if (!canGenerate()) { bubble('assistant', t('clear') + NO_MORE); return; }
                if (!pendingFirst.length) {
                    pendingFirst.push(text === t('like_photo') ? '' : text);
                    firstChat.push({ role: 'user', content }, { role: 'assistant', content: ASK });
                    bubble('assistant', ASK);
                    return;
                }
                firstChat.push({ role: 'user', content });
                const typing = bubble('assistant typing', '…');
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
                        : NO.test(short) && short.split(' ').length <= 3 ? { action: 'skip', reply: NO_MORE }
                        : null;
                    if (!answer) {
                        typing.remove();
                        firstChat.pop();
                        $('chat-message').textContent = e.message;
                        $('chat-input').value = text;
                        $('chat-send').disabled = false;
                        return;
                    }
                }
                typing.remove();
                if (answer.reply) { bubble('assistant', answer.reply); firstChat.push({ role: 'assistant', content: answer.reply }); }
                if (answer.action === 'ask') {
                    pendingFirst.push(text);
                } else {
                    userRequests.pop(); // "أيوه" أو "لا" مو جزء من الوصف
                    if (answer.action === 'generate') {
                        const note = (answer.note || pendingFirst.filter(Boolean).join('\n')).trim();
                        if (await generateFirst(note)) {
                            chatMessages.push({ role: 'user', content: note || t('like_photo') }, { role: 'assistant', content: t('made_it') });
                            pendingFirst.length = 0;
                            firstChat.length = 0;
                            bubble('assistant', t('here_is_cake'), state.aiImage);
                        }
                    }
                }
                $('chat-send').disabled = false;
                return;
            }
            chatMessages.push({ role: 'user', content });
            const typing = bubble('assistant typing', '…');
            $('chat-send').disabled = true;
            try {
                const res = await fetch(chatUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ ...specs(), messages: chatMessages.slice(-12) }),
                });
                const data = await res.json();
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
        // الوصف (مع الصور) اللي ينتظر رد الزبون: يبي صورة بالذكاء الاصطناعي أو لا
        const pendingFirst = [];
        const firstChat = []; // محادثة ما قبل أول صورة (للذكاء الاصطناعي يفهم رد الزبون)
        const YES = /^(اي+وه?|ايوا|إي+|أيو[هة]|ايو[هة]|اي|نعم|اكيد|أكيد|يس|yes|y|ok|okay|sure|yeah|yep|please|go ahead|اوك|اوكي|أوكي|تمام|طيب|ماشي|ولدي?|ولّدي?|يلا|بدي|ابي|أبي|ابغى|أبغى)( .*)?$/i;
        const NO = /^(لا+|لأ|لاء|no|n|nope|no thanks|not now|مابدي|ما بدي|مابي|ما ابي|ما أبي|ماابغى|ما ابغى|ما أبغى|بدون|مو لازم|لا شكرا)( .*)?$/i;
        const NO_MORE = t('no_more');
        $('chat-send').addEventListener('click', sendChat);
        $('chat-input').addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendChat(); } });

        $('ai-approve').addEventListener('click', () => {
            state.aiApproved = true;
            saveDraft();
            $('ai-result').classList.add('is-approved');
            showAiMessage('', 'info');
            renderSummary($('summary'));
        });
        $('ai-reject').addEventListener('click', () => {
            state.aiApproved = false;
            $('ai-result').className = 'ai-result';
            $('ai-history').hidden = !aiImages.length; // حتى يقدر يرجع لها أو يحمّلها
            showAiMessage(canGenerate()
                ? t('reject_msg')
                : t('reject_msg2'), 'info');
            renderSummary($('summary'));
            if (canGenerate()) {
                $('chat-input').scrollIntoView({ behavior: 'smooth', block: 'center' });
                $('chat-input').focus({ preventScroll: true });
            }
        });

        $('delivery-date').addEventListener('input', () => renderSummary($('summary')));

        // أقرب تاريخ: بعد يومين
        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 2);
        $('delivery-date').min = minDate.toISOString().slice(0, 10);

        // ===== الملخص =====
        function escapeHtml(text) {
            return text.replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        }
        // الملخص بدلائل بصرية: صورة الشكل، رسمة المقاس والطبقات، صورة النكهة والحشوة
        const visual = (pic, text) => `<span class="vis"><span>${escapeHtml(String(text))}</span><span class="vis-pic">${pic}</span></span>`;
        const imgPic = (url, icon) => url ? `<img src="${url}" alt="">` : `<span class="vis-icon">${icon}</span>`;
        const layersPic = n => `<span class="vis-bars">${Array.from({ length: n }, (_, i) => `<i style="background:${LAYER_COLORS[i % LAYER_COLORS.length]}"></i>`).join('')}</span>`;
        const TIER_ICON = '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"><path d="M4 20h16v-5H4zM6.5 15v-4h11v4M9 11V8h6v3M12 8V5.5"/></svg>';

        function renderSummary(box) {
            const rows = [
                [t('sum_shape'), visual(`<img class="fit" src="${SHAPE_IMAGES}/${state.card.key}.webp${SHAPE_V}" alt="">`, state.card.name)],
                [t('sum_tiers'), visual(TIER_ICON, TIER_LABELS[state.card.tiers])],
                [t('sum_size'), visual(sizePicture(state.size), sizeLabel(state.size))],
                ...(shape() === 'cone' ? [] : [[t('sum_layers'), visual(layersPic(state.layers), t('layers_n', { n: state.layers }))]]),
                [t('sum_flavor'), state.flavor ? visual(imgPic(flavorPic(state.flavor), '🍰'), itemName(state.flavor)) : '—'],
                [t('sum_filling'), state.filling ? visual(imgPic(fillingPic(state.filling, state.flavor), '🍓'), itemName(state.filling)) : '—'],
                [t('sum_desc'), escapeHtml(userRequests.join(' • ') || '—')],
                [t('sum_date'), visual('📅', $('delivery-date').value || '—')],
            ];
            const images = [];
            if (state.aiImage && state.aiApproved) images.push(`<figure><img src="${state.aiImage}" alt=""><div>${t('ai_design')}</div></figure>`);
            refFiles.forEach(f => images.push(`<figure><img src="${URL.createObjectURL(f)}" alt=""><div>${t('attached_photo')}</div></figure>`));
            const price = total();
            box.innerHTML = `<dl>${rows.map(([k, v]) => `<div class="line"><dt>${k}</dt><dd>${v}</dd></div>`).join('')}
                <div class="line total"><dt>${t('start_price')}</dt><dd>${price === undefined ? '—' : money(price)}</dd></div></dl>
                ${images.length ? `<div class="summary-images">${images.join('')}</div>` : ''}`;
        }

        $('submit').addEventListener('click', () => {
            const missing = [];
            if (!$('delivery-date').value) missing.push(t('sum_date'));
            if (!state.aiApproved && !refFiles.length) missing.push(t('missing_photo'));
            if (missing.length) { $('submit-message').textContent = t('complete') + missing.join(t('list_sep')); return; }
            $('submit-message').textContent = '';
            renderSummary($('modal-summary'));
            $('done-modal').classList.add('show');
            // الطلب انرسل: الطلب الجاي يبدأ من جديد
            try { sessionStorage.removeItem(DRAFT_KEY); } catch (e) {}
            // بعد الطلب تتجدد صور الذكاء الاصطناعي
            fetch(reviewUrl, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } })
                .then(res => res.json())
                .then(data => { state.aiRemaining = data.remaining; renderRemaining(); })
                .catch(() => {});
        });
        $('close-modal').addEventListener('click', () => $('done-modal').classList.remove('show'));

        // ===== المسودة: الصور والتصميم يبقون لو الزبون حدّث الصفحة (في نفس التبويب) =====
        // الصور المرفقة تنحفظ مصغّرة في sessionStorage. لو المتصفح ما سمح (مساحة أو وضع خاص)
        // نطلع تنبيه قبل التحديث إن الصور بتروح
        const DRAFT_KEY = 'special-order-draft';
        const dataUrls = new WeakMap();
        let draftFailed = false, restoring = false;
        async function toDataUrl(file) {
            if (dataUrls.has(file)) return dataUrls.get(file);
            const bitmap = await createImageBitmap(file);
            const scale = Math.min(1, 1280 / Math.max(bitmap.width, bitmap.height));
            const canvas = document.createElement('canvas');
            canvas.width = Math.round(bitmap.width * scale);
            canvas.height = Math.round(bitmap.height * scale);
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#fff'; // الصور الشفافة ما تطلع سودا
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
            const url = canvas.toDataURL('image/jpeg', 0.82);
            dataUrls.set(file, url);
            return url;
        }
        let saveTimer = null;
        function saveDraft() {
            if (restoring) return;
            clearTimeout(saveTimer);
            saveTimer = setTimeout(async () => {
                try {
                    const draft = {
                        card: state.card.key, size: state.size, layers: state.layers, tab: state.tab,
                        flavor: state.flavor?.id ?? null, filling: state.filling?.id ?? null,
                        refs: await Promise.all(refFiles.map(toDataUrl)),
                        aiImages, imageEdits, aiImage: state.aiImage, aiApproved: state.aiApproved,
                        chatMessages, userRequests, chatLog,
                    };
                    sessionStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
                    draftFailed = false;
                } catch (e) {
                    draftFailed = refFiles.length > 0 || aiImages.length > 0;
                }
            }, 300);
        }
        window.addEventListener('beforeunload', e => {
            if (draftFailed) { e.preventDefault(); e.returnValue = ''; }
        });
        async function restoreDraft() {
            let draft;
            try { draft = JSON.parse(sessionStorage.getItem(DRAFT_KEY) || 'null'); } catch (e) { draft = null; }
            if (!draft) return 0;
            restoring = true;
            try {
                state.card = SHAPE_CARDS.find(c => c.key === draft.card) || state.card;
                if (sizesOf(state.card).includes(draft.size)) state.size = draft.size;
                if (layersOf(state.card, state.size).includes(draft.layers)) state.layers = draft.layers;
                state.flavor = options.flavors.find(f => f.id === draft.flavor) || state.flavor;
                state.filling = options.fillings.find(f => f.id === draft.filling) || state.filling;
                for (const url of draft.refs || []) {
                    const blob = await (await fetch(url)).blob();
                    const file = new File([blob], 'photo.jpg', { type: 'image/jpeg' });
                    dataUrls.set(file, url);
                    refFiles.push(file);
                }
                aiImages.push(...(draft.aiImages || []));
                Object.assign(imageEdits, draft.imageEdits || {});
                chatMessages.push(...(draft.chatMessages || []));
                userRequests.push(...(draft.userRequests || []));
                (draft.chatLog || []).forEach(m => bubble(m.role, m.text, m.image));
                if (draft.aiImage) {
                    showAiImage(draft.aiImage);
                    if (draft.aiApproved) $('ai-approve').click();
                }
                renderRef();
            } catch (e) {
                console.error(e);
            } finally {
                restoring = false;
            }
            return draft.tab || 0;
        }

        const startTab = await restoreDraft();
        renderShapes();
        renderSizes();
        renderItems();
        renderRemaining();
        goTo(startTab, false);
        refresh();
    </script>
<script>
    // خانة المحادثة تكبر مع الكتابة، وتطلع فوق الكيبورد في الجوال عشان الزبون يشوف وش يكتب
    (function () {
        const t = document.getElementById('chat-input');
        if (!t) return;
        const grow = () => { t.style.height = 'auto'; t.style.height = Math.min(t.scrollHeight + 2, 200) + 'px'; };
        t.addEventListener('input', grow);
        const show = () => setTimeout(() => t.scrollIntoView({ block: 'center', behavior: 'smooth' }), 350);
        t.addEventListener('focus', show);
        if (window.visualViewport) window.visualViewport.addEventListener('resize', () => { if (document.activeElement === t) show(); });
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
