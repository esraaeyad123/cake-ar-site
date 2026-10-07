<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * صفحة تجربة: توليد صور كيك من وصف نصي عبر OpenAI، أو تعديل صورة كيكة حقيقية مرفوعة.
 * كل صورة تُحفظ مع ملف JSON صغير فيه الوصف والجودة والوقت، حتى نقارن النتائج قبل اعتماد الميزة.
 */
class AiImageController extends Controller
{
    private const DIR = 'ai-images';

    private const QUALITIES = ['low', 'medium', 'high'];

    public function index()
    {
        return view('ai-images', [
            'history' => $this->history(),
            'qualities' => self::QUALITIES,
            'hasKey' => filled(config('services.openai.key')),
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:1000'],
            'quality' => ['required', 'in:'.implode(',', self::QUALITIES)],
            // اختياري: صورة كيكة حقيقية للتعديل عليها بدل التوليد من الصفر
            'base_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:20480'],
        ]);

        if (blank(config('services.openai.key'))) {
            return back()->withInput()->withErrors(['prompt' => 'لا يوجد مفتاح OpenAI. أضيفي OPENAI_API_KEY في ملف .env ثم شغّلي php artisan config:clear']);
        }

        // التوليد قد يأخذ دقيقة أو أكثر، والحد الافتراضي في PHP 30 ثانية
        set_time_limit(180);
        $started = microtime(true);

        $fields = [
            'model' => config('services.openai.image_model'),
            'prompt' => $validated['prompt'],
            'size' => '1024x1024',
            'quality' => $validated['quality'],
            'n' => 1,
        ];
        $base = $request->file('base_image');

        try {
            $client = Http::withToken(config('services.openai.key'))->acceptJson()->timeout(170);

            // مع صورة: نرسلها كملف (multipart) إلى عنوان التعديل. بدون صورة: توليد من الصفر.
            $response = $base
                ? $client->attach('image[]', file_get_contents($base->getRealPath()), $base->getClientOriginalName(), ['Content-Type' => $base->getMimeType()])
                    ->post('https://api.openai.com/v1/images/edits', $fields)
                : $client->post('https://api.openai.com/v1/images/generations', $fields);
        } catch (ConnectionException) {
            return back()->withInput()->withErrors(['prompt' => 'تعذر الوصول إلى OpenAI (انتهت المهلة أو مشكلة في الشبكة). جربي مرة ثانية.']);
        }

        if ($response->failed()) {
            $message = $response->json('error.message') ?? 'Unknown error from OpenAI.';

            return back()->withInput()->withErrors(['prompt' => "OpenAI error ({$response->status()}): {$message}"]);
        }

        $base64Image = $response->json('data.0.b64_json');

        if (! $base64Image) {
            return back()->withInput()->withErrors(['prompt' => 'OpenAI لم ترجع صورة. جربي مرة ثانية.']);
        }

        $name = self::DIR.'/'.now()->format('Ymd-His').'-'.Str::random(6);
        $disk = Storage::disk('public');
        $disk->put("{$name}.png", base64_decode($base64Image));
        $basePath = $base ? $base->storeAs(self::DIR, basename($name).'-original.'.$base->extension(), 'public') : null;
        $disk->put("{$name}.json", json_encode([
            'mode' => $base ? 'edit' : 'generate',
            'base' => $basePath,
            'prompt' => $validated['prompt'],
            'quality' => $validated['quality'],
            'model' => config('services.openai.image_model'),
            'seconds' => round(microtime(true) - $started, 1),
            'usage' => $response->json('usage'),
            'created_at' => now()->toDateTimeString(),
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return redirect()->route('ai-images.index')->with('latest', basename($name));
    }

    /**
     * آخر الصور المولّدة (الأحدث أولاً) مع بياناتها.
     */
    private function history(): array
    {
        $disk = Storage::disk('public');

        return collect($disk->files(self::DIR))
            ->filter(fn (string $file) => str_ends_with($file, '.json'))
            ->sortDesc()
            ->take(24)
            ->map(function (string $file) use ($disk) {
                $png = Str::replaceLast('.json', '.png', $file);

                if (! $disk->exists($png)) {
                    return null;
                }

                $meta = json_decode($disk->get($file), true);

                return $meta + [
                    'id' => basename($file, '.json'),
                    'url' => $disk->url($png),
                    'base_url' => ! empty($meta['base']) ? $disk->url($meta['base']) : null,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
