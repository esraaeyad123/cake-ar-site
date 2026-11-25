<?php

namespace App\Services;

/**
 * تعليمات قصيرة للصور (مثل طريقة المدير مع ChatGPT): مجسم الشكل + صورة الزبون + المواصفات الأساسية.
 * القديمة الطويلة باقية في الكنترولرات، وتشتغل لو AI_PROMPT_STYLE=strict
 */
class SimplePrompt
{
    public static function on(): bool
    {
        return config('cake_ar.prompt_style', 'simple') !== 'strict';
    }

    private static function finish(string $board, string $shape): string
    {
        return "Make it a real buttercream cake that a bakery can make, on {$board} with a small white \"JOMACAKE\" label (pink letters) at its front-left corner. "
            .'Realistic bakery photo from the front, slightly above, plain light background, no people or hands. '
            .'Do not write any words on the cake unless the customer asked for specific words. '
            ."Remember: the cake shape is {$shape}.";
    }

    /**
     * أول صورة. $specs مثل "cone-shaped, 1 tier, 6 inches, 2 sponge layers"
     */
    public static function generate(string $shape, string $specs, string $board, bool $withShapeImage, int $references, ?string $note, string $what = 'the customer\'s cake'): string
    {
        $refs = $references === 1 ? 'the next image' : "the next {$references} images";

        return ($withShapeImage ? 'Image 1 is a plain grey mock-up of the required cake: use it ONLY for the shape, size and proportions. ' : '')
            .'Recreate '.($withShapeImage ? $refs : 'the attached image').' ('.$what.') with the SAME design: the same colours, decorations and details. '
            ."Build that design onto this cake: {$specs}. "
            .(filled($note) ? "Customer request: {$note}. " : '')
            .self::finish($board, $shape);
    }

    public static function edit(string $shape, string $specs, string $board, string $instruction, array $previous, bool $withReferences): string
    {
        $previous = array_values(array_filter(array_map('trim', $previous)));

        return "Edit this cake photo. Change ONLY this: {$instruction}. "
            .($withReferences ? 'The other images show what the customer means; take only that from them. ' : '')
            .($previous ? 'Keep these earlier changes: '.implode('; ', $previous).'. ' : '')
            ."Keep everything else exactly the same: the cake ({$specs}), its decorations, the board, angle and background. "
            .self::finish($board, $shape);
    }
}
