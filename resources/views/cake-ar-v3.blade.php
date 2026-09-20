<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $cake->name }} - جوماكيك</title>

    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>

    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, 'Segoe UI', Tahoma, sans-serif;
            background: #fffafb;
            text-align: center;
        }

        header { padding: 20px 16px 4px; }
        .logo { max-width: 150px; margin: 0 auto; display: block; }

        model-viewer {
            width: 100%;
            height: 46vh;
            background-color: #F1EFE8;
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
            flex: 0 0 48px;
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
            src="{{ asset('storage/models/cake_4in_layers2_v35.glb') }}"
            ios-src="{{ asset('storage/models/cake_4in_layers2_v35.usdz') }}"
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
            shadow-intensity="0.2"
            shadow-softness="1"
            exposure="1"
            environment-image="neutral"
        >
            <button id="ar-button-el" slot="ar-button">شاهدها على طاولتك</button>
        </model-viewer>

        <div class="serves-badge" id="serves-badge">
            <x-mdi-account-group class="serves-icon" />
            <span id="serves-text">{{ $defaultVariant->serves_label }}</span>
        </div>
    </div>

    <x-cake-spec-badges
        :tier-count="1"
        :size-label="$defaultVariant->size_label"
        :layers-count="2"
        id="spec-badges-el"
    />

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
        <div class="section-title">عدد طبقات الحشو الداخلي</div>
        <div class="layers-row" id="layers-row">
            {{-- يتم تعبيتها بالجافاسكريبت حسب الحجم المختار (لكل مقاس مدى مختلف مسموح) --}}
        </div>
    </div>

    <script>
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

        const viewer = document.getElementById('viewer');
        const modelsBaseUrl = "{{ asset('storage/models') }}";
        const MODEL_VERSION = "v35"; // غيّر هذا الرقم كل مرة تحدّث ملفات النماذج — جزء من اسم الملف نفسه

        let currentSizeKey = "4in";  // مطابق لاسم الملف: 4in/6in/8in/10in
        let currentLayers = 2;

        // المدى المسموح بعدد الطبقات لكل مقاس/كومبو — حاليًا نكتفي بـ 2-3 لكل الخيارات
        const layerLimits = {
            "4in":  [2, 3],
            "6in":  [2, 3],
            "8in":  [2, 3],
            "10in": [2, 3],
            "tier2_6-4":     [2, 3],
            "tier2_8-6":     [2, 3],
            "tier2_10-8":    [2, 3],
            "tier3_8-6-4":   [2, 3],
            "tier3_10-8-6":  [2, 3],
        };

        const layerColors = ['#F4C0D1','#ED93B1','#D4537E','#993556','#72243E','#4B1528','#2C1018'];

        function renderLayerChips(sizeKey) {
            const container = document.getElementById('layers-row');
            container.innerHTML = '';
            const allowed = layerLimits[sizeKey] || [1,2,3,4,5,6,7];

            if (!allowed.includes(currentLayers)) {
                currentLayers = allowed[0];
            }

            allowed.forEach(n => {
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

        function updateModel() {
            const path = `${modelsBaseUrl}/cake_${currentSizeKey}_layers${currentLayers}_${MODEL_VERSION}`;
            viewer.src = path + '.glb';
            viewer.iosSrc = path + '.usdz';
        }

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
                card.innerHTML = `
                    ${s.badge ? '<div class="badge">الأكثر طلبًا</div>' : ''}
                    <div class="check">✓</div>
                    <div class="circle" style="width:${circleSize}px;height:${circleSize}px;"></div>
                    <div class="num">${s.label} إنش</div>
                    <div class="serves">${s.serves}</div>
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

        document.querySelectorAll('.tier-option').forEach(opt => {
            opt.addEventListener('click', () => {
                document.querySelectorAll('.tier-option').forEach(o => o.classList.remove('active'));
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

        renderSizeCards(1);
        renderLayerChips(currentSizeKey);
        updateModel();
    </script>

</body>
</html>
