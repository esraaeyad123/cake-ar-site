{{-- انميشن تحضير الكيكة وقت "جاري التوليد" (بدل الساعة الرملية): الطبقات تنزل وحدة فوق الثانية،
     كيس الكريمة يرسم كريمة وردية، رشّة نقاط، وبعدها يعيد. رسم بخطوط بسيطة بألوان جوماكيك --}}
<style>
    .cake-loader { width: 170px; height: 150px; display: block; margin: 0 auto 4px; overflow: visible; }
    .cake-loader .ink { fill: none; stroke: #4B1528; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }
    .cake-loader .layer { transform-box: fill-box; animation: cl-drop1 4.8s ease-out infinite both; }
    .cake-loader .layer.l2 { animation-name: cl-drop2; }
    .cake-loader .layer.l3 { animation-name: cl-drop3; }
    .cake-loader .cream { fill: none; stroke: #D4537E; stroke-width: 6; stroke-linecap: round; stroke-dasharray: 120; animation: cl-cream 4.8s ease-in-out infinite both; }
    .cake-loader .bag { animation: cl-bag 4.8s ease-in-out infinite both; }
    .cake-loader .dot { transform-box: fill-box; transform-origin: center; animation: cl-pop 4.8s ease-out infinite both; }
    .cake-loader .dot:nth-of-type(2) { animation-delay: .12s; }
    .cake-loader .dot:nth-of-type(3) { animation-delay: .24s; }
    .cake-loader .dot:nth-of-type(4) { animation-delay: .36s; }
    .cake-loader .dot:nth-of-type(5) { animation-delay: .48s; }
    /* كل طبقة تنزل بوقتها، وكلهم يختفون مع بعض في الآخر */
    @keyframes cl-drop1 {
        0% { transform: translateY(-46px); opacity: 0; }
        10% { transform: translateY(0); opacity: 1; }
        13% { transform: translateY(-3px); }
        16%, 86% { transform: translateY(0); opacity: 1; }
        100% { transform: translateY(0); opacity: 0; }
    }
    @keyframes cl-drop2 {
        0%, 7% { transform: translateY(-46px); opacity: 0; }
        17% { transform: translateY(0); opacity: 1; }
        20% { transform: translateY(-3px); }
        23%, 86% { transform: translateY(0); opacity: 1; }
        100% { transform: translateY(0); opacity: 0; }
    }
    @keyframes cl-drop3 {
        0%, 14% { transform: translateY(-46px); opacity: 0; }
        24% { transform: translateY(0); opacity: 1; }
        27% { transform: translateY(-3px); }
        30%, 86% { transform: translateY(0); opacity: 1; }
        100% { transform: translateY(0); opacity: 0; }
    }
    @keyframes cl-cream {
        0%, 33% { stroke-dashoffset: 120; opacity: 1; }
        60%, 86% { stroke-dashoffset: 0; opacity: 1; }
        100% { stroke-dashoffset: 0; opacity: 0; }
    }
    @keyframes cl-bag {
        0%, 28% { transform: translate(66px, 40px); opacity: 0; }
        33% { transform: translate(74px, 56px); opacity: 1; }
        60% { transform: translate(134px, 56px); opacity: 1; }
        68%, 100% { transform: translate(148px, 38px); opacity: 0; }
    }
    @keyframes cl-pop {
        0%, 62% { transform: scale(0); opacity: 0; }
        68% { transform: scale(1.4); opacity: 1; }
        72%, 86% { transform: scale(1); opacity: 1; }
        100% { transform: scale(1); opacity: 0; }
    }
    @media (prefers-reduced-motion: reduce) {
        .cake-loader .layer, .cake-loader .cream, .cake-loader .bag, .cake-loader .dot { animation: none; }
        .cake-loader .cream { stroke-dashoffset: 0; }
        .cake-loader .bag { opacity: 0; }
    }
</style>
<svg class="cake-loader" viewBox="0 0 200 170" role="img" aria-hidden="true">
    {{-- القاعدة --}}
    <path class="ink" d="M22 152 H178" />
    <path d="M30 152 h140 v7 h-140 z" fill="#F7EEF1" stroke="#4B1528" stroke-width="2" stroke-linejoin="round" />
    {{-- الطبقات: تنزل من تحت لفوق --}}
    <g class="layer l1"><rect x="48" y="118" width="104" height="34" rx="7" fill="#FBEAF0" stroke="#4B1528" stroke-width="2.2" /></g>
    <g class="layer l2"><rect x="60" y="88" width="80" height="30" rx="7" fill="#F4C0D1" stroke="#4B1528" stroke-width="2.2" /></g>
    <g class="layer l3"><rect x="72" y="60" width="56" height="28" rx="7" fill="#FBEAF0" stroke="#4B1528" stroke-width="2.2" /></g>
    {{-- الكريمة اللي يرسمها الكيس فوق الكيكة --}}
    <path class="cream" d="M74 60 q6 9 12 0 t12 0 t12 0 t12 0 t12 0" />
    {{-- كيس الكريمة (رسم خطوط) --}}
    <g class="bag">
        <path d="M0 0 L-6 -10 L22 -44 L40 -30 L6 -2 Z" fill="#fff" stroke="#4B1528" stroke-width="2.2" stroke-linejoin="round" />
        <path class="ink" d="M22 -44 q14 -6 18 14" />
        <path d="M-6 -10 L0 0 L-2 4 Z" fill="#D4537E" />
    </g>
    {{-- رشّة نقاط --}}
    <circle class="dot" cx="80" cy="48" r="3.2" fill="#D4537E" />
    <circle class="dot" cx="94" cy="40" r="2.6" fill="#ED93B1" />
    <circle class="dot" cx="106" cy="46" r="3.2" fill="#993556" />
    <circle class="dot" cx="118" cy="39" r="2.6" fill="#D4537E" />
    <circle class="dot" cx="128" cy="48" r="3" fill="#ED93B1" />
</svg>
