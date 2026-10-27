<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopTest extends TestCase
{
    private const API = 'https://jomacake.com/api/customer/v1';

    private function discount(int $id, int $amount, array $sizes, string $from, ?string $to, array $stores = [['id' => 1]]): array
    {
        return ['id' => $id, 'amount' => (string) $amount, 'type' => 'percent', 'is_active' => true, 'from_date' => $from, 'to_date' => $to,
            'sizes' => array_map(fn ($s) => ['id' => $s], $sizes), 'stores' => $stores];
    }

    private function variation(int $sizeId, string $inch, int $price, int $layerPrice, int $sort, string $minLayer = '2 layer'): array
    {
        return ['size_id' => $sizeId, 'price' => $price, 'layer_price' => $layerPrice, 'is_active' => true, 'sort_order' => $sort,
            'size' => ['id' => $sizeId, 'name' => "{$inch} Inch"], 'min_layer' => ['name' => $minLayer]];
    }

    /** مختصر من رد shop?store_id=1&category_id[0]=28 الحقيقي */
    private function fakeShop(): void
    {
        $discounts = [
            $this->discount(17, 10, [2], '2026-08-23T21:00:00.000000Z', null),
            $this->discount(18, 25, [4, 9, 10], '2026-08-23T21:00:00.000000Z', null),
            // خصم منتهي (مايو) ما ينحسب
            $this->discount(15, 15, [4, 9, 10], '2026-05-11T21:00:00.000000Z', '2026-05-25T20:59:59.000000Z', []),
        ];
        $flavors = [
            ['id' => 66, 'title_ar' => 'شوكولاته', 'title_en' => 'chocolate', 'price' => '0.00', 'sliced_image_url' => 'https://jomacake.com/public/s.png'],
            ['id' => 12, 'title_ar' => 'فانيلا', 'title_en' => 'vanilla', 'price' => '5.00', 'sliced_image_url' => null],
        ];
        $sizes = [
            ['id' => 2, 'people_number' => '1-2 people'], ['id' => 4, 'people_number' => '4-6 people'],
            ['id' => 9, 'people_number' => '8-10 people'],
        ];

        Http::fake([self::API.'/shop*' => Http::response(['cakes' => ['data' => [
            ['id' => 1454, 'enabled' => '1', 'is_active' => '1', 'name_ar' => 'كيكة سبايدر مان', 'image_url' => 'https://jomacake.com/api/public/products/1454.jpg',
                'require_text_writing' => true, 'flavors' => $flavors,
                'stuffings' => [['id' => 38, 'title_ar' => 'ويب كريم مع نوتيلا', 'title_en' => 'Nutella']],
                'sizes' => $sizes, 'variations' => [$this->variation(2, '4', 76, 0, 0)], 'discounts' => $discounts],
            ['id' => 1305, 'enabled' => '1', 'is_active' => '1', 'name_ar' => null, 'image_url' => 'https://jomacake.com/api/public/products/1305.jpg',
                'require_text_writing' => true, 'flavors' => $flavors,
                'stuffings' => [['id' => 313, 'title_ar' => 'ويب كريم مع زبدة الفستق', 'title_en' => 'Pistachio']],
                'sizes' => $sizes, 'variations' => [$this->variation(9, '8', 235, 25, 1), $this->variation(4, '6', 155, 20, 0)], 'discounts' => $discounts],
        ]]])]);
    }

    public function test_products_grid_shows_discounted_prices_and_serial_numbers(): void
    {
        $this->fakeShop();

        $this->get('/products')
            ->assertOk()
            ->assertSee('كيكة سبايدر مان')
            ->assertSee('S.N: 1305')
            ->assertSee('https://jomacake.com/api/public/products/1305.jpg')
            // 76 بخصم 10% = 68، و 155 (أول مقاس 6 إنش) بخصم 25% = 116، والخصم المنتهي ما ينحسب
            ->assertSeeInOrder(['68', '76'])
            ->assertSeeInOrder(['116', '155'])
            ->assertSee('خصم 25%')
            ->assertDontSee('خصم 15%')
            ->assertSee(route('shop.show', 1305));
    }

    public function test_product_page_has_sizes_layers_flavors_and_stuffings(): void
    {
        $this->fakeShop();

        $this->get('/products/1305')
            ->assertOk()
            ->assertSee('>الطلب<', false)
            ->assertSee('شاهد على طاولتك')
            ->assertDontSee('اكتب على الكعكة')
            ->assertDontSee('شوفيها 3D')
            ->assertSeeInOrder(['data-tab="0"', 'الشكل', 'data-tab="1"', 'الحجم', 'data-tab="2"', 'التعديل', 'data-tab="3"', 'النكهة', 'data-tab="4"', 'الطلب'], false)
            ->assertSee('S.N: 1305')
            // المقاسات بترتيب الموقع، وسعر الطبقة الزيادة
            ->assertSee('"sizes":[{"id":4,"inch":"6","people":"4-6","price":155,"min_layers":2,"max_layers":3,"layer_price":20,"discount":{"type":"percent","amount":25}}', false)
            ->assertSee('"extra":5', false)
            ->assertSee(json_encode('ويب كريم مع زبدة الفستق'), false)
            ->assertDontSee('إرسال للمراجعة');
    }

    public function test_unknown_product_is_404(): void
    {
        $this->fakeShop();

        $this->get('/products/999')->assertNotFound();
    }

    public function test_api_failure_shows_message_instead_of_products(): void
    {
        Http::fake([self::API.'/shop*' => Http::response('down', 500)]);

        $this->get('/products')->assertOk()->assertSee('المتجر مو متاح الحين');
    }

    private function fakeOpenAi(): void
    {
        Http::fake([
            'https://jomacake.com/api/public/products/*' => Http::response('jpeg-bytes', 200, ['Content-Type' => 'image/jpeg']),
            'https://api.openai.com/*' => Http::response(['data' => [['b64_json' => base64_encode('png')]]]),
        ]);
    }

    public function test_ai_edits_the_product_photo_with_the_customer_changes(): void
    {
        Storage::fake('public');
        config(['services.openai.key' => 'test-key', 'shop.ai_limit' => 5]);
        $this->fakeShop();
        $this->fakeOpenAi();

        $this->post('/products/1305/ai', [
            'shape' => 'heart',
            'note' => 'خلي اللون وردي',
            'writing' => 'Happy Birthday Sara',
            'size' => 9,
            'shape_image' => UploadedFile::fake()->image('shape.png'),
            'layers' => 3,
            'references' => [UploadedFile::fake()->image('pink.jpg')],
            'job' => 'job-12345678',
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('remaining', 4)
            ->assertJsonStructure(['image']);

        // صورة المنتج أول، وبعدها صور الزبون، والوصف والمقاس الحقيقي في الأمر
        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://api.openai.com/v1/images/edits') {
                return false;
            }
            $parts = collect($request->data());
            $prompt = $parts->firstWhere('name', 'prompt')['contents'];

            return $parts->where('name', 'image[]')->count() === 3
                && $parts->firstWhere('name', 'image[]')['contents'] === 'jpeg-bytes'
                && str_contains($prompt, 'Happy Birthday Sara')
                && str_contains($prompt, 'HEART-shaped')
                && str_contains($prompt, 'خلي اللون وردي')
                && str_contains($prompt, '8 inches (20 cm)')
                && $parts->firstWhere('name', 'quality')['contents'] === 'medium';
        });

        $this->getJson('/products/ai/job-12345678')->assertOk()->assertJsonPath('status', 'done');
    }

    public function test_ai_needs_a_shape_and_respects_the_limit(): void
    {
        config(['services.openai.key' => 'test-key', 'shop.ai_limit' => 1]);
        $this->fakeShop();
        $this->fakeOpenAi();
        Storage::fake('public');
        $this->withoutMiddleware(ThrottleRequests::class);

        $this->postJson('/products/1305/ai', ['shape' => 'square', 'size' => 9, 'layers' => 2, 'job' => 'job-aaaaaaaa'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('shape');

        $this->postJson('/products/1305/ai', ['shape' => 'round', 'size' => 9, 'layers' => 2, 'job' => 'job-bbbbbbbb'])->assertOk();
        $this->postJson('/products/1305/ai', ['shape' => 'round', 'note' => 'أزرق', 'size' => 9, 'layers' => 2, 'job' => 'job-cccccccc'])->assertStatus(429);
    }

    public function test_product_chat_knows_the_fixed_specs_and_edit_keeps_the_shape(): void
    {
        Storage::fake('public');
        config(['services.openai.key' => 'test-key', 'shop.ai_limit' => 5]);
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->fakeShop();
        Http::fake([
            'https://jomacake.com/api/public/products/*' => Http::response('jpeg-bytes', 200, ['Content-Type' => 'image/jpeg']),
            'https://api.openai.com/v1/chat/completions' => Http::response(['choices' => [['message' => ['content' => json_encode(
                ['reply' => 'تمام، بخليها ذهبي', 'action' => 'edit', 'instruction' => 'Make the writing gold.'], JSON_UNESCAPED_UNICODE)]]]]),
            'https://api.openai.com/v1/images/edits' => Http::response(['data' => [['b64_json' => base64_encode('png')]]]),
        ]);
        $specs = ['shape' => 'star', 'size' => 4, 'layers' => 2];

        $this->postJson('/products/1305/ai/chat', $specs + ['messages' => [['role' => 'user', 'content' => 'خلي الكتابة ذهبي']]])
            ->assertOk()
            ->assertJsonPath('action', 'edit')
            ->assertJsonPath('instruction', 'Make the writing gold.');
        Http::assertSent(fn ($r) => str_contains($r->url(), 'chat/completions')
            && str_contains($r['messages'][0]['content'], 'FIVE-POINTED STAR')
            && str_contains($r['messages'][0]['content'], '6 inches'));

        $first = $this->postJson('/products/1305/ai', $specs + ['job' => 'job-first123'])->assertOk();
        $this->postJson('/products/1305/ai/edit', $specs + [
            'image' => basename($first->json('image')), 'instruction' => 'Make the writing gold.', 'job' => 'job-edit1234',
            'previous' => ['Replace the giraffe with an elephant.', 'Use 6 balloons.'],
        ])->assertOk()->assertJsonPath('remaining', 3);

        Http::assertSent(function ($r) {
            $prompt = collect($r->data())->firstWhere('name', 'prompt')['contents'] ?? '';

            // التعديلات اللي قبل تبقى، وما يتغير عدد أو اتجاه شي ما انطلب
            return str_contains($prompt, 'Apply ONLY these new changes: Make the writing gold.') && str_contains($prompt, 'FIVE-POINTED STAR')
                && str_contains($prompt, '1) Replace the giraffe with an elephant. 2) Use 6 balloons.')
                && str_contains($prompt, 'exact NUMBER');
        });

        // صورة مو من نفس الزائر ما تنعدّل
        $this->postJson('/products/1305/ai/edit', $specs + [
            'image' => '20260101-000000-abcdef-product-1305.png', 'instruction' => 'x', 'job' => 'job-edit5678',
        ])->assertStatus(422);
    }
}

