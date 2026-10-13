<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $cake->name }} - جوماكيك</title>

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
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, 'Segoe UI', Tahoma, sans-serif;
            background: #FFFFFF;
            text-align: center;
        }

        header { padding: 20px 16px 4px; }
        .logo { max-width: 150px; margin: 0 auto; display: block; }

        model-viewer {
            width: 100%;
            height: 46vh;
            background-color: #FFFFFF;
        }

        .viewer-wrap { position: relative; }
        .serves-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #fff;
            border: 1.5px solid #F4C0D1;
            border-radius: 14px;
            padding: 10px 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .serves-icon { width: 24px; height: 24px; color: #D4537E; }
        .serves-badge span {
            font-size: 13px;
            font-weight: 700;
            color: #4B1528;
            white-space: nowrap;
        }

        /* السعر المبدئي */
        .price-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #fff;
            border: 1.5px solid #F4C0D1;
            border-radius: 14px;
            padding: 8px 14px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .price-badge[hidden] { display: none; }
        .price-badge .amount { font-size: 18px; font-weight: 800; color: #4B1528; white-space: nowrap; }
        .price-badge .caption { font-size: 11px; font-weight: 700; color: #993556; }

        /* سادة / فنتج */
        .style-switch {
            position: absolute;
            right: 12px;
            bottom: 74px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .style-option {
            background: #fff;
            border: 1.5px solid #F4C0D1;
            border-radius: 12px;
            padding: 7px 16px;
            font-size: 14px;
            font-weight: 700;
            color: #993556;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            transition: background 0.15s ease, color 0.15s ease, transform 0.1s ease;
        }
        .style-option.active { background: #D4537E; border-color: #D4537E; color: #fff; }
        .style-option:active { transform: scale(0.95); }

        .viewer-loading {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: #993556;
            pointer-events: none;
        }
        .viewer-loading[hidden] { display: none; }

        #ar-button-el {
            background: #D4537E;
            color: #fff;
            border: none;
            border-radius: 30px;
            padding: 12px 26px;
            font-size: 15px;
            font-weight: 700;
            position: absolute;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        #ar-button-el:active { transform: translateX(-50%) scale(0.94); }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #4B1528;
            text-align: right;
            padding: 16px 16px 8px;
            max-width: 480px;
            margin: 0 auto;
        }

        .options-panel {
            display: none;
            animation: slideDown 0.2s ease;
        }
        .options-panel.open { display: block; }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* مفتاح الأدوار */
        .tier-switch {
            display: flex;
            background: #FBEAF0;
            border-radius: 12px;
            padding: 3px;
            max-width: 480px;
            margin: 0 auto 4px;
            padding-inline: 16px;
        }
        .tier-option {
            flex: 1;
            text-align: center;
            padding: 9px 4px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            color: #993556;
            cursor: pointer;
            transition: background 0.15s ease, color 0.15s ease, transform 0.1s ease;
        }
        .tier-option.active {
            background: #D4537E;
            color: #fff;
        }
        .tier-option:active { transform: scale(0.96); }
        .tier-option.disabled, .style-option.disabled { opacity: 0.35; cursor: not-allowed; }
        .tier-note {
            font-size: 11px;
            color: #993556;
            padding: 6px 16px 4px;
            max-width: 480px;
            margin: 0 auto;
            text-align: right;
        }

        /* بطاقات المقاس */
        .cards-row {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 8px 16px 6px;
            max-width: 480px;
            margin: 0 auto;
            scroll-snap-type: x mandatory;
        }
        .size-card {
            flex: 0 0 92px;
            scroll-snap-align: start;
            background: #fff;
            border: 1.5px solid #F4C0D1;
            border-radius: 14px;
            padding: 14px 6px 10px;
            text-align: center;
            position: relative;
            cursor: pointer;
            transition: border-color 0.15s ease, background 0.15s ease, transform 0.12s ease, box-shadow 0.15s ease;
        }
        .size-card:active { transform: scale(0.95); }
        .size-card.active {
            border: 2px solid #D4537E;
            background: #FBEAF0;
            box-shadow: 0 4px 10px rgba(212, 83, 126, 0.18);
        }
        .size-card .check {
            position: absolute;
            top: 6px;
            left: 6px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #D4537E;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .size-card.active .check { display: flex; }
        .size-card .badge {
            position: absolute;
            top: -9px;
            left: 50%;
            transform: translateX(-50%);
            background: #D4537E;
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 9px;
            border-radius: 8px;
            white-space: nowrap;
        }
        .size-card .circle {
            border-radius: 50%;
            background: #F4C0D1;
            margin: 4px auto 8px;
            transition: background 0.15s ease;
        }
        .size-card.active .circle { background: #D4537E; }
        .size-card .num {
            font-size: 16px;
            font-weight: 700;
            color: #4B1528;
            white-space: nowrap;
        }
        .size-card .serves {
            font-size: 10px;
            font-weight: 700;
            color: #993556;
            margin-top: 2px;
        }

        /* عدد الطبقات */
        .layers-row {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 6px 16px 14px;
            max-width: 480px;
            margin: 0 auto;
            scroll-snap-type: x mandatory;
        }
        .layer-chip {
            flex: 0 0 52px;
            scroll-snap-align: start;
            background: #fff;
            border: 1.5px solid #F4C0D1;
            border-radius: 12px;
            padding: 8px 4px;
            text-align: center;
            cursor: pointer;
            position: relative;
            transition: border-color 0.15s ease, background 0.15s ease, transform 0.12s ease;
        }
        .layer-chip:active { transform: scale(0.94); }
        .layer-chip.active {
            background: #FBEAF0;
            border: 2px solid #D4537E;
        }
        .layer-chip .check {
            position: absolute;
            top: 4px;
            left: 4px;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background: #D4537E;
            color: #fff;
            font-size: 8px;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .layer-chip.active .check { display: flex; }
        .layer-chip .bars {
            height: 24px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            gap: 1.5px;
            margin-bottom: 6px;
        }
        .layer-chip .bars div { border-radius: 2px; }
        /* سعر صغير تحت المقاس والطبقة */
        .size-card .card-price, .layer-chip .chip-price {
            font-size: 10px;
            font-weight: 700;
            color: #D4537E;
            margin-top: 3px;
            white-space: nowrap;
        }
        /* رمز الريال السعودي */
        .riyal {
            width: 0.85em;
            height: 0.85em;
            vertical-align: -0.08em;
            margin-inline-start: 2px;
        }

        .layer-chip .lnum {
            font-size: 14px;
            font-weight: 700;
            color: #4B1528;
        }

        /* الدلالات البصرية */
        .cake-spec-badges {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 10px;
            padding: 16px 8px;
            border-top: 1px solid #F4C0D1;
            max-width: 480px;
            margin: 8px auto 0;
        }
        .spec-badge { display: flex; flex-direction: column; align-items: center; gap: 4px; min-width: 60px; }
        .spec-icon, .spec-icon-mdi { width: 26px; height: 26px; color: #D4537E; }
        .spec-label { font-size: 12px; font-weight: 700; color: #4B1528; }
    </style>
</head>
<body>

    <header>
        <img src="{{ asset('images/icons/logo.png') }}" alt="JOMACAKE" class="logo">
    </header>

    <div class="viewer-wrap">
        <model-viewer
            id="viewer"
            alt="{{ $cake->name }}"
            ar
            ar-modes="webxr scene-viewer quick-look"
            ar-scale="fixed"
            ar-placement="floor"
            camera-controls
            auto-rotate
            camera-orbit="0deg 78deg 105%"
            min-camera-orbit="auto 65deg auto"
            max-camera-orbit="auto 85deg auto"
            shadow-intensity="0.1"
            shadow-softness="1"
            exposure="1"
            environment-image="neutral"
        >
            <button id="ar-button-el" slot="ar-button">شاهدها على طاولتك</button>
        </model-viewer>

        <div class="viewer-loading" id="viewer-loading">جاري تجهيز الكيكة…</div>

        <div class="price-badge" id="price-badge" hidden>
            <div class="amount" id="price-amount"></div>
            <div class="caption">السعر المبدئي</div>
        </div>

        <div class="style-switch" id="style-switch">
            <div class="style-option" data-style="vintage">فنتج</div>
            <div class="style-option active" data-style="plain">سادة</div>
        </div>

        <div class="serves-badge" id="serves-badge">
            <x-mdi-account-group class="serves-icon" />
            <span id="serves-text">{{ $defaultVariant->serves_label }}</span>
        </div>
    </div>

    <x-cake-spec-badges
        :tier-count="1"
        :size-label="$defaultVariant->size_label"
        :layers-count="2"
        :with-shape="true"
        id="spec-badges-el"
    />

    <div id="panel-shape" class="options-panel">
        <div class="section-title">شكل الكيكة</div>
        <div class="tier-switch" id="shape-switch">
            <div class="tier-option shape-option active" data-shape="round">دائري</div>
            <div class="tier-option shape-option" data-shape="heart">قلب</div>
            <div class="tier-option shape-option" data-shape="cone">مخروطي</div>
        </div>
    </div>

    <div id="panel-tier" class="options-panel">
        <div class="section-title">عدد الأدوار</div>
        <div class="tier-switch" id="tier-switch">
            <div class="tier-option active" data-tier="1">دور واحد</div>
            <div class="tier-option" data-tier="2">دورين</div>
            <div class="tier-option" data-tier="3">3 أدوار</div>
        </div>
    </div>

    <div id="panel-size" class="options-panel">
        <div class="section-title">اختر الحجم</div>
        <div class="cards-row" id="size-cards">
            {{-- يتم تعبيتها بالجافاسكريبت حسب الدور المختار --}}
        </div>
    </div>

    <div id="panel-layers" class="options-panel">
        <div class="section-title">عدد طبقات الكيك</div>
        <div class="layers-row" id="layers-row">
            {{-- يتم تعبيتها بالجافاسكريبت حسب الحجم المختار (لكل مقاس مدى مختلف مسموح) --}}
        </div>
    </div>

    <script type="module">
        import { composeCake } from "{{ asset('js/cake-composer.js') }}";

        // فتح/قفل لوحة الخيارات حسب البادج المضغوط (وحدة مفتوحة بنفس الوقت)
        document.querySelectorAll('.spec-badge.clickable').forEach(badge => {
            badge.addEventListener('click', () => {
                const targetId = badge.dataset.target;
                const targetPanel = document.getElementById(targetId);
                const isOpen = targetPanel.classList.contains('open');

                document.querySelectorAll('.options-panel').forEach(p => p.classList.remove('open'));
                document.querySelectorAll('.spec-badge.clickable').forEach(b => b.classList.remove('open'));

                if (!isOpen) {
                    targetPanel.classList.add('open');
                    badge.classList.add('open');
                }
            });
        });
        // فتح لوحة "الشكل" افتراضيًا عند تحميل الصفحة (أول اختيار)
        (function openShapePanelByDefault() {
            const defaultPanel = document.getElementById('panel-shape');
            const defaultBadge = document.querySelector('.spec-badge.clickable[data-target="panel-shape"]');
            if (defaultPanel) defaultPanel.classList.add('open');
            if (defaultBadge) defaultBadge.classList.add('open');
        })();

        const viewer = document.getElementById('viewer');
        const modelsBaseUrl = "{{ asset('storage/models') }}";
        const MODEL_VERSION = "v45"; // غيّر هذا الرقم كل مرة تحدّث ملفات النماذج — جزء من اسم الملف نفسه

        let currentSizeKey = "4in";  // مطابق لاسم الملف: 4in/6in/8in/10in
        let currentLayers = 2;
        let currentStyle = "plain";  // plain = سادة، vintage = فنتج
        let currentShape = "round";  // round = دائري، heart = قلب، cone = مخروطي

        // المدى المسموح بعدد الطبقات لكل مقاس/كومبو (من config/cake_ar.php)
        const layerLimits = @json($layerLimits);
        // السعر المبدئي لكل مقاس/كومبو وعدد طبقات
        const prices = @json((object) $prices);
        const CAKE_COLOR = @json(config('cake_ar.cake_color'));
        const LAYER_HEIGHT = @json(config('cake_ar.layer_height'));
        const BOARD_COLORS = @json(config('cake_ar.board_colors'));
        // النماذج الجاهزة فيها طبقتين و3 طبقات — أكثر من ذلك يُبنى من نموذج 3 طبقات + ارتفاع إضافي
        const MAX_MODEL_LAYERS = 3;

        // رمز الريال السعودي الجديد (SVG حتى يظهر على كل الأجهزة)
        const RIYAL = '<svg class="riyal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-label="ريال"><path d="M8.5 2.5v14.5M14 4.5v15.5M3 11.5l18-4M3 16l18-4M14.5 20.5l6.5-1.5"/></svg>';
        const money = n => `${Math.round(n)}${RIYAL}`;

        const layerColors = ['#F4C0D1','#ED93B1','#D4537E','#993556','#72243E','#4B1528','#2C1018'];

        function renderLayerChips(sizeKey) {
            const container = document.getElementById('layers-row');
            container.innerHTML = '';
            const allowed = layerLimits[sizeKey] || [1,2,3,4,5,6,7];

            if (!allowed.includes(currentLayers)) {
                currentLayers = allowed[0];
            }

            const sizePrices = prices[sizeKey];
            allowed.forEach(n => {
                // زيادة السعر عن أقل عدد طبقات (مثل موقع جوماكيك: 2 طبقة +0، 3 طبقات +20...)
                const extra = sizePrices?.[n] !== undefined ? sizePrices[n] - sizePrices[allowed[0]] : null;
                const chip = document.createElement('div');
                chip.className = 'layer-chip' + (n === currentLayers ? ' active' : '');
                chip.dataset.layers = n;

                let barsHtml = '';
                for (let b = 1; b <= n; b++) {
                    const h = Math.max(2.5, 6 - n * 0.4);
                    barsHtml += `<div style="height:${h}px;background:${layerColors[(b-1) % layerColors.length]};"></div>`;
                }

                chip.innerHTML = `
                    <div class="check">✓</div>
                    <div class="bars">${barsHtml}</div>
                    <div class="lnum">${n}</div>
                    ${extra !== null ? `<div class="chip-price">+${money(extra)}</div>` : ''}
                `;

                chip.addEventListener('click', () => {
                    container.querySelectorAll('.layer-chip').forEach(c => c.classList.remove('active'));
                    chip.classList.add('active');
                    currentLayers = n;
                    updateModel();
                    document.querySelectorAll('.spec-label')[2].textContent = n + (n == 1 ? ' طبقة' : ' طبقات');
                });

                container.appendChild(chip);
            });
        }

        // كل تغيير يركّب الكيكة من جديد؛ نتجاهل النتيجة إذا اختار الزبون شي ثاني قبل ما تخلص
        let composeToken = 0;
        let currentBlob = null;
        const loadingEl = document.getElementById('viewer-loading');

        async function updateModel() {
            updatePrice();
            const token = ++composeToken;
            const modelLayers = Math.min(currentLayers, MAX_MODEL_LAYERS);
            const base = `${modelsBaseUrl}/cake_${currentSizeKey}_layers${modelLayers}_${MODEL_VERSION}.glb`;

            loadingEl.hidden = !!currentBlob;
            try {
                const url = await composeCake(base, {
                    color: CAKE_COLOR,
                    boardColor: BOARD_COLORS[currentSizeKey] || BOARD_COLORS.default,
                    shape: currentShape,
                    style: currentShape === 'cone' ? 'plain' : currentStyle,
                    extraHeight: (currentLayers - modelLayers) * LAYER_HEIGHT,
                });
                if (token !== composeToken) { URL.revokeObjectURL(url); return; }
                if (currentBlob) URL.revokeObjectURL(currentBlob);
                currentBlob = url;
                viewer.src = url;
            } catch (e) {
                console.error(e);
                if (token === composeToken) loadingEl.textContent = 'تعذر تحميل الكيكة، حدّث الصفحة';
                return;
            }
            loadingEl.hidden = true;
        }

        function updatePrice() {
            const badge = document.getElementById('price-badge');
            const price = prices[currentSizeKey]?.[currentLayers];
            badge.hidden = price === undefined;
            if (price !== undefined) {
                document.getElementById('price-amount').innerHTML = money(price);
            }
        }

        document.querySelectorAll('.style-option').forEach(opt => {
            opt.addEventListener('click', () => {
                if (opt.dataset.style === currentStyle || opt.classList.contains('disabled')) return;
                document.querySelectorAll('.style-option').forEach(o => o.classList.remove('active'));
                opt.classList.add('active');
                currentStyle = opt.dataset.style;
                updateModel();
            });
        });

        // بيانات المقاسات لكل خيار "دور" — دور واحد شغال بالكامل (نماذج حقيقية)
        const sizesData = {
            1: [
                @foreach($variants as $v)
                {
                    label: "{{ $v->size_label }}",
                    sizeKey: "{{ $v->size_label }}in",
                    serves: "{{ $v->serves_label }}",
                    badge: {{ $v->is_default ? 'true' : 'false' }},
                },
                @endforeach
            ],
            2: [
                { label: "6/4",  sizeKey: "tier2_6-4",  serves: "6 أشخاص",  badge: false },
                { label: "8/6",  sizeKey: "tier2_8-6",  serves: "14 شخص",  badge: false },
                { label: "10/8", sizeKey: "tier2_10-8", serves: "23 شخص",  badge: false },
            ],
            3: [
                { label: "8/6/4",  sizeKey: "tier3_8-6-4",  serves: "15 شخص", badge: false },
                { label: "10/8/6", sizeKey: "tier3_10-8-6", serves: "28 شخص", badge: false },
            ],
        };

        function renderSizeCards(tier) {
            const container = document.getElementById('size-cards');
            container.innerHTML = '';
            sizesData[tier].forEach((s, i) => {
                const isActive = s.sizeKey === currentSizeKey;
                const card = document.createElement('div');
                card.className = 'size-card' + (isActive ? ' active' : '');
                card.dataset.sizeKey = s.sizeKey || '';
                card.dataset.size = s.label;
                card.dataset.serves = s.serves;

                const circleSize = 26 + i * 8;
                // سعر المقاس/الكومبو بأقل عدد طبقات
                const basePrice = prices[s.sizeKey]?.[(layerLimits[s.sizeKey] || [2])[0]];
                card.innerHTML = `
                    ${s.badge ? '<div class="badge">الأكثر طلبًا</div>' : ''}
                    <div class="check">✓</div>
                    <div class="circle" style="width:${circleSize}px;height:${circleSize}px;"></div>
                    <div class="num">${s.label} إنش</div>
                    <div class="serves">${s.serves}</div>
                    ${basePrice !== undefined ? `<div class="card-price">${money(basePrice)}</div>` : ''}
                `;

                card.addEventListener('click', () => {
                    container.querySelectorAll('.size-card').forEach(c => c.classList.remove('active'));
                    card.classList.add('active');

                    currentSizeKey = s.sizeKey;
                    renderLayerChips(currentSizeKey);
                    updateModel();
                    document.querySelectorAll('.spec-label')[2].textContent =
                        currentLayers + (currentLayers == 1 ? ' طبقة' : ' طبقات');

                    document.querySelectorAll('.spec-label')[1].textContent = s.label + ' إنش';
                    document.getElementById('serves-text').textContent = s.serves;
                });

                container.appendChild(card);
            });
        }

        document.querySelectorAll('#tier-switch .tier-option').forEach(opt => {
            opt.addEventListener('click', () => {
                if (opt.classList.contains('disabled')) return;
                document.querySelectorAll('#tier-switch .tier-option').forEach(o => o.classList.remove('active'));
                opt.classList.add('active');

                const tier = parseInt(opt.dataset.tier);

                // اختيار أول مقاس افتراضيًا من هذا الدور
                const firstOption = sizesData[tier][0];
                currentSizeKey = firstOption.sizeKey;

                renderSizeCards(tier);
                renderLayerChips(currentSizeKey);
                updateModel();

                const tierLabel = tier === 1 ? 'دور واحد' : (tier === 2 ? 'دورين' : '3 أدوار');
                document.querySelectorAll('.spec-label')[0].textContent = tierLabel;
                document.querySelectorAll('.spec-label')[1].textContent = firstOption.label + ' إنش';
                document.getElementById('serves-text').textContent = firstOption.serves;
                document.querySelectorAll('.spec-label')[2].textContent =
                    currentLayers + (currentLayers == 1 ? ' طبقة' : ' طبقات');
            });
        });

        // الشكل: القلب والمخروطي دور واحد فقط، والمخروطي ارتفاعه ثابت (بدون طبقات) وبدون فنتج
        const SHAPE_LABELS = { round: 'دائري', heart: 'قلب', cone: 'مخروطي' };
        const SHAPE_ICONS = {
            round: 'M12 4a8 8 0 1 0 0 16a8 8 0 1 0 0-16z',
            heart: 'M12 20.5s-7.5-4.6-7.5-10.1A4.3 4.3 0 0 1 12 7.6a4.3 4.3 0 0 1 7.5 2.8c0 5.5-7.5 10.1-7.5 10.1z',
            cone: 'M12 3.5c-.8 0-1.3.6-1.6 1.4L5.5 19.5h13L13.6 4.9c-.3-.8-.8-1.4-1.6-1.4z',
        };
        document.querySelectorAll('.shape-option').forEach(opt => {
            opt.addEventListener('click', () => {
                if (opt.dataset.shape === currentShape) return;
                document.querySelectorAll('.shape-option').forEach(o => o.classList.remove('active'));
                opt.classList.add('active');
                currentShape = opt.dataset.shape;
                document.getElementById('shape-label').textContent = SHAPE_LABELS[currentShape];
                document.getElementById('shape-icon').setAttribute('d', SHAPE_ICONS[currentShape]);

                const singleTierOnly = currentShape !== 'round';
                const tierOne = document.querySelector('#tier-switch .tier-option[data-tier="1"]');
                if (singleTierOnly && !tierOne.classList.contains('active')) tierOne.click();
                document.querySelectorAll('#tier-switch .tier-option:not([data-tier="1"])')
                    .forEach(o => o.classList.toggle('disabled', singleTierOnly));

                const isCone = currentShape === 'cone';
                document.querySelector('.spec-badge[data-target="panel-layers"]').style.display = isCone ? 'none' : '';
                document.querySelector('.style-option[data-style="vintage"]').classList.toggle('disabled', isCone);
                if (isCone) {
                    currentLayers = (layerLimits[currentSizeKey] || [2])[0];
                    renderLayerChips(currentSizeKey);
                    document.querySelectorAll('.spec-label')[2].textContent = currentLayers + ' طبقات';
                }

                updateModel();
            });
        });

        renderSizeCards(1);
        renderLayerChips(currentSizeKey);
        updateModel();
    </script>

</body>
</html>
