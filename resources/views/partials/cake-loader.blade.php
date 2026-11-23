{{-- انميشن تحضير الكيكة وقت "جاري التوليد" (بدل الساعة الرملية): كيكة دور واحد تنزل،
     وكيس الكريمة يرسم حواف فنتج (صدف) فوق وتحت، وبعدها حبال كريمة وكرز، ويعيد. خطوط بسيطة بألوان جوماكيك --}}
<style>
    .cake-loader { width: 170px; height: 150px; display: block; margin: 0 auto 4px; overflow: visible; }
    .cake-loader .ink { fill: none; stroke: #4B1528; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }
    .cake-loader .cake { transform-box: fill-box; animation: cl-drop 5.6s ease-out infinite both; }
    .cake-loader .pipe { fill: none; stroke: #D4537E; stroke-width: 5; stroke-linecap: round; stroke-linejoin: round; stroke-dasharray: 220; }
    .cake-loader .pipe.top { animation: cl-top 5.6s ease-in-out infinite both; }
    .cake-loader .pipe.bottom { animation: cl-bottom 5.6s ease-in-out infinite both; }
    .cake-loader .pipe.swag { stroke-width: 3; stroke: #ED93B1; animation: cl-swag 5.6s ease-in-out infinite both; }
    .cake-loader .bag { animation: cl-bag 5.6s ease-in-out infinite both; }
    .cake-loader .cherry { transform-box: fill-box; transform-origin: center; animation: cl-pop 5.6s ease-out infinite both; }
    .cake-loader .cherry:nth-of-type(2) { animation-delay: .12s; }
    .cake-loader .cherry:nth-of-type(3) { animation-delay: .24s; }
    @keyframes cl-drop {
        0% { transform: translateY(-50px); opacity: 0; }
        10% { transform: translateY(0); opacity: 1; }
        13% { transform: translateY(-4px); }
        16%, 88% { transform: translateY(0); opacity: 1; }
        100% { transform: translateY(0); opacity: 0; }
    }
    /* الكيس يرسم الحافة الفوقية من اليسار لليمين، وبعدها التحتية، وبعدها حبال الكريمة */
    @keyframes cl-top { 0%, 18% { stroke-dashoffset: 220; opacity: 1; } 42%, 88% { stroke-dashoffset: 0; opacity: 1; } 100% { stroke-dashoffset: 0; opacity: 0; } }
    @keyframes cl-bottom { 0%, 46% { stroke-dashoffset: 220; opacity: 1; } 68%, 88% { stroke-dashoffset: 0; opacity: 1; } 100% { stroke-dashoffset: 0; opacity: 0; } }
    @keyframes cl-swag { 0%, 68% { stroke-dashoffset: 220; opacity: 1; } 78%, 88% { stroke-dashoffset: 0; opacity: 1; } 100% { stroke-dashoffset: 0; opacity: 0; } }
    @keyframes cl-bag {
        0%, 14% { transform: translate(40px, 70px); opacity: 0; }
        18% { transform: translate(50px, 90px); opacity: 1; }
        42% { transform: translate(150px, 90px); opacity: 1; }
        46% { transform: translate(50px, 146px); opacity: 1; }
        68% { transform: translate(150px, 146px); opacity: 1; }
        74%, 100% { transform: translate(166px, 126px); opacity: 0; }
    }
    @keyframes cl-pop {
        0%, 76% { transform: scale(0); opacity: 0; }
        81% { transform: scale(1.35); opacity: 1; }
        85%, 88% { transform: scale(1); opacity: 1; }
        100% { transform: scale(1); opacity: 0; }
    }
    @media (prefers-reduced-motion: reduce) {
        .cake-loader .cake, .cake-loader .pipe, .cake-loader .bag, .cake-loader .cherry { animation: none; }
        .cake-loader .pipe { stroke-dashoffset: 0; }
        .cake-loader .bag { opacity: 0; }
    }
</style>
<svg class="cake-loader" viewBox="0 0 200 170" role="img" aria-hidden="true">
    {{-- القاعدة --}}
    <path class="ink" d="M22 152 H178" />
    <path d="M30 152 h140 v7 h-140 z" fill="#F7EEF1" stroke="#4B1528" stroke-width="2" stroke-linejoin="round" />
    {{-- كيكة دور واحد --}}
    <g class="cake"><rect x="50" y="92" width="100" height="60" rx="9" fill="#FBEAF0" stroke="#4B1528" stroke-width="2.2" /></g>
    {{-- فنتج: صدف فوق وتحت، وحبال كريمة على الجنب --}}
    <path class="pipe top" d="M54 92 q5.5 -9 11 0 t11 0 t11 0 t11 0 t11 0 t11 0 t11 0 t11 0 t11 0" />
    <path class="pipe bottom" d="M54 150 q5.5 -9 11 0 t11 0 t11 0 t11 0 t11 0 t11 0 t11 0 t11 0 t11 0" />
    <path class="pipe swag" d="M56 104 q11 14 22 0 q11 14 22 0 q11 14 22 0 q11 14 22 0" />
    {{-- كيس الكريمة (رسم خطوط) --}}
    <g class="bag">
        <path d="M0 0 L-6 -10 L22 -44 L40 -30 L6 -2 Z" fill="#fff" stroke="#4B1528" stroke-width="2.2" stroke-linejoin="round" />
        <path class="ink" d="M22 -44 q14 -6 18 14" />
        <path d="M-6 -10 L0 0 L-2 4 Z" fill="#D4537E" />
    </g>
    {{-- كرز فوق --}}
    <circle class="cherry" cx="78" cy="80" r="5" fill="#B4234D" stroke="#4B1528" stroke-width="1.6" />
    <circle class="cherry" cx="100" cy="78" r="5" fill="#B4234D" stroke="#4B1528" stroke-width="1.6" />
    <circle class="cherry" cx="122" cy="80" r="5" fill="#B4234D" stroke="#4B1528" stroke-width="1.6" />
</svg>
