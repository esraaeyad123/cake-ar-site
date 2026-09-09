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
        .logo { max-width: 160px; margin: 0 auto 4px; display: block; }
        .tagline { color: #D4537E; font-size: 11px; letter-spacing: 2px; margin: 0 0 12px; }

        model-viewer {
            width: 100%;
            height: 50vh;
            background-color: #F1EFE8;
        }

        .size-selector {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            padding: 16px;
            max-width: 480px;
            margin: 0 auto;
        }
        .size-card {
            background: #fff;
            border: 1.5px solid #F4C0D1;
            border-radius: 12px;
            padding: 10px 4px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.15s;
        }
        .size-card.active {
            border-color: #D4537E;
            border-width: 2px;
        }
        .size-card .size-num {
            font-size: 18px;
            font-weight: 600;
            color: #4B1528;
        }
        .size-card .size-serves {
            font-size: 11px;
            color: #993556;
        }

        .layers-selector {
            padding: 4px 16px 8px;
            max-width: 480px;
            margin: 0 auto;
        }
        .layers-selector label {
            font-size: 13px;
            color: #4B1528;
            display: block;
            margin-bottom: 6px;
        }
        .layers-selector select {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            border: 1.5px solid #F4C0D1;
            font-size: 14px;
            color: #4B1528;
            background: #fff;
        }

        #ar-button-el {
            background: #D4537E;
            color: #fff;
            border: none;
            border-radius: 30px;
            padding: 12px 24px;
            font-size: 15px;
            position: absolute;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            cursor: pointer;
        }
    </style>
</head>
<body>

    <header>
        <img src="{{ asset('images/icons/logo.png') }}" alt="JOMACAKE" class="logo">
        <p class="tagline">MADE WITH LOVE</p>
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

    <div class="size-selector" id="size-selector">
        @foreach($variants as $variant)
            <div class="size-card {{ $variant->is_default ? 'active' : '' }}"
                 data-glb="{{ $variant->glb_url }}"
                 data-usdz="{{ $variant->usdz_url }}"
                 data-serves="{{ $variant->serves_label }}"
                 data-size="{{ $variant->size_label }}">
                <div class="size-num">{{ $variant->size_label }}"</div>
                <div class="size-serves">{{ $variant->serves_label }}</div>
            </div>
        @endforeach
    </div>

    <div class="layers-selector">
        <label for="layers-select">عدد طبقات الحشو الداخلي</label>
        <select id="layers-select">
            @foreach(range(1, 7) as $n)
                <option value="{{ $n }}">{{ $n }} {{ $n == 1 ? 'طبقة' : 'طبقات' }}</option>
            @endforeach
        </select>
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
        const cards = document.querySelectorAll('.size-card');

        cards.forEach(card => {
            card.addEventListener('click', () => {
                cards.forEach(c => c.classList.remove('active'));
                card.classList.add('active');

                viewer.src = card.dataset.glb;
                if (card.dataset.usdz) {
                    viewer.iosSrc = card.dataset.usdz;
                }

                document.querySelectorAll('.spec-label')[1].textContent = card.dataset.size + ' إنش';
                document.querySelectorAll('.spec-label')[3].textContent = card.dataset.serves;
            });
        });

        document.getElementById('layers-select').addEventListener('change', (e) => {
            const val = e.target.value;
            document.querySelectorAll('.spec-label')[2].textContent = val + (val == 1 ? ' طبقة' : ' طبقات');
        });
    </script>

</body>
</html>
