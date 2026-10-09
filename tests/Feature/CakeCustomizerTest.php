<?php

namespace Tests\Feature;

use App\Models\Cake;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CakeCustomizerTest extends TestCase
{
    use RefreshDatabase;

    private const SELECTION = [
        'size' => '6in',
        'flavor' => 'chocolate',
        'filling' => 'strawberry',
        'layers' => 3,
        'color' => 'blue',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Cake::create([
            'name' => 'Signature Cake',
            'slug' => 'signature-cake',
            'diameter_cm' => 10.16,
            'height_cm' => 8,
            'model_glb_path' => 'models/cake.glb',
            'is_active' => true,
        ]);
    }

    public function test_page_renders(): void
    {
        $this->get('/cake/signature-cake/customize')
            ->assertOk()
            ->assertSee('صمّم كيكتك');
    }

    public function test_price_is_calculated_from_selection(): void
    {
        // 6 إنش 110 + فراولة 10 + 3 طبقات 10
        $this->postJson('/cake/signature-cake/customize/price', self::SELECTION)
            ->assertOk()
            ->assertJsonPath('total', 130);
    }

    public function test_cart_ignores_price_sent_by_browser(): void
    {
        $this->postJson('/cake/signature-cake/customize/cart', self::SELECTION + ['total' => 1])
            ->assertOk()
            ->assertJsonPath('item.total', 130)
            ->assertJsonPath('cart_count', 1);
    }

    public function test_unknown_option_is_rejected(): void
    {
        $this->postJson('/cake/signature-cake/customize/price', ['size' => '12in'] + self::SELECTION)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('size');
    }

    public function test_cart_stores_text_and_print_image_as_is(): void
    {
        Storage::fake('local');
        $image = UploadedFile::fake()->image('photo.jpg', 800, 800);

        $response = $this->post('/cake/signature-cake/customize/cart', self::SELECTION + [
            'text' => 'كل عام وأنت بخير',
            'text_target' => 'cake',
            'print_image' => $image,
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('item.total', 130)
            ->assertJsonPath('item.selection.text', 'كل عام وأنت بخير');

        $path = $response->json('item.print_image');
        Storage::disk('local')->assertExists($path);
        $this->assertSame($image->getSize(), Storage::disk('local')->size($path));
    }

    public function test_text_longer_than_limit_is_rejected(): void
    {
        $this->postJson('/cake/signature-cake/customize/price', self::SELECTION + [
            'text' => str_repeat('ا', 41),
            'text_target' => 'board',
        ])->assertJsonValidationErrors('text');
    }

    public function test_toppings_are_priced_by_server(): void
    {
        // 130 + لؤلؤ 24 + كرز 20
        $this->postJson('/cake/signature-cake/customize/price', self::SELECTION + ['toppings' => ['pearls', 'cherries']])
            ->assertOk()
            ->assertJsonPath('total', 174);
    }

    public function test_unknown_topping_is_rejected(): void
    {
        $this->postJson('/cake/signature-cake/customize/price', self::SELECTION + ['toppings' => ['gold']])
            ->assertJsonValidationErrors('toppings');
    }

    public function test_designs_page_lists_presets_with_server_price(): void
    {
        // لؤلؤ وردي: 8/6 = 265 + لؤلؤ 24
        $this->get('/cake/signature-cake/designs')
            ->assertOk()
            ->assertSee('لؤلؤ وردي')
            ->assertSee('289 ر.س')
            ->assertSee('design=pink-pearls', false)
            // Tony: 6 إنش 110 + 3 طبقات 10 + الرسمة 40 + حبات كريمة 15
            ->assertSee('175 ر.س');
    }

    public function test_design_preset_prefills_customizer(): void
    {
        $this->get('/cake/signature-cake/customize?design=pink-pearls')
            ->assertOk()
            ->assertSee('"toppings":["pearls"]', false);
    }

    public function test_photo_design_adds_its_drawing_price(): void
    {
        // Tony: 6 إنش 110 + 3 طبقات 10 + الرسمة 40 + حبات كريمة 15
        $this->postJson('/cake/signature-cake/customize/price', [
            'size' => '6in', 'flavor' => 'vanilla', 'filling' => 'plain', 'layers' => 3,
            'color' => 'rose', 'toppings' => ['beads'], 'design' => 'tony',
        ])->assertOk()->assertJsonPath('total', 175);
    }

    public function test_design_without_drawing_is_rejected(): void
    {
        $this->postJson('/cake/signature-cake/customize/price', self::SELECTION + ['design' => 'pink-pearls'])
            ->assertJsonValidationErrors('design');
    }

    public function test_vintage_design_is_listed_and_priced(): void
    {
        // فنتج 6 إنش: 110 + فنتج 35
        $this->get('/cake/signature-cake/designs')
            ->assertOk()
            ->assertSee('فنتج 6 إنش')
            ->assertSee('145 ر.س')
            ->assertSee('design=vintage-6', false);

        $this->get('/cake/signature-cake/customize?design=vintage-6')
            ->assertOk()
            ->assertSee('"toppings":["vintage"]', false);
    }
}
