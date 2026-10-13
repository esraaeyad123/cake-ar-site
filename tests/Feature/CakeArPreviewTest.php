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
            ->assertSee('const prices = {"4in":{"2":70,"3":80},"6in":{"2":110,"3":130,"4":150}}', false)
            ->assertSee('"6in":[2,3,4]', false);
    }

    public function test_fallback_prices_are_used_when_api_is_down(): void
    {
        Http::fake(['*' => Http::response(null, 500)]);

        // 8 إنش 155 + 4 طبقات 50
        $this->get('/cake/signature-cake/ar')
            ->assertOk()
            ->assertSee('"8in":{"2":155,"3":180,"4":205}', false);
    }
}
