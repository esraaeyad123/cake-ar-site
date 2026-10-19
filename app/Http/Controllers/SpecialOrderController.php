<?php

namespace App\Http\Controllers;

use App\Services\CakePricing;
use App\Services\SpecialOrderOptions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * عينة تجريبية للطلب الخاص بتصميم المدير: الزبون يختار المواصفات (3D + AR + السعر)،
 * ويقدر يرفع صورة كيكة يحبها فيولّد الذكاء الاصطناعي صورتها بنفس المواصفات اللي اختارها.
 * زر "إرسال للمراجعة" يعرض الملخص فقط ولا يرسل شيئًا لموقع جوماكيك.
 */
class SpecialOrderController extends Controller
{
    private const DIR = 'ai-cakes';

    private const SHAPES = [
        'round' => 'round',
        'heart' => 'heart-shaped',
        'cone' => 'tall cone-shaped (tapering to a point at the top)',
        'sunset' => 'sunset-shaped: a half-circle (semicircle) cake standing upright on its flat edge, like a setting sun, with the round face to the front',
        'wall' => 'wall cake: a rectangular slab cake standing upright like a wall, decorated on its front face',
    ];

    public function show(CakePricing $pricing, SpecialOrderOptions $options)
    {
        return view('special-order', [
            'options' => $options->all(),
            'prices' => $pricing->startingPrices(),
            'layerLimits' => config('cake_ar.layers'),
            'tierPeople' => config('special_order.tier_people'),
            'shapes' => config('special_order.shapes'),
            'aiRemaining' => $this->remaining(),
            'hasKey' => filled(config('services.openai.key')),
        ]);
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'shape' => ['required', 'in:'.implode(',', array_keys(self::SHAPES))],
            'size' => ['required', 'in:'.implode(',', array_keys(config('cake_ar.layers')))],
            'layers' => ['required', 'integer', 'between:1,4'],
            'style' => ['required', 'in:plain,vintage'],
            'note' => ['nullable', 'string', 'max:300'],
            'reference' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:10240'],
        ]);

        // الأشكال الخاصة (السن ست) دور واحد بمقاساتها وطبقاتها فقط
        if ($special = config("special_order.shapes.{$data['shape']}")) {
            $request->validate([
                'size' => ['in:'.implode(',', array_map(fn ($inch) => "{$inch}in", $special['sizes']))],
                'layers' => ['in:'.$special['layers']],
            ]);
        }

        if ($this->remaining() <= 0) {
            return response()->json(['message' => 'استخدمتِ كل محاولات التوليد ('.config('special_order.ai_limit').'). كمّلي الطلب بالوصف والصور.', 'remaining' => 0], 429);
        }

        if (blank(config('services.openai.key'))) {
            return response()->json(['message' => 'لا يوجد مفتاح OpenAI. أضيفي OPENAI_API_KEY في ملف .env ثم شغّلي php artisan config:clear'], 503);
        }

        set_time_limit(180);

        $file = $request->file('reference');
        try {
            $response = Http::withToken(config('services.openai.key'))
                ->acceptJson()
                ->timeout(170)
                ->attach('image[]', file_get_contents($file->getRealPath()), $file->getClientOriginalName(), ['Content-Type' => $file->getMimeType()])
                ->post('https://api.openai.com/v1/images/edits', [
                    'model' => config('services.openai.image_model'),
                    'prompt' => $this->prompt($data),
                    'size' => '1024x1024',
                    'quality' => config('services.openai.image_quality'),
                    'n' => 1,
                ]);
        } catch (ConnectionException) {
            return $this->failed('تعذر الوصول إلى OpenAI. جربي مرة ثانية.');
        }

        if ($response->failed()) {
            $code = $response->json('error.code');
            $message = (string) $response->json('error.message');

            return $this->failed($code === 'moderation_blocked' || str_contains(strtolower($message), 'safety')
                ? 'الذكاء الاصطناعي رفض هذه الصورة. جربي صورة ثانية.'
                : "خطأ من OpenAI ({$response->status()}): {$message}");
        }

        $image = $response->json('data.0.b64_json');
        if (! $image) {
            return $this->failed('ما رجعت صورة من الذكاء الاصطناعي. جربي مرة ثانية.');
        }

        // المحاولة تنحسب فقط إذا رجعت صورة
        $request->session()->increment('special_order.ai_used');

        $path = self::DIR.'/'.now()->format('Ymd-His').'-'.Str::random(6).'-special.png';
        Storage::disk('public')->put($path, base64_decode($image));

        return response()->json(['image' => asset('storage/'.$path), 'remaining' => $this->remaining()]);
    }

    private function remaining(): int
    {
        return max(0, config('special_order.ai_limit') - (int) session('special_order.ai_used', 0));
    }

    private function prompt(array $data): string
    {
        // "tier2_8-6" => [8, 6]، "6in" => [6]
        $inches = str_starts_with($data['size'], 'tier')
            ? array_map('intval', explode('-', substr($data['size'], strpos($data['size'], '_') + 1)))
            : [(int) $data['size']];

        $structure = count($inches) === 1
            ? "a single-tier cake, {$inches[0]} inches (".round($inches[0] * 2.54).' cm) in diameter'
            : count($inches).'-tier stacked cake, tiers from bottom to top: '
                .implode(', ', array_map(fn ($i) => "{$i} inches", $inches));

        if ($data['shape'] === 'wall') {
            $structure = "2 stacked wall tiers: the bottom wall is {$inches[0]} inches (".round($inches[0] * 2.54).' cm) wide and the top wall is narrower and shorter';
        }

        $part = $data['shape'] === 'wall' ? 'bottom wall' : 'half-circle';
        $height = match ($data['shape']) {
            'cone' => '',
            'sunset', 'wall' => "The {$part} is {$inches[0]} inches (".round($inches[0] * 2.54).' cm) wide and about '.($data['layers'] * 4 + 2).' cm thick from front to back. ',
            default => "Each tier has {$data['layers']} sponge layers, about ".($data['layers'] * 4 + 2).' cm tall. ',
        };

        $finish = $data['style'] === 'vintage' && ! config("special_order.shapes.{$data['shape']}")
            ? 'Use vintage (Lambeth) style piped cream borders on the top edge and the base. '
            : 'Keep the sides smooth (plain cream finish), apart from the reference decorations. ';

        $shape = self::SHAPES[$data['shape']];
        $note = filled($data['note'] ?? null) ? "Customer changes to apply: {$data['note']}. " : '';

        return 'The attached photo is the customer\'s reference cake. Create a realistic professional bakery photo of ONE cake that copies '
            .'the reference cake\'s decoration, colours, toppings, piping and writing as closely as possible, '
            ."but rebuilt with these exact specifications: shape {$shape}; {$structure}. "
            .$height.$finish.$note
            .'Show the whole cake on a simple round cake board, front three-quarter view, on a plain light background. No people, no hands.';
    }

    private function failed(string $message)
    {
        return response()->json(['message' => $message], 422);
    }
}
