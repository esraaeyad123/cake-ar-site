<?php

namespace App\Http\Controllers;

use App\Models\Cake;
use App\Services\CakePricing;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * عينة تجريبية: الزبون يصف التصميم (ويرفع صور مرجعية)، الذكاء الاصطناعي يولّد
 * تصميم سطح الكيكة وتصميم جوانبها، والصفحة تطبعهم على الكيكة 3D للمعاينة والواقع المعزز.
 */
class AiCakeController extends Controller
{
    private const DIR = 'ai-cakes';

    private const SHAPES = ['round' => 'round', 'heart' => 'heart-shaped'];

    public function show(string $slug, CakePricing $pricing)
    {
        $cake = Cake::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $sizes = collect(config('cake_ar.layers'))
            ->filter(fn ($_, $key) => ! str_starts_with($key, 'tier'));

        return view('cake-ai', [
            'cake' => $cake,
            'sizes' => $sizes,
            'prices' => $pricing->startingPrices(),
            'hasKey' => filled(config('services.openai.key')),
        ]);
    }

    public function design(Request $request, string $slug)
    {
        Cake::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $data = $request->validate([
            'shape' => ['required', 'in:'.implode(',', array_keys(self::SHAPES))],
            'description' => ['required', 'string', 'max:500'],
            'references' => ['nullable', 'array', 'max:4'],
            'references.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:10240'],
        ]);

        if (blank(config('services.openai.key'))) {
            return response()->json(['message' => 'لا يوجد مفتاح OpenAI. أضيفي OPENAI_API_KEY في ملف .env ثم شغّلي php artisan config:clear'], 503);
        }

        // توليد صورتين بالتوازي قد يأخذ دقيقة أو أكثر
        set_time_limit(180);

        $shape = self::SHAPES[$data['shape']];
        $references = $request->file('references', []);
        $prompts = [
            'top' => $this->topPrompt($shape, $data['description']),
            'side' => $this->sidePrompt($data['description']),
        ];
        $sizes = ['top' => '1024x1024', 'side' => '1536x1024'];

        try {
            $responses = Http::pool(fn (Pool $pool) => collect($prompts)->map(
                fn (string $prompt, string $part) => $this->imageRequest($pool->as($part), $prompt, $sizes[$part], $references)
            )->all());
        } catch (ConnectionException) {
            return $this->failed('تعذر الوصول إلى OpenAI. جربي مرة ثانية.');
        }

        $id = now()->format('Ymd-His').'-'.Str::random(6);
        $urls = [];

        foreach (array_keys($prompts) as $part) {
            $response = $responses[$part];
            if (! $response instanceof Response) {
                return $this->failed('تعذر الوصول إلى OpenAI. جربي مرة ثانية.');
            }
            if ($response->failed()) {
                return $this->failed($this->friendlyError($response));
            }
            $image = $response->json('data.0.b64_json');
            if (! $image) {
                return $this->failed('ما رجعت صورة من الذكاء الاصطناعي. جربي مرة ثانية.');
            }

            $path = self::DIR."/{$id}-{$part}.png";
            Storage::disk('public')->put($path, base64_decode($image));
            $urls[$part] = Storage::disk('public')->url($path);
        }

        return response()->json(['top' => $urls['top'], 'side' => $urls['side']]);
    }

    /**
     * مع صور مرجعية: عنوان التعديل (يقبل عدة صور). بدونها: توليد من الصفر.
     */
    private function imageRequest($pending, string $prompt, string $size, array $references)
    {
        $pending = $pending->withToken(config('services.openai.key'))->acceptJson()->timeout(170);
        $fields = [
            'model' => config('services.openai.image_model'),
            'prompt' => $prompt,
            'size' => $size,
            'quality' => config('services.openai.image_quality'),
            'n' => 1,
        ];

        if (! $references) {
            return $pending->post('https://api.openai.com/v1/images/generations', $fields);
        }

        foreach ($references as $file) {
            $pending->attach('image[]', file_get_contents($file->getRealPath()), $file->getClientOriginalName(), ['Content-Type' => $file->getMimeType()]);
        }

        return $pending->post('https://api.openai.com/v1/images/edits', $fields);
    }

    private function topPrompt(string $shape, string $description): string
    {
        return "Flat printable design for the TOP surface of a {$shape} cake, seen straight from above. "
            .'Fill the whole square image edge to edge like an edible print sheet. The design will be cropped to the '
            ."{$shape} outline, so keep important elements and any text near the centre. "
            .'Where there is no artwork use a soft cream background (#F6EBD5). '
            .'No cake, no plate, no table, no perspective, no shadows, no photo of food — only the flat artwork. '
            .'If reference images are provided, use them as the main subject and style. '
            ."Customer request: {$description}";
    }

    private function sidePrompt(string $description): string
    {
        return 'Flat printable border design for the SIDE wall of a cake, a wide horizontal strip that wraps around the cake. '
            .'It must tile seamlessly: the left and right edges must continue into each other. '
            .'Keep it simple and decorative (patterns, small motifs), no large text. '
            .'Soft cream background (#F6EBD5). No cake, no perspective, no shadows — only the flat artwork. '
            ."It must match this customer request in colours and style: {$description}";
    }

    private function friendlyError(Response $response): string
    {
        $code = $response->json('error.code');
        $message = (string) $response->json('error.message');

        if ($code === 'moderation_blocked' || str_contains(strtolower($message), 'safety')) {
            return 'الذكاء الاصطناعي رفض هذا الطلب (غالبًا بسبب شخصية محمية بحقوق أو محتوى غير مسموح). جربي وصفًا مختلفًا.';
        }

        return "خطأ من OpenAI ({$response->status()}): {$message}";
    }

    private function failed(string $message)
    {
        return response()->json(['message' => $message], 422);
    }
}
