<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>صمّم كيكتك - جوماكيك</title>
    <script type="importmap">
        { "imports": {
            "three": "https://unpkg.com/three@0.172.0/build/three.module.js",
            "three/addons/": "https://unpkg.com/three@0.172.0/examples/jsm/"
        } }
    </script>
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --pink: #D4537E;
            --pink-dark: #993556;
            --pink-soft: #FBEAF0;
            --pink-line: #F4C0D1;
            --ink: #4B1528;
            --muted: #8A6B76;
            --bg: #FFFFFF;
            --rail-w: 78px;
            --footer-h: 128px;
        }
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html, body { margin: 0; background: var(--bg); color: var(--ink); font-family: 'Tajawal', -apple-system, 'Segoe UI', Tahoma, sans-serif; }
        body { padding-bottom: calc(var(--footer-h) + env(safe-area-inset-bottom)); }
        button { font-family: inherit; }

        /* ===== الهيدر ===== */
        .topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 16px 4px;
        }
        .topbar .title { display: flex; align-items: center; gap: 6px; }
        .topbar h1 { margin: 0; font-size: 24px; font-weight: 800; }
        .topbar .back { width: 26px; height: 26px; color: var(--pink-dark); }
        .topbar .total { font-size: 26px; font-weight: 800; white-space: nowrap; }
        .topbar .total small { font-size: 15px; color: var(--pink-dark); font-weight: 700; margin-inline-start: 2px; }
        .total.bump { animation: bump .35s ease; }
        @keyframes bump { 40% { transform: scale(1.12); color: var(--pink); } }

        /* ===== المجسم ===== */
        .stage { position: relative; }
        model-viewer {
            width: 100%; height: 38vh; min-height: 250px; max-height: 420px;
            --poster-color: transparent; background: transparent;
        }
        .ar-btn {
            position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%);
            background: var(--ink); color: #fff; border: 0; border-radius: 999px;
            padding: 9px 18px; font-size: 14px; font-weight: 700;
            display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 14px rgba(75,21,40,.25);
        }
        .ar-btn svg { width: 18px; height: 18px; }
        .inside-card {
            position: absolute; top: 6px; left: 12px; width: 104px;
            background: rgba(255,255,255,.92); border: 1px solid var(--pink-line); border-radius: 14px;
            padding: 4px 6px 6px; text-align: center; box-shadow: 0 4px 14px rgba(75,21,40,.08);
        }
        .inside-card .lbl { font-size: 11px; color: var(--muted); font-weight: 700; }
        .inside-card svg { width: 100%; height: auto; display: block; }
        .loading-pill {
            position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%);
            font-size: 13px; color: var(--muted); background: rgba(255,255,255,.9);
            padding: 6px 12px; border-radius: 999px; display: none;
        }
        .stage.loading .loading-pill { display: block; }

        /* ===== الخطوات والخيارات ===== */
        .builder { display: flex; align-items: flex-start; gap: 10px; padding: 6px 0 0; }
        .rail {
            position: sticky; top: 8px; flex: 0 0 var(--rail-w);
            background: var(--pink-dark); border-radius: 18px 0 0 18px;
            padding: 8px 6px; display: flex; flex-direction: column; gap: 6px;
        }
        .step-btn {
            position: relative; border: 0; background: transparent; color: #fff;
            border-radius: 12px; padding: 8px 2px 6px; display: flex; flex-direction: column;
            align-items: center; gap: 2px; cursor: pointer; font-size: 13px; font-weight: 700;
        }
        .step-btn svg.ico { width: 30px; height: 30px; }
        .step-btn.active { background: rgba(255,255,255,.22); }
        .step-btn .done {
            position: absolute; top: 2px; left: 4px; width: 17px; height: 17px; border-radius: 50%;
            background: #7BC74D; color: #fff; display: none; align-items: center; justify-content: center;
            border: 2px solid var(--pink-dark);
        }
        .step-btn .done svg { width: 11px; height: 11px; }
        .step-btn.completed .done { display: flex; }

        .panel { flex: 1; min-width: 0; padding-inline-end: 12px; }
        .step-title { font-size: 17px; font-weight: 800; margin: 2px 0 10px; }
        .step-sub { font-size: 13px; color: var(--muted); margin: -6px 0 10px; }
        .step { display: none; animation: fade .25s ease; }
        .step.active { display: block; }
        @keyframes fade { from { opacity: 0; transform: translateY(6px); } }

        .tier-switch { display: flex; background: var(--pink-soft); border-radius: 12px; padding: 4px; margin-bottom: 12px; }
        .tier-switch button {
            flex: 1; border: 0; background: transparent; padding: 8px 4px; border-radius: 9px;
            font-size: 14px; font-weight: 700; color: var(--pink-dark); cursor: pointer;
        }
        .tier-switch button.active { background: #fff; color: var(--ink); box-shadow: 0 1px 4px rgba(75,21,40,.12); }

        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .card {
            position: relative; border: 2px solid transparent; background: #fff; border-radius: 16px;
            padding: 8px 6px 10px; text-align: center; cursor: pointer;
            transition: background .15s, border-color .15s, transform .1s;
        }
        .card:active { transform: scale(.97); }
        .card.active { background: var(--pink-soft); border-color: var(--pink-line); }
        .card svg.art { width: 100%; max-width: 150px; height: auto; display: block; margin: 0 auto; }
        .card .name { font-size: 15px; font-weight: 800; margin-top: 2px; }
        .card .meta { font-size: 12px; color: var(--muted); margin-top: 1px; }
        .card .price { font-size: 13px; color: var(--pink); font-weight: 800; margin-top: 2px; }
        .card .tag {
            position: absolute; top: 6px; right: 6px; background: var(--pink); color: #fff;
            font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 999px;
        }

        .swatches { display: flex; flex-wrap: wrap; gap: 18px; padding: 4px 2px; }
        .swatch { display: flex; flex-direction: column; align-items: center; gap: 6px; cursor: pointer; border: 0; background: none; }
        .swatch .dot {
            width: 54px; height: 54px; border-radius: 50%; border: 3px solid #fff;
            box-shadow: 0 0 0 1px #E5D3DA; transition: box-shadow .15s;
        }
        .swatch.active .dot { box-shadow: 0 0 0 3px var(--pink); }
        .swatch .name { font-size: 13px; font-weight: 700; color: var(--ink); }

        /* ===== الإضافات ===== */
        .cat-chips { display: flex; gap: 6px; overflow-x: auto; margin-bottom: 10px; scrollbar-width: none; }
        .cat-chips::-webkit-scrollbar { display: none; }
        .cat-chips button {
            flex: 0 0 auto; border: 0; background: #F4EEF0; color: var(--ink); border-radius: 999px;
            padding: 7px 14px; font-size: 13px; font-weight: 700; cursor: pointer;
        }
        .cat-chips button.active { background: var(--pink-dark); color: #fff; }
        .card .tick {
            position: absolute; top: 6px; left: 6px; width: 20px; height: 20px; border-radius: 50%;
            background: var(--pink); color: #fff; font-size: 12px; display: none; align-items: center; justify-content: center;
        }
        .card.active .tick { display: flex; }
        .card .none-art { aspect-ratio: 150 / 116; display: flex; align-items: center; justify-content: center; }
        .card .none-art svg { width: 54px; height: 54px; color: #D9C7CE; }
        .topbar a.back-link { display: flex; color: inherit; }

        /* ===== صور حقيقية للقطع ===== */
        .card img.photo { width: 100%; max-width: 150px; aspect-ratio: 150 / 116; object-fit: contain; display: block; margin: 0 auto; }

        /* ===== منظر علوي (خطوة الكتابة) ===== */
        .view-toggle {
            position: absolute; top: 6px; right: 12px; z-index: 2; display: none; gap: 2px;
            background: rgba(255,255,255,.94); border: 1px solid var(--pink-line); border-radius: 999px; padding: 3px;
        }
        .stage.has-photo .view-toggle { display: flex; }
        .view-toggle button { border: 0; background: transparent; padding: 6px 12px; border-radius: 999px; font-size: 13px; font-weight: 700; color: var(--pink-dark); cursor: pointer; }
        .view-toggle button.active { background: var(--pink-dark); color: #fff; }
        .photo-view { display: none; height: 38vh; min-height: 250px; max-height: 420px; align-items: center; justify-content: center; }
        .photo-view img { height: 94%; aspect-ratio: 1 / 1; object-fit: cover; border-radius: 18px; box-shadow: 0 6px 18px rgba(75,21,40,.15); }
        .stage.photo-mode model-viewer, .stage.photo-mode .inside-card { display: none; }
        .stage.photo-mode .photo-view { display: flex; }
        #sheet-art img { width: 96px; height: 96px; object-fit: contain; display: block; }

        /* ===== الكتابة ورفع الصورة ===== */
        .write-box { border: 1px solid var(--pink-line); border-radius: 14px; padding: 10px; position: relative; }
        .write-box legend { font-size: 13px; color: var(--pink-dark); font-weight: 700; padding: 0 6px; display: flex; align-items: center; gap: 4px; }
        .write-box legend svg { width: 16px; height: 16px; }
        .target-switch { display: flex; background: var(--pink-soft); border-radius: 10px; padding: 3px; margin-bottom: 8px; }
        .target-switch button { flex: 1; border: 0; background: transparent; padding: 7px; border-radius: 8px; font-size: 14px; font-weight: 700; color: var(--pink-dark); cursor: pointer; }
        .target-switch button.active { background: #fff; color: var(--ink); box-shadow: 0 1px 4px rgba(75,21,40,.12); }
        .write-input { width: 100%; border: 0; outline: 0; font-family: inherit; font-size: 17px; padding: 6px 2px; color: var(--ink); background: transparent; }
        .counter { text-align: left; font-size: 12px; color: var(--muted); margin-top: 4px; }
        .drop {
            margin-top: 14px; border: 2px dashed #E5C4D0; border-radius: 14px; padding: 18px 10px;
            display: flex; flex-direction: column; align-items: center; gap: 6px; cursor: pointer;
            color: var(--muted); font-size: 14px; font-weight: 700; background: #FFFBFC; text-align: center;
        }
        .drop svg { width: 32px; height: 32px; color: var(--pink); }
        .drop small { font-weight: 400; font-size: 12px; }
        .picked { margin-top: 14px; display: none; align-items: center; gap: 10px; border: 1px solid var(--pink-line); border-radius: 14px; padding: 8px; }
        .picked.show { display: flex; }
        .picked img { width: 56px; height: 56px; object-fit: cover; border-radius: 50%; }
        .picked .info { flex: 1; font-size: 13px; color: var(--muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .picked .info b { display: block; color: var(--ink); font-size: 14px; }
        .picked button { border: 0; background: var(--pink-soft); color: var(--pink-dark); border-radius: 10px; padding: 8px 12px; font-weight: 700; cursor: pointer; }

        /* ===== الفوتر ===== */
        .footer {
            position: fixed; bottom: 0; left: 0; right: 0; background: #fff;
            border-top: 1px solid var(--pink-line); padding: 8px 14px calc(10px + env(safe-area-inset-bottom));
            box-shadow: 0 -6px 20px rgba(75,21,40,.06); z-index: 10;
        }
        .chips { display: flex; gap: 6px; overflow-x: auto; padding-bottom: 8px; scrollbar-width: none; }
        .chips::-webkit-scrollbar { display: none; }
        .chip {
            flex: 0 0 auto; border: 1px solid var(--pink-line); border-radius: 10px; padding: 4px 10px;
            font-size: 12px; color: var(--muted); background: #fff; white-space: nowrap;
        }
        .chip b { color: var(--pink-dark); margin-inline-start: 4px; }
        .next-btn {
            width: 100%; border: 0; border-radius: 12px; background: var(--pink); color: #fff;
            font-size: 17px; font-weight: 800; padding: 13px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .next-btn:disabled { opacity: .6; }
        .next-btn svg { width: 22px; height: 22px; }

        /* ===== نافذة السلة ===== */
        .sheet-bg {
            position: fixed; inset: 0; background: rgba(43,10,22,.45); display: none;
            align-items: flex-end; justify-content: center; z-index: 20;
        }
        .sheet-bg.open { display: flex; }
        .sheet {
            width: 100%; max-width: 520px; background: #fff; border-radius: 22px 22px 0 0;
            padding: 18px 18px calc(18px + env(safe-area-inset-bottom)); animation: up .25s ease;
        }
        @keyframes up { from { transform: translateY(40px); opacity: 0; } }
        .sheet h2 { margin: 0 0 4px; font-size: 20px; display: flex; align-items: center; gap: 8px; }
        .sheet h2 .ok { width: 26px; height: 26px; border-radius: 50%; background: #7BC74D; color: #fff; display: inline-flex; align-items: center; justify-content: center; }
        .sheet h2 .ok svg { width: 16px; height: 16px; }
        .sheet .hint { font-size: 12px; color: var(--muted); margin: 0 0 12px; }
        .sheet-body { display: flex; gap: 12px; align-items: center; }
        .sheet-body svg { width: 96px; flex: 0 0 96px; }
        .lines { flex: 1; font-size: 14px; }
        .lines div { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #F1DDE4; }
        .lines div span:first-child { color: var(--muted); }
        .lines .sum { border: 0; font-weight: 800; font-size: 17px; padding-top: 8px; }
        .sheet .close { margin-top: 14px; }

        @media (min-width: 900px) {
            .wrap { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1.1fr 1fr; gap: 24px; align-items: start; }
            model-viewer, .photo-view { height: 62vh; max-height: 620px; }
            .stage { position: sticky; top: 0; }
            .footer-inner { max-width: 1100px; margin: 0 auto; }
            .grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
    </style>
</head>
<body>

    <header class="topbar">
        <div class="title">
            <a class="back-link" href="{{ route('cake.designs', $cake->slug) }}" aria-label="التصاميم"><x-mdi-chevron-right class="back" /></a>
            <h1>صمّم كيكتك</h1>
        </div>
        <div class="total" id="total-top">0 <small>ر.س</small></div>
    </header>

    <div class="wrap">
        <div class="stage" id="stage">
            <model-viewer
                id="viewer"
                alt="{{ $cake->name }}"
                ar
                ar-modes="webxr scene-viewer quick-look"
                ar-scale="fixed"
                ar-placement="floor"
                camera-controls
                auto-rotate
                camera-orbit="0deg 75deg 105%"
                min-camera-orbit="auto 60deg auto"
                max-camera-orbit="auto 85deg auto"
                shadow-intensity="0.3"
                shadow-softness="1"
                exposure="1"
                environment-image="neutral"
                interaction-prompt="none"
            >
                <button slot="ar-button" class="ar-btn">
                    <x-mdi-cube-scan />
                    شاهدها على طاولتك
                </button>
            </model-viewer>

            <div class="inside-card">
                <div class="lbl">من الداخل</div>
                <div id="inside-art"></div>
            </div>
            <div class="view-toggle" id="view-toggle">
                <button type="button" data-view="photo">الصورة الأصلية</button>
                <button type="button" data-view="3d" class="active">تصميمك 3D</button>
            </div>
            <div class="photo-view"><img id="photo-view-img" alt="الصورة الأصلية"></div>
            <div class="loading-pill">جاري تجهيز الكيكة…</div>
        </div>

        <div class="builder">
            <nav class="rail" id="rail">
                <button class="step-btn active" data-step="0"><x-mdi-cake-layered class="ico" /><span>الحجم</span><span class="done"><x-mdi-check-bold /></span></button>
                <button class="step-btn" data-step="1"><x-mdi-bowl-mix-outline class="ico" /><span>النكهة</span><span class="done"><x-mdi-check-bold /></span></button>
                <button class="step-btn" data-step="2"><x-mdi-spoon-sugar class="ico" /><span>الحشوة</span><span class="done"><x-mdi-check-bold /></span></button>
                <button class="step-btn" data-step="3"><x-mdi-layers-triple-outline class="ico" /><span>الطبقات</span><span class="done"><x-mdi-check-bold /></span></button>
                <button class="step-btn" data-step="4"><x-mdi-palette-outline class="ico" /><span>اللون</span><span class="done"><x-mdi-check-bold /></span></button>
                <button class="step-btn" data-step="5"><x-mdi-cupcake class="ico" /><span>الإضافات</span><span class="done"><x-mdi-check-bold /></span></button>
                <button class="step-btn" data-step="6"><x-mdi-pencil-outline class="ico" /><span>الكتابة</span><span class="done"><x-mdi-check-bold /></span></button>
            </nav>

            <main class="panel">
                <section class="step active" data-step="0">
                    <div class="step-title">الحجم وعدد الأدوار</div>
                    <div class="tier-switch" id="tier-switch"></div>
                    <div class="grid" id="size-grid"></div>
                </section>

                <section class="step" data-step="1">
                    <div class="step-title">اختر النكهة</div>
                    <div class="grid" id="flavor-grid"></div>
                </section>

                <section class="step" data-step="2">
                    <div class="step-title">اختر الحشوة</div>
                    <div class="grid" id="filling-grid"></div>
                </section>

                <section class="step" data-step="3">
                    <div class="step-title">عدد طبقات الكيك</div>
                    <div class="grid" id="layers-grid"></div>
                </section>

                <section class="step" data-step="4">
                    <div class="step-title">لون الكيكة</div>
                    <div class="step-sub">يتغير اللون مباشرة على المجسم وفي الواقع المعزز</div>
                    <div class="swatches" id="color-swatches"></div>
                </section>

                <section class="step" data-step="5">
                    <div class="step-title">الإضافات</div>
                    <div class="step-sub">تقدر تختار أكثر من إضافة — تظهر مباشرة على المجسم</div>
                    <div class="cat-chips" id="cat-chips"></div>
                    <div class="grid" id="topping-grid"></div>
                </section>

                <section class="step" data-step="6">
                    <div class="step-title">الكتابة والصورة</div>
                    <div class="step-sub">اختياري — الصورة تُطبع كما هي بدون أي تعديل</div>

                    <fieldset class="write-box">
                        <legend><x-mdi-pencil /> اكتب على الكيكة</legend>
                        <div class="target-switch" id="target-switch"></div>
                        <input class="write-input" id="write-input" type="text" placeholder="مثال: كل عام وأنت بخير"
                               maxlength="{{ $options['writing']['max_length'] }}" autocomplete="off">
                    </fieldset>
                    <div class="counter" dir="ltr"><span id="write-count">0</span> / {{ $options['writing']['max_length'] }}</div>

                    <label class="drop" id="drop">
                        <input type="file" id="print-input" accept="image/*" hidden>
                        <x-mdi-image-plus-outline />
                        ارفع صورة للطباعة على الكيكة
                        <small>JPG أو PNG — حتى {{ intdiv($options['writing']['print_image_max_kb'], 1024) }}MB</small>
                    </label>
                    <div class="picked" id="picked">
                        <img id="picked-img" alt="">
                        <div class="info"><b>صورة الطباعة</b><span id="picked-name"></span></div>
                        <button type="button" id="picked-remove">حذف</button>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-inner">
            <div class="chips" id="chips"></div>
            <button class="next-btn" id="next-btn">التالي</button>
        </div>
    </footer>

    <div class="sheet-bg" id="sheet">
        <div class="sheet">
            <h2><span class="ok"><x-mdi-check-bold /></span> تمت الإضافة للسلة</h2>
            <p class="hint">السعر محسوب من السيرفر</p>
            <div class="sheet-body">
                <div id="sheet-art"></div>
                <div class="lines" id="sheet-lines"></div>
            </div>
            <button class="next-btn close" id="sheet-close">متابعة التصميم</button>
        </div>
    </div>

    <script type="module">
        import { composeCake, boardTextAspect } from "{{ asset('js/cake-composer.js') }}";

        const OPTIONS = @json($options);
        const URLS = {
            models: "{{ asset('storage/models') }}",
            public: "{{ rtrim(asset(''), '/') }}",
            price: "{{ route('cake.customize.price', $cake->slug) }}",
            cart: "{{ route('cake.customize.cart', $cake->slug) }}",
        };
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const CART_ICON = `{!! str_replace('`', '', svg('mdi-cart-plus')->toHtml()) !!}`;

        const byKey = (list, field, value) => OPTIONS[list].find(o => o[field] === value);
        const popularSize = OPTIONS.sizes.find(s => s.popular) || OPTIONS.sizes[0];

        const INITIAL = @json($initial);
        const state = {
            tier: popularSize.tier,
            size: popularSize.key,
            flavor: OPTIONS.flavors[0].key,
            filling: OPTIONS.fillings[0].key,
            layers: OPTIONS.layers[0].count,
            color: OPTIONS.colors[0].key,
            toppings: [],
            design: null,
            text: '',
            textTarget: Object.keys(OPTIONS.writing.targets)[0],
        };
        // تصميم جاهز من الكولكشن (?design=...) يعبّي الاختيارات
        const ORIGINAL_DESIGN = INITIAL?.design ? byKey('designs', 'key', INITIAL.design) : null;
        if (INITIAL) {
            Object.assign(state, INITIAL, { toppings: [...(INITIAL.toppings || [])] });
            state.tier = byKey('sizes', 'key', state.size).tier;
        }
        let printFile = null, printUrl = null;
        let toppingCat = 'all';
        const STEPS = 7;
        const WRITING_STEP = 6;
        let currentStep = 0;
        const completed = new Set();

        // ================= الرسومات (دلالات بصرية) =================
        let gid = 0;

        function shade(hex, amt) {
            const n = parseInt(hex.slice(1), 16);
            const c = [n >> 16, (n >> 8) & 255, n & 255].map(v => Math.max(0, Math.min(255, Math.round(v + amt * (amt > 0 ? 255 - v : v)))));
            return '#' + c.map(v => v.toString(16).padStart(2, '0')).join('');
        }

        // كيكة بأدوار فوق بورد — حجم كل دور متناسب مع قطره الحقيقي
        function cakeArt(diameters, hex) {
            const id = 'g' + (++gid);
            const W = 150, H = 120, cx = W / 2, k = 10.5;
            const tierH = diameters.length === 1 ? 36 : (diameters.length === 2 ? 27 : 22);
            const boardRx = 10 * k / 2 + 14;
            let y = H - 22, out = '';

            out += `<defs><linearGradient id="${id}" x1="0" x2="1">
                <stop offset="0" stop-color="${shade(hex, -0.18)}"/><stop offset=".45" stop-color="${shade(hex, 0.12)}"/>
                <stop offset="1" stop-color="${shade(hex, -0.28)}"/></linearGradient></defs>`;
            out += `<ellipse cx="${cx}" cy="${y + 7}" rx="${boardRx}" ry="13" fill="#99355699"/>`;
            out += `<ellipse cx="${cx}" cy="${y + 3}" rx="${boardRx}" ry="13" fill="#F4C0D1"/>`;

            diameters.forEach(d => {
                const rx = d * k / 2, ry = Math.max(4, rx * 0.26), top = y - tierH;
                out += `<path d="M${cx - rx},${top} L${cx - rx},${y} A${rx},${ry} 0 0 0 ${cx + rx},${y} L${cx + rx},${top} Z" fill="url(#${id})"/>`;
                out += `<ellipse cx="${cx}" cy="${top}" rx="${rx}" ry="${ry}" fill="${shade(hex, 0.18)}"/>`;
                y = top;
            });
            return `<svg class="art" viewBox="0 0 ${W} ${H}" xmlns="http://www.w3.org/2000/svg">${out}</svg>`;
        }

        // قطعة كيك مقطوعة تبين النكهة + الحشوة + عدد الطبقات + لون التغطية
        function sliceArt({ flavor, filling, layers, color }) {
            const f = byKey('flavors', 'key', flavor);
            const fl = byKey('fillings', 'key', filling);
            const coat = byKey('colors', 'key', color).hex;
            const L = { x: 18, top: 44, h: 58 }, R = { x: 132, top: 30, h: 60 };
            const at = (side, t) => side.top + side.h * t;
            const band = (t0, t1, fill) =>
                `<polygon points="${L.x},${at(L, t0)} ${R.x},${at(R, t0)} ${R.x},${at(R, t1)} ${L.x},${at(L, t1)}" fill="${fill}"/>`;

            let out = `<ellipse cx="76" cy="104" rx="64" ry="9" fill="#4B152814"/>`;
            // الوجه الخارجي (التغطية)
            const back = { x: 142, top: R.top - 16 };
            out += `<polygon points="${R.x},${R.top} ${back.x},${back.top} ${back.x},${back.top + R.h} ${R.x},${R.top + R.h}" fill="${shade(coat, -0.15)}"/>`;

            const coatT = 0.08, s = (1 - coatT) / (layers + (layers - 1) * 0.45), fT = s * 0.45;
            out += band(0, coatT, coat);
            let t = coatT;
            for (let i = 0; i < layers; i++) {
                out += band(t, t + s, f.sponge);
                // نقاط فتات الإسفنج
                for (let j = 0; j < 7; j++) {
                    const u = (j + 0.5 + (i % 2) * 0.4) / 7.2, v = t + s * (0.3 + ((j * 37) % 5) / 10);
                    const x = L.x + (R.x - L.x) * u, y = at(L, v) + (at(R, v) - at(L, v)) * u;
                    out += `<circle cx="${x.toFixed(1)}" cy="${y.toFixed(1)}" r="1.4" fill="${f.crumb}"/>`;
                }
                t += s;
                if (i < layers - 1) {
                    out += band(t, t + fT, fl.cream);
                    if (fl.fruit) {
                        for (let j = 0; j < 6; j++) {
                            const u = (j + 0.6) / 6.3, v = t + fT / 2;
                            const x = L.x + (R.x - L.x) * u, y = at(L, v) + (at(R, v) - at(L, v)) * u;
                            out += `<ellipse cx="${x.toFixed(1)}" cy="${y.toFixed(1)}" rx="4" ry="${(fT * 22).toFixed(1)}" fill="${fl.fruit}"/>`;
                        }
                    }
                    t += fT;
                }
            }
            // السطح العلوي
            out += `<polygon points="${L.x},${L.top} ${R.x},${R.top} ${back.x},${back.top}" fill="${shade(coat, 0.15)}"/>`;
            out += `<polygon points="${L.x},${L.top} ${R.x},${R.top} ${R.x},${R.top + 3} ${L.x},${L.top + 3}" fill="${shade(coat, -0.08)}"/>`;
            return `<svg class="art" viewBox="0 0 150 116" xmlns="http://www.w3.org/2000/svg">${out}</svg>`;
        }

        // ================= منظر علوي: الكتابة والصورة المطبوعة =================
        const esc = t => t.replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
        const isDark = hex => {
            const n = parseInt(hex.slice(1), 16);
            return (0.299 * (n >> 16) + 0.587 * ((n >> 8) & 255) + 0.114 * (n & 255)) < 150;
        };

        // ================= صور السطح: رسمة التصميم + الكتابة + الصورة المطبوعة =================
        const imgCache = new Map();
        function loadImg(src) {
            if (!imgCache.has(src)) {
                imgCache.set(src, new Promise((resolve, reject) => {
                    const im = new Image();
                    im.onload = () => resolve(im);
                    im.onerror = reject;
                    im.src = src;
                }));
            }
            return imgCache.get(src);
        }
        const fontReady = document.fonts ? document.fonts.load('800 80px Tajawal').catch(() => {}) : Promise.resolve();

        function drawText(ctx, text, x, y, maxW, maxFs, fill, halo) {
            const fs = Math.min(maxFs, maxW / (text.length * 0.5));
            ctx.font = `800 ${fs}px Tajawal, sans-serif`;
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.direction = 'rtl';
            if (halo) {
                ctx.lineJoin = 'round';
                ctx.lineWidth = fs * 0.2;
                ctx.strokeStyle = halo;
                ctx.strokeText(text, x, y);
            }
            ctx.fillStyle = fill;
            ctx.fillText(text, x, y);
        }

        // صورة مربعة تغطي سطح الدور العلوي كاملًا
        async function buildTopCanvas() {
            const design = state.design ? byKey('designs', 'key', state.design) : null;
            const text = state.text.trim();
            const onCake = text && state.textTarget === 'cake';
            if (!design && !printUrl && !onCake) return null;

            const N = 1024, c = document.createElement('canvas');
            c.width = c.height = N;
            const ctx = c.getContext('2d');

            if (printUrl) {
                const im = await loadImg(printUrl);
                const r = N * 0.42, k = Math.max((2 * r) / im.width, (2 * r) / im.height);
                ctx.save();
                ctx.beginPath();
                ctx.arc(N / 2, N / 2, r, 0, Math.PI * 2);
                ctx.clip();
                ctx.drawImage(im, N / 2 - im.width * k / 2, N / 2 - im.height * k / 2, im.width * k, im.height * k);
                ctx.restore();
            }
            if (design) ctx.drawImage(await loadImg(`${URLS.public}/${design.decal}`), 0, 0, N, N);
            if (onCake) {
                await fontReady;
                const busy = design || printUrl;
                const dark = isDark(byKey('colors', 'key', state.color).hex);
                // مع رسمة أو صورة: الكتابة أسفل السطح داخل حدود الحواف
                drawText(ctx, text, N / 2, busy ? N * 0.73 : N / 2, busy ? N * 0.58 : N * 0.7, busy ? 92 : 120,
                    busy ? '#4B1528' : (dark ? '#FFFFFF' : '#4B1528'), busy ? '#FFFFFF' : null);
            }
            return c;
        }

        // شريط الكتابة على البورد أمام الكيكة
        async function buildBoardCanvas(baseUrl) {
            const text = state.text.trim();
            if (!text || state.textTarget !== 'board') return null;
            const aspect = await boardTextAspect(baseUrl);
            if (!aspect) return null;
            await fontReady;
            const H = 160, W = Math.min(2048, Math.round(H * aspect));
            const c = document.createElement('canvas');
            c.width = W; c.height = H;
            drawText(c.getContext('2d'), text, W / 2, H / 2, W * 0.92, H * 0.62, '#4B1528', null);
            return c;
        }

        function renderTargets() {
            const box = document.getElementById('target-switch');
            box.innerHTML = '';
            Object.entries(OPTIONS.writing.targets).forEach(([key, label]) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.textContent = label;
                b.className = key === state.textTarget ? 'active' : '';
                b.addEventListener('click', () => { state.textTarget = key; renderTargets(); refreshWriting(); });
                box.appendChild(b);
            });
        }

        let modelTimer;
        function refreshWriting() {
            showPrice(localQuote());
            confirmPrice();
            clearTimeout(modelTimer);
            modelTimer = setTimeout(updateModel, 350);
        }

        const writeInput = document.getElementById('write-input');
        writeInput.addEventListener('input', () => {
            state.text = writeInput.value;
            document.getElementById('write-count').textContent = writeInput.value.length;
            refreshWriting();
        });

        const printInput = document.getElementById('print-input');
        printInput.addEventListener('change', () => {
            const file = printInput.files[0];
            if (!file) return;
            if (file.size > OPTIONS.writing.print_image_max_kb * 1024) {
                alert('حجم الصورة كبير، الحد الأقصى ' + Math.floor(OPTIONS.writing.print_image_max_kb / 1024) + 'MB');
                printInput.value = '';
                return;
            }
            if (printUrl) URL.revokeObjectURL(printUrl);
            printFile = file;
            printUrl = URL.createObjectURL(file);
            document.getElementById('picked-img').src = printUrl;
            document.getElementById('picked-name').textContent = file.name;
            document.getElementById('picked').classList.add('show');
            document.getElementById('drop').style.display = 'none';
            refreshWriting();
        });
        document.getElementById('picked-remove').addEventListener('click', () => {
            if (printUrl) URL.revokeObjectURL(printUrl);
            printFile = null; printUrl = null; printInput.value = '';
            document.getElementById('picked').classList.remove('show');
            document.getElementById('drop').style.display = '';
            refreshWriting();
        });

        // ================= عرض الخيارات =================
        function photo(option) {
            return `<img class="photo" src="${URLS.public}/${option.image}" alt="${option.name}" loading="lazy">`;
        }
        function layersLabel(n) { return n === 2 ? 'طبقتين' : `${n} طبقات`; }
        function money(n) { return `${n} <small>ر.س</small>`; }
        function plus(n) { return n > 0 ? `+${n} ر.س` : 'بدون إضافة'; }

        function card({ active, art, name, meta, price, tag, onClick }) {
            const el = document.createElement('div');
            el.className = 'card' + (active ? ' active' : '');
            el.innerHTML = `${tag ? `<div class="tag">${tag}</div>` : ''}${art}
                <div class="name">${name}</div>${meta ? `<div class="meta">${meta}</div>` : ''}
                <div class="price">${price}</div>`;
            el.addEventListener('click', onClick);
            return el;
        }

        function renderTiers() {
            const box = document.getElementById('tier-switch');
            box.innerHTML = '';
            Object.entries(OPTIONS.tiers).forEach(([tier, label]) => {
                const b = document.createElement('button');
                b.textContent = label;
                b.className = Number(tier) === state.tier ? 'active' : '';
                b.addEventListener('click', () => {
                    state.tier = Number(tier);
                    state.size = OPTIONS.sizes.find(s => s.tier === state.tier).key;
                    update();
                });
                box.appendChild(b);
            });
        }

        function renderSizes() {
            const grid = document.getElementById('size-grid');
            grid.innerHTML = '';
            const hex = byKey('colors', 'key', state.color).hex;
            OPTIONS.sizes.filter(s => s.tier === state.tier).forEach(s => grid.appendChild(card({
                active: s.key === state.size,
                art: cakeArt(s.diameters, hex),
                name: `${s.label} إنش`,
                meta: `يكفي ${s.serves}`,
                price: `${s.price} ر.س`,
                tag: s.popular ? 'الأكثر طلبًا' : '',
                onClick: () => { state.size = s.key; update(); },
            })));
        }

        function renderFlavors() {
            const grid = document.getElementById('flavor-grid');
            grid.innerHTML = '';
            OPTIONS.flavors.forEach(f => grid.appendChild(card({
                active: f.key === state.flavor,
                art: f.image ? photo(f) : sliceArt({ ...state, flavor: f.key }),
                name: f.name, price: plus(f.price),
                onClick: () => { state.flavor = f.key; update(); },
            })));
        }

        function renderFillings() {
            const grid = document.getElementById('filling-grid');
            grid.innerHTML = '';
            OPTIONS.fillings.forEach(f => grid.appendChild(card({
                active: f.key === state.filling,
                art: f.image ? photo(f) : sliceArt({ ...state, filling: f.key }),
                name: f.name, price: plus(f.price),
                onClick: () => { state.filling = f.key; update(); },
            })));
        }

        function renderLayers() {
            const grid = document.getElementById('layers-grid');
            grid.innerHTML = '';
            OPTIONS.layers.forEach(l => grid.appendChild(card({
                active: l.count === state.layers,
                art: sliceArt({ ...state, layers: l.count }),
                name: layersLabel(l.count), price: plus(l.price),
                onClick: () => { state.layers = l.count; update(); },
            })));
        }

        function renderToppings() {
            const chips = document.getElementById('cat-chips');
            chips.innerHTML = '';
            Object.entries({ all: 'الكل', ...OPTIONS.topping_categories }).forEach(([key, label]) => {
                const b = document.createElement('button');
                b.textContent = label;
                b.className = key === toppingCat ? 'active' : '';
                b.addEventListener('click', () => { toppingCat = key; renderToppings(); });
                chips.appendChild(b);
            });

            const grid = document.getElementById('topping-grid');
            grid.innerHTML = '';
            grid.appendChild(card({
                active: state.toppings.length === 0,
                art: `<div class="none-art">{!! str_replace('`', '', svg('mdi-cancel')->toHtml()) !!}</div>`,
                name: 'بدون', price: '',
                onClick: () => { state.toppings = []; update(); },
            }));
            if (ORIGINAL_DESIGN && toppingCat === 'all') {
                const el = card({
                    active: state.design === ORIGINAL_DESIGN.key,
                    art: `<img class="photo" src="${URLS.public}/${ORIGINAL_DESIGN.photo}" alt="" style="border-radius:12px;object-fit:cover">`,
                    name: 'رسمة التصميم', price: plus(ORIGINAL_DESIGN.drawing_price),
                    onClick: () => { state.design = state.design ? null : ORIGINAL_DESIGN.key; update(); },
                });
                el.insertAdjacentHTML('afterbegin', '<div class="tick">✓</div>');
                grid.appendChild(el);
            }
            OPTIONS.toppings.filter(t => toppingCat === 'all' || t.category === toppingCat).forEach(t => {
                const el = card({
                    active: state.toppings.includes(t.key),
                    art: photo(t),
                    name: t.name, price: plus(t.price),
                    onClick: () => {
                        state.toppings = state.toppings.includes(t.key)
                            ? state.toppings.filter(k => k !== t.key)
                            : [...state.toppings, t.key];
                        update();
                    },
                });
                el.insertAdjacentHTML('afterbegin', '<div class="tick">✓</div>');
                grid.appendChild(el);
            });
        }

        function renderColors() {
            const box = document.getElementById('color-swatches');
            box.innerHTML = '';
            OPTIONS.colors.forEach(c => {
                const b = document.createElement('button');
                b.className = 'swatch' + (c.key === state.color ? ' active' : '');
                b.innerHTML = `<span class="dot" style="background:${c.hex}"></span><span class="name">${c.name}</span>`;
                b.addEventListener('click', () => { state.color = c.key; update(); });
                box.appendChild(b);
            });
        }

        // ================= السعر =================
        // عرض فوري من نفس الكتالوج، ثم تأكيد من السيرفر (السيرفر هو المرجع)
        function localQuote() {
            const size = byKey('sizes', 'key', state.size);
            const items = [
                { group: 'الحجم', price: size.price },
                { group: 'النكهة', price: byKey('flavors', 'key', state.flavor).price },
                { group: 'الحشوة', price: byKey('fillings', 'key', state.filling).price },
                { group: 'الطبقات', price: byKey('layers', 'count', state.layers).price },
                { group: 'اللون', price: byKey('colors', 'key', state.color).price },
            ];
            if (state.design) {
                const d = byKey('designs', 'key', state.design);
                items.push({ group: 'الرسمة', label: d.name, price: d.drawing_price });
            }
            state.toppings.forEach(k => {
                const t = byKey('toppings', 'key', k);
                items.push({ group: 'إضافة', label: t.name, price: t.price });
            });
            if (state.text.trim()) items.push({ group: 'الكتابة', price: OPTIONS.writing.text_price });
            if (printFile) items.push({ group: 'صورة للطباعة', price: OPTIONS.writing.print_image_price });
            return { items, total: items.reduce((a, i) => a + i.price, 0) };
        }

        let lastTotal = null;
        function showPrice(quote) {
            const top = document.getElementById('total-top');
            top.innerHTML = money(quote.total);
            if (lastTotal !== null && lastTotal !== quote.total) {
                top.classList.remove('bump'); void top.offsetWidth; top.classList.add('bump');
            }
            lastTotal = quote.total;
            document.getElementById('chips').innerHTML = quote.items
                .filter(i => i.price > 0)
                .map(i => `<span class="chip">${i.group === 'إضافة' || i.group === 'الرسمة' ? esc(i.label) : i.group}<b>${i.price} ر.س</b></span>`).join('');
        }

        function selection() {
            const sel = { size: state.size, flavor: state.flavor, filling: state.filling, layers: state.layers, color: state.color, toppings: state.toppings };
            if (state.design) sel.design = state.design;
            if (state.text.trim()) Object.assign(sel, { text: state.text.trim(), text_target: state.textTarget });
            return sel;
        }

        async function send(url, body, json) {
            const headers = { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF };
            if (json) headers['Content-Type'] = 'application/json';
            const res = await fetch(url, { method: 'POST', headers, body });
            if (!res.ok) throw new Error(res.status);
            return res.json();
        }

        // السعر: الاختيارات فقط (+ هل في صورة)
        const postPrice = () => send(URLS.price, JSON.stringify({ ...selection(), has_print_image: !!printFile }), true);

        // السلة: multipart عشان الصورة تنرفع كما هي
        function postCart() {
            const form = new FormData();
            Object.entries(selection()).forEach(([k, v]) =>
                Array.isArray(v) ? v.forEach(x => form.append(k + '[]', x)) : form.append(k, v));
            if (printFile) form.append('print_image', printFile);
            return send(URLS.cart, form, false);
        }

        let priceTimer, priceSeq = 0;
        function confirmPrice() {
            clearTimeout(priceTimer);
            const seq = ++priceSeq;
            priceTimer = setTimeout(async () => {
                try {
                    const quote = await postPrice();
                    if (seq === priceSeq) showPrice(quote);
                } catch (e) { /* يبقى العرض المحلي */ }
            }, 250);
        }

        // ================= المجسم واللون =================
        const viewer = document.getElementById('viewer');
        const stage = document.getElementById('stage');

        // نركّب الكيكة (جسم بحواف دائرية + اللون + الإضافات) في المتصفح ونعرضها كملف واحد.
        // الآيفون: model-viewer يولّد ملف usdz من نفس المشهد عند الضغط على زر الواقع المعزز.
        let composeSeq = 0, currentBlob = null;
        async function updateModel() {
            const seq = ++composeSeq;
            stage.classList.add('loading');
            const baseUrl = `${URLS.models}/cake_${state.size}_layers${state.layers}_${OPTIONS.model_version}.glb`;
            try {
                const [topCanvas, boardCanvas] = await Promise.all([buildTopCanvas(), buildBoardCanvas(baseUrl)]);
                if (seq !== composeSeq) return;
                const url = await composeCake(baseUrl, {
                    color: byKey('colors', 'key', state.color).hex,
                    toppings: state.toppings,
                    topCanvas,
                    boardCanvas,
                });
                if (seq !== composeSeq) { URL.revokeObjectURL(url); return; }
                const old = currentBlob;
                currentBlob = url;
                viewer.src = url;
                if (old) setTimeout(() => URL.revokeObjectURL(old), 2000);
            } catch (e) {
                console.error(e);
                if (seq === composeSeq) stage.classList.remove('loading');
            }
        }
        viewer.addEventListener('load', () => stage.classList.remove('loading'));

        // الصورة الأصلية ↔ المجسم (للتصاميم المأخوذة من صور حقيقية)
        function setView(view) {
            stage.classList.toggle('photo-mode', view === 'photo');
            document.querySelectorAll('#view-toggle button').forEach(b => b.classList.toggle('active', b.dataset.view === view));
        }
        document.querySelectorAll('#view-toggle button').forEach(b => b.addEventListener('click', () => setView(b.dataset.view)));
        if (ORIGINAL_DESIGN?.photo) {
            stage.classList.add('has-photo');
            document.getElementById('photo-view-img').src = `${URLS.public}/${ORIGINAL_DESIGN.photo}`;
        }

        const DEFAULT_ORBIT = viewer.getAttribute('camera-orbit');

        // ================= الخطوات =================
        const nextBtn = document.getElementById('next-btn');

        function goTo(step) {
            currentStep = step;
            document.querySelectorAll('.step').forEach(s => s.classList.toggle('active', Number(s.dataset.step) === step));
            document.querySelectorAll('.step-btn').forEach(b => {
                const n = Number(b.dataset.step);
                b.classList.toggle('active', n === step);
                b.classList.toggle('completed', completed.has(n));
            });
            // في خطوة الكتابة نرفع الكاميرا لنشوف سطح الكيكة والبورد
            viewer.cameraOrbit = step === WRITING_STEP ? '0deg 32deg auto' : DEFAULT_ORBIT;
            viewer.autoRotate = step !== WRITING_STEP;
            if (step === WRITING_STEP) setView('3d');
            const last = step === STEPS - 1;
            nextBtn.innerHTML = last ? `${CART_ICON} أضف للسلة` : 'التالي';
        }

        document.querySelectorAll('.step-btn').forEach(b => b.addEventListener('click', () => goTo(Number(b.dataset.step))));

        nextBtn.addEventListener('click', async () => {
            completed.add(currentStep);
            if (currentStep < STEPS - 1) {
                goTo(currentStep + 1);
                document.querySelector('.builder').scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
            goTo(currentStep);
            nextBtn.disabled = true;
            try {
                const { item } = await postCart();
                showPrice(item);
                let snap = '';
                try { snap = stage.classList.contains('photo-mode') ? '' : viewer.toDataURL('image/png'); } catch (e) { /* */ }
                document.getElementById('sheet-art').innerHTML = snap ? `<img src="${snap}" alt="">` : sliceArt(state);
                document.getElementById('sheet-lines').innerHTML = item.items
                    .map(i => `<div><span>${i.group}</span><span>${esc(i.label)}${i.price ? ` (${i.price})` : ''}</span></div>`).join('')
                    + `<div class="sum"><span>الإجمالي</span><span>${item.total} ر.س</span></div>`;
                document.getElementById('sheet').classList.add('open');
            } catch (e) {
                alert('تعذّر الإضافة للسلة، حاول مرة ثانية');
            } finally {
                nextBtn.disabled = false;
            }
        });
        document.getElementById('sheet-close').addEventListener('click', () => document.getElementById('sheet').classList.remove('open'));

        // ================= التحديث =================
        function update() {
            renderTiers();
            renderSizes();
            renderFlavors();
            renderFillings();
            renderLayers();
            renderColors();
            renderToppings();
            document.getElementById('inside-art').innerHTML = sliceArt(state);
            showPrice(localQuote());
            confirmPrice();
            updateModel();
        }

        renderTargets();
        update();
        goTo(0);
    </script>
</body>
</html>
