<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * خيارات الطلب الخاص من موقع جوماكيك: المقاسات (عدد الأشخاص)، النكهات والحشوات بصورها
 * وزيادة سعر كل وحدة حسب المقاس. بنفس شكل الاحتياطي في config/special_order.php
 */
class SpecialOrderOptions
{
    public function all(): array
    {
        if ($cached = Cache::get('special_order.options')) {
            return $cached;
        }

        try {
            $options = $this->fetch();
            $minutes = config('special_order.cache_minutes');
        } catch (Throwable $e) {
            Log::warning('Jomacake special-order API unavailable, using fallback options', ['error' => $e->getMessage()]);
            $options = config('special_order.fallback') + ['shapePrices' => $this->fallbackShapePrices()];
            $minutes = 5;
        }

        Cache::put('special_order.options', $options, now()->addMinutes($minutes));

        return $options;
    }

    private function fetch(): array
    {
        $api = rtrim(config('cake_ar.pricing.api_url'), '/');
        $client = Http::acceptJson()->timeout(5);

        $sizeRows = $client->get("{$api}/special-request/sizes")->throw()->json('data');
        $pricing = collect($client->get("{$api}/cakes-pricing")->throw()->json());

        $sizes = [];
        foreach ($sizeRows as $size) {
            // "6 Inch" => "6"، "4-6 people" => "4-6"
            if (preg_match('/^(\d+)/', $size['name'], $m)) {
                $sizes[$m[1]] = ['id' => $size['id'], 'people' => trim(preg_replace('/people/i', '', $size['people_number']))];
            }
        }

        if (! $sizes) {
            throw new \RuntimeException('Empty sizes response');
        }

        return [
            'sizes' => $sizes,
            'shapePrices' => $this->shapePrices($client, $api, $sizes),
            'flavors' => $this->items($client->get("{$api}/cake-items/flavor", ['type' => 'flavor'])->throw()->json(), $pricing, 'flavor', $sizes),
            'fillings' => $this->items($client->get("{$api}/cake-items/stuffing", ['type' => 'stuffing'])->throw()->json(), $pricing, 'stuffing', $sizes),
        ];
    }

    /**
     * سعر الأشكال الخاصة (مثل السن ست) من منتجها في المتجر، بنفس شكل أسعار الصفحة:
     * ['sunset' => ['6in' => [2 => 255], '8in' => [2 => 335]]]
     * نأخذ السعر قبل الخصم. إذا المنتج ما رد نستخدم الاحتياطي لهذا الشكل بس.
     */
    private function shapePrices($client, string $api, array $sizes): array
    {
        $result = [];
        foreach (config('special_order.shapes') as $shape => $conf) {
            try {
                foreach (array_keys($conf['fallback']) as $inch) {
                    $sizeId = $sizes[$inch]['id'] ?? config("special_order.fallback.sizes.{$inch}.id");
                    $rows = $client->get("{$api}/shop/layers/{$conf['product_id']}/{$sizeId}", [
                        'product_id' => $conf['product_id'],
                        'size_id' => $sizeId,
                    ])->throw()->json();

                    $tier = collect(collect($rows)->firstWhere('name', "{$conf['layers']} layer")['tiers'] ?? [])->firstWhere('name', '1 tier');
                    if (! $tier) {
                        throw new \RuntimeException("No {$conf['layers']} layer price for {$shape} {$inch}in");
                    }
                    $result[$shape]["{$inch}in"][$conf['layers']] = (float) ($tier['price_before_discount'] ?? $tier['price']);
                }
            } catch (Throwable $e) {
                Log::warning("Jomacake {$shape} price unavailable, using fallback", ['error' => $e->getMessage()]);
                $result[$shape] = $this->fallbackShapePrices()[$shape];
            }
        }

        return $result;
    }

    private function fallbackShapePrices(): array
    {
        return collect(config('special_order.shapes'))
            ->map(fn ($conf) => collect($conf['fallback'])
                ->mapWithKeys(fn ($price, $inch) => ["{$inch}in" => [$conf['layers'] => (float) $price]])
                ->all())
            ->all();
    }

    /**
     * فقط العناصر اللي لها سعر في cakes-pricing (هذي خيارات الطلب الخاص)
     */
    private function items(array $rows, $pricing, string $type, array $sizes): array
    {
        $column = $type === 'flavor' ? 'flavor_id' : 'stuffing_id';
        $priced = $pricing->where('type', $type)->groupBy($column);

        return collect($rows)
            ->filter(fn ($row) => $priced->has((string) $row['id']))
            ->map(fn ($row) => [
                'id' => $row['id'],
                'name' => $row['title_ar'] ?: $row['title_en'],
                'image' => $row['sliced_image_url'] ?? $row['element_image_url'] ?? $row['front_image_url'] ?? null,
                'extra' => collect($sizes)->map(fn ($size) => (float) ($priced[(string) $row['id']]->firstWhere('size_id', (string) $size['id'])['price'] ?? 0))->all(),
            ])
            ->values()
            ->all();
    }
}
