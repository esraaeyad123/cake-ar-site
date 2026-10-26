<?php

namespace Tests\Feature;

use Database\Seeders\CakeSeeder;
use Database\Seeders\CakeVariantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CakeArPreviewTest extends TestCase
{
    use RefreshDatabase;

    private const API = 'https://jomacake.com/api/customer/v1';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([CakeSeeder::class, CakeVariantSeeder::class]);
    }

    public function test_preview_page_has_shapes_and_style_switch_but_no_prices(): void
    {
        // صفحة معاينة فقط: ما تطلب أسعار من موقع جوماكيك
        Http::preventStrayRequests();

        $this->get('/cake/signature-cake/ar')
            ->assertOk()
            ->assertSee('فنتج')
            ->assertSee('سادة')
            ->assertDontSee('السعر المبدئي')
            ->assertDontSee('const prices', false)
            ->assertDontSee('card-price', false)
            ->assertSee('data-shape="heart"', false)
            ->assertSee('data-shape="cone"', false)
            // الأشكال الخاصة بأيقونات بدون كلمات
            ->assertSee('data-shape="sunset"', false)
            ->assertSee('data-shape="star"', false)
            ->assertSee('data-shape="dome"', false)
            ->assertSee('data-shape="wall"', false)
            ->assertSee('"wall":{"sizes":["tier2_8-6"],"layers":2,"tiers":2,"price_factor":0.5,"vintage":true}', false)
            ->assertDontSee('data-shape="heart">قلب</div>', false)
            ->assertSee('"6in":[2,3,4]', false);
    }

    public function test_home_opens_the_preview_with_a_versioned_composer(): void
    {
        Http::fake(['*' => Http::response(null, 500)]);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-shape="wall"', false)
            ->assertSee('js/cake-composer.js?v='.filemtime(public_path('js/cake-composer.js')), false);
    }
}
