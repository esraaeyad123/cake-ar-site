<?php

namespace App\Http\Controllers;

use App\Services\ShopProducts;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * تعديل صورة منتج جوماكيك بالذكاء الاصطناعي: نفس الكيكة، والزبون يكتب وش يبي يغيّر
 * (لون، كتابة، إضافة…) ويقدر يرفع صور للي يبيه. مستقل عن الطلب الخاص (عداده وإعداداته لحاله).
 * نفس طريقة الطلب الخاص: رقم لكل توليد (job) والصفحة تسأل عن نتيجته إذا انقطع الطلب.
 */
class ShopAiController extends Controller
{
    private const DIR = 'ai-cakes';
    private const MAX_REFERENCES = 6;

    public function generate(Request $request, int $id, ShopProducts $shop)
    {
        $product = $shop->find($id);
        abort_unless($product, 404);

        $data = $request->validate([
            'note' => ['required', 'string', 'max:500'],
            'size' => ['required', 'integer'],
            'layers' => ['required', 'integer', 'between:1,4'],
            'references' => ['nullable', 'array', 'max:'.self::MAX_REFERENCES],
            'references.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:10240'],
            'job' => ['required', 'string', 'regex:/^[A-Za-z0-9-]{8,64}$/'],
        ], [
            'note.required' => 'اكتبي وش تبين تغيّرين في الكيكة.',
            'note.max' => 'الوصف طويل، الحد 500 حرف.',
            'references.max' => 'الحد '.self::MAX_REFERENCES.' صور.',
        ]);

        $size = collect($product['sizes'])->firstWhere('id', (int) $data['size']);
        if (! $size) {
            return response()->json(['message' => 'المقاس غير متاح لهذا المنتج.'], 422);
        }

        if ($this->remaining() === 0) {
            return response()->json(['message' => 'استخدمتِ كل محاولات التوليد ('.config('shop.ai_limit').').', 'remaining' => 0], 429);
        }

        if (blank(config('services.openai.key'))) {
            return response()->json(['message' => 'لا يوجد مفتاح OpenAI. أضيفي OPENAI_API_KEY في ملف .env ثم شغّلي php artisan config:clear'], 503);
        }

        $job = $data['job'];
        Cache::put($this->jobKey($job), ['status' => 'working', 'owner' => $this->visitor()], now()->addMinutes(15));
        ignore_user_abort(true);
        set_time_limit(330);

        try {
            // صورة المنتج من رابطه في رد المتجر (مو من الزبون)
            $photo = Http::timeout(20)->get($product['image'])->throw();
            $pending = Http::withToken(config('services.openai.key'))->acceptJson()->timeout(300)
                ->attach('image[]', $photo->body(), 'product.jpg', ['Content-Type' => $photo->header('Content-Type') ?: 'image/jpeg']);

            foreach ($request->file('references', []) as $file) {
                $pending->attach('image[]', file_get_contents($file->getRealPath()), $file->getClientOriginalName(), ['Content-Type' => $file->getMimeType()]);
            }

            $response = $pending->post('https://api.openai.com/v1/images/edits', [
                'model' => config('services.openai.image_model'),
                'prompt' => $this->prompt($data['note'], $size, (int) $data['layers'], (bool) $request->file('references')),
                'size' => '1024x1024',
                'quality' => config('shop.image_quality') ?: config('services.openai.image_quality'),
                'n' => 1,
            ]);
        } catch (ConnectionException) {
            return $this->finish($request, $job, 422, ['message' => 'تعذر الوصول إلى OpenAI. جربي مرة ثانية.']);
        } catch (\Illuminate\Http\Client\RequestException) {
            return $this->finish($request, $job, 422, ['message' => 'تعذر تحميل صورة المنتج من جوماكيك. جربي بعد شوي.']);
        }

        if ($response->failed()) {
            $code = $response->json('error.code');
            $message = (string) $response->json('error.message');

            return $this->finish($request, $job, 422, ['message' => $code === 'moderation_blocked' || str_contains(strtolower($message), 'safety')
                ? 'الذكاء الاصطناعي رفض هذا الطلب. غيّري الوصف أو الصور وجربي.'
                : "خطأ من OpenAI ({$response->status()}): {$message}"]);
        }

        $image = $response->json('data.0.b64_json');
        if (! $image) {
            return $this->finish($request, $job, 422, ['message' => 'ما رجعت صورة من الذكاء الاصطناعي. جربي مرة ثانية.']);
        }

        Cache::increment($this->usedKey());
        $path = self::DIR.'/'.now()->format('Ymd-His').'-'.Str::random(6)."-product-{$product['id']}.png";
        Storage::disk('public')->put($path, base64_decode($image));

        return $this->finish($request, $job, 200, ['image' => asset('storage/'.$path)]);
    }

    public function status(string $job)
    {
        $result = Cache::get($this->jobKey($job));

        if (! $result || $result['owner'] !== $this->visitor()) {
            return response()->json(['status' => 'missing', 'remaining' => $this->remaining()], 404);
        }

        return response()->json(collect($result)->except('owner')->all() + ['remaining' => $this->remaining()]);
    }

    private function prompt(string $note, array $size, int $layers, bool $withReferences): string
    {
        $width = round((int) $size['inch'] * 2.54);
        $height = $layers * ((int) $size['inch'] <= 4 ? 3 : 4);

        return 'Image 1 is a real cake from the JOMACAKE bakery. Edit THIS cake: keep the same cake, the same shape, '
            .'the same board with its JOMACAKE label, the same camera angle and background, and keep every part of the design '
            .'that the customer does not ask to change. '
            ."Real size: {$size['inch']} inches ({$width} cm) wide and about {$height} cm tall ({$layers} sponge layers). "
            .'Customer changes (apply them exactly; if they include words or a greeting to write, write them on the cake exactly as given, '
            ."in the same language): {$note}. "
            .($withReferences ? 'The other images are the customer\'s reference photos for the requested changes: take the elements, colours or style they ask for from them, but do NOT copy their cake shape. ' : '')
            .'Result: a realistic professional bakery photo of ONE cake. No people, no hands.';
    }

    private function finish(Request $request, string $job, int $status, array $payload)
    {
        if (defined('LARAVEL_START')) {
            Log::info('shop AI image', [
                'ok' => $status === 200,
                'seconds' => round(microtime(true) - LARAVEL_START),
                'quality' => config('shop.image_quality'),
                'references' => count($request->file('references', [])),
            ]);
        }

        Cache::put($this->jobKey($job), $payload + [
            'status' => $status === 200 ? 'done' : 'failed',
            'owner' => $this->visitor(),
        ], now()->addMinutes(15));

        return response()->json($payload + ['remaining' => $this->remaining()], $status);
    }

    private function jobKey(string $job): string
    {
        return "shop.ai_job.{$job}";
    }

    private function usedKey(): string
    {
        return 'shop.ai_used.'.$this->visitor();
    }

    private function visitor(): string
    {
        if (! session()->has('shop.visitor')) {
            session()->put('shop.visitor', (string) Str::uuid());
        }

        return session('shop.visitor');
    }

    // null = بدون حد
    public function remaining(): ?int
    {
        $limit = (int) config('shop.ai_limit');

        return $limit > 0 ? max(0, $limit - (int) Cache::get($this->usedKey(), 0)) : null;
    }
}
