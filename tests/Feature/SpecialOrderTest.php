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
            ->assertSee('"extra":{"4":10,"8":15}', false);
    }

    public function test_fallback_options_when_api_is_down(): void
    {
        Http::fake(['*' => Http::response(null, 500)]);

        $this->get('/special-order')
            ->assertOk()
            ->assertSee('"name":'.json_encode('ويب كريم مع فراوله'), false)
            ->assertSee('"tier3_10-8-6":{"2":200,"3":230}', false);
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

    public function test_ai_requires_a_reference_photo(): void
    {
        Http::fake();

        $this->postJson('/special-order/ai', ['shape' => 'round', 'size' => '6in', 'layers' => 2, 'style' => 'plain'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('reference');
        Http::assertNothingSent();
    }
}
