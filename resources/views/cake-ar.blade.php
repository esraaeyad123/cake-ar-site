<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $cake->name }} - معاينة بالأبعاد الحقيقية</title>

    {{-- مكتبة model-viewer: تتولى تفعيل WebXR على أندرويد و AR Quick Look على آيفون تلقائيًا --}}
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>

    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, 'Segoe UI', Tahoma, sans-serif;
            background: #faf9f7;
            text-align: center;
        }
        header {
            padding: 20px 16px 8px;
        }
        header h1 {
            font-size: 22px;
            margin: 0 0 4px;
        }
        header p {
            color: #777;
            font-size: 14px;
            margin: 0;
        }
        model-viewer {
            width: 100%;
            height: 60vh;
            background-color: #f2f1ee;
            --poster-color: transparent;
        }
        .dimensions {
            padding: 14px 16px;
            font-size: 13px;
            color: #555;
        }
        .ar-hint {
            padding: 0 16px 24px;
            font-size: 13px;
            color: #999;
        }
    </style>
</head>
<body>

    <header>
        <h1>{{ $cake->name }}</h1>
        <p>معاينة بالأبعاد الحقيقية 100%</p>
    </header>

    <model-viewer
        src="{{ $cake->glb_url }}"
        @if($cake->usdz_url) ios-src="{{ $cake->usdz_url }}" @endif
        alt="{{ $cake->name }} - نموذج ثلاثي الأبعاد"
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
        <button slot="ar-button" style="
            background: #1a1a1a;
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
        ">
            شاهدها على طاولتك
        </button>

        <div slot="progress-bar"></div>
    </model-viewer>

    <div class="dimensions">
        القطر: {{ $cake->diameter_cm }} سم &nbsp;|&nbsp; الارتفاع: {{ $cake->height_cm }} سم
    </div>

    <div class="ar-hint">
        اضغط "شاهدها على طاولتك" ثم وجّه الكاميرا نحو أي سطح مستوٍ
    </div>

</body>
</html>
