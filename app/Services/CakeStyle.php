<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;

/**
 * أسلوب جوماكيك: الكيك كريمة، والرسم والكتابة يدوي بالكيس (مو طباعة ولا فوندان)،
 * والرسمة معدولة مع القاعدة من جهة ملصق JOMACAKE.
 * صور الكيك الحقيقي في public/images/style تنرسل مع أول صورة كمثال على طريقة التنفيذ.
 */
class CakeStyle
{
    public const DIR = 'images/style';

    /**
     * @return string[] مسارات صور الأسلوب
     */
    public static function images(): array
    {
        // من داخل المشروع نفسه (مو public_path): على السيرفر مجلد public منفصل عن المشروع
        $files = glob(base_path('public/'.self::DIR.'/*.{jpg,jpeg,png,webp}'), GLOB_BRACE) ?: [];
        sort($files, SORT_NATURAL);

        return array_slice($files, 0, 10);
    }

    /**
     * يضيف صور الأسلوب للطلب (بعد صور الزبون)، بحدود 16 صورة للطلب كله. يرجع كم صورة انضافت
     */
    public static function attach(PendingRequest $pending, int $alreadyAttached): int
    {
        $files = array_slice(self::images(), 0, max(0, 16 - $alreadyAttached));
        foreach ($files as $i => $file) {
            $pending->attach('image[]', file_get_contents($file), 'jomacake-style-'.($i + 1).'.jpg', ['Content-Type' => 'image/jpeg']);
        }

        return count($files);
    }

    /**
     * قاعدة الأسلوب. $styleImages = كم صورة أسلوب في آخر الطلب (0 = القاعدة بس)
     */
    public static function rule(int $styleImages = 0): string
    {
        return 'JOMACAKE HOUSE STYLE (very important): this must look like a real handmade JOMACAKE cake that the bakery can actually make. '
            .'The cake is covered in buttercream with a soft, slightly uneven hand-spread spatula texture (not fondant, not glossy, not perfectly smooth). '
            .'Every drawing, figure, face, logo and piece of writing is hand-piped and spread with buttercream: simple flat colour areas spread with a small spatula, '
            .'outlined with thin piped lines, in a charming naive hand-drawn style with limited detail; people are drawn simply, often with blank faces. '
            .'Writing is piped by hand in cursive or rounded lettering. Borders, shells, rosettes and roses, when used, are piped buttercream. '
            .'NEVER an edible printed photo, never airbrushed or photo-realistic artwork, never printed logos or fondant cut-outs. '
            .'ORIENTATION: the top design and writing must be upright and straight, readable from the front of the board (the side with the JOMACAKE label), '
            .'aligned parallel to the board\'s front edge, never rotated or diagonal. '
            .($styleImages > 0
                ? "The LAST {$styleImages} images are photos of real JOMACAKE cakes: copy ONLY their decorating technique, buttercream finish, piping style and level of detail. "
                    .'Do NOT copy their designs, characters, colours, text, shape, size or board. '
                : '');
    }
}
