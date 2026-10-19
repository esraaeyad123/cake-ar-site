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
            $options = config('special_order.fallback');
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
            'flavors' => $this->items($client->get("{$api}/cake-items/flavor", ['type' => 'flavor'])->throw()->json(), $pricing, 'flavor', $sizes),
            'fillings' => $this->items($client->get("{$api}/cake-items/stuffing", ['type' => 'stuffing'])->throw()->json(), $pricing, 'stuffing', $sizes),
        ];
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
