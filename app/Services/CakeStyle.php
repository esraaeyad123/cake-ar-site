<?php

namespace App\Services;

/**
 * أسلوب جوماكيك (قاعدة مكتوبة بس): الكيك كريمة، والرسم والكتابة يدوي بالكيس (مو طباعة ولا فوندان)،
 * والرسمة معدولة مع القاعدة، وزاوية التصوير مثل صور جوماكيك.
 * ما نرسل صور أمثلة: نماذج الصور كانت تنسخ رسوماتها (مثل رسمة الزوجين) في كل كيكة.
 */
class CakeStyle
{
    /**
     * زاوية التصوير مثل صور جوماكيك: من فوق ومن قدّام شوي يسار، وسطح الكيكة والرسمة واضحة، والملصق قدّام
     */
    public static function camera(): string
    {
        return 'CAMERA: photograph it exactly like JOMACAKE\'s own product photos: a HIGH angle from the front, looking down at the cake at about 45 to 60 degrees, '
            .'slightly from the front-left, so the whole top surface and its design are clearly visible and the side is seen below it. '
            .'The square board is visible under the cake with the JOMACAKE label on its front-left edge, nearest to the camera. '
            .'The cake is centred and fills most of the frame, soft natural light, plain light background. ';
    }

    public static function rule(): string
    {
        return 'JOMACAKE HOUSE STYLE (very important): this must look like a real handmade JOMACAKE cake that the bakery can actually make. '
            .'The cake is covered in buttercream with a soft, slightly uneven hand-spread spatula texture (not fondant, not glossy, not perfectly smooth). '
            .'Every drawing, figure, face, logo and piece of writing is hand-piped and spread with buttercream: simple flat colour areas spread with a small spatula, '
            .'outlined with thin piped lines, in a charming naive hand-drawn style with limited detail; people are drawn simply, often with blank faces. '
            .'Writing is piped by hand in cursive or rounded lettering. Borders, shells, rosettes and roses, when used, are piped buttercream. '
            .'NEVER an edible printed photo, never airbrushed or photo-realistic artwork, never printed logos or fondant cut-outs. '
            .'NOTHING EXTRA: do NOT add any drawing, figure, person, character, scene, text or decoration that the customer did not ask for '
            .'and that is not in the customer\'s reference photos. '
            .'ORIENTATION: the top design and writing must be upright and straight, readable from the front of the board (the side with the JOMACAKE label), '
            .'aligned parallel to the board\'s front edge, never rotated or diagonal. '
            .self::camera();
    }
}
