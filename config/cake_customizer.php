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

    // اللون يُطبق مباشرة على جسم الكيكة المركّب (public/js/cake-composer.js)
    'colors' => [
        ['key' => 'cream', 'name' => 'كريمي', 'hex' => '#F5EFBC', 'price' => 0],
        ['key' => 'white', 'name' => 'أبيض',  'hex' => '#FBF8F2', 'price' => 0],
        ['key' => 'ivory', 'name' => 'عاجي',  'hex' => '#F1EBDA', 'price' => 0],
        ['key' => 'rose',  'name' => 'وردي غامق', 'hex' => '#DE7F88', 'price' => 0],
        ['key' => 'pink',  'name' => 'وردي',  'hex' => '#F2A9B9', 'price' => 0],
        ['key' => 'blue',  'name' => 'أزرق',  'hex' => '#1F4A8C', 'price' => 0],
        ['key' => 'red',   'name' => 'أحمر',  'hex' => '#C21E2E', 'price' => 0],
    ],

    // الإضافات: key = اسم الدالة في cake-composer.js — الأسعار تجريبية
    'topping_categories' => [
        'borders' => 'الحواف',
        'sides' => 'الجوانب',
        'top' => 'فوق الكيكة',
    ],

    'toppings' => [
        ['key' => 'beads',     'name' => 'حبات كريمة', 'category' => 'borders', 'price' => 15, 'image' => 'images/toppings/beads.png'],
        ['key' => 'piping',    'name' => 'حواف كريمة', 'category' => 'borders', 'price' => 18, 'image' => 'images/toppings/piping.png'],
        ['key' => 'pearls',    'name' => 'لؤلؤ',       'category' => 'sides',   'price' => 24, 'image' => 'images/toppings/pearls.png'],
        ['key' => 'cherries',  'name' => 'كرز',        'category' => 'top',     'price' => 20, 'image' => 'images/toppings/cherries.png'],
        ['key' => 'sprinkles', 'name' => 'سبرنكلز',    'category' => 'top',     'price' => 12, 'image' => 'images/toppings/sprinkles.png'],
    ],

    // تصاميم جاهزة (الكولكشن): مجرد اختيارات محفوظة تفتح صفحة التخصيص معبأة
    // photo = صورة حقيقية للكيكة، decal = الرسمة المستخرجة منها (تُلصق على سطح المجسم)، drawing_price = سعر الرسمة (تجريبي)
    'designs' => [
        [
            'key' => 'tony', 'name' => 'عيد ميلاد سعيد', 'image' => 'images/designs/photos/tony.jpg',
            'photo' => 'images/designs/photos/tony.jpg', 'decal' => 'images/designs/decals/tony.png', 'drawing_price' => 40,
            'selection' => ['size' => '6in', 'flavor' => 'vanilla', 'filling' => 'plain', 'layers' => 3, 'color' => 'rose', 'toppings' => ['beads'], 'design' => 'tony'],
        ],
        [
            'key' => 'mama', 'name' => 'ماما حامل', 'image' => 'images/designs/photos/mama.jpg',
            'photo' => 'images/designs/photos/mama.jpg', 'decal' => 'images/designs/decals/mama.png', 'drawing_price' => 0,
            'selection' => ['size' => '4in', 'flavor' => 'vanilla', 'filling' => 'plain', 'layers' => 2, 'color' => 'ivory', 'toppings' => [], 'design' => 'mama'],
        ],
        [
            'key' => 'he-or-she', 'name' => 'He or She', 'image' => 'images/designs/photos/he-or-she.jpg',
            'photo' => 'images/designs/photos/he-or-she.jpg', 'decal' => 'images/designs/decals/he-or-she.png', 'drawing_price' => 30,
            'selection' => ['size' => '8in', 'flavor' => 'vanilla', 'filling' => 'plain', 'layers' => 2, 'color' => 'ivory', 'toppings' => [], 'design' => 'he-or-she'],
        ],
        [
            'key' => 'pink-pearls', 'name' => 'لؤلؤ وردي', 'image' => 'images/designs/pink-pearls.png',
            'selection' => ['size' => 'tier2_8-6', 'flavor' => 'vanilla', 'filling' => 'plain', 'layers' => 2, 'color' => 'pink', 'toppings' => ['pearls']],
        ],
        [
            'key' => 'cherry-classic', 'name' => 'كرز كلاسيك', 'image' => 'images/designs/cherry-classic.png',
            'selection' => ['size' => '6in', 'flavor' => 'chocolate', 'filling' => 'strawberry', 'layers' => 3, 'color' => 'white', 'toppings' => ['piping', 'cherries']],
        ],
        [
            'key' => 'blue-party', 'name' => 'احتفال أزرق', 'image' => 'images/designs/blue-party.png',
            'selection' => ['size' => '8in', 'flavor' => 'vanilla', 'filling' => 'plain', 'layers' => 2, 'color' => 'blue', 'toppings' => ['piping', 'sprinkles']],
        ],
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
