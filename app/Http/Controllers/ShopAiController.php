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

    // الشكل المطلوب من فوق، والأشكال اللي لازم ما يطلع عليها (لو صورة المنتج شكلها مختلف)
    private const OUTLINES = [
        'round' => ['perfectly ROUND (a circle when seen from above)', 'NOT heart-shaped, NOT square, NOT oval'],
        'heart' => ['HEART-shaped when seen from above', 'NOT round, NOT square'],
        'star' => ['a FIVE-POINTED STAR when seen from above', 'NOT round, NOT heart-shaped'],
        'dome' => ['a smooth DOME (half-sphere), round when seen from above', 'NOT a flat-topped cylinder, NOT heart-shaped, with NO ears'],
        'cone' => ['a tall CONE tapering to a point', 'NOT a cylinder, NOT round-topped'],
        'sunset' => ['a HALF-CIRCLE standing upright on its flat edge', 'NOT a full round cake, NOT heart-shaped'],
    ];

    public function generate(Request $request, int $id, ShopProducts $shop)
    {
        $product = $shop->find($id);
        abort_unless($product, 404);

        $data = $request->validate([
            'shape' => ['required', 'in:'.implode(',', array_keys(self::OUTLINES))],
            'note' => ['nullable', 'string', 'max:500'],
            'writing' => ['nullable', 'string', 'max:40'],
            'shape_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'size' => ['required', 'integer'],
            'layers' => ['required', 'integer', 'between:1,4'],
            'references' => ['nullable', 'array', 'max:'.self::MAX_REFERENCES],
            'references.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:10240'],
            'job' => ['required', 'string', 'regex:/^[A-Za-z0-9-]{8,64}$/'],
        ], [
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

            // الترتيب: صورة المنتج (التصميم)، صورة المجسم (الشكل والمقاس)، صور الزبون (التعديل)
            foreach (array_filter([$request->file('shape_image'), ...$request->file('references', [])]) as $file) {
                $pending->attach('image[]', file_get_contents($file->getRealPath()), $file->getClientOriginalName(), ['Content-Type' => $file->getMimeType()]);
            }

            $response = $pending->post('https://api.openai.com/v1/images/edits', [
                'model' => config('services.openai.image_model'),
                'prompt' => $this->prompt($data, $size, (bool) $request->file('shape_image'), (bool) $request->file('references')),
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

    private function prompt(array $data, array $size, bool $withShapeImage, bool $withReferences): string
    {
        $layers = (int) $data['layers'];
        $width = round((int) $size['inch'] * 2.54);
        $height = $layers * ((int) $size['inch'] <= 4 ? 3 : 4);
        [$outline, $notShapes] = self::OUTLINES[$data['shape']];
        $refIndex = $withShapeImage ? 3 : 2;

        // نفس درس الطلب الخاص: الشكل شرط أول، وإلا ينسخ شكل صورة المنتج
        return "HARD RULE (most important): the cake must be {$outline}, with exactly ONE tier, {$notShapes}, "
            .'even if image 1 shows a different shape. '
            .'Image 1 is a real cake from the JOMACAKE bakery: copy its design onto the required shape: the frosting colours, '
            .'textures and every decoration (drawings, figures, piping, fruit…), re-arranged to fit the new shape. '
            .($withShapeImage ? 'Image 2 is a grey clay mock-up of the required cake: use it ONLY for the shape, outline, proportions and height; ignore its grey colour. ' : '')
            .($withReferences ? "Images {$refIndex} and after are the customer's reference photos for the requested changes: take the elements, colours or style they ask for, but do NOT copy their cake shape. " : '')
            ."Real size: {$size['inch']} inches ({$width} cm) wide and about {$height} cm tall ({$layers} sponge layers), clearly wider than tall. "
            .(filled($data['note'] ?? null) ? "Customer changes (apply them exactly): {$data['note']}. " : 'Keep the design of image 1 as it is. ')
            .(filled($data['writing'] ?? null) ? "Write exactly this text on the cake, in the same language: \"{$data['writing']}\". " : '')
            .'Put the cake on a SQUARE flat '.((int) $size['inch'] <= 4 ? 'very light pink' : 'off-white').' cake board (NOT round), '
            .'with a small white rectangular label with "JOMACAKE" in pink serif capital letters at the front-left corner. '
            .'A realistic professional bakery photo of ONE cake, front three-quarter view, plain light background. No people, no hands. '
            ."Remember: {$outline}.";
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
