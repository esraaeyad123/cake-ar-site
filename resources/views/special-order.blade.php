<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>طلب كيكة خاصة - جوماكيك</title>

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
            position: sticky; top: 0; z-index: 20;
        }
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
        model-viewer { width: 100%; height: 42vh; min-height: 280px; background-color: #FFFFFF; }
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
        .people-card { top: 12px; left: 12px; }
        .people-card .amount { font-size: 15px; font-weight: 800; white-space: nowrap; }
        .float-card .caption { font-size: 10px; font-weight: 700; color: var(--pink-dark); }
        .riyal { width: 0.85em; height: 0.85em; vertical-align: -0.08em; margin-inline-start: 2px; }
        .style-toggle {
            position: absolute; bottom: 14px; right: 12px; display: flex; background: #fff;
            border: 1.5px solid var(--pink-line); border-radius: 20px; padding: 3px;
        }
        .style-toggle button {
            border: none; background: none; border-radius: 16px; padding: 6px 12px;
            font-size: 12px; font-weight: 800; color: var(--pink-dark); cursor: pointer;
        }
        .style-toggle button.active { background: var(--pink); color: #fff; }
        .style-toggle button:disabled { opacity: 0.35; cursor: not-allowed; }
        #ar-button-el {
            position: absolute; bottom: 14px; left: 12px; background: var(--pink); color: #fff; border: none;
            border-radius: 20px; padding: 9px 16px; font-size: 13px; font-weight: 800; cursor: pointer;
            display: flex; align-items: center; gap: 6px;
        }

        /* شريط التبويبات */
        .tabs {
            display: grid; grid-template-columns: repeat(5, 1fr); background: #fff;
            border-top: 1px solid #F3E3E9; border-bottom: 1px solid #F3E3E9;
            position: sticky; top: 55px; z-index: 10;
        }
        .tab {
            border: none; background: none; padding: 9px 2px 8px; cursor: pointer; color: var(--muted);
            font-size: 11px; font-weight: 700; line-height: 1.35; display: flex; flex-direction: column;
            align-items: center; gap: 4px; border-bottom: 3px solid transparent;
        }
        .tab .num {
            width: 22px; height: 22px; border-radius: 50%; background: #F5EEF1; color: var(--muted);
            display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;
        }
        .tab.done .num { background: var(--pink-soft); color: var(--pink); }
        .tab.active { color: var(--pink); border-bottom-color: var(--pink); }
        .tab.active .num { background: var(--pink); color: #fff; }

        main { max-width: 520px; margin: 0 auto; padding: 4px 16px 40px; }
        .panel { display: none; }
        .panel.active { display: block; }
        .section-title { font-size: 14px; font-weight: 800; margin: 18px 0 10px; }
        .section-title small { font-weight: 600; color: var(--muted); font-size: 12px; }
        .hint { font-size: 12px; color: var(--muted); margin-top: 6px; line-height: 1.6; }

        .options { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .options.two { grid-template-columns: repeat(2, 1fr); }
        .option {
            border: 1.5px solid #EFDDE4; background: #fff; border-radius: 14px; padding: 12px 6px;
            font-size: 13px; font-weight: 800; color: var(--ink); cursor: pointer; text-align: center;
            display: flex; flex-direction: column; align-items: center; gap: 4px;
        }
        .option small { font-size: 11px; font-weight: 600; color: var(--muted); }
        .option > svg { width: 30px; height: 30px; }
        #shape-options { grid-template-columns: repeat(5, 1fr); }
        #shape-options .option { padding: 12px 4px; }
        #shape-options .option > svg { width: 36px; height: 36px; }
        .option.active { border-color: var(--pink); background: var(--pink-soft); color: var(--pink-dark); }
        .option:disabled { opacity: 0.35; cursor: not-allowed; }

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
        .thumbs img { width: 58px; height: 58px; object-fit: cover; border-radius: 10px; border: 1px solid var(--pink-line); }
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
    </style>
</head>
<body>

    <header class="site-header">
        <a href="https://jomacake.com"><img src="{{ asset('images/icons/logo.png') }}" alt="JOMACAKE" class="logo"></a>
        <nav>
            <a href="https://jomacake.com">الرئيسية</a>
            <a href="https://jomacake.com/shop">المتجر</a>
            <a href="#" class="active">طلب خاص</a>
        </nav>
        <a href="https://jomacake.com/cart" class="cart" aria-label="السلة">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 5 3H2"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/></svg>
        </a>
    </header>
    <div class="sample-tag">عينة تجريبية — الطلب ما يُرسل لموقع جوماكيك</div>

    <div class="viewer-wrap">
        <model-viewer
            id="viewer"
            alt="كيكة خاصة"
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
            <button id="ar-button-el" slot="ar-button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 7.5v9L12 21l8-4.5v-9z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/></svg>
                AR
            </button>
        </model-viewer>
        <div class="viewer-status" id="viewer-status">جاري تجهيز الكيكة…</div>
        <div class="float-card price-card">
            <div class="amount" id="price-amount">—</div>
            <div class="caption">السعر المبدئي</div>
        </div>
        <div class="float-card people-card">
            <div class="amount" id="people-amount">—</div>
            <div class="caption">عدد الأشخاص</div>
        </div>
        <div class="style-toggle" id="style-toggle">
            <button type="button" data-style="vintage">فنتج</button>
            <button type="button" data-style="plain" class="active">سادة</button>
        </div>
    </div>

    <div class="tabs" id="tabs">
        <button type="button" class="tab active" data-tab="0"><span class="num">1</span>الشكل</button>
        <button type="button" class="tab" data-tab="1"><span class="num">2</span>الحجم<br>والطبقات</button>
        <button type="button" class="tab" data-tab="2"><span class="num">3</span>النكهة<br>والحشوة</button>
        <button type="button" class="tab" data-tab="3"><span class="num">4</span>الكتابة<br>والوصف</button>
        <button type="button" class="tab" data-tab="4"><span class="num">5</span>التاريخ<br>والتأكيد</button>
    </div>

    <main>
        {{-- 1) الشكل --}}
        <section class="panel active" data-panel="0">
            <div class="section-title">شكل الكيكة</div>
            <div class="options" id="shape-options">
                <button type="button" class="option active" data-shape="round" aria-label="دائري" title="دائري"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 7.5c0-1.7 3.1-3 7-3s7 1.3 7 3-3.1 3-7 3-7-1.3-7-3zM5 7.5v9c0 1.7 3.1 3 7 3s7-1.3 7-3v-9"/></svg></button>
                <button type="button" class="option" data-shape="heart" aria-label="قلب" title="قلب"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 20.5s-7.5-4.6-7.5-10.1A4.3 4.3 0 0 1 12 7.6a4.3 4.3 0 0 1 7.5 2.8c0 5.5-7.5 10.1-7.5 10.1z"/></svg></button>
                <button type="button" class="option" data-shape="cone" aria-label="مخروطي" title="مخروطي"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3.5c-.8 0-1.3.6-1.6 1.4L5.5 19.5h13L13.6 4.9c-.3-.8-.8-1.4-1.6-1.4z"/></svg></button>
                <button type="button" class="option" data-shape="sunset" aria-label="سن ست" title="سن ست"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M3 18.5h18M4.5 18.5a7.5 7.5 0 0 1 15 0"/></svg></button>
                <button type="button" class="option" data-shape="wall" aria-label="وال كيك" title="وال كيك"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"><path d="M4 19.5h16v-7H4zM7 12.5h10V7H7z"/></svg></button>
            </div>

            <div class="section-title">عدد الأدوار</div>
            <div class="options" id="tier-options">
                <button type="button" class="option active" data-tiers="1">دور واحد</button>
                <button type="button" class="option" data-tiers="2">دورين</button>
                <button type="button" class="option" data-tiers="3">3 أدوار</button>
            </div>
            <div class="hint" id="tier-hint" hidden></div>
        </section>

        {{-- 2) الحجم والطبقات --}}
        <section class="panel" data-panel="1">
            <div class="section-title">الحجم</div>
            <div class="options" id="size-options"></div>

            <div id="layers-block">
                <div class="section-title">عدد الطبقات <small>(لكل دور)</small></div>
                <div class="options" id="layer-options"></div>
            </div>
            <div class="hint" id="cone-hint" hidden>المخروطي بارتفاع ثابت.</div>
        </section>

        {{-- 3) النكهة والحشوة --}}
        <section class="panel" data-panel="2">
            <div class="section-title">النكهة <small>(لكل الكيكة)</small></div>
            <div class="item-cards" id="flavor-cards"></div>

            <div class="section-title">الحشوة <small>(لكل الكيكة)</small></div>
            <div class="item-cards" id="filling-cards"></div>
        </section>

        {{-- 4) الكتابة والوصف --}}
        <section class="panel" data-panel="3">
            <div class="ai-box">
                <h3>✨ شوفي كيكتك قبل الطلب <span class="badge">اختياري</span></h3>
                <div class="hint" style="margin-top:0">ارفعي صورة كيكة عاجبتك، والذكاء الاصطناعي يسوي لك صورتها بنفس المواصفات اللي اخترتيها (الشكل، الحجم، الأدوار، الطبقات).</div>

                <div class="section-title">صورة الكيكة</div>
                <label class="upload">
                    <input type="file" id="ai-reference" accept="image/png,image/jpeg,image/webp">
                    📷 اختاري صورة
                </label>
                <div class="thumbs" id="ai-thumb"></div>

                <div class="section-title">ملاحظة <small>(اختياري)</small></div>
                <textarea id="ai-note" maxlength="300" placeholder="مثال: غيّري لون الورد إلى أبيض"></textarea>

                <button type="button" class="btn" id="ai-generate" {{ $hasKey && $aiRemaining > 0 ? '' : 'disabled' }}>✨ ولّدي الصورة</button>
                <div class="hint" id="ai-remaining"></div>
                <div class="message {{ $hasKey ? 'info' : 'error' }}" id="ai-message">{{ $hasKey ? '' : 'لا يوجد مفتاح OpenAI. أضيفي OPENAI_API_KEY في ملف .env ثم شغّلي php artisan config:clear' }}</div>

                <div class="ai-result" id="ai-result">
                    <img id="ai-image" alt="صورة الكيكة بالذكاء الاصطناعي">
                    <div class="row">
                        <button type="button" class="btn" id="ai-approve">👍 اعتمدي هذا التصميم</button>
                        <button type="button" class="btn light" id="ai-reject">ما عجبني</button>
                    </div>
                    <div class="approved">✓ تم اعتماد التصميم، بيظهر في ملخص الطلب</div>
                </div>
            </div>

            <div class="section-title">صور التصميم <small>(اختياري)</small></div>
            <label class="upload">
                <input type="file" id="design-images" accept="image/png,image/jpeg,image/webp" multiple>
                🖼️ أضيفي صور للتصميم
            </label>
            <div class="thumbs" id="design-thumbs"></div>

            <div class="section-title">وصف التصميم</div>
            <textarea id="description" maxlength="1000" placeholder="اكتبي وصف الكيكة: الألوان، الزينة، الفكرة…"></textarea>
            <div class="voice">
                <button type="button" id="voice-btn">🎤 سجّلي وصف صوتي</button>
                <audio id="voice-audio" controls hidden></audio>
            </div>

            <div class="section-title">الكتابة على الكيكة <small>(اختياري)</small></div>
            <input type="text" id="cake-text" maxlength="40" placeholder="مثال: كل عام وأنتِ بخير">
        </section>

        {{-- 5) التاريخ والتأكيد --}}
        <section class="panel" data-panel="4">
            <div class="section-title">تاريخ الاستلام</div>
            <input type="date" id="delivery-date">
            <div class="hint">الطلب الخاص يحتاج يومين على الأقل.</div>

            <div class="summary" id="summary"></div>
            <div class="message error" id="submit-message"></div>
            <button type="button" class="btn" id="submit">إرسال للمراجعة</button>
        </section>

        <div class="nav-buttons">
            <button type="button" class="btn light" id="prev">السابق</button>
            <button type="button" class="btn" id="next">التالي</button>
        </div>
    </main>

    <div class="modal" id="done-modal">
        <div class="sheet">
            <h2>تم تجهيز طلبك ✓</h2>
            <div class="hint">هذه عينة تجريبية، الطلب ما انرسل لموقع جوماكيك. هذا الملخص اللي بيوصل للمراجعة:</div>
            <div class="summary" id="modal-summary"></div>
            <button type="button" class="btn" id="close-modal">تمام</button>
        </div>
    </div>

    <script type="module">
        import { composeCake } from "{{ asset('js/cake-composer.js') }}";

        const options = @json($options);
        const prices = @json((object) $prices);
        const layerLimits = @json($layerLimits);
        const tierPeople = @json($tierPeople);
        const aiUrl = "{{ route('special-order.ai') }}";
        const modelsBaseUrl = "{{ asset('storage/models') }}";
        const CAKE_COLOR = @json(config('cake_ar.cake_color'));
        const BOARD_COLORS = @json(config('cake_ar.board_colors'));
        const LAYER_HEIGHT = @json(config('cake_ar.layer_height'));
        const MAX_MODEL_LAYERS = 3;
        const RIYAL = '<svg class="riyal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-label="ريال"><path d="M8.5 2.5v14.5M14 4.5v15.5M3 11.5l18-4M3 16l18-4M14.5 20.5l6.5-1.5"/></svg>';
        const SHAPE_LABELS = { round: 'دائري', heart: 'قلب', cone: 'مخروطي', sunset: 'سن ست', wall: 'وال كيك' };
        // أشكال خاصة (السن ست، الوال كيك): مقاساتها وطبقاتها وأدوارها ثابتة، وسعرها من أسعار
        // الكعكة الخاصة لنفس المقاس مضروب في price_factor (الوال كيك نص السعر). السن ست سادة فقط
        const SHAPES = @json($shapes);
        const special = () => SHAPES[state.shape];
        const factor = () => special()?.price_factor ?? 1;
        const isPlainOnly = () => state.shape === 'cone' || (!!special() && !special().vintage);
        const TIER_LABELS = { 1: 'دور واحد', 2: 'دورين', 3: '3 أدوار' };
        const $ = id => document.getElementById(id);

        const sizesByTier = { 1: [], 2: [], 3: [] };
        Object.keys(layerLimits).forEach(key => {
            const tiers = key.startsWith('tier') ? Number(key[4]) : 1;
            sizesByTier[tiers].push(key);
        });

        const state = {
            shape: 'round', tiers: 1, size: sizesByTier[1][0], layers: 2, style: 'plain',
            flavor: options.flavors[0] || null, filling: options.fillings[0] || null,
            aiImage: null, aiApproved: false, aiRemaining: {{ $aiRemaining }},
            voice: null, tab: 0,
        };

        // "tier2_8-6" => 8 (الدور السفلي يحدد السعر)، "6in" => 6
        const bottomInch = key => key.startsWith('tier') ? key.split('_')[1].split('-')[0] : key.replace('in', '');
        const sizeLabel = key => key.startsWith('tier') ? key.split('_')[1].replaceAll('-', '/') + ' إنش' : bottomInch(key) + ' إنش';
        const people = key => key.startsWith('tier') ? tierPeople[key] : options.sizes[bottomInch(key)]?.people;
        const money = n => Math.round(n * 10) / 10 + RIYAL;
        const extra = item => item ? (item.extra[bottomInch(state.size)] || 0) * factor() : 0;

        const basePrice = (size, n) => prices[size]?.[n] === undefined ? undefined : prices[size][n] * factor();

        function total() {
            const base = basePrice(state.size, state.layers);
            return base === undefined ? undefined : base + extra(state.flavor) + extra(state.filling);
        }

        function selectIn(container, el) {
            container.querySelectorAll('.option, .item-card').forEach(o => o.classList.toggle('active', o === el));
        }

        function optionButton(html, active, onClick) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'option' + (active ? ' active' : '');
            b.innerHTML = html;
            b.addEventListener('click', () => { selectIn(b.parentElement, b); onClick(); });
            return b;
        }

        function renderSizes() {
            const box = $('size-options');
            const list = special() ? special().sizes.map(inch => inch + 'in') : sizesByTier[state.tiers];
            if (!list.includes(state.size)) state.size = list[0];
            box.innerHTML = '';
            list.forEach(key => box.appendChild(optionButton(
                `${sizeLabel(key)}<small>${people(key) || ''} أشخاص</small>`,
                key === state.size,
                () => { state.size = key; renderLayers(); renderItems(); refresh(); },
            )));
            renderLayers();
        }

        function renderLayers() {
            const isCone = state.shape === 'cone';
            $('layers-block').hidden = isCone;
            $('cone-hint').hidden = !isCone;
            const allowed = special() ? [special().layers] : layerLimits[state.size];
            if (isCone || !allowed.includes(state.layers)) state.layers = allowed[0];
            const box = $('layer-options');
            box.innerHTML = '';
            allowed.forEach(n => box.appendChild(optionButton(
                `${n} طبقات<small>${basePrice(state.size, n) !== undefined ? money(basePrice(state.size, n)) : ''}</small>`,
                n === state.layers,
                () => { state.layers = n; refresh(); },
            )));
        }

        function renderItems() {
            [['flavor-cards', 'flavors', 'flavor', '🍰'], ['filling-cards', 'fillings', 'filling', '🍓']].forEach(([boxId, list, key, icon]) => {
                const box = $(boxId);
                box.innerHTML = '';
                options[list].forEach(item => {
                    const card = document.createElement('button');
                    card.type = 'button';
                    card.className = 'item-card' + (state[key]?.id === item.id ? ' active' : '');
                    const add = extra(item);
                    card.innerHTML = `<div class="pic" ${item.image ? `style="background-image:url('${item.image}')"` : ''}>${item.image ? '' : icon}</div>${item.name}<span class="extra">${add ? '+' + money(add) : 'بدون زيادة'}</span>`;
                    card.addEventListener('click', () => { state[key] = item; selectIn(box, card); refresh(); });
                    box.appendChild(card);
                });
            });
        }

        function updateBadges() {
            const price = total();
            $('price-amount').innerHTML = price === undefined ? '—' : money(price);
            $('people-amount').textContent = people(state.size) || '—';
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
                    shape: state.shape,
                    style: isPlainOnly() ? 'plain' : state.style,
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
            const key = [state.shape, state.size, state.layers, state.style].join('|');
            if (key !== lastModelKey) { lastModelKey = key; modelReady = updateModel(); }
        }

        // ===== الشكل والأدوار =====
        document.querySelectorAll('#shape-options .option').forEach(btn => btn.addEventListener('click', () => {
            const wasSpecial = !!special();
            state.shape = btn.dataset.shape;
            selectIn(btn.parentElement, btn);
            // الدائري كل الأدوار، القلب والمخروطي دور واحد، والأشكال الخاصة حسب إعداداتها
            const tiers = state.shape === 'round' ? null : (special()?.tiers ?? 1);
            document.querySelectorAll('#tier-options .option').forEach(t => { t.disabled = tiers !== null && Number(t.dataset.tiers) !== tiers; });
            $('tier-hint').hidden = tiers === null;
            $('tier-hint').textContent = `${SHAPE_LABELS[state.shape]} ${TIER_LABELS[tiers] || ''} فقط.`;
            if (tiers !== null && state.tiers !== tiers) document.querySelector(`#tier-options [data-tiers="${tiers}"]`).click();
            // من شكل خاص (مثل الوال كيك دورين) للدائري نرجع لدور واحد
            if (tiers === null && wasSpecial && state.tiers !== 1) document.querySelector('#tier-options [data-tiers="1"]').click();
            // المخروطي والسن ست سادة فقط
            document.querySelector('#style-toggle [data-style="vintage"]').disabled = isPlainOnly();
            if (isPlainOnly()) document.querySelector('#style-toggle [data-style="plain"]').click();
            renderSizes();
            renderItems();
            refresh();
        }));
        document.querySelectorAll('#tier-options .option').forEach(btn => btn.addEventListener('click', () => {
            if (btn.disabled) return;
            state.tiers = Number(btn.dataset.tiers);
            selectIn(btn.parentElement, btn);
            renderSizes();
            renderItems();
            refresh();
        }));
        document.querySelectorAll('#style-toggle button').forEach(btn => btn.addEventListener('click', () => {
            if (btn.disabled) return;
            state.style = btn.dataset.style;
            document.querySelectorAll('#style-toggle button').forEach(b => b.classList.toggle('active', b === btn));
            refresh();
        }));

        // ===== التبويبات =====
        const tabs = [...document.querySelectorAll('.tab')];
        function goTo(i, scroll = true) {
            state.tab = i;
            tabs.forEach((t, n) => { t.classList.toggle('active', n === i); t.classList.toggle('done', n < i); });
            document.querySelectorAll('.panel').forEach(p => p.classList.toggle('active', Number(p.dataset.panel) === i));
            $('prev').style.visibility = i === 0 ? 'hidden' : 'visible';
            $('next').style.display = i === tabs.length - 1 ? 'none' : '';
            renderSummary($('summary'));
            if (scroll) window.scrollTo({ top: $('tabs').offsetTop - 55, behavior: 'smooth' });
        }
        tabs.forEach((t, i) => t.addEventListener('click', () => goTo(i)));
        $('prev').addEventListener('click', () => goTo(Math.max(0, state.tab - 1)));
        $('next').addEventListener('click', () => goTo(Math.min(tabs.length - 1, state.tab + 1)));

        // ===== الذكاء الاصطناعي =====
        const aiRef = $('ai-reference');
        function showAiMessage(text, type) { $('ai-message').textContent = text; $('ai-message').className = 'message ' + type; }
        function renderRemaining() {
            $('ai-remaining').textContent = state.aiRemaining > 0
                ? `باقي لك ${state.aiRemaining === 1 ? 'محاولة وحدة' : state.aiRemaining + ' محاولات'}.`
                : 'خلصت محاولات التوليد. كمّلي بالوصف والصور تحت.';
            if (state.aiRemaining <= 0) $('ai-generate').disabled = true;
        }
        aiRef.addEventListener('change', () => {
            $('ai-thumb').innerHTML = '';
            if (aiRef.files[0]) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(aiRef.files[0]);
                $('ai-thumb').appendChild(img);
            }
        });
        // صورة المجسم بالمواصفات المختارة من الأمام، تنرسل تلقائيًا مع صورة الزبون حتى ياخذ الذكاء الاصطناعي الشكل منها
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
                return blob;
            } catch (e) {
                console.error(e);
                return null;
            }
        }

        $('ai-generate').addEventListener('click', async () => {
            if (!aiRef.files[0]) { showAiMessage('اختاري صورة الكيكة أول.', 'error'); return; }
            const form = new FormData();
            form.append('shape', state.shape);
            form.append('size', state.size);
            form.append('layers', state.layers);
            form.append('style', isPlainOnly() ? 'plain' : state.style);
            form.append('note', $('ai-note').value.trim());
            form.append('reference', aiRef.files[0]);

            $('ai-generate').disabled = true;
            const shot = await snapshotModel();
            if (shot) form.append('shape_image', shot, 'shape.png');
            const started = Date.now();
            const tick = () => showAiMessage(`الذكاء الاصطناعي يجهّز صورة كيكتك… ⏳ ${Math.round((Date.now() - started) / 1000)} ثانية`, 'info');
            tick();
            const timer = setInterval(tick, 1000);
            try {
                const res = await fetch(aiUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: form,
                });
                const data = await res.json().catch(() => ({}));
                clearInterval(timer);
                if (data.remaining !== undefined) state.aiRemaining = data.remaining;
                if (!res.ok) {
                    const firstError = data.errors && Object.values(data.errors)[0]?.[0];
                    throw new Error(firstError || data.message || 'صار خطأ، جربي مرة ثانية.');
                }
                state.aiImage = data.image;
                state.aiApproved = false;
                $('ai-image').src = data.image;
                $('ai-result').className = 'ai-result show';
                showAiMessage('هذي كيكتك بالمواصفات اللي اخترتيها. عجبتك؟', 'info');
            } catch (e) {
                showAiMessage(e.message, 'error');
            } finally {
                clearInterval(timer);
                $('ai-generate').disabled = state.aiRemaining <= 0;
                renderRemaining();
                renderSummary($('summary'));
            }
        });
        $('ai-approve').addEventListener('click', () => {
            state.aiApproved = true;
            $('ai-result').classList.add('is-approved');
            showAiMessage('', 'info');
            renderSummary($('summary'));
        });
        $('ai-reject').addEventListener('click', () => {
            state.aiApproved = false;
            $('ai-result').className = 'ai-result';
            showAiMessage(state.aiRemaining > 0
                ? 'تقدري تغيّري الملاحظة وتولّدي مرة ثانية، أو تكمّلي بالوصف والصور تحت.'
                : 'كمّلي بالوصف والصور تحت.', 'info');
            renderSummary($('summary'));
        });

        // ===== الصور والوصف الصوتي =====
        const designInput = $('design-images');
        designInput.addEventListener('change', () => {
            $('design-thumbs').innerHTML = '';
            [...designInput.files].forEach(f => {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(f);
                $('design-thumbs').appendChild(img);
            });
            renderSummary($('summary'));
        });

        let recorder = null, chunks = [];
        $('voice-btn').addEventListener('click', async () => {
            if (recorder && recorder.state === 'recording') { recorder.stop(); return; }
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                recorder = new MediaRecorder(stream);
                chunks = [];
                recorder.ondataavailable = e => chunks.push(e.data);
                recorder.onstop = () => {
                    stream.getTracks().forEach(t => t.stop());
                    state.voice = URL.createObjectURL(new Blob(chunks, { type: recorder.mimeType }));
                    $('voice-audio').src = state.voice;
                    $('voice-audio').hidden = false;
                    $('voice-btn').classList.remove('recording');
                    $('voice-btn').textContent = '🎤 سجّلي من جديد';
                    renderSummary($('summary'));
                };
                recorder.start();
                $('voice-btn').classList.add('recording');
                $('voice-btn').textContent = '⏹ إيقاف التسجيل';
            } catch (e) {
                alert('ما قدرنا نوصل للمايك. اسمحي للمتصفح باستخدامه.');
            }
        });

        ['description', 'cake-text', 'delivery-date'].forEach(id => $(id).addEventListener('input', () => renderSummary($('summary'))));

        // أقرب تاريخ: بعد يومين
        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 2);
        $('delivery-date').min = minDate.toISOString().slice(0, 10);

        // ===== الملخص =====
        function escapeHtml(text) {
            return text.replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        }
        function renderSummary(box) {
            const rows = [
                ['الشكل', SHAPE_LABELS[state.shape]],
                ['الأدوار', TIER_LABELS[state.tiers]],
                ['الحجم', `${sizeLabel(state.size)} (${people(state.size) || '—'} أشخاص)`],
                ...(state.shape === 'cone' ? [] : [['الطبقات', `${state.layers} طبقات`]]),
                ['الستايل', !isPlainOnly() && state.style === 'vintage' ? 'فنتج' : 'سادة'],
                ['النكهة', state.flavor?.name || '—'],
                ['الحشوة', state.filling?.name || '—'],
                ['الكتابة على الكيكة', $('cake-text').value.trim() || '—'],
                ['الوصف', $('description').value.trim() || (state.voice ? 'وصف صوتي' : '—')],
                ['تاريخ الاستلام', $('delivery-date').value || '—'],
            ];
            const images = [];
            if (state.aiImage && state.aiApproved) images.push(`<figure><img src="${state.aiImage}" alt=""><div>تصميم الذكاء الاصطناعي</div></figure>`);
            [...designInput.files].forEach(f => images.push(`<figure><img src="${URL.createObjectURL(f)}" alt=""><div>صورة مرفقة</div></figure>`));
            const price = total();
            box.innerHTML = `<dl>${rows.map(([k, v]) => `<div class="line"><dt>${k}</dt><dd>${escapeHtml(String(v))}</dd></div>`).join('')}
                ${state.voice ? `<div class="line"><dt>وصف صوتي</dt><dd><audio controls src="${state.voice}" style="height:30px;max-width:180px"></audio></dd></div>` : ''}
                <div class="line total"><dt>السعر المبدئي</dt><dd>${price === undefined ? '—' : money(price)}</dd></div></dl>
                ${images.length ? `<div class="summary-images">${images.join('')}</div>` : ''}`;
        }

        $('submit').addEventListener('click', () => {
            const missing = [];
            if (!$('delivery-date').value) missing.push('تاريخ الاستلام');
            if (!$('description').value.trim() && !state.voice && !state.aiApproved && !designInput.files.length) missing.push('وصف التصميم أو صورة');
            if (missing.length) { $('submit-message').textContent = 'كمّلي: ' + missing.join('، '); return; }
            $('submit-message').textContent = '';
            renderSummary($('modal-summary'));
            $('done-modal').classList.add('show');
        });
        $('close-modal').addEventListener('click', () => $('done-modal').classList.remove('show'));

        renderSizes();
        renderItems();
        renderRemaining();
        goTo(0, false);
        refresh();
    </script>
</body>
</html>
