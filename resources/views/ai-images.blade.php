<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تجربة توليد صور الكيك - جوماكيك</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --pink: #D4537E; --pink-dark: #993556; --pink-soft: #FBEAF0; --pink-line: #F4C0D1; --ink: #4B1528; --muted: #8A6B76; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #fff; color: var(--ink); font-family: 'Tajawal', system-ui, sans-serif; }
        .wrap { max-width: 980px; margin: 0 auto; padding: 24px 16px 48px; }
        header { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
        header h1 { margin: 0; font-size: 26px; font-weight: 800; }
        header img { height: 30px; }
        .badge { font-size: 12px; font-weight: 700; color: var(--pink-dark); background: var(--pink-soft); border-radius: 999px; padding: 4px 10px; }
        .panel { border: 1px solid var(--pink-line); border-radius: 18px; padding: 16px; }
        label { font-weight: 700; display: block; margin-bottom: 6px; }
        textarea { width: 100%; min-height: 110px; padding: 12px; font: inherit; font-size: 15px; border: 1px solid var(--pink-line); border-radius: 12px; direction: ltr; text-align: left; resize: vertical; }
        textarea:focus, select:focus { outline: 2px solid var(--pink); outline-offset: 1px; }
        .hint { font-size: 13px; color: var(--muted); margin: 6px 0 0; }
        .examples { display: flex; flex-wrap: wrap; gap: 6px; margin: 12px 0; }
        .examples button { border: 1px solid var(--pink-line); background: #fff; color: var(--pink-dark); border-radius: 999px; padding: 6px 12px; font: inherit; font-size: 13px; font-weight: 700; cursor: pointer; }
        .examples button:hover { background: var(--pink-soft); }
        .row { display: flex; gap: 10px; align-items: end; flex-wrap: wrap; margin-top: 12px; }
        select { padding: 10px 12px; font: inherit; border: 1px solid var(--pink-line); border-radius: 10px; background: #fff; }
        .go { flex: 1; min-width: 200px; border: 0; border-radius: 12px; background: var(--pink); color: #fff; font: inherit; font-size: 17px; font-weight: 800; padding: 13px; cursor: pointer; }
        .go:disabled { opacity: .6; cursor: wait; }
        .alert { margin-top: 12px; padding: 12px 14px; border-radius: 12px; background: #fdecec; color: #9b1c1c; }
        .alert.warn { background: #fff4e5; color: #8a4b00; }
        .alert .en { direction: ltr; text-align: left; display: block; }
        h2 { font-size: 19px; margin: 28px 0 12px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px; }
        .item { border: 1px solid #F1DDE4; border-radius: 16px; overflow: hidden; background: #fff; min-width: 0; }
        .item.latest { border: 2px solid var(--pink); box-shadow: 0 6px 18px rgba(212,83,126,.18); }
        .item img { width: 100%; aspect-ratio: 1 / 1; object-fit: cover; display: block; background: #f7f2f4; }
        .meta { padding: 10px 12px 12px; display: grid; gap: 6px; }
        .meta .prompt { font-size: 13px; direction: ltr; text-align: left; color: var(--ink); overflow-wrap: anywhere; }
        .tags { display: flex; flex-wrap: wrap; gap: 6px; font-size: 12px; }
        .tags span { background: var(--pink-soft); color: var(--pink-dark); border-radius: 999px; padding: 2px 8px; font-weight: 700; }
        .upload { margin-top: 14px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; border: 2px dashed #E5C4D0; border-radius: 14px; padding: 12px; background: #FFFBFC; }
        .upload input { font: inherit; font-size: 14px; max-width: 100%; }
        .upload img { width: 72px; height: 72px; object-fit: cover; border-radius: 10px; display: none; }
        .upload img.show { display: block; }
        .upload .txt { flex: 1; min-width: 200px; font-size: 13px; color: var(--muted); }
        .upload .txt b { display: block; color: var(--ink); font-size: 14px; }
        .examples .lbl { font-size: 12px; color: var(--muted); align-self: center; margin-inline-end: 4px; }
        .pair { display: grid; grid-template-columns: 1fr 1fr; }
        .pair figure { margin: 0; position: relative; }
        .pair figcaption { position: absolute; top: 6px; right: 6px; background: rgba(75,21,40,.75); color: #fff; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; }
        .empty { color: var(--muted); border: 1px dashed var(--pink-line); border-radius: 16px; padding: 24px; text-align: center; }
    </style>
</head>
<body>
<div class="wrap">
    <header>
        <h1>تجربة توليد صور الكيك</h1>
        <span class="badge">صفحة داخلية للتجربة</span>
    </header>

    @unless ($hasKey)
        <div class="alert warn">لا يوجد مفتاح OpenAI بعد. أضيفي <code>OPENAI_API_KEY</code> في ملف <code>.env</code> ثم شغّلي <code>php artisan config:clear</code>.</div>
    @endunless

    <form class="panel" method="POST" action="{{ route('ai-images.generate') }}" id="ai-form" enctype="multipart/form-data">
        @csrf
        <label for="prompt">وصف الكيكة (Prompt)</label>
        <textarea id="prompt" name="prompt" maxlength="1000" required
                  placeholder="A round white cake with red beads around the top edge...">{{ old('prompt') }}</textarea>
        <p class="hint">الوصف بالإنجليزي يعطي غالباً نتائج أدق. جربي الأمثلة:</p>

        <label class="upload" for="base_image">
            <img id="base-preview" alt="الصورة المرفوعة">
            <span class="txt"><b>اختياري: صورة كيكة للتعديل عليها</b>إذا رفعتِ صورة، يعدّل عليها حسب الوصف. بدون صورة يولّد صورة جديدة من الصفر.</span>
            <input type="file" id="base_image" name="base_image" accept="image/png,image/jpeg,image/webp">
        </label>

        <div class="examples">
            <span class="lbl">أمثلة تعديل (مع صورة):</span>
            <button type="button" data-prompt="Change the number 60 on the cake to 25, using the same green piped cream style. Keep everything else in the photo exactly the same.">غيّري الرقم إلى 25</button>
            <button type="button" data-prompt="Change the cake frosting color to soft pink. Keep the drawing, the text, the board and the background exactly the same.">لوّني الكيكة وردي</button>
            <button type="button" data-prompt="Add a ring of small white cream beads around the top edge of the cake. Keep everything else exactly the same.">أضيفي حبات على الحافة</button>
        </div>

        <div class="examples">
            <span class="lbl">أمثلة توليد (بدون صورة):</span>
            <button type="button" data-prompt="A round 6-inch pink buttercream cake with small white cream beads around the top edge, 'Happy Birthday Sara' piped in white on top, on a white square cake board, studio photo, soft light, plain light background">وردية بحبات وكتابة</button>
            <button type="button" data-prompt="A round ivory buttercream bento cake with a simple black line drawing on top of a Saudi man wearing a red and white checkered shemagh, 'Congratulations Bassam' written in gold, on a white square cake board, realistic photo, top-down angle">تخرج بشماغ أحمر</button>
            <button type="button" data-prompt="A two-tier white wedding cake covered with small pearls, pink roses on the side, on a white cake board, elegant realistic photo, soft natural light">زواج بلؤلؤ</button>
            <button type="button" data-prompt="A round blue cake with colorful sprinkles on top and white cream piping on the edges, three red cherries on top, realistic bakery photo">زرقاء بسبرنكلز وكرز</button>
        </div>

        <div class="row">
            <div>
                <label for="quality">الجودة</label>
                <select id="quality" name="quality">
                    @foreach ($qualities as $q)
                        <option value="{{ $q }}" @selected(old('quality', 'low') === $q)>
                            {{ ['low' => 'منخفضة (أسرع وأرخص)', 'medium' => 'متوسطة', 'high' => 'عالية (أبطأ وأغلى)'][$q] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button class="go" type="submit" id="go">ولّدي الصورة</button>
        </div>

        @if ($errors->any())
            <div class="alert"><span class="en">{{ $errors->first() }}</span></div>
        @endif
    </form>

    <h2>الصور المولّدة</h2>
    @if (count($history))
        <div class="grid">
            @foreach ($history as $item)
                <figure class="item {{ session('latest') === $item['id'] ? 'latest' : '' }}" style="margin:0">
                    @if (! empty($item['base_url']))
                        <div class="pair">
                            <figure><a href="{{ $item['base_url'] }}" target="_blank" rel="noopener"><img src="{{ $item['base_url'] }}" alt="الصورة الأصلية" loading="lazy"></a><figcaption>قبل</figcaption></figure>
                            <figure><a href="{{ $item['url'] }}" target="_blank" rel="noopener"><img src="{{ $item['url'] }}" alt="الصورة المعدّلة" loading="lazy"></a><figcaption>بعد</figcaption></figure>
                        </div>
                    @else
                        <a href="{{ $item['url'] }}" target="_blank" rel="noopener"><img src="{{ $item['url'] }}" alt="صورة مولّدة" loading="lazy"></a>
                    @endif
                    <figcaption class="meta">
                        <div class="prompt">{{ $item['prompt'] }}</div>
                        <div class="tags">
                            <span>{{ ($item['mode'] ?? 'generate') === 'edit' ? 'تعديل' : 'توليد' }}</span>
                            <span>{{ $item['quality'] }}</span>
                            <span>{{ $item['seconds'] }} ثانية</span>
                            <span>{{ $item['created_at'] }}</span>
                        </div>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    @else
        <div class="empty">ما فيه صور بعد. اكتبي وصفاً أو اختاري مثالاً واضغطي «ولّدي الصورة».</div>
    @endif
</div>

<script>
    document.querySelectorAll('.examples button').forEach(function (b) {
        b.addEventListener('click', function () { document.getElementById('prompt').value = b.dataset.prompt; });
    });
    document.getElementById('base_image').addEventListener('change', function (e) {
        const img = document.getElementById('base-preview');
        const file = e.target.files[0];
        if (img.src) URL.revokeObjectURL(img.src);
        img.classList.toggle('show', !!file);
        if (file) img.src = URL.createObjectURL(file);
    });
    document.getElementById('ai-form').addEventListener('submit', function () {
        const go = document.getElementById('go');
        go.disabled = true;
        go.textContent = 'جاري التوليد... قد يأخذ حتى دقيقة';
    });
</script>
</body>
</html>
