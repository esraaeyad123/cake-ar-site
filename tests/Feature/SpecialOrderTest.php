<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SpecialOrderTest extends TestCase
{
    private const API = 'https://jomacake.com/api/customer/v1';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.openai.key' => 'test-key']);
    }

    private function fakeJomacake(): void
    {
        $item = fn ($id, $ar, $type) => ['id' => $id, 'title_ar' => $ar, 'title_en' => $ar, 'type' => $type, 'parent_id' => '301',
            'sliced_image_url' => "https://jomacake.com/public/{$id}.png", 'element_image_url' => null, 'front_image_url' => null];

        Http::fake([
            self::API.'/special-request/sizes' => Http::response(['success' => true, 'data' => [
                ['id' => 2, 'name' => '4 Inch', 'people_number' => '1-2 people', 'price' => '65.00'],
                ['id' => 9, 'name' => '8 Inch', 'people_number' => '8-10 people', 'price' => '155.00'],
            ]]),
            self::API.'/cakes-pricing' => Http::response([
                ['type' => 'flavor', 'flavor_id' => '314', 'stuffing_id' => null, 'layer_id' => null, 'size_id' => '2', 'price' => '0.00'],
                ['type' => 'stuffing', 'flavor_id' => null, 'stuffing_id' => '312', 'layer_id' => null, 'size_id' => '2', 'price' => '10.00'],
                ['type' => 'stuffing', 'flavor_id' => null, 'stuffing_id' => '312', 'layer_id' => null, 'size_id' => '9', 'price' => '15.00'],
                ['type' => 'layer', 'flavor_id' => null, 'stuffing_id' => null, 'layer_id' => '2', 'size_id' => '2', 'price' => '0.00'],
            ]),
            self::API.'/cake-items/flavor*' => Http::response([$item(314, 'شوكولاته', 'flavor'), $item(12, 'فانيلا عادية', 'flavor')]),
            self::API.'/cake-items/stuffing*' => Http::response([$item(312, 'ويب كريم مع نوتيلا', 'stuffing')]),
        ]);
    }

    public function test_page_has_five_tabs_and_special_order_options_from_api(): void
    {
        $this->fakeJomacake();

        $this->get('/special-order')
            ->assertOk()
            ->assertSeeInOrder(['الشكل', 'الحجم', 'النكهة', 'الكتابة', 'التاريخ'])
            ->assertSee('إرسال للمراجعة')
            ->assertSee('data-style="vintage"', false)
            ->assertSee('"people":"8-10"', false)
            // نكهات الطلب الخاص فقط (اللي لها سعر)، والحشوة بزيادة حسب المقاس
            ->assertSee('"name":'.json_encode('شوكولاته'), false)
            ->assertDontSee(json_encode('فانيلا عادية'), false)
            ->assertSee('"extra":{"4":10,"8":15}', false)
            ->assertSee('data-shape="sunset"', false)
            ->assertSee('data-shape="wall"', false)
            // السن ست بنفس سعر الكعكة الخاصة، والوال كيك دورين بنص السعر
            ->assertSee('"sunset":{"sizes":["6","8"],"layers":2,"tiers":1,"price_factor":1,"vintage":false}', false)
            ->assertSee('"wall":{"sizes":["6","8"],"layers":2,"tiers":2,"price_factor":0.5,"vintage":true}', false);
    }

    public function test_ai_image_uses_reference_photo_and_selected_specs(): void
    {
        Storage::fake('public');
        Http::fake(['api.openai.com/*' => Http::response(['data' => [['b64_json' => base64_encode('png')]]])]);

        $response = $this->post('/special-order/ai', [
            'shape' => 'round',
            'size' => 'tier2_8-6',
            'layers' => 3,
            'style' => 'vintage',
            'note' => 'ورد أبيض',
            'reference' => UploadedFile::fake()->image('cake.jpg'),
        ], ['Accept' => 'application/json'])->assertOk();

        $this->assertSame(1, $response->json('remaining'));
        $this->assertCount(1, Storage::disk('public')->files('ai-cakes'));
        Http::assertSent(function ($request) {
            $prompt = collect($request->data())->firstWhere('name', 'prompt')['contents'] ?? '';

            return str_contains($request->url(), 'images/edits')
                && str_contains($prompt, '2-tier stacked cake, tiers from bottom to top: 8 inches, 6 inches')
                && str_contains($prompt, '3 sponge layers')
                && str_contains($prompt, 'Lambeth')
                && str_contains($prompt, 'ورد أبيض');
        });
    }

    public function test_3d_snapshot_is_sent_first_to_set_the_shape(): void
    {
        Storage::fake('public');
        Http::fake(['api.openai.com/*' => Http::response(['data' => [['b64_json' => base64_encode('png')]]])]);

        $this->post('/special-order/ai', [
            'shape' => 'heart', 'size' => '6in', 'layers' => 2, 'style' => 'plain',
            'reference' => UploadedFile::fake()->image('cake.jpg'),
            'shape_image' => UploadedFile::fake()->image('shape.png'),
        ], ['Accept' => 'application/json'])->assertOk();

        Http::assertSent(function ($request) {
            $parts = collect($request->data());
            $images = $parts->where('name', 'image[]')->pluck('filename')->values()->all();

            return $images === ['shape.png', 'cake.jpg']
                && str_contains($parts->firstWhere('name', 'prompt')['contents'], 'Image 1 is a plain 3D model')
                && str_contains($parts->firstWhere('name', 'prompt')['contents'], 'heart-shaped');
        });
    }

    public function test_ai_is_limited_to_two_images_per_customer(): void
    {
        Storage::fake('public');
        Http::fake(['api.openai.com/*' => Http::response(['data' => [['b64_json' => base64_encode('png')]]])]);
        $send = fn () => $this->post('/special-order/ai', [
            'shape' => 'heart', 'size' => '6in', 'layers' => 2, 'style' => 'plain',
            'reference' => UploadedFile::fake()->image('cake.jpg'),
        ], ['Accept' => 'application/json']);

        $send()->assertOk();
        $send()->assertOk()->assertJson(['remaining' => 0]);
        $send()->assertStatus(429);
        Http::assertSentCount(2);
    }

    public function test_sunset_ai_image_only_allows_its_sizes(): void
    {
        Storage::fake('public');
        Http::fake(['api.openai.com/*' => Http::response(['data' => [['b64_json' => base64_encode('png')]]])]);
        $send = fn ($size) => $this->post('/special-order/ai', [
            'shape' => 'sunset', 'size' => $size, 'layers' => 2, 'style' => 'plain',
            'reference' => UploadedFile::fake()->image('cake.jpg'),
        ], ['Accept' => 'application/json']);

        $send('10in')->assertStatus(422)->assertJsonValidationErrors('size');
        $send('8in')->assertOk();
        Http::assertSent(function ($request) {
            $prompt = collect($request->data())->firstWhere('name', 'prompt')['contents'] ?? '';

            return str_contains($prompt, 'half-circle') && str_contains($prompt, '8 inches (20 cm) wide');
        });
    }

    public function test_wall_cake_ai_prompt_has_two_wall_tiers(): void
    {
        Storage::fake('public');
        Http::fake(['api.openai.com/*' => Http::response(['data' => [['b64_json' => base64_encode('png')]]])]);

        $this->post('/special-order/ai', [
            'shape' => 'wall', 'size' => '6in', 'layers' => 2, 'style' => 'vintage',
            'reference' => UploadedFile::fake()->image('cake.jpg'),
        ], ['Accept' => 'application/json'])->assertOk();

        Http::assertSent(function ($request) {
            $prompt = collect($request->data())->firstWhere('name', 'prompt')['contents'] ?? '';

            return str_contains($prompt, 'standing upright like a wall')
                && str_contains($prompt, '2 stacked wall tiers: the bottom wall is 6 inches (15 cm) wide')
                && str_contains($prompt, 'Lambeth');
        });
    }

    public function test_ai_requires_a_reference_photo(): void
    {
        Http::fake();

        $this->postJson('/special-order/ai', ['shape' => 'round', 'size' => '6in', 'layers' => 2, 'style' => 'plain'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('reference');
        Http::assertNothingSent();
    }
}
