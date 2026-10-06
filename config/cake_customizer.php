<?php

/*
|--------------------------------------------------------------------------
| خيارات صفحة "صمّم كيكتك" (عينة)
|--------------------------------------------------------------------------
|
| كل الأسعار هنا هي مصدر الحقيقة الوحيد: الصفحة تعرضها للعميل فقط،
| والسيرفر يعيد حساب السعر من المفاتيح المختارة عند الإضافة للسلة.
| لاحقًا تُستبدل هذه القوائم ببيانات قاعدة بيانات المتجر الرئيسي.
|
*/

return [

    'model_version' => 'v45',

    // لون الكيكة الأصلي المحفوظ داخل ملفات glb (vertex color) — نحتاجه لتعويض اللون بدقة
    'base_vertex_color' => [245, 239, 188],

    'tiers' => [
        1 => 'دور واحد',
        2 => 'دورين',
        3 => '3 أدوار',
    ],

    // key = جزء اسم ملف النموذج. diameters = أقطار الأدوار بالإنش (من الأسفل للأعلى) للرسم.
    // أسعار الدور الواحد من jomacake.com — أسعار الدورين والثلاثة تجريبية (مجموع أسعار الأدوار)
    'sizes' => [
        ['key' => '4in',          'tier' => 1, 'label' => '4',      'diameters' => [4],        'serves' => '1-2 أشخاص',   'price' => 65],
        ['key' => '6in',          'tier' => 1, 'label' => '6',      'diameters' => [6],        'serves' => '4-6 أشخاص',   'price' => 110, 'popular' => true],
        ['key' => '8in',          'tier' => 1, 'label' => '8',      'diameters' => [8],        'serves' => '8-10 أشخاص',  'price' => 155],
        ['key' => '10in',         'tier' => 1, 'label' => '10',     'diameters' => [10],       'serves' => '12-15 شخص',   'price' => 200],
        ['key' => 'tier2_6-4',    'tier' => 2, 'label' => '6/4',    'diameters' => [6, 4],     'serves' => '6 أشخاص',     'price' => 175],
        ['key' => 'tier2_8-6',    'tier' => 2, 'label' => '8/6',    'diameters' => [8, 6],     'serves' => '14 شخص',      'price' => 265],
        ['key' => 'tier2_10-8',   'tier' => 2, 'label' => '10/8',   'diameters' => [10, 8],    'serves' => '23 شخص',      'price' => 355],
        ['key' => 'tier3_8-6-4',  'tier' => 3, 'label' => '8/6/4',  'diameters' => [8, 6, 4],  'serves' => '15 شخص',      'price' => 330],
        ['key' => 'tier3_10-8-6', 'tier' => 3, 'label' => '10/8/6', 'diameters' => [10, 8, 6], 'serves' => '28 شخص',      'price' => 465],
    ],

    // image = صورة حقيقية للقطعة داخل public/ (مثل images/flavors/vanilla.webp) — إذا null تُستخدم رسمة بديلة
    // sponge/crumb = ألوان الرسمة البديلة
    'flavors' => [
        ['key' => 'vanilla',   'name' => 'فانيلا',   'price' => 0, 'image' => null, 'sponge' => '#F3D98B', 'crumb' => '#E6C36A'],
        ['key' => 'chocolate', 'name' => 'شوكولاتة', 'price' => 0, 'image' => null, 'sponge' => '#6B3A22', 'crumb' => '#4E2715'],
    ],

    'fillings' => [
        ['key' => 'plain',      'name' => 'ويب كريم ساده',       'price' => 0,  'image' => null, 'cream' => '#FFFDF7', 'fruit' => null],
        ['key' => 'strawberry', 'name' => 'ويب كريم مع فراوله', 'price' => 10, 'image' => null, 'cream' => '#FBD3DC', 'fruit' => '#D7263D'],
    ],

    'layers' => [
        ['count' => 2, 'price' => 0],
        ['count' => 3, 'price' => 10],
    ],

    // key = لاحقة ملف usdz الملوّن (null = الملف الأصلي). لازم تطابق tools/generate_color_usdz.py
    'colors' => [
        ['key' => 'cream', 'name' => 'كريمي', 'hex' => '#F5EFBC', 'price' => 0, 'usdz_suffix' => null],
        ['key' => 'blue',  'name' => 'أزرق',  'hex' => '#1F4A8C', 'price' => 0, 'usdz_suffix' => 'blue'],
        ['key' => 'red',   'name' => 'أحمر',  'hex' => '#C21E2E', 'price' => 0, 'usdz_suffix' => 'red'],
    ],

    // الكتابة على البورد أو الكيكة + صورة للطباعة (تُطبع كما هي)
    'writing' => [
        'max_length' => 40,
        'targets' => ['board' => 'على البورد', 'cake' => 'على الكيكة'],
        'text_price' => 0,
        'print_image_price' => 0,
        'print_image_max_kb' => 10240,
    ],

];
