<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            // كيك سادة فقط: ما فيه اختيار فنتج
            ->assertDontSee('data-style="vintage"', false)
            ->assertSee('"people":"8-10"', false)
            // نكهات الطلب الخاص فقط (اللي لها سعر)، والحشوة بزيادة حسب المقاس
            ->assertSee('"name":'.json_encode('شوكولاته'), false)
            ->assertDontSee(json_encode('فانيلا عادية'), false)
            ->assertSee('"extra":{"4":10,"8":15}', false)
            // كروت الأشكال، وكيك الأدوار شكل بحاله
            ->assertSee("{ key: 'tier2', name: ", false)
            ->assertSee("{ key: 'sunset', name: ", false)
            ->assertSee("{ key: 'wall', name: ", false)
            // السن ست بنفس سعر الكعكة الخاصة، والوال كيك دورين بنص السعر
            ->assertSee('"sunset":{"sizes":["6in","8in"],"layers":2,"tiers":1,"price_factor":1,"vintage":false}', false)
            ->assertSee('"wall":{"sizes":["tier2_8-6"],"layers":2,"tiers":2,"price_factor":0.5,"vintage":true}', false);
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
            'references' => [UploadedFile::fake()->image('cake.jpg')], 'job' => 'job-'.Str::random(10),
        ], ['Accept' => 'application/json'])->assertOk();

        $this->assertSame(4, $response->json('remaining'));
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
            'references' => [UploadedFile::fake()->image('cake.jpg')], 'job' => 'job-'.Str::random(10),
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

    public function test_five_real_images_per_visitor_and_failures_do_not_count(): void
    {
        // حد الطلبات بالدقيقة (throttle) موضوع ثاني، نوقفه هنا عشان نختبر عدد الصور بس
        $this->withoutMiddleware(ThrottleRequests::class);
        Storage::fake('public');
        Http::fakeSequence('api.openai.com/*')
            ->push(['error' => ['code' => 'server_error', 'message' => 'boom']], 500) // فشل: ما ينحسب
            ->push(['data' => [['b64_json' => base64_encode('png')]]])
            ->push(['data' => [['b64_json' => base64_encode('png')]]])
            ->push(['data' => [['b64_json' => base64_encode('png')]]])
            ->push(['data' => [['b64_json' => base64_encode('png')]]])
            ->push(['data' => [['b64_json' => base64_encode('png')]]])
            ->push(['data' => [['b64_json' => base64_encode('png')]]]);
        $send = fn () => $this->post('/special-order/ai', [
            'shape' => 'heart', 'size' => '6in', 'layers' => 2, 'style' => 'plain',
            'references' => [UploadedFile::fake()->image('cake.jpg')], 'job' => 'job-'.Str::random(10),
        ], ['Accept' => 'application/json']);

        $send()->assertStatus(422)->assertJson(['remaining' => 5]);
        foreach ([4, 3, 2, 1, 0] as $left) {
            $send()->assertOk()->assertJson(['remaining' => $left]);
        }
        $send()->assertStatus(429);
        Http::assertSentCount(6);

        // "إرسال للمراجعة" يجدّد الصور
        $this->postJson('/special-order/review')->assertOk()->assertJson(['remaining' => 5]);
        $send()->assertOk()->assertJson(['remaining' => 4]);
    }

    public function test_result_can_be_fetched_by_job_if_the_request_was_cut(): void
    {
        Storage::fake('public');
        Http::fake(['api.openai.com/*' => Http::response(['data' => [['b64_json' => base64_encode('png')]]])]);

        $this->post('/special-order/ai', [
            'shape' => 'round', 'size' => '6in', 'layers' => 2, 'style' => 'plain',
            'references' => [UploadedFile::fake()->image('cake.jpg')], 'job' => 'job-abc12345',
        ], ['Accept' => 'application/json'])->assertOk();

        $this->getJson('/special-order/ai/job-abc12345')
            ->assertOk()
            ->assertJson(['status' => 'done', 'remaining' => 4])
            ->assertJsonStructure(['image']);
        $this->getJson('/special-order/ai/job-unknown1')->assertNotFound()->assertJson(['status' => 'missing']);
    }

    public function test_sunset_ai_image_only_allows_its_sizes(): void
    {
        Storage::fake('public');
        Http::fake(['api.openai.com/*' => Http::response(['data' => [['b64_json' => base64_encode('png')]]])]);
        $send = fn ($size) => $this->post('/special-order/ai', [
            'shape' => 'sunset', 'size' => $size, 'layers' => 2, 'style' => 'plain',
            'references' => [UploadedFile::fake()->image('cake.jpg')], 'job' => 'job-'.Str::random(10),
        ], ['Accept' => 'application/json']);

        $send('10in')->assertStatus(422)->assertJsonValidationErrors('size');
        $send('8in')->assertOk();
        Http::assertSent(function ($request) {
            $prompt = collect($request->data())->firstWhere('name', 'prompt')['contents'] ?? '';

            return str_contains($prompt, 'half-circle') && str_contains($prompt, '8 inches (20 cm) wide');
        });
    }

    public function test_wall_cake_is_half_of_an_8_6_cake_on_a_standing_board(): void
    {
        Storage::fake('public');
        Http::fake(['api.openai.com/*' => Http::response(['data' => [['b64_json' => base64_encode('png')]]])]);
        $send = fn ($size) => $this->post('/special-order/ai', [
            'shape' => 'wall', 'size' => $size, 'layers' => 2, 'style' => 'vintage',
            'references' => [UploadedFile::fake()->image('cake.jpg')], 'job' => 'job-'.Str::random(10),
        ], ['Accept' => 'application/json']);

        $send('8in')->assertStatus(422)->assertJsonValidationErrors('size');
        $send('tier2_8-6')->assertOk();

        Http::assertSent(function ($request) {
            $prompt = collect($request->data())->firstWhere('name', 'prompt')['contents'] ?? '';

            return str_contains($prompt, 'FRONT HALF of a round tiered cake')
                && str_contains($prompt, 'leans slightly back')
                && str_contains($prompt, 'bottom tier 8 inches (20 cm) wide, top tier 6 inches')
                && str_contains($prompt, 'Lambeth');
        });
    }

    public function test_ai_note_allows_500_characters(): void
    {
        Storage::fake('public');
        Http::fake(['api.openai.com/*' => Http::response(['data' => [['b64_json' => base64_encode('png')]]])]);
        $send = fn ($note) => $this->post('/special-order/ai', [
            'shape' => 'round', 'size' => '6in', 'layers' => 2, 'style' => 'plain', 'note' => $note,
            'references' => [UploadedFile::fake()->image('cake.jpg')], 'job' => 'job-'.Str::random(10),
        ], ['Accept' => 'application/json']);

        $send(str_repeat('ا', 500))->assertOk();
        $send(str_repeat('ا', 501))->assertStatus(422)->assertJsonPath('errors.note.0', 'الملاحظة طويلة، الحد 500 حرف.');
    }

    public function test_up_to_six_reference_photos_are_sent_after_the_3d_snapshot(): void
    {
        Storage::fake('public');
        Http::fake(['api.openai.com/*' => Http::response(['data' => [['b64_json' => base64_encode('png')]]])]);
        $send = fn ($count) => $this->post('/special-order/ai', [
            'shape' => 'round', 'size' => '6in', 'layers' => 2, 'style' => 'plain', 'job' => 'job-'.Str::random(10),
            'references' => collect(range(1, $count))->map(fn ($i) => UploadedFile::fake()->image("ref{$i}.jpg"))->all(),
            'shape_image' => UploadedFile::fake()->image('shape.png'),
        ], ['Accept' => 'application/json']);

        $send(7)->assertStatus(422)->assertJsonPath('errors.references.0', 'الحد 6 صور.');
        $send(6)->assertOk();

        Http::assertSent(fn ($request) => collect($request->data())->where('name', 'image[]')->pluck('filename')->values()->all()
            === ['shape.png', 'ref1.jpg', 'ref2.jpg', 'ref3.jpg', 'ref4.jpg', 'ref5.jpg', 'ref6.jpg']);
    }

    public function test_ai_requires_a_reference_photo(): void
    {
        Http::fake();

        $this->postJson('/special-order/ai', ['shape' => 'round', 'size' => '6in', 'layers' => 2, 'style' => 'plain'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('references');
        Http::assertNothingSent();
    }
}
