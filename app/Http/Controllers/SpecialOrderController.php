<?php

namespace App\Http\Controllers;

use App\Services\AiCakeChat;
use App\Services\CakePricing;
use App\Services\SpecialOrderOptions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

    // الشكل من فوق، والأشكال اللي لازم ما يطلع عليها (لو صورة الزبون شكلها مختلف)
    private const OUTLINES = [
        'round' => ['perfectly ROUND (a circle when seen from above)', 'NOT heart-shaped, NOT square, NOT oval'],
        'heart' => ['HEART-shaped when seen from above', 'NOT round, NOT square'],
        'star' => ['a FIVE-POINTED STAR when seen from above', 'NOT round, NOT heart-shaped'],
        'dome' => ['a smooth DOME (half-sphere), round when seen from above', 'NOT a flat-topped cylinder, NOT heart-shaped, with NO ears'],
        'cone' => ['a tall CONE tapering to a point', 'NOT a cylinder, NOT round-topped'],
        'sunset' => ['a HALF-CIRCLE standing upright on its flat edge', 'NOT a full round cake, NOT heart-shaped'],
        'wall' => ['the FRONT HALF of a round tiered cake mounted on a standing board', 'NOT a full free-standing cake, NOT heart-shaped'],
    ];

    private const SHAPES = [
        'round' => 'round',
        'heart' => 'heart-shaped',
        'star' => 'five-pointed star-shaped (with softly rounded points)',
        'dome' => 'dome-shaped: a smooth half-sphere, round when seen from above, with no ears, face or figure shapes',
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
                'layers' => ['in:'.implode(',', (array) $special['layers'])],
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
        // الجودة العالية ممكن تاخذ 4-5 دقايق
        set_time_limit(330);

        $shapeImage = $request->file('shape_image');
        try {
            $pending = Http::withToken(config('services.openai.key'))->acceptJson()->timeout(300);
            // الترتيب مهم: الصورة الأولى للشكل، والثانية للزينة
            foreach (array_filter([$shapeImage, ...$request->file('references')]) as $file) {
                $pending->attach('image[]', file_get_contents($file->getRealPath()), $file->getClientOriginalName(), ['Content-Type' => $file->getMimeType()]);
            }
            $response = $pending
                ->post('https://api.openai.com/v1/images/edits', [
                    'model' => config('services.openai.image_model'),
                    'prompt' => $this->prompt($data, (bool) $shapeImage),
                    'size' => '1024x1024',
                    'quality' => config('special_order.image_quality') ?: config('services.openai.image_quality'),
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
        $this->rememberImage($path);

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
        Cache::forget($this->chatKey());

        return response()->json(['remaining' => $this->remaining()]);
    }

    /**
     * المحادثة بعد أول صورة: موديل نصي يرد على الزبون بالعربي، ويقرر: يسأل سؤال، أو يرفض
     * (لو الطلب يغيّر المواصفات الثابتة)، أو يحوّل الطلب لتعديل بالإنجليزي على آخر صورة.
     * ما يولّد صور بنفسه: الصفحة ترسل التعديل لـ edit بعدين (التوليد ياخذ دقايق)
     */
    public function chat(Request $request)
    {
        $data = $request->validate($this->specRules() + [
            'messages' => ['required', 'array', 'min:1', 'max:20'],
            'messages.*.role' => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:500'],
        ], [
            'messages.*.content.max' => 'الرسالة طويلة، الحد 500 حرف.',
        ]);
        $this->validateSpecialShape($request, $data);

        if (blank(config('services.openai.key'))) {
            return response()->json(['message' => 'لا يوجد مفتاح OpenAI. أضيفي OPENAI_API_KEY في ملف .env ثم شغّلي php artisan config:clear'], 503);
        }

        $limit = (int) config('special_order.chat_limit');
        if ($limit > 0 && (int) Cache::get($this->chatKey(), 0) >= $limit) {
            return response()->json(['message' => "وصلتِ لحد الرسائل ({$limit}). كمّلي الطلب بالتصميم اللي اخترتيه."], 429);
        }

        try {
            $answer = app(AiCakeChat::class)->reply($this->chatSpecs($data), $data['messages']);
        } catch (ConnectionException) {
            return response()->json(['message' => 'تعذر الوصول إلى OpenAI. جربي مرة ثانية.'], 422);
        }

        if (! $answer) {
            return response()->json(['message' => 'ما قدر الذكاء الاصطناعي يرد الحين. جربي مرة ثانية.'], 422);
        }

        Cache::increment($this->chatKey());

        return response()->json($answer);
    }

    /**
     * تعديل على صورة طلعت قبل لنفس الزائر، بالمواصفات الثابتة (الشكل، الأدوار، الحجم، البورد)
     */
    public function edit(Request $request)
    {
        $data = $request->validate($this->specRules() + [
            'image' => ['required', 'string', 'regex:/^[A-Za-z0-9-]+-special\.png$/'],
            'instruction' => ['required', 'string', 'max:1000'],
            'previous' => ['nullable', 'array', 'max:12'],
            'previous.*' => ['string', 'max:1000'],
            'job' => ['required', 'string', 'regex:/^[A-Za-z0-9-]{8,64}$/'],
        ]);
        $this->validateSpecialShape($request, $data);

        $path = self::DIR.'/'.$data['image'];
        if (! in_array($path, Cache::get($this->imagesKey(), []), true) || ! Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'الصورة مو موجودة. ولّدي صورة جديدة.'], 422);
        }

        if ($this->remaining() === 0) {
            return response()->json(['message' => 'استخدمتِ كل محاولات التوليد ('.config('special_order.ai_limit').'). كمّلي الطلب بالتصميم اللي اخترتيه.', 'remaining' => 0], 429);
        }

        if (blank(config('services.openai.key'))) {
            return response()->json(['message' => 'لا يوجد مفتاح OpenAI. أضيفي OPENAI_API_KEY في ملف .env ثم شغّلي php artisan config:clear'], 503);
        }

        $job = $data['job'];
        Cache::put($this->jobKey($job), ['status' => 'working', 'owner' => $this->visitor()], now()->addMinutes(15));
        ignore_user_abort(true);
        set_time_limit(330);

        try {
            $response = Http::withToken(config('services.openai.key'))->acceptJson()->timeout(300)
                ->attach('image[]', Storage::disk('public')->get($path), 'current.png', ['Content-Type' => 'image/png'])
                ->post('https://api.openai.com/v1/images/edits', [
                    'model' => config('services.openai.image_model'),
                    'prompt' => $this->editPrompt($data),
                    'size' => '1024x1024',
                    'quality' => config('special_order.image_quality') ?: config('services.openai.image_quality'),
                    'n' => 1,
                ]);
        } catch (ConnectionException) {
            return $this->finish($request, $job, 422, ['message' => 'تعذر الوصول إلى OpenAI. جربي مرة ثانية.']);
        }

        if ($response->failed() || ! ($image = $response->json('data.0.b64_json'))) {
            $code = $response->json('error.code');
            $message = (string) $response->json('error.message');
            Log::warning('special-order AI edit failed', ['code' => $code, 'message' => $message]);

            return $this->finish($request, $job, 422, ['message' => $code === 'moderation_blocked' || str_contains(strtolower($message), 'safety')
                ? 'الذكاء الاصطناعي رفض هذا التعديل. جربي تكتبينه بطريقة ثانية.'
                : 'ما طلع التعديل. جربي مرة ثانية.']);
        }

        Cache::increment($this->usedKey());
        $newPath = self::DIR.'/'.now()->format('Ymd-His').'-'.Str::random(6).'-special.png';
        Storage::disk('public')->put($newPath, base64_decode($image));
        $this->rememberImage($newPath);

        return $this->finish($request, $job, 200, ['image' => asset('storage/'.$newPath)]);
    }

    private function specRules(): array
    {
        return [
            'shape' => ['required', 'in:'.implode(',', array_keys(self::SHAPES))],
            'size' => ['required', 'in:'.implode(',', array_keys(config('cake_ar.layers')))],
            'layers' => ['required', 'integer', 'between:1,4'],
            'style' => ['required', 'in:plain,vintage'],
        ];
    }

    // الأشكال الخاصة (السن ست، القبة…) بمقاساتها وطبقاتها فقط
    private function validateSpecialShape(Request $request, array $data): void
    {
        if ($special = config("cake_ar.shapes.{$data['shape']}")) {
            $request->validate([
                'size' => ['in:'.implode(',', $special['sizes'])],
                'layers' => ['in:'.implode(',', (array) $special['layers'])],
            ]);
        }
    }

    /**
     * المواصفات الثابتة بالإنجليزي (للمحادثة والتعديل)
     */
    private function fixedSpecs(array $data): array
    {
        $inches = str_starts_with($data['size'], 'tier')
            ? array_map('intval', explode('-', substr($data['size'], strpos($data['size'], '_') + 1)))
            : [(int) $data['size']];
        $tiers = $data['shape'] === 'wall' ? 2 : count($inches);
        [$outline, $notShapes] = self::OUTLINES[$data['shape']];

        return [
            'outline' => $outline,
            'not' => $notShapes,
            'tiers' => $tiers === 1 ? 'exactly ONE tier' : "exactly {$tiers} tiers",
            'size' => implode('/', $inches).' inches',
            'height' => $data['shape'] === 'cone' || $data['shape'] === 'sunset' ? '' : $this->sizeText($inches, (int) $data['layers'], $data['shape']),
        ];
    }

    private function chatSpecs(array $data): string
    {
        $f = $this->fixedSpecs($data);

        return "shape {$f['outline']}; {$f['tiers']}; size {$f['size']}; {$data['layers']} sponge layers; "
            .($data['style'] === 'vintage' ? 'vintage piped borders' : 'plain style with no vintage piped borders')
            .'; a square JOMACAKE board with its logo label.';
    }

    private function editPrompt(array $data): string
    {
        $f = $this->fixedSpecs($data);

        return "HARD RULE (most important): the cake must stay {$f['outline']}, with {$f['tiers']}, {$f['not']}. "
            .'This image is the customer\'s current cake design from JOMACAKE bakery. Apply ONLY these new changes: '.$data['instruction'].' '
            .$this->keepText($data['previous'] ?? [])
            .'Keep everything else exactly the same: the same cake shape, size and proportions, the same number of tiers and layers, '
            .'the same square board with the JOMACAKE label, the same camera angle, lighting and background. '
            .$f['height']
            .'A realistic professional bakery photo of ONE cake. No people, no hands. '
            ."Remember: {$f['outline']}, {$f['tiers']}.";
    }

    /**
     * التعديلات السابقة اللي طلعت منها الصورة الحالية، وقاعدة إن أي شي ما انطلب ما يتغير
     */
    private function keepText(array $previous): string
    {
        $previous = array_values(array_filter(array_map('trim', $previous)));
        $list = $previous
            ? 'This image already includes these earlier customer changes, and ALL of them must stay exactly as they are now: '
                .implode(' ', array_map(fn ($p, $i) => ($i + 1).') '.$p, $previous, array_keys($previous))).' '
            : '';

        return $list.'Do not add, remove or move anything that is not mentioned in the new changes: keep the exact NUMBER, position, '
            .'colour, size and facing direction of every element (for example the number of balloons and which way each figure faces). ';
    }

    private function chatKey(): string
    {
        return 'special_order.ai_chat.'.$this->visitor();
    }

    private function imagesKey(): string
    {
        return 'special_order.ai_images.'.$this->visitor();
    }

    private function rememberImage(string $path): void
    {
        Cache::put($this->imagesKey(), [...array_slice(Cache::get($this->imagesKey(), []), -19), $path], now()->addHours(12));
    }

    private function finish(Request $request, string $job, int $status, array $payload)
    {
        // كم أخذ التوليد وبأي جودة، عشان نعرف وين البطء (storage/logs/laravel.log)
        if (defined('LARAVEL_START')) {
            Log::info('special-order AI image', [
                'ok' => $status === 200,
                'seconds' => round(microtime(true) - LARAVEL_START),
                'quality' => config('special_order.image_quality') ?: config('services.openai.image_quality'),
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
            'sunset' => 'The half-circle is '."{$inches[0]} inches (".round($inches[0] * 2.54).' cm) wide and about '.($data['layers'] * 4).' cm thick from front to back. ',
            default => $this->sizeText($inches, $data['layers'], $data['shape']),
        };

        $finish = $data['style'] === 'vintage' && config("cake_ar.shapes.{$data['shape']}.vintage", $data['shape'] !== 'cone')
            ? 'Use vintage (Lambeth) style piped cream borders on the top edge and the base. '
            : 'No vintage piped borders. ';

        $shape = self::SHAPES[$data['shape']];
        // خانة "الوصف": تعديلات الزبون، وممكن فيها عبارة يبيها مكتوبة على الكيكة (مثل: كل عام وانتم بخير)
        $note = filled($data['note'] ?? null)
            ? "Customer description (apply it; if it contains a greeting or words to write, write them on the cake exactly as given): {$data['note']}. "
            : '';

        $specs = "Required cake: {$shape}; {$structure}. {$height}";

        // الشكل شرط أول وثابت: لما نطلب نسخ الزينة "بالضبط" كان ينسخ شكل صورة الزبون (قلب بدل دائري)
        [$outline, $notShapes] = self::OUTLINES[$data['shape']];
        $tiers = $data['shape'] === 'wall' ? 2 : count($inches);
        $tierText = $tiers === 1 ? 'exactly ONE tier' : "exactly {$tiers} tiers";
        $rule = "HARD RULE (most important): the cake must be {$outline}, with {$tierText}, {$notShapes}, "
            .'even if the customer\'s reference photos show a different shape or number of tiers. '
            .'Never copy the outline or tier count of the reference photos. ';

        $intro = $withShapeImage
            ? 'Image 1 is a grey clay mock-up of this exact required cake: use it ONLY for the shape, outline, number of tiers, proportions and height. '
                .'Its grey colour and smooth surface are NOT part of the design: ignore them completely (keep only its square board shape). '
            : '';
        $intro .= 'The '.($withShapeImage ? 'other images are' : 'attached photos are').' the customer\'s reference photos: take the frosting colours, '
            .'surface patterns and textures and every decorative element from them (for example cherries, ribbons, piping, flowers, figures and writing), '
            .'combine them if there are several, and re-arrange them on the required shape, covering the cake as much as the reference does '
            .'(if the reference sides and top are covered, cover the sides and top). ';

        return $rule.$intro.'Create a realistic professional bakery photo of ONE cake. '
            .$specs.$finish.$note
            .$this->boardText($data)
            .'Show the whole cake and board, front three-quarter view, on a plain light background. No people, no hands. '
            ."Remember: {$outline}, {$tierText}.";
    }

    /**
     * المقاس الحقيقي بالسنتيمتر (نفس النموذج 3D: الطبقة 4 سم، و3 سم في 4 إنش)، حتى ما تطلع الكيكة
     * أكبر أو أطول من المختار. الكيكة الصغيرة أعرض من طولها
     */
    private function sizeText(array $inches, int $layers, string $shape): string
    {
        $width = round($inches[0] * 2.54);
        $height = $layers * ($inches[0] <= 4 ? 3 : 4);
        if ($shape === 'dome') {
            // نفس المجسم: القبة ما تطول أكثر من نص قطرها
            $height = min($height, (int) round($width / 2));
        }
        $people = ['4' => 'a small mini cake for 1-2 people', '6' => 'a small cake for 4-6 people', '8' => 'a medium cake for 8-10 people', '10' => 'a large cake for 12-15 people'][$inches[0]] ?? 'a cake';
        $tiers = count($inches) > 1 ? 'Each tier is' : 'The cake is';

        return "Real size: {$people}. {$tiers} {$height} cm tall ({$layers} sponge layers); the bottom tier is {$width} cm wide, "
            .'so the cake is clearly WIDER THAN IT IS TALL, with the same proportions as image 1. Do not make it taller or bigger. ';
    }

    /**
     * بورد جوماكيك: مربع (مو دائري)، وردي فاتح لـ 4 إنش وأبيض مكسور لغيره، وعليه شعار JOMACAKE
     */
    private function boardText(array $data): string
    {
        $logo = 'a small white rectangular label with "JOMACAKE" in pink serif capital letters';
        if ($data['shape'] === 'wall') {
            return "The square white board stands upright behind the cake, with {$logo} at its bottom-left corner. ";
        }
        $colour = $data['size'] === '4in' ? 'very light pink' : 'off-white';

        return "Put the cake on a SQUARE flat {$colour} cake board (NOT round), about 1.4 times wider than the bottom tier, "
            ."with {$logo} at the front-left corner of the board. ";
    }

    private function failed(string $message)
    {
        return response()->json(['message' => $message], 422);
    }
}
