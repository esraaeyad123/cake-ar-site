@extends('shop.layout')

@section('title', $product['name'] ?? 'S.N: '.$product['id'])

@section('styles')
        /* نفس ترتيب صفحة المنتج في جوماكيك: الصورة على جنب والطلب على الجنب الثاني (وفي الجوال الصورة فوق) */
        .page { max-width: 1100px; margin: 0 auto; padding: 14px 16px 50px; }
        .crumbs { font-size: 13px; color: var(--muted); margin: 4px 0 14px; display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
        .crumbs a { text-decoration: none; }
        .crumbs b { color: var(--pink); }
        .layout { display: grid; grid-template-columns: 1fr; gap: 18px; }
        @media (min-width: 860px) {
            .layout { grid-template-columns: 1fr 1fr; gap: 32px; align-items: start; }
            .photo { position: sticky; top: 14px; }
        }
        .photo img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 14px; display: block; background: #FAF3F5; }
        .photo .switch { display: flex; gap: 6px; justify-content: center; margin-top: 8px; }
        .photo .switch[hidden] { display: none; }
        .photo .switch button { border: 1.5px solid #EFDDE4; background: #fff; border-radius: 16px; padding: 5px 12px; font-size: 12px; font-weight: 800; color: var(--ink); cursor: pointer; }
        .photo .switch button.active { border-color: var(--pink); background: var(--pink-soft); }

        h1 { font-size: 24px; margin: 0 0 6px; }
        .meta { font-size: 15px; color: var(--pink-dark); margin: 6px 0; }
        .meta b { color: var(--ink); font-weight: 700; }
        .price-line { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin: 4px 0 2px; }
        .price-line .amount { font-size: 26px; font-weight: 800; white-space: nowrap; }
        .price-line .old { font-size: 14px; }
        .photo .specs { margin-top: 10px; background: #FAF5F7; border-radius: 14px; padding: 10px 12px; }
        .photo .spec { font-size: 12px; }

        .section-title { font-size: 16px; font-weight: 800; color: var(--pink-dark); margin: 20px 0 10px; }
        .section-title small { font-weight: 600; color: var(--muted); font-size: 12px; }
        .hint { font-size: 12px; color: var(--muted); margin-top: 6px; line-height: 1.6; }

        .row { display: flex; gap: 8px; overflow-x: auto; padding: 2px 2px 6px; scrollbar-width: none; }
        .row::-webkit-scrollbar { display: none; }
        .opt {
            flex: 0 0 auto; min-width: 82px; border: 1.5px solid #EFDDE4; background: #fff; border-radius: 14px;
            padding: 9px 10px 8px; cursor: pointer; text-align: center; color: var(--ink);
            display: flex; flex-direction: column; align-items: center; gap: 3px;
        }
        .opt .num { font-size: 14px; font-weight: 800; }
        .opt small { font-size: 11px; font-weight: 700; color: var(--muted); }
        .opt .price { font-size: 11px; font-weight: 800; color: var(--pink-dark); }
        .opt .badge { font-size: 10px; padding: 1px 6px; }
        .opt.active { border-color: var(--pink); background: var(--pink-soft); }

        .item-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .item-card {
            border: 1.5px solid #EFDDE4; background: #fff; border-radius: 14px; padding: 6px 6px 9px;
            cursor: pointer; text-align: center; font-size: 12px; font-weight: 800; color: var(--ink);
        }
        .item-card .pic { width: 100%; aspect-ratio: 1; border-radius: 10px; background: #FAF3F5 center / cover no-repeat; margin-bottom: 6px; }
        .item-card .extra { display: block; font-size: 11px; font-weight: 700; color: var(--pink-dark); margin-top: 3px; }
        .item-card.active { border-color: var(--pink); background: var(--pink-soft); }
        .chips { display: flex; flex-wrap: wrap; gap: 8px; }
        .chip { border: 1.5px solid #EFDDE4; background: #fff; border-radius: 20px; padding: 8px 12px; cursor: pointer; font-size: 12px; font-weight: 800; color: var(--ink); }
        .chip.active { border-color: var(--pink); background: var(--pink-soft); }

        input[type=text], input[type=date], textarea {
            width: 100%; border: 1.5px solid #EFDDE4; border-radius: 12px; padding: 12px; font-size: 14px; color: var(--ink); background: #fff; font-family: inherit;
        }
        textarea { min-height: 80px; resize: vertical; }
        input:focus, textarea:focus { outline: none; border-color: var(--pink); }
        .counter { font-size: 11px; color: var(--muted); text-align: left; margin-top: 4px; }

        /* تعديل الكيكة بالذكاء الاصطناعي */
        .ai-box { border: 1.5px solid var(--pink-line); border-radius: 16px; padding: 14px; margin-top: 22px; background: linear-gradient(180deg, #FFF8FA, #fff); }
        .ai-box h3 { margin: 0 0 4px; font-size: 15px; }
        .ai-box .upload { display: inline-flex; align-items: center; gap: 6px; border: 1.5px dashed var(--pink-line); border-radius: 12px; padding: 9px 12px; font-size: 12px; font-weight: 800; color: var(--pink-dark); cursor: pointer; background: #fff; margin-top: 8px; }
        .ai-box .upload input { display: none; }
        .thumbs { display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
        .thumbs .t { position: relative; }
        .thumbs img { width: 58px; height: 58px; object-fit: cover; border-radius: 10px; border: 1px solid var(--pink-line); display: block; cursor: pointer; }
        .thumbs .t button { position: absolute; top: -6px; left: -6px; width: 20px; height: 20px; border-radius: 50%; border: none; background: var(--ink); color: #fff; font-size: 12px; line-height: 20px; padding: 0; cursor: pointer; }
        .thumbs img.current { outline: 3px solid var(--pink); outline-offset: 1px; }
        .ai-result { display: none; margin-top: 12px; }
        .ai-result.show { display: block; }
        .ai-result img { width: 100%; border-radius: 14px; border: 1px solid var(--pink-line); display: block; }
        .ai-result .actions { display: flex; gap: 8px; margin-top: 8px; }
        .ai-result .actions > * { flex: 1; margin: 0; }
        .ai-result .approved { display: none; text-align: center; font-size: 13px; font-weight: 800; color: #2E7D32; margin-top: 8px; }
        .ai-result.is-approved .approved { display: block; }
        .ai-result.is-approved .actions .choose { display: none; }
        .download { display: block; text-align: center; text-decoration: none; border: 1.5px solid var(--pink-line); border-radius: 14px; padding: 11px; font-size: 13px; font-weight: 800; color: var(--pink-dark); margin-top: 8px; }

        .summary { border: 1.5px solid #EFDDE4; border-radius: 16px; padding: 4px 14px; margin-top: 16px; }
        .summary .line { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-bottom: 1px solid #F5EBEF; font-size: 13px; }
        .summary .line:last-child { border-bottom: none; }
        .summary dl { margin: 0; }
        .summary dt { color: var(--muted); font-weight: 700; }
        .summary dd { margin: 0; font-weight: 800; text-align: left; }
        .summary .total dd { font-size: 17px; color: var(--pink); }
        .summary .design img { width: 64px; height: 64px; object-fit: cover; border-radius: 10px; border: 1px solid var(--pink-line); }
        .message { font-size: 12px; font-weight: 700; margin-top: 10px; min-height: 1em; color: var(--pink-dark); }
        .message.error { color: #B42318; }
        .btn {
            width: 100%; border: none; border-radius: 14px; padding: 14px; font-size: 15px; font-weight: 800;
            background: var(--pink); color: #fff; cursor: pointer; margin-top: 12px;
        }
        .btn.light { background: var(--pink-soft); color: var(--pink-dark); }
        .btn:disabled { opacity: .5; cursor: not-allowed; }

        .modal { position: fixed; inset: 0; background: rgba(75,21,40,0.45); display: none; align-items: flex-end; justify-content: center; z-index: 50; }
        .modal.show { display: flex; }
        .modal .sheet { background: #fff; width: 100%; max-width: 520px; max-height: 88vh; overflow: auto; border-radius: 20px 20px 0 0; padding: 18px 16px 26px; }
        .modal h2 { margin: 0 0 4px; font-size: 18px; }
        .modal.center { align-items: center; padding: 16px; }
        .modal.center .sheet { border-radius: 20px; text-align: center; padding: 26px 18px 18px; }
        .modal .thumb { width: 90px; height: 90px; object-fit: cover; border-radius: 12px; border: 1px solid var(--pink-line); display: block; margin: 10px auto 0; }
        .wait-icon { font-size: 40px; animation: tilt 1.6s ease-in-out infinite; display: inline-block; }
        @keyframes tilt { 50% { transform: rotate(180deg); } }
        .wait-time { font-size: 26px; font-weight: 800; color: var(--pink); margin: 8px 0 2px; }
@endsection

@section('top')
    @include('shop.partials.header')
@endsection

@section('content')
    @php
        $first = $product['sizes'][0];
        $title = $product['name'] ?? 'كيكة جاهزة';
    @endphp
    <div class="page">
        <div class="crumbs">
            <a href="https://jomacake.com">الرئيسية</a> ›
            <a href="{{ route('shop.index') }}">المتجر</a> ›
            <b>{{ $product['name'] ?? $product['id'] }}</b>
        </div>

        <div class="layout">
            <div class="photo">
                <img id="main-photo" src="{{ $product['image'] }}" alt="{{ $title }}">
                <div class="switch" id="photo-switch" hidden>
                    <button type="button" class="active" data-photo="original">الصورة الأصلية</button>
                    <button type="button" data-photo="ai">✨ تصميمك</button>
                </div>
                @include('shop.partials.specs', ['size' => $first, 'id' => 'specs'])
            </div>

            <div class="info">
                @if ($product['name'])
                    <h1>{{ $product['name'] }}</h1>
                @endif
                <div class="meta">الرقم التسلسلي: <b>{{ $product['id'] }}</b></div>
                <div class="meta">المقاس: <span id="size-label">{{ $first['inch'] }} إنش</span></div>
                <div class="meta">السعر:</div>
                <div class="price-line">
                    <span class="amount"><span id="price">{{ \App\Services\ShopProducts::finalPrice($first['price'], $first['discount']) }}</span>@include('shop.partials.riyal')</span>
                    <span class="old" id="old-price"></span>
                    <span class="badge" id="badge" hidden></span>
                    <span class="tax">(شامل الضريبة)</span>
                </div>

                <div class="section-title">اكتب على الكعكة @unless ($product['requires_text'])<small>(اختياري)</small>@endunless</div>
                <input type="text" id="writing" maxlength="40" placeholder="مثال: عيد ميلاد سعيد">
                <div class="counter"><span id="writing-count">0</span>/40</div>

                @if ($product['flavors'])
                    <div class="section-title">النكهة</div>
                    <div class="item-cards" id="flavors"></div>
                @endif

                @if ($product['stuffings'])
                    <div class="section-title">الحشوات</div>
                    <div class="chips" id="stuffings"></div>
                @endif

                <div class="section-title">الحجم</div>
                <div class="row" id="sizes"></div>

                <div class="section-title">الطبقة</div>
                <div class="row" id="layers"></div>

                <div class="ai-box">
                    <h3>✨ تبين تغيّرين شي في الكيكة؟</h3>
                    <div class="hint">اكتبي التعديل (لون، كتابة، إضافة…)، وارفعي صور للي تبينه إذا عندك، والذكاء الاصطناعي يوريك الكيكة بعد التعديل.</div>
                    <textarea id="ai-note" maxlength="500" placeholder="مثال: خلي اللون وردي فاتح واكتبي Happy Birthday Sara"></textarea>
                    <div class="counter"><span id="ai-note-count">0</span>/500</div>
                    <label class="upload">📷 صور للتعديل (اختياري، لين 6)
                        <input type="file" id="ai-reference" accept="image/png,image/jpeg,image/webp" multiple>
                    </label>
                    <div class="thumbs" id="ai-thumbs"></div>
                    <button type="button" class="btn" id="ai-generate" {{ $hasKey && $aiRemaining !== 0 ? '' : 'disabled' }}>✨ ورّيني التعديل</button>
                    <div class="hint" id="ai-remaining"></div>
                    <div class="message {{ $hasKey ? '' : 'error' }}" id="ai-message">{{ $hasKey ? '' : 'لا يوجد مفتاح OpenAI. أضيفي OPENAI_API_KEY في ملف .env ثم شغّلي php artisan config:clear' }}</div>

                    <div class="ai-result" id="ai-result">
                        <img id="ai-image" alt="الكيكة بعد التعديل">
                        <div class="actions">
                            <button type="button" class="btn choose" id="ai-approve">👍 اعتمدي التعديل</button>
                            <button type="button" class="btn light choose" id="ai-reject">ما عجبني</button>
                        </div>
                        <div class="approved">✓ تم اعتماد التعديل، بيظهر في طلبك</div>
                        <a class="download" id="ai-download" href="#" download>⬇️ تحميل الصورة</a>
                    </div>
                    <div id="ai-history" hidden>
                        <div class="hint">صورك اللي طلعت (اضغطي على صورة عشان تختارينها):</div>
                        <div class="thumbs" id="ai-history-thumbs"></div>
                    </div>
                </div>

                <div class="message error" id="message"></div>
                <button type="button" class="btn" id="review">الطلب</button>
            </div>
        </div>
    </div>

    <div class="modal center" id="ai-wait-modal" role="dialog" aria-live="polite">
        <div class="sheet">
            <div class="wait-icon">⏳</div>
            <h2>الرجاء الانتظار، جاري التعديل</h2>
            <div class="wait-time" id="ai-wait-time">0 ثانية</div>
            <div class="hint">الذكاء الاصطناعي يجهّز صورة كيكتك، ممكن ياخذ دقيقة أو أكثر.</div>
            <button type="button" class="btn light" id="ai-cancel">إلغاء</button>
        </div>
    </div>

    <div class="modal" id="done-modal">
        <div class="sheet">
            <h2>تم تجهيز طلبك ✓</h2>
            <div class="hint">هذه عينة تجريبية، الطلب ما انرسل لموقع جوماكيك. هذا الملخص اللي بيوصل:</div>
            <img class="thumb" id="modal-photo" src="{{ $product['image'] }}" alt="">
            <div class="summary" id="modal-summary"></div>
            <button type="button" class="btn" id="close-modal">تمام</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const product = @json($product);
        const RIYAL = `@include('shop.partials.riyal')`;
        const CSRF = '{{ csrf_token() }}';
        const AI_URL = '{{ route('shop.ai', $product['id']) }}';
        const AI_STATUS_URL = '{{ url('/products/ai') }}/';
        const HAS_KEY = @json($hasKey);
        const MAX_REFERENCES = 6;
        const $ = id => document.getElementById(id);
        const esc = t => String(t).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
        const state = {
            size: product.sizes[0], layers: product.sizes[0].min_layers, flavor: product.flavors[0] ?? null, stuffing: null,
            aiRemaining: @json($aiRemaining), aiImage: null, aiApproved: false,
        };

        // نفس حساب الموقع: الخصم على المجموع، والكسر ينقص (355 بخصم 25% = 266)
        function discounted(price, discount) {
            if (discount) price = discount.type === 'percent' ? price * (1 - discount.amount / 100) : price - discount.amount;
            return Math.floor(Math.max(0, price) + 1e-9);
        }
        const discountLabel = d => d ? (d.type === 'percent' ? `خصم ${d.amount}%` : `خصم ${d.amount}`) : '';
        const layerExtra = (size, n) => (n - size.min_layers) * size.layer_price;

        function totals() {
            const before = state.size.price + layerExtra(state.size, state.layers) + (state.flavor?.extra ?? 0);
            return { before, final: discounted(before, state.size.discount) };
        }

        function bind(container, selector, onPick) {
            container.querySelectorAll(selector).forEach(b => b.addEventListener('click', () => { onPick(b.dataset.i); render(); }));
        }

        function renderOptions() {
            $('sizes').innerHTML = product.sizes.map((s, i) => `
                <button type="button" class="opt ${s === state.size ? 'active' : ''}" data-i="${i}">
                    <span class="num">${s.inch} إنش</span>
                    <small>${s.people} أشخاص</small>
                    <span class="price">${discounted(s.price, s.discount)}${RIYAL}</span>
                    ${s.discount ? `<span class="badge">${discountLabel(s.discount)}</span>` : ''}
                </button>`).join('');
            bind($('sizes'), '.opt', i => {
                state.size = product.sizes[i];
                state.layers = Math.min(Math.max(state.layers, state.size.min_layers), state.size.max_layers);
            });

            const s = state.size, counts = [];
            for (let n = s.min_layers; n <= s.max_layers; n++) counts.push(n);
            $('layers').innerHTML = counts.map(n => `
                <button type="button" class="opt ${n === state.layers ? 'active' : ''}" data-i="${n}">
                    <span class="num">${n} طبقة</span>
                    <span class="price">${n === s.min_layers ? 'الأساسي' : (layerExtra(s, n) ? `+${layerExtra(s, n)}${RIYAL}` : 'بدون زيادة')}</span>
                </button>`).join('');
            bind($('layers'), '.opt', n => { state.layers = +n; });

            if ($('flavors')) {
                $('flavors').innerHTML = product.flavors.map((f, i) => `
                    <button type="button" class="item-card ${f === state.flavor ? 'active' : ''}" data-i="${i}">
                        <div class="pic" ${f.image ? `style="background-image:url('${f.image}')"` : ''}></div>
                        ${esc(f.name)}
                        <span class="extra">${f.extra ? `+${f.extra}` : 'بدون زيادة'}</span>
                    </button>`).join('');
                bind($('flavors'), '.item-card', i => { state.flavor = product.flavors[i]; });
            }

            if ($('stuffings')) {
                $('stuffings').innerHTML = product.stuffings.map((f, i) => `
                    <button type="button" class="chip ${f === state.stuffing ? 'active' : ''}" data-i="${i}">${esc(f.name)}</button>`).join('');
                bind($('stuffings'), '.chip', i => { state.stuffing = product.stuffings[i]; });
            }
        }

        function renderPrice() {
            const { before, final } = totals();
            $('price').textContent = final;
            $('old-price').textContent = final < before ? before : '';
            $('badge').hidden = !state.size.discount;
            $('badge').textContent = discountLabel(state.size.discount);
            $('size-label').textContent = `${state.size.inch} إنش`;
            $('specs').querySelector('[data-spec=layers]').textContent = `${state.layers} طبقة`;
            $('specs').querySelector('[data-spec=inch]').textContent = `${state.size.inch} إنش`;
            $('specs').querySelector('[data-spec=people]').textContent = state.size.people;
        }

        function renderSummary(el) {
            const { before, final } = totals();
            const lines = [
                ['المنتج', `S.N: ${product.id}`],
                ['الحجم', `${state.size.inch} إنش (${state.size.people} أشخاص)`],
                ['الطبقات', `${state.layers}`],
                ['النكهة', state.flavor ? esc(state.flavor.name) : '—'],
                ['الحشوة', state.stuffing ? esc(state.stuffing.name) : '—'],
                ['الكتابة', $('writing').value.trim() ? esc($('writing').value.trim()) : '—'],
            ];
            if (state.aiApproved) {
                lines.push(['التعديل', esc($('ai-note').value.trim())]);
                lines.push(['التصميم', `<span class="design"><img src="${state.aiImage}" alt=""></span>`]);
            }
            if (final < before) lines.push(['قبل الخصم', `<span class="old">${before}</span>`]);
            el.innerHTML = `<dl>${lines.map(([k, v]) => `<div class="line"><dt>${k}</dt><dd>${v}</dd></div>`).join('')}
                <div class="line total"><dt>الإجمالي</dt><dd>${final}${RIYAL}</dd></div></dl>`;
        }

        function render() {
            renderOptions(); renderPrice();
            $('message').textContent = '';
        }

        $('writing').addEventListener('input', () => { $('writing-count').textContent = $('writing').value.length; $('message').textContent = ''; });

        // ===== تعديل الكيكة بالذكاء الاصطناعي =====
        let refFiles = [];
        let aiAbort = null;
        const aiImages = [];
        const canGenerate = () => state.aiRemaining === null || state.aiRemaining > 0;
        function aiMessage(text, error = false) { $('ai-message').textContent = text; $('ai-message').className = 'message' + (error ? ' error' : ''); }
        function renderRemaining() {
            $('ai-remaining').textContent = state.aiRemaining === null ? '' : `باقي لك ${state.aiRemaining} محاولات.`;
            $('ai-generate').disabled = !HAS_KEY || !canGenerate();
        }

        $('ai-note').addEventListener('input', () => { $('ai-note-count').textContent = $('ai-note').value.length; });

        function renderRefs() {
            $('ai-thumbs').innerHTML = '';
            refFiles.forEach((f, i) => {
                const wrap = document.createElement('div');
                wrap.className = 't';
                const img = document.createElement('img');
                img.src = URL.createObjectURL(f);
                const del = document.createElement('button');
                del.type = 'button';
                del.textContent = '×';
                del.setAttribute('aria-label', 'حذف الصورة');
                del.addEventListener('click', () => { refFiles.splice(i, 1); renderRefs(); });
                wrap.append(img, del);
                $('ai-thumbs').appendChild(wrap);
            });
        }
        $('ai-reference').addEventListener('change', e => {
            refFiles = [...refFiles, ...e.target.files].slice(0, MAX_REFERENCES);
            if (e.target.files.length + refFiles.length > MAX_REFERENCES) aiMessage(`الحد ${MAX_REFERENCES} صور.`);
            e.target.value = '';
            renderRefs();
        });

        // نصغّر الصور (أطول ضلع 2048) قبل الرفع
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

        function showPhoto(which) {
            $('main-photo').src = which === 'ai' && state.aiApproved ? state.aiImage : product.image;
            $('photo-switch').querySelectorAll('button').forEach(b => b.classList.toggle('active', b.dataset.photo === which));
        }
        $('photo-switch').querySelectorAll('button').forEach(b => b.addEventListener('click', () => showPhoto(b.dataset.photo)));

        function showAiImage(url) {
            state.aiImage = url;
            state.aiApproved = false;
            $('ai-image').src = url;
            $('ai-download').href = url;
            $('ai-download').download = `jomacake-${product.id}-${aiImages.indexOf(url) + 1}.png`;
            $('ai-result').className = 'ai-result show';
            $('photo-switch').hidden = true;
            showPhoto('original');
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

        // إذا الاستضافة قطعت الطلب، التوليد مكمّل في السيرفر: نسأل عن نتيجته كل 5 ثواني (لين 6 دقايق)
        async function waitForJob(job, signal) {
            for (let i = 0; i < 72; i++) {
                await new Promise(res => setTimeout(res, 5000));
                if (signal.aborted) throw new DOMException('cancelled', 'AbortError');
                try {
                    const res = await fetch(AI_STATUS_URL + job, { headers: { 'Accept': 'application/json' }, signal });
                    const data = await res.json();
                    if (data.status === 'done') return { data, ok: true };
                    if (data.status === 'failed' || data.status === 'missing') return { data, ok: false };
                } catch (e) {
                    if (signal.aborted) throw e;
                }
            }
            return { data: { message: 'التوليد طوّل، جربي مرة ثانية.' }, ok: false };
        }

        $('ai-cancel').addEventListener('click', () => aiAbort?.abort());

        $('ai-generate').addEventListener('click', async () => {
            const note = $('ai-note').value.trim();
            if (!note) { aiMessage('اكتبي وش تبين تغيّرين في الكيكة.', true); $('ai-note').focus(); return; }
            const abort = aiAbort = new AbortController();
            const started = Date.now();
            const tick = () => { $('ai-wait-time').textContent = `${Math.round((Date.now() - started) / 1000)} ثانية`; };
            tick();
            const timer = setInterval(tick, 1000);
            $('ai-wait-modal').classList.add('show');
            aiMessage('');
            $('ai-generate').disabled = true;

            const form = new FormData();
            form.append('note', note);
            form.append('size', state.size.id);
            form.append('layers', state.layers);
            for (const f of await Promise.all(refFiles.map(shrink))) form.append('references[]', f);
            const job = Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
            form.append('job', job);

            try {
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
                $('ai-generate').textContent = '✨ جربي تعديل ثاني';
                aiMessage('هذي الكيكة بعد التعديل. عجبتك؟');
            } catch (e) {
                aiMessage(abort.signal.aborted ? 'تم الإلغاء. غيّري اللي تبين وجربي من جديد.' : e.message, !abort.signal.aborted);
            } finally {
                clearInterval(timer);
                $('ai-wait-modal').classList.remove('show');
                if (aiAbort === abort) aiAbort = null;
                renderRemaining();
            }
        });

        $('ai-approve').addEventListener('click', () => {
            state.aiApproved = true;
            $('ai-result').classList.add('is-approved');
            $('photo-switch').hidden = false;
            showPhoto('ai');
        });
        $('ai-reject').addEventListener('click', () => {
            state.aiApproved = false;
            $('ai-result').className = 'ai-result';
            $('ai-history').hidden = !aiImages.length;
            aiMessage('غيّري الوصف أو الصور وجربي مرة ثانية.');
            $('ai-note').focus();
        });

        $('review').addEventListener('click', () => {
            const missing = [];
            if (product.stuffings.length && !state.stuffing) missing.push('الحشوة');
            if (product.requires_text && !$('writing').value.trim()) missing.push('الكتابة على الكعكة');
            if (missing.length) { $('message').textContent = 'باقي: ' + missing.join('، '); return; }
            $('modal-photo').src = state.aiApproved ? state.aiImage : product.image;
            renderSummary($('modal-summary'));
            $('done-modal').classList.add('show');
        });
        $('close-modal').addEventListener('click', () => $('done-modal').classList.remove('show'));

        renderRemaining();
        render();
    </script>
@endpush
