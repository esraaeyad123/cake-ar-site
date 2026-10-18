<?php

namespace Tests\Feature;

use Database\Seeders\CakeSeeder;
use Database\Seeders\CakeVariantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AiCakeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([CakeSeeder::class, CakeVariantSeeder::class]);
        config(['services.openai.key' => 'test-key']);
        Http::fake(['jomacake.com/*' => Http::response(null, 500)]);
    }

    public function test_page_renders(): void
    {
        $this->get('/cake/signature-cake/ai')
            ->assertOk()
            ->assertSee('صمّمي الكيكة بالذكاء الاصطناعي')
            ->assertSee('data-shape="heart"', false);
    }

    public function test_generates_top_and_side_designs(): void
    {
        Storage::fake('public');
        Http::fake(['api.openai.com/*' => Http::response(['data' => [['b64_json' => base64_encode('png')]]])]);

        $response = $this->postJson('/cake/signature-cake/ai', ['shape' => 'round', 'description' => 'ورد وردي'])
            ->assertOk()
            ->assertJsonStructure(['top', 'side']);

        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'images/generations')
            && str_contains($request['prompt'], 'TOP surface') && $request['size'] === '1024x1024');
        Http::assertSent(fn ($request) => str_contains($request['prompt'] ?? '', 'SIDE wall') && $request['size'] === '1536x1024');
        $this->assertCount(2, Storage::disk('public')->files('ai-cakes'));
        $this->assertStringEndsWith('-top.png', $response->json('top'));
        $this->assertStringStartsWith(url('/storage/ai-cakes/'), $response->json('top'));
    }

    public function test_reference_images_use_the_edits_endpoint(): void
    {
        Storage::fake('public');
        Http::fake(['api.openai.com/*' => Http::response(['data' => [['b64_json' => base64_encode('png')]]])]);

        $this->post('/cake/signature-cake/ai', [
            'shape' => 'heart',
            'description' => 'نفس الرسمة',
            'references' => [UploadedFile::fake()->image('ref.jpg')],
        ], ['Accept' => 'application/json'])->assertOk();

        Http::assertSent(fn ($request) => str_contains($request->url(), 'images/edits'));
    }

    public function test_safety_rejection_returns_friendly_message(): void
    {
        Http::fake(['api.openai.com/*' => Http::response(['error' => ['code' => 'moderation_blocked', 'message' => 'blocked by safety system']], 400)]);

        $this->postJson('/cake/signature-cake/ai', ['shape' => 'round', 'description' => 'شخصية'])
            ->assertStatus(422)
            ->assertJsonPath('message', fn ($m) => str_contains($m, 'رفض'));
    }

    public function test_missing_key_is_reported(): void
    {
        config(['services.openai.key' => null]);

        $this->postJson('/cake/signature-cake/ai', ['shape' => 'round', 'description' => 'ورد'])
            ->assertStatus(503);
    }
}
