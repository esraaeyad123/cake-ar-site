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

    public function test_page_shows_style_switch_and_prices_from_jomacake_api(): void
    {
        Http::fake([
            self::API.'/special-request/sizes' => Http::response(['success' => true, 'data' => [
                ['id' => 2, 'name' => '4 Inch', 'people_number' => '1-2 people', 'price' => '70.00'],
                ['id' => 4, 'name' => '6 Inch', 'people_number' => '4-6 people', 'price' => '110.00'],
            ]]),
            self::API.'/cakes-pricing' => Http::response([
                ['type' => 'flavor', 'flavor_id' => '314', 'layer_id' => null, 'size_id' => '4', 'price' => '0.00'],
                ['type' => 'layer', 'layer_id' => '2', 'size_id' => '2', 'price' => '0.00'],
                ['type' => 'layer', 'layer_id' => '3', 'size_id' => '2', 'price' => '10.00'],
                ['type' => 'layer', 'layer_id' => '2', 'size_id' => '4', 'price' => '0.00'],
                ['type' => 'layer', 'layer_id' => '3', 'size_id' => '4', 'price' => '20.00'],
                ['type' => 'layer', 'layer_id' => '13', 'size_id' => '4', 'price' => '40.00'], // 4 طبقات
            ]),
        ]);

        $this->get('/cake/signature-cake/ar')
            ->assertOk()
            ->assertSee('فنتج')
            ->assertSee('سادة')
            ->assertSee('السعر المبدئي')
            ->assertSee('data-shape="heart"', false)
            ->assertSee('data-shape="cone"', false)
            // الأشكال الخاصة: السن ست (سادة) والوال كيك (دورين، نص السعر، فيه فنتج)، وأيقونات بدون كلمات
            ->assertSee('data-shape="sunset"', false)
            ->assertSee('data-shape="star"', false)
            ->assertSee('data-shape="dome"', false)
            ->assertSee('data-shape="wall"', false)
            ->assertSee('"wall":{"sizes":["tier2_8-6"],"layers":2,"tiers":2,"price_factor":0.5,"vintage":true}', false)
            ->assertDontSee('data-shape="heart">قلب</div>', false)
            // دورين 6/4 = سعر 6 إنش (الدور السفلي) بنفس عدد الطبقات
            ->assertSee('const prices = {"4in":{"2":70,"3":80},"6in":{"2":110,"3":130,"4":150},"tier2_6-4":{"2":110,"3":130}}', false)
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

    public function test_fallback_prices_are_used_when_api_is_down(): void
    {
        Http::fake(['*' => Http::response(null, 500)]);

        // 8 إنش 155 + 4 طبقات 50، و3 أدوار 10/8/6 بسعر 10 إنش
        $this->get('/cake/signature-cake/ar')
            ->assertOk()
            ->assertSee('"8in":{"2":155,"3":180,"4":205}', false)
            ->assertSee('"tier3_10-8-6":{"2":200,"3":230}', false);
    }
}
