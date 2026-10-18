<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>صمّمي كيكتك بالذكاء الاصطناعي - جوماكيك</title>

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
            color: #4B1528;
        }
        header { padding: 18px 16px 4px; text-align: center; }
        .logo { max-width: 140px; }
        .sample-tag {
            display: inline-block;
            margin-top: 6px;
            font-size: 11px;
            font-weight: 700;
            color: #993556;
            background: #FBEAF0;
            border-radius: 8px;
            padding: 3px 10px;
        }

        .viewer-wrap { position: relative; }
        model-viewer { width: 100%; height: 44vh; background-color: #FFFFFF; }
        #ar-button-el {
            background: #D4537E; color: #fff; border: none; border-radius: 30px;
            padding: 12px 26px; font-size: 15px; font-weight: 700;
            position: absolute; bottom: 16px; left: 50%; transform: translateX(-50%); cursor: pointer;
        }
        .price-badge {
            position: absolute; top: 12px; right: 12px; background: #fff;
            border: 1.5px solid #F4C0D1; border-radius: 14px; padding: 8px 14px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08); text-align: center;
        }
        .price-badge .amount { font-size: 18px; font-weight: 800; white-space: nowrap; }
        .price-badge .caption { font-size: 11px; font-weight: 700; color: #993556; }
        .riyal { width: 0.85em; height: 0.85em; vertical-align: -0.08em; margin-inline-start: 2px; }
        .viewer-status {
            position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: #993556; pointer-events: none;
        }
        .viewer-status[hidden] { display: none; }

        main { max-width: 480px; margin: 0 auto; padding: 8px 16px 40px; }
        .section-title { font-size: 13px; font-weight: 700; margin: 18px 0 8px; }
        .chips { display: flex; gap: 8px; flex-wrap: wrap; }
        .chip {
            border: 1.5px solid #F4C0D1; background: #fff; border-radius: 12px;
            padding: 9px 16px; font-size: 14px; font-weight: 700; color: #993556; cursor: pointer;
            font-family: inherit;
        }
        .chip.active { background: #D4537E; border-color: #D4537E; color: #fff; }

        textarea {
            width: 100%; min-height: 96px; border: 1.5px solid #F4C0D1; border-radius: 12px;
            padding: 12px; font-size: 15px; font-family: inherit; resize: vertical; color: #4B1528;
        }
        textarea:focus { outline: 2px solid #D4537E; border-color: transparent; }
        .hint { font-size: 12px; color: #993556; margin-top: 6px; line-height: 1.6; }

        .upload {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            border: 1.5px dashed #ED93B1; border-radius: 12px; padding: 14px;
            font-size: 14px; font-weight: 700; color: #993556; cursor: pointer; background: #FFF8FA;
        }
        .upload input { display: none; }
        .thumbs { display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
        .thumbs img { width: 56px; height: 56px; object-fit: cover; border-radius: 10px; border: 1px solid #F4C0D1; }

        .generate {
            width: 100%; margin-top: 20px; background: #D4537E; color: #fff; border: none;
            border-radius: 30px; padding: 15px; font-size: 16px; font-weight: 800; cursor: pointer;
            font-family: inherit;
        }
        .generate:disabled { opacity: 0.55; cursor: wait; }
        .message { margin-top: 12px; font-size: 13px; font-weight: 700; line-height: 1.6; }
        .message.error { color: #B3261E; }
        .message.info { color: #993556; }

        .results { display: none; gap: 10px; margin-top: 14px; }
        .results.show { display: flex; }
        .results figure { margin: 0; flex: 1; text-align: center; }
        .results img { width: 100%; border-radius: 12px; border: 1px solid #F4C0D1; }
        .results figcaption { font-size: 11px; font-weight: 700; color: #993556; margin-top: 4px; }
    </style>
</head>
<body>

    <header>
        <img src="{{ asset('images/icons/logo.png') }}" alt="JOMACAKE" class="logo">
        <div><span class="sample-tag">عينة تجريبية — تصميم بالذكاء الاصطناعي</span></div>
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
            camera-orbit="0deg 62deg 105%"
            min-camera-orbit="auto 20deg auto"
            max-camera-orbit="auto 85deg auto"
            shadow-intensity="0.1"
            shadow-softness="1"
            exposure="1"
            environment-image="neutral"
        >
            <button id="ar-button-el" slot="ar-button">شاهدها على طاولتك</button>
        </model-viewer>
        <div class="viewer-status" id="viewer-status">جاري تجهيز الكيكة…</div>
        <div class="price-badge" id="price-badge" hidden>
            <div class="amount" id="price-amount"></div>
            <div class="caption">السعر المبدئي</div>
        </div>
    </div>

    <main>
        <div class="section-title">الشكل</div>
        <div class="chips" id="shape-chips">
            <button type="button" class="chip active" data-shape="round">دائري</button>
            <button type="button" class="chip" data-shape="heart">قلب</button>
        </div>

        <div class="section-title">الحجم</div>
        <div class="chips" id="size-chips">
            @foreach ($sizes as $key => $layers)
                <button type="button" class="chip {{ $loop->first ? 'active' : '' }}" data-size="{{ $key }}">{{ rtrim($key, 'in') }} إنش</button>
            @endforeach
        </div>

        <div class="section-title">عدد الطبقات</div>
        <div class="chips" id="layer-chips"></div>

        <div class="section-title">صفي التصميم اللي تبينه</div>
        <textarea id="description" maxlength="500" placeholder="مثال: ورد وردي ناعم على الأطراف، وفي النص كتابة «كل عام وأنتِ بخير سارة» بخط ذهبي"></textarea>
        <div class="hint">الذكاء الاصطناعي يصمم سطح الكيكة وجوانبها، وينطبع التصميم على الكيكة. الشخصيات المحمية بحقوق (ديزني، سانريو، أنمي…) ما يقبلها.</div>

        <div class="section-title">صور مرجعية (اختياري، لين 4)</div>
        <label class="upload">
            <input type="file" id="references" accept="image/png,image/jpeg,image/webp" multiple>
            📷 أضيفي صور (شخص، رسمة، ألوان…)
        </label>
        <div class="thumbs" id="thumbs"></div>

        <button type="button" class="generate" id="generate" {{ $hasKey ? '' : 'disabled' }}>✨ صمّمي الكيكة بالذكاء الاصطناعي</button>
        <div class="message {{ $hasKey ? 'info' : 'error' }}" id="message">
            {{ $hasKey ? '' : 'لا يوجد مفتاح OpenAI. أضيفي OPENAI_API_KEY في ملف .env ثم شغّلي php artisan config:clear' }}
        </div>

        <div class="results" id="results">
            <figure><img id="top-img" alt="تصميم السطح"><figcaption>تصميم السطح</figcaption></figure>
            <figure><img id="side-img" alt="تصميم الجوانب"><figcaption>تصميم الجوانب</figcaption></figure>
        </div>
    </main>

    <script type="module">
        import { composeCake } from "{{ asset('js/cake-composer.js') }}";

        const viewer = document.getElementById('viewer');
        const statusEl = document.getElementById('viewer-status');
        const messageEl = document.getElementById('message');
        const generateBtn = document.getElementById('generate');
        const modelsBaseUrl = "{{ asset('storage/models') }}";
        const designUrl = "{{ route('cake.ai.design', $cake->slug) }}";
        const layerLimits = @json($sizes);
        const prices = @json((object) $prices);
        const CAKE_COLOR = @json(config('cake_ar.cake_color'));
        const BOARD_COLORS = @json(config('cake_ar.board_colors'));
        const LAYER_HEIGHT = @json(config('cake_ar.layer_height'));
        const MAX_MODEL_LAYERS = 3;
        const RIYAL = '<svg class="riyal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-label="ريال"><path d="M8.5 2.5v14.5M14 4.5v15.5M3 11.5l18-4M3 16l18-4M14.5 20.5l6.5-1.5"/></svg>';

        const state = { shape: 'round', size: Object.keys(layerLimits)[0], layers: 2, top: null, side: null };

        function selectChip(container, chip) {
            container.querySelectorAll('.chip').forEach(c => c.classList.toggle('active', c === chip));
        }

        function renderLayerChips() {
            const container = document.getElementById('layer-chips');
            const allowed = layerLimits[state.size];
            if (!allowed.includes(state.layers)) state.layers = allowed[0];
            container.innerHTML = '';
            allowed.forEach(n => {
                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'chip' + (n === state.layers ? ' active' : '');
                chip.textContent = n + ' طبقات';
                chip.addEventListener('click', () => { state.layers = n; selectChip(container, chip); update(); });
                container.appendChild(chip);
            });
        }

        function updatePrice() {
            const price = prices[state.size]?.[state.layers];
            document.getElementById('price-badge').hidden = price === undefined;
            if (price !== undefined) document.getElementById('price-amount').innerHTML = Math.round(price) + RIYAL;
        }

        let token = 0, currentBlob = null;
        async function update() {
            updatePrice();
            const mine = ++token;
            const modelLayers = Math.min(state.layers, MAX_MODEL_LAYERS);
            const base = `${modelsBaseUrl}/cake_${state.size}_layers${modelLayers}_v45.glb`;
            statusEl.hidden = !!currentBlob;
            try {
                const url = await composeCake(base, {
                    color: CAKE_COLOR,
                    boardColor: BOARD_COLORS[state.size] || BOARD_COLORS.default,
                    shape: state.shape,
                    extraHeight: (state.layers - modelLayers) * LAYER_HEIGHT,
                    topImage: state.top,
                    sideImage: state.side,
                });
                if (mine !== token) { URL.revokeObjectURL(url); return; }
                if (currentBlob) URL.revokeObjectURL(currentBlob);
                currentBlob = url;
                viewer.src = url;
                statusEl.hidden = true;
            } catch (e) {
                console.error(e);
                if (mine === token) { statusEl.hidden = false; statusEl.textContent = 'تعذر تحميل الكيكة، حدّثي الصفحة'; }
            }
        }

        document.querySelectorAll('#shape-chips .chip').forEach(chip => chip.addEventListener('click', () => {
            state.shape = chip.dataset.shape;
            selectChip(chip.parentElement, chip);
            update();
        }));
        document.querySelectorAll('#size-chips .chip').forEach(chip => chip.addEventListener('click', () => {
            state.size = chip.dataset.size;
            selectChip(chip.parentElement, chip);
            renderLayerChips();
            update();
        }));

        const refsInput = document.getElementById('references');
        refsInput.addEventListener('change', () => {
            const files = [...refsInput.files].slice(0, 4);
            const thumbs = document.getElementById('thumbs');
            thumbs.innerHTML = '';
            files.forEach(f => {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(f);
                thumbs.appendChild(img);
            });
            if (refsInput.files.length > 4) showMessage('نستخدم أول 4 صور بس.', 'info');
        });

        function showMessage(text, type) {
            messageEl.textContent = text;
            messageEl.className = 'message ' + type;
        }

        generateBtn.addEventListener('click', async () => {
            const description = document.getElementById('description').value.trim();
            if (!description) { showMessage('اكتبي وصف التصميم أول.', 'error'); return; }

            const form = new FormData();
            form.append('shape', state.shape);
            form.append('description', description);
            [...refsInput.files].slice(0, 4).forEach(f => form.append('references[]', f));

            generateBtn.disabled = true;
            showMessage('الذكاء الاصطناعي يصمم السطح والجوانب… ممكن ياخذ دقيقة ⏳', 'info');
            try {
                const res = await fetch(designUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: form,
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    const firstError = data.errors && Object.values(data.errors)[0]?.[0];
                    throw new Error(firstError || data.message || 'صار خطأ، جربي مرة ثانية.');
                }
                state.top = data.top;
                state.side = data.side;
                document.getElementById('top-img').src = data.top;
                document.getElementById('side-img').src = data.side;
                document.getElementById('results').classList.add('show');
                showMessage('تم! لفّي الكيكة وشوفيها، أو اضغطي «شاهدها على طاولتك».', 'info');
                await update();
            } catch (e) {
                showMessage(e.message, 'error');
            } finally {
                generateBtn.disabled = false;
            }
        });

        renderLayerChips();
        update();
    </script>
</body>
</html>
