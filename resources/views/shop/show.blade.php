@extends('shop.layout')

@section('title', $product['name'] ?? 'S.N: '.$product['id'])

@section('styles')
        /* الصورة والسعر ثابتين فوق، والخيارات تتحرك تحتها */
        .top-fixed { position: sticky; top: 0; z-index: 20; background: #fff; box-shadow: 0 4px 8px -6px rgba(75,21,40,0.18); }
        .photo { position: relative; background: #FAF3F5; }
        .photo img { width: 100%; height: 28vh; min-height: 190px; max-height: 320px; object-fit: contain; display: block; }
        .photo .back {
            position: absolute; top: 10px; right: 10px; background: #fff; border-radius: 50%; width: 34px; height: 34px;
            display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 1px 4px rgba(0,0,0,0.12);
        }
        .price-bar { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 14px 4px; }
        .price-bar .title { font-size: 14px; font-weight: 800; }
        .price-bar .title small { display: block; font-size: 11px; color: var(--muted); font-weight: 700; }
        .price-bar .amount { font-size: 20px; font-weight: 800; white-space: nowrap; }
        .price-bar .left { text-align: left; }
        .price-bar .left .line { display: flex; align-items: center; gap: 6px; justify-content: flex-end; }
        .top-fixed .specs { margin: 4px 12px 8px; background: #FAF5F7; border-radius: 14px; padding: 8px; }

        main { max-width: 520px; margin: 0 auto; padding: 4px 16px 40px; }
        .section-title { font-size: 14px; font-weight: 800; margin: 18px 0 10px; }
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

        input[type=text], input[type=date] {
            width: 100%; border: 1.5px solid #EFDDE4; border-radius: 12px; padding: 11px 12px; font-size: 14px; color: var(--ink); background: #fff;
        }
        input:focus { outline: none; border-color: var(--pink); }
        .counter { font-size: 11px; color: var(--muted); text-align: left; margin-top: 4px; }

        .summary { border: 1.5px solid #EFDDE4; border-radius: 16px; padding: 4px 14px; margin-top: 16px; }
        .summary .line { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-bottom: 1px solid #F5EBEF; font-size: 13px; }
        .summary .line:last-child { border-bottom: none; }
        .summary dl { margin: 0; }
        .summary dt { color: var(--muted); font-weight: 700; }
        .summary dd { margin: 0; font-weight: 800; text-align: left; }
        .summary .total dd { font-size: 17px; color: var(--pink); }
        .message { font-size: 12px; font-weight: 700; color: #B42318; margin-top: 10px; min-height: 1em; }
        .btn {
            width: 100%; border: none; border-radius: 14px; padding: 14px; font-size: 15px; font-weight: 800;
            background: var(--pink); color: #fff; cursor: pointer; margin-top: 12px;
        }

        .modal { position: fixed; inset: 0; background: rgba(75,21,40,0.45); display: none; align-items: flex-end; justify-content: center; z-index: 50; }
        .modal.show { display: flex; }
        .modal .sheet { background: #fff; width: 100%; max-width: 520px; max-height: 88vh; overflow: auto; border-radius: 20px 20px 0 0; padding: 18px 16px 26px; }
        .modal h2 { margin: 0 0 4px; font-size: 18px; }
        .modal .thumb { width: 90px; height: 90px; object-fit: cover; border-radius: 12px; border: 1px solid var(--pink-line); display: block; margin: 10px auto 0; }
@endsection

@section('top')
    @php $first = $product['sizes'][0]; @endphp
    <div class="top-fixed">
        @include('shop.partials.header')
        <div class="photo">
            <img src="{{ $product['image'] }}" alt="{{ $product['name'] ?? 'S.N: '.$product['id'] }}">
            <a href="{{ route('shop.index') }}" class="back" aria-label="رجوع للمتجر">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="m9 6 6 6-6 6"/></svg>
            </a>
        </div>
        <div class="price-bar">
            <div class="title">
                {{ $product['name'] ?? 'كيكة جاهزة' }}
                <small>S.N: {{ $product['id'] }}</small>
            </div>
            <div class="left">
                <div class="line">
                    <span class="badge" id="badge" hidden></span>
                    <span class="amount"><span id="price">{{ \App\Services\ShopProducts::finalPrice($first['price'], $first['discount']) }}</span>@include('shop.partials.riyal')</span>
                </div>
                <div class="line"><span class="old" id="old-price"></span><span class="tax">شامل الضريبة</span></div>
            </div>
        </div>
        @include('shop.partials.specs', ['size' => $first, 'id' => 'top-specs'])
    </div>
@endsection

@section('content')
    <main>
        <div class="section-title">الحجم</div>
        <div class="row" id="sizes"></div>

        <div class="section-title">الطبقات</div>
        <div class="row" id="layers"></div>

        @if ($product['flavors'])
            <div class="section-title">النكهة</div>
            <div class="item-cards" id="flavors"></div>
        @endif

        @if ($product['stuffings'])
            <div class="section-title">الحشوة</div>
            <div class="chips" id="stuffings"></div>
        @endif

        <div class="section-title">اكتب على الكعكة @unless ($product['requires_text'])<small>(اختياري)</small>@endunless</div>
        <input type="text" id="writing" maxlength="40" placeholder="مثال: عيد ميلاد سعيد">
        <div class="counter"><span id="writing-count">0</span>/40</div>

        <div class="section-title">تاريخ الاستلام</div>
        <input type="date" id="delivery-date" min="{{ now()->toDateString() }}">

        <div class="summary" id="summary"></div>
        <div class="message" id="message"></div>
        <button type="button" class="btn" id="review">مراجعة الطلب</button>
    </main>

    <div class="modal" id="done-modal">
        <div class="sheet">
            <h2>تم تجهيز طلبك ✓</h2>
            <div class="hint">هذه عينة تجريبية، الطلب ما انرسل لموقع جوماكيك. هذا الملخص اللي بيوصل:</div>
            <img class="thumb" src="{{ $product['image'] }}" alt="">
            <div class="summary" id="modal-summary"></div>
            <button type="button" class="btn" id="close-modal">تمام</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const product = @json($product);
        const RIYAL = `@include('shop.partials.riyal')`;
        const $ = id => document.getElementById(id);
        const state = { size: product.sizes[0], layers: product.sizes[0].min_layers, flavor: product.flavors[0] ?? null, stuffing: null };

        // نفس حساب الموقع: الخصم على المجموع، والكسر ينقص (355 بخصم 25% = 266)
        function discounted(price, discount) {
            if (discount) price = discount.type === 'percent' ? price * (1 - discount.amount / 100) : price - discount.amount;
            return Math.floor(Math.max(0, price) + 1e-9);
        }
        const discountLabel = d => d && d.type === 'percent' ? `خصم ${d.amount}%` : (d ? `خصم ${d.amount}` : '');
        const layerExtra = (size, n) => (n - size.min_layers) * size.layer_price;

        function totals() {
            const before = state.size.price + layerExtra(state.size, state.layers) + (state.flavor?.extra ?? 0);
            return { before, final: discounted(before, state.size.discount) };
        }

        function renderSizes() {
            $('sizes').innerHTML = product.sizes.map((s, i) => `
                <button type="button" class="opt ${s === state.size ? 'active' : ''}" data-i="${i}">
                    <span class="num">${s.inch} إنش</span>
                    <small>${s.people} أشخاص</small>
                    <span class="price">${discounted(s.price, s.discount)}${RIYAL}</span>
                    ${s.discount ? `<span class="badge">${discountLabel(s.discount)}</span>` : ''}
                </button>`).join('');
            $('sizes').querySelectorAll('.opt').forEach(b => b.addEventListener('click', () => {
                state.size = product.sizes[b.dataset.i];
                state.layers = Math.min(Math.max(state.layers, state.size.min_layers), state.size.max_layers);
                render();
            }));
        }

        function renderLayers() {
            const s = state.size, items = [];
            for (let n = s.min_layers; n <= s.max_layers; n++) items.push(n);
            $('layers').innerHTML = items.map(n => `
                <button type="button" class="opt ${n === state.layers ? 'active' : ''}" data-n="${n}">
                    <span class="num">${n} طبقات</span>
                    <span class="price">${n === s.min_layers ? 'الأساسي' : (layerExtra(s, n) ? `+${layerExtra(s, n)}${RIYAL}` : 'بدون زيادة')}</span>
                </button>`).join('');
            $('layers').querySelectorAll('.opt').forEach(b => b.addEventListener('click', () => { state.layers = +b.dataset.n; render(); }));
        }

        function renderFlavors() {
            if (!$('flavors')) return;
            $('flavors').innerHTML = product.flavors.map((f, i) => `
                <button type="button" class="item-card ${f === state.flavor ? 'active' : ''}" data-i="${i}">
                    <div class="pic" ${f.image ? `style="background-image:url('${f.image}')"` : ''}></div>
                    ${f.name}
                    <span class="extra">${f.extra ? `+${f.extra}` : 'بدون زيادة'}</span>
                </button>`).join('');
            $('flavors').querySelectorAll('.item-card').forEach(b => b.addEventListener('click', () => { state.flavor = product.flavors[b.dataset.i]; render(); }));
        }

        function renderStuffings() {
            if (!$('stuffings')) return;
            $('stuffings').innerHTML = product.stuffings.map((s, i) => `
                <button type="button" class="chip ${s === state.stuffing ? 'active' : ''}" data-i="${i}">${s.name}</button>`).join('');
            $('stuffings').querySelectorAll('.chip').forEach(b => b.addEventListener('click', () => { state.stuffing = product.stuffings[b.dataset.i]; render(); }));
        }

        function renderTop() {
            const { before, final } = totals();
            $('price').textContent = final;
            $('old-price').textContent = final < before ? before : '';
            $('badge').hidden = !state.size.discount;
            $('badge').textContent = discountLabel(state.size.discount);
            const specs = $('top-specs');
            specs.querySelector('[data-spec=layers]').textContent = `${state.layers} طبقة`;
            specs.querySelector('[data-spec=inch]').textContent = `${state.size.inch} إنش`;
            specs.querySelector('[data-spec=people]').textContent = state.size.people;
        }

        function renderSummary(el) {
            const { before, final } = totals();
            const esc = t => t.replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
            const lines = [
                ['المنتج', `S.N: ${product.id}`],
                ['الحجم', `${state.size.inch} إنش (${state.size.people} أشخاص)`],
                ['الطبقات', `${state.layers}`],
                ['النكهة', state.flavor?.name ?? '—'],
                ['الحشوة', state.stuffing?.name ?? '—'],
                ['الكتابة', $('writing').value.trim() ? esc($('writing').value.trim()) : '—'],
                ['التاريخ', $('delivery-date').value || '—'],
            ];
            if (final < before) lines.push(['قبل الخصم', `<span class="old">${before}</span>`]);
            el.innerHTML = `<dl>${lines.map(([k, v]) => `<div class="line"><dt>${k}</dt><dd>${v}</dd></div>`).join('')}
                <div class="line total"><dt>الإجمالي</dt><dd>${final}${RIYAL}</dd></div></dl>`;
        }

        function render() {
            renderSizes(); renderLayers(); renderFlavors(); renderStuffings(); renderTop(); renderSummary($('summary'));
            $('message').textContent = '';
        }

        $('writing').addEventListener('input', () => { $('writing-count').textContent = $('writing').value.length; renderSummary($('summary')); });
        $('delivery-date').addEventListener('input', () => renderSummary($('summary')));

        $('review').addEventListener('click', () => {
            const missing = [];
            if (product.stuffings.length && !state.stuffing) missing.push('الحشوة');
            if (product.requires_text && !$('writing').value.trim()) missing.push('الكتابة على الكعكة');
            if (!$('delivery-date').value) missing.push('تاريخ الاستلام');
            if (missing.length) { $('message').textContent = 'باقي: ' + missing.join('، '); return; }
            renderSummary($('modal-summary'));
            $('done-modal').classList.add('show');
        });
        $('close-modal').addEventListener('click', () => $('done-modal').classList.remove('show'));

        render();
    </script>
@endpush
