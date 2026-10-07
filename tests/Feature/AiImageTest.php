<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AiImageTest extends TestCase
{
    // صورة PNG بحجم 1×1
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        config(['services.openai.key' => 'sk-test', 'services.openai.image_model' => 'gpt-image-2']);
    }

    public function test_page_renders(): void
    {
        $this->get('/ai-images')->assertOk()->assertSee('ولّدي الصورة');
    }

    public function test_generates_saves_and_lists_image(): void
    {
        Http::fake(['api.openai.com/*' => Http::response(['data' => [['b64_json' => self::PNG]], 'usage' => ['total_tokens' => 5]])]);

        $this->post('/ai-images', ['prompt' => 'A pink cake', 'quality' => 'medium'])
            ->assertRedirect('/ai-images');

        Http::assertSent(fn (Request $r) => $r->url() === 'https://api.openai.com/v1/images/generations'
            && $r->hasHeader('Authorization', 'Bearer sk-test')
            && $r['model'] === 'gpt-image-2' && $r['prompt'] === 'A pink cake' && $r['quality'] === 'medium');

        $png = collect(Storage::disk('public')->files('ai-images'))->first(fn ($f) => str_ends_with($f, '.png'));
        $this->assertSame(base64_decode(self::PNG), Storage::disk('public')->get($png));

        $this->get('/ai-images')->assertSee('A pink cake')->assertSee('medium');
    }

    public function test_openai_error_is_shown(): void
    {
        Http::fake(['api.openai.com/*' => Http::response(['error' => ['message' => 'Incorrect API key provided']], 401)]);

        $this->from('/ai-images')->followingRedirects()
            ->post('/ai-images', ['prompt' => 'A cake', 'quality' => 'low'])
            ->assertSee('OpenAI error (401): Incorrect API key provided');
    }

    public function test_missing_key_is_explained_without_calling_openai(): void
    {
        config(['services.openai.key' => null]);
        Http::fake();

        $this->from('/ai-images')->followingRedirects()
            ->post('/ai-images', ['prompt' => 'A cake', 'quality' => 'low'])
            ->assertSee('لا يوجد مفتاح OpenAI');
        Http::assertNothingSent();
    }

    public function test_invalid_quality_is_rejected(): void
    {
        Http::fake();
        $this->post('/ai-images', ['prompt' => 'A cake', 'quality' => 'ultra'])->assertSessionHasErrors('quality');
        Http::assertNothingSent();
    }
}
