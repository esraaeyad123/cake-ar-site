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
            flex: 0 0 86px;
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
            font-size: 20px;
            font-weight: 700;
            color: #4B1528;
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

    <model-viewer
        id="viewer"
        src="{{ $defaultVariant->glb_url }}"
        @if($defaultVariant->usdz_url) ios-src="{{ $defaultVariant->usdz_url }}" @endif
        alt="{{ $cake->name }}"
        ar
        ar-modes="webxr scene-viewer quick-look"
        ar-scale="fixed"
        ar-placement="floor"
        camera-controls
        auto-rotate
        shadow-intensity="1"
        exposure="1"
        environment-image="neutral"
    >
        <button id="ar-button-el" slot="ar-button">شاهدها على طاولتك</button>
    </model-viewer>

    <div class="section-title">عدد الأدوار</div>
    <div class="tier-switch" id="tier-switch">
        <div class="tier-option active" data-tier="1">دور واحد</div>
        <div class="tier-option" data-tier="2">دورين</div>
        <div class="tier-option" data-tier="3">3 أدوار</div>
    </div>
    <div class="tier-note" id="tier-note" style="display:none;">
        * هذا الخيار قيد التجهيز حاليًا — العرض هنا للتصميم فقط
    </div>

    <div class="section-title">اختر الحجم</div>
    <div class="cards-row" id="size-cards">
        {{-- يتم تعبيتها بالجافاسكريبت حسب الدور المختار --}}
    </div>

    <div class="section-title">عدد طبقات الحشو الداخلي</div>
    <div class="layers-row" id="layers-row">
        @for($i = 1; $i <= 7; $i++)
            <div class="layer-chip {{ $i == 1 ? 'active' : '' }}" data-layers="{{ $i }}">
                <div class="check">✓</div>
                <div class="bars">
                    @for($b = 1; $b <= $i; $b++)
                        <div style="height: {{ max(2.5, 6 - $i * 0.4) }}px; background: {{ ['#F4C0D1','#ED93B1','#D4537E','#993556','#72243E','#4B1528','#2C1018'][$b-1] }};"></div>
                    @endfor
                </div>
                <div class="lnum">{{ $i }}</div>
            </div>
        @endfor
    </div>

    <x-cake-spec-badges
        :tier-count="1"
        :size-label="$defaultVariant->size_label"
        :layers-count="1"
        :serves-label="$defaultVariant->serves_label"
        id="spec-badges-el"
    />

    <script>
        const viewer = document.getElementById('viewer');

        // بيانات المقاسات لكل خيار "دور" — دور واحد شغال بالكامل (نماذج حقيقية)
        // دورين و3 أدوار حاليًا للعرض التصميمي فقط، بانتظار النماذج من المصمم
        const sizesData = {
            1: [
                @foreach($variants as $v)
                {
                    label: "{{ $v->size_label }}",
                    serves: "{{ $v->serves_label }}",
                    glb: "{{ $v->glb_url }}",
                    usdz: "{{ $v->usdz_url }}",
                    badge: {{ $v->is_default ? 'true' : 'false' }},
                    active: {{ $v->is_default ? 'false' : 'false' }},
                },
                @endforeach
            ],
            2: [
                { label: "10/8", serves: "قيد التحديد", glb: null, usdz: null, badge: false },
                { label: "8/6",  serves: "قيد التحديد", glb: null, usdz: null, badge: false },
                { label: "6/4",  serves: "قيد التحديد", glb: null, usdz: null, badge: false },
            ],
            3: [
                { label: "10/8/6", serves: "قيد التحديد", glb: null, usdz: null, badge: false },
                { label: "8/6/4",  serves: "قيد التحديد", glb: null, usdz: null, badge: false },
            ],
        };

        // تعيين المقاس "4" كنشط افتراضيًا لخيار دور واحد
        sizesData[1].forEach(s => s.active = (s.label === "4"));

        function renderSizeCards(tier) {
            const container = document.getElementById('size-cards');
            container.innerHTML = '';
            sizesData[tier].forEach((s, i) => {
                const card = document.createElement('div');
                card.className = 'size-card' + (s.active ? ' active' : '');
                card.dataset.glb = s.glb || '';
                card.dataset.usdz = s.usdz || '';
                card.dataset.size = s.label;
                card.dataset.serves = s.serves;

                const circleSize = 26 + i * 8;
                card.innerHTML = `
                    ${s.badge ? '<div class="badge">الأكثر طلبًا</div>' : ''}
                    <div class="check">✓</div>
                    <div class="circle" style="width:${circleSize}px;height:${circleSize}px;"></div>
                    <div class="num">${s.label}"</div>
                    <div class="serves">${s.serves}</div>
                `;

                card.addEventListener('click', () => {
                    container.querySelectorAll('.size-card').forEach(c => c.classList.remove('active'));
                    card.classList.add('active');

                    if (s.glb) {
                        viewer.src = s.glb;
                        if (s.usdz) viewer.iosSrc = s.usdz;
                    }

                    document.querySelectorAll('.spec-label')[1].textContent = s.label + ' إنش';
                    document.querySelectorAll('.spec-label')[3].textContent = s.serves;
                });

                container.appendChild(card);
            });
        }

        document.querySelectorAll('.tier-option').forEach(opt => {
            opt.addEventListener('click', () => {
                document.querySelectorAll('.tier-option').forEach(o => o.classList.remove('active'));
                opt.classList.add('active');

                const tier = parseInt(opt.dataset.tier);
                renderSizeCards(tier);

                const note = document.getElementById('tier-note');
                note.style.display = tier === 1 ? 'none' : 'block';

                const tierLabel = tier === 1 ? 'دور واحد' : (tier === 2 ? 'دورين' : '3 أدوار');
                document.querySelectorAll('.spec-label')[0].textContent = tierLabel;
            });
        });

        document.querySelectorAll('.layer-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                document.querySelectorAll('.layer-chip').forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                const val = chip.dataset.layers;
                document.querySelectorAll('.spec-label')[2].textContent = val + (val == 1 ? ' طبقة' : ' طبقات');
            });
        });

        renderSizeCards(1);
    </script>

</body>
</html>
