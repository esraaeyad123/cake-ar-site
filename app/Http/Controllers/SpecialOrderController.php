<?php

namespace App\Http\Controllers;

use App\Services\CakePricing;
use App\Services\SpecialOrderOptions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * عينة تجريبية للطلب الخاص بتصميم المدير: الزبون يختار المواصفات (3D + AR + السعر)،
 * ويقدر يرفع صورة كيكة يحبها فيولّد الذكاء الاصطناعي صورتها بنفس المواصفات اللي اختارها.
 * زر "إرسال للمراجعة" يعرض الملخص فقط ولا يرسل شيئًا لموقع جوماكيك.
 *
 * التوليد ياخذ لين دقيقتين، والاستضافة ممكن تقطع الطلب قبلها. فكل توليد له رقم (job) تحفظ نتيجته
 * في الكاش، والصفحة تسأل عنها (status) إذا انقطع الطلب. والمحاولة ما تنحسب إلا إذا طلعت صورة.
 */
class SpecialOrderController extends Controller
{
    private const DIR = 'ai-cakes';

    private const MAX_REFERENCES = 6;

    private const SHAPES = [
        'round' => 'round',
        'heart' => 'heart-shaped',
        'cone' => 'tall cone-shaped (tapering to a point at the top)',
        'sunset' => 'sunset-shaped: a half-circle (semicircle) cake standing upright on its flat edge, like a setting sun, with the round face to the front',
        'wall' => 'wall cake: the FRONT HALF of a round tiered cake, cut vertically through the middle, with its flat back mounted on a square white cake board that stands upright and leans slightly back like a picture frame',
    ];

    public function show(CakePricing $pricing, SpecialOrderOptions $options)
    {
        return view('special-order', [
            'options' => $options->all(),
            'prices' => $pricing->startingPrices(),
            'layerLimits' => config('cake_ar.layers'),
            'tierPeople' => config('special_order.tier_people'),
            'shapes' => config('cake_ar.shapes'),
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
            'note' => ['nullable', 'string', 'max:500'],
            // صور الزبون (لين 6): الشكل والورد والكتابة... الصفحة تصغّرها قبل الرفع
            'references' => ['required', 'array', 'min:1', 'max:'.self::MAX_REFERENCES],
            'references.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:10240'],
            // صورة المجسم 3D بالمواصفات المختارة، تلتقطها الصفحة تلقائيًا عند الضغط على "ولّدي الصورة"
            'shape_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'job' => ['required', 'string', 'regex:/^[A-Za-z0-9-]{8,64}$/'],
        ], [
            'note.max' => 'الملاحظة طويلة، الحد 500 حرف.',
            'references.required' => 'اختاري صورة الكيكة أول.',
            'references.max' => 'الحد '.self::MAX_REFERENCES.' صور.',
        ]);

        // الأشكال الخاصة (السن ست) دور واحد بمقاساتها وطبقاتها فقط
        if ($special = config("cake_ar.shapes.{$data['shape']}")) {
            $request->validate([
                'size' => ['in:'.implode(',', $special['sizes'])],
                'layers' => ['in:'.$special['layers']],
            ]);
        }

        if ($this->remaining() === 0) {
            return response()->json(['message' => 'استخدمتِ كل محاولات التوليد ('.config('special_order.ai_limit').'). كمّلي الطلب بالوصف والصور.', 'remaining' => 0], 429);
        }

        if (blank(config('services.openai.key'))) {
            return response()->json(['message' => 'لا يوجد مفتاح OpenAI. أضيفي OPENAI_API_KEY في ملف .env ثم شغّلي php artisan config:clear'], 503);
        }

        $job = $data['job'];
        Cache::put($this->jobKey($job), ['status' => 'working', 'owner' => $this->visitor()], now()->addMinutes(15));

        // نكمّل حتى لو انقطع الاتصال مع الصفحة، عشان تقدر تاخذ النتيجة من status
        ignore_user_abort(true);
        set_time_limit(180);

        $shapeImage = $request->file('shape_image');
        try {
            $pending = Http::withToken(config('services.openai.key'))->acceptJson()->timeout(170);
            // الترتيب مهم: الصورة الأولى للشكل، والثانية للزينة
            foreach (array_filter([$shapeImage, ...$request->file('references')]) as $file) {
                $pending->attach('image[]', file_get_contents($file->getRealPath()), $file->getClientOriginalName(), ['Content-Type' => $file->getMimeType()]);
            }
            $response = $pending
                ->post('https://api.openai.com/v1/images/edits', [
                    'model' => config('services.openai.image_model'),
                    'prompt' => $this->prompt($data, (bool) $shapeImage),
                    'size' => '1024x1024',
                    'quality' => config('services.openai.image_quality'),
                    'n' => 1,
                ]);
        } catch (ConnectionException) {
            return $this->finish($request, $job, 422, ['message' => 'تعذر الوصول إلى OpenAI. جربي مرة ثانية.']);
        }

        if ($response->failed()) {
            $code = $response->json('error.code');
            $message = (string) $response->json('error.message');

            return $this->finish($request, $job, 422, ['message' => $code === 'moderation_blocked' || str_contains(strtolower($message), 'safety')
                ? 'الذكاء الاصطناعي رفض هذه الصورة. جربي صورة ثانية.'
                : "خطأ من OpenAI ({$response->status()}): {$message}"]);
        }

        $image = $response->json('data.0.b64_json');
        if (! $image) {
            return $this->finish($request, $job, 422, ['message' => 'ما رجعت صورة من الذكاء الاصطناعي. جربي مرة ثانية.']);
        }

        // المحاولة تنحسب فقط إذا رجعت صورة
        Cache::increment($this->usedKey());

        $path = self::DIR.'/'.now()->format('Ymd-His').'-'.Str::random(6).'-special.png';
        Storage::disk('public')->put($path, base64_decode($image));

        return $this->finish($request, $job, 200, ['image' => asset('storage/'.$path)]);
    }

    /**
     * نتيجة توليد سابق، للصفحة إذا انقطع طلب التوليد قبل ما يرجع
     */
    public function status(Request $request, string $job)
    {
        $result = Cache::get($this->jobKey($job));
        if (! $result || $result['owner'] !== $this->visitor()) {
            return response()->json(['status' => 'missing', 'remaining' => $this->remaining()], 404);
        }

        return response()->json(collect($result)->except('owner')->all() + ['remaining' => $this->remaining()]);
    }

    /**
     * "إرسال للمراجعة": في العينة يجدّد محاولات التوليد (بعد الربط بنظام الطلبات: لما ينطلب فعلًا)
     */
    public function review(Request $request)
    {
        Cache::forget($this->usedKey());

        return response()->json(['remaining' => $this->remaining()]);
    }

    private function finish(Request $request, string $job, int $status, array $payload)
    {
        Cache::put($this->jobKey($job), $payload + [
            'status' => $status === 200 ? 'done' : 'failed',
            'owner' => $this->visitor(),
        ], now()->addMinutes(15));

        return response()->json($payload + ['remaining' => $this->remaining()], $status);
    }

    private function jobKey(string $job): string
    {
        return "special_order.ai_job.{$job}";
    }

    // العداد في الكاش لكل زائر (مش في الجلسة) حتى ما تضيع الزيادة إذا انحفظت الجلسة من طلب ثاني بنفس الوقت
    private function usedKey(): string
    {
        return 'special_order.ai_used.'.$this->visitor();
    }

    // رقم ثابت للزائر في جلسته، ينكتب مرة وحدة
    private function visitor(): string
    {
        if (! session()->has('special_order.visitor')) {
            session()->put('special_order.visitor', (string) Str::uuid());
        }

        return session('special_order.visitor');
    }

    // null = بدون حد
    private function remaining(): ?int
    {
        $limit = (int) config('special_order.ai_limit');

        return $limit > 0 ? max(0, $limit - (int) Cache::get($this->usedKey(), 0)) : null;
    }

    private function prompt(array $data, bool $withShapeImage): string
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
            $structure = "half of a 2-tier cake: bottom tier {$inches[0]} inches (".round($inches[0] * 2.54)." cm) wide, top tier {$inches[1]} inches";
        }

        $height = match ($data['shape']) {
            'cone' => '',
            'sunset' => 'The half-circle is '."{$inches[0]} inches (".round($inches[0] * 2.54).' cm) wide and about '.($data['layers'] * 4 + 2).' cm thick from front to back. ',
            default => "Each tier has {$data['layers']} sponge layers, about ".($data['layers'] * 4 + 2).' cm tall. ',
        };

        $finish = $data['style'] === 'vintage' && config("cake_ar.shapes.{$data['shape']}.vintage", $data['shape'] !== 'cone')
            ? 'Use vintage (Lambeth) style piped cream borders on the top edge and the base. '
            : 'No vintage piped borders. ';

        $shape = self::SHAPES[$data['shape']];
        $note = filled($data['note'] ?? null) ? "Customer changes to apply: {$data['note']}. " : '';

        $specs = "Required cake: {$shape}; {$structure}. {$height}";

        $intro = $withShapeImage
            ? 'Image 1 is a plain 3D model of the EXACT cake the customer ordered: copy its shape, outline, number of tiers, '
                .'proportions and height exactly. The other images are the customer\'s reference photos: use them ONLY for the decoration, '
                .'frosting colours, toppings, flowers, figures and writing (combine them if there are several), and re-arrange them to fit the new shape. '
                .'Do NOT keep the shape or number of tiers of the reference photos. '
            : 'The attached photos are the customer\'s reference photos. Copy their decoration, frosting colours, toppings and writing '
                .'(combine them if there are several), but do NOT keep their shape: rebuild the cake with the required shape below. ';

        return $intro.'Create a realistic professional bakery photo of ONE cake. '
            .$specs.$finish.$note
            .'Show the whole cake on a simple cake board, front three-quarter view, on a plain light background. No people, no hands.';
    }

    private function failed(string $message)
    {
        return response()->json(['message' => $message], 422);
    }
}
