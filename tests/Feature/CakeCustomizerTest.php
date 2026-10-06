<?php

namespace Tests\Feature;

use App\Models\Cake;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
