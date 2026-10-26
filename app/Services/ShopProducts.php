<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * منتجات متجر جوماكيك الجاهزة، مرتبة بشكل بسيط للصفحة: المقاسات بأسعارها وخصمها،
 * الطبقات، النكهات والحشوات. الأسعار شاملة الضريبة مثل الموقع.
 */
class ShopProducts
{
    public function all(): array
    {
        if (($cached = Cache::get('shop.products')) !== null) {
            return $cached;
        }

        try {
            $products = $this->fetch();
            $minutes = config('shop.cache_minutes');
        } catch (Throwable $e) {
            // ما نخترع منتجات: الصفحة تقول إن المتجر مو متاح الحين
            Log::warning('Jomacake shop API unavailable', ['error' => $e->getMessage()]);
            $products = [];
            $minutes = 2;
        }

        Cache::put('shop.products', $products, now()->addMinutes($minutes));

        return $products;
    }

    /**
     * السعر بعد الخصم، مقرّب لتحت مثل الموقع (355 بخصم 25% = 266)
     */
    public static function finalPrice(float $price, ?array $discount): int
    {
        if ($discount) {
            $price = $discount['type'] === 'percent'
                ? $price * (1 - $discount['amount'] / 100)
                : $price - $discount['amount'];
        }

        return (int) floor(max(0, $price) + 1e-9);
    }

    public function find(int $id): ?array
    {
        return collect($this->all())->firstWhere('id', $id);
    }

    private function fetch(): array
    {
        $api = rtrim(config('cake_ar.pricing.api_url'), '/');
        $query = ['store_id' => config('shop.store_id'), 'page' => 1, 'per_page' => config('shop.per_page')];
        foreach (config('shop.category_ids') as $i => $category) {
            $query["category_id[{$i}]"] = $category;
        }

        $rows = Http::acceptJson()->timeout(8)->get("{$api}/shop", $query)->throw()->json('cakes.data');

        if (! is_array($rows)) {
            throw new \RuntimeException('Unexpected shop response');
        }

        return collect($rows)
            ->filter(fn ($row) => ($row['is_active'] ?? '1') == '1' && ($row['enabled'] ?? '1') == '1')
            ->map(fn ($row) => $this->product($row))
            ->filter(fn ($product) => $product['sizes'])
            ->values()
            ->all();
    }

    private function product(array $row): array
    {
        $people = collect($row['sizes'] ?? [])->mapWithKeys(fn ($s) => [$s['id'] => trim(preg_replace('/people/i', '', $s['people_number'] ?? ''))]);

        $sizes = collect($row['variations'] ?? [])
            ->filter(fn ($v) => $v['is_active'] ?? true)
            ->sortBy('sort_order')
            ->map(function ($v) use ($row, $people) {
                preg_match('/(\d+)/', $v['size']['name'] ?? '', $inch);
                preg_match('/(\d+)/', $v['min_layer']['name'] ?? '', $layers);
                $minLayers = (int) ($layers[1] ?? 2);

                return [
                    'id' => (int) $v['size_id'],
                    'inch' => $inch[1] ?? '',
                    'people' => $people[$v['size_id']] ?? '',
                    'price' => (float) $v['price'],
                    'min_layers' => $minLayers,
                    // الطبقة الزيادة لين 3 طبقات
                    'max_layers' => max($minLayers, 3),
                    'layer_price' => (float) ($v['layer_price'] ?? 0),
                    'discount' => $this->discount($row['discounts'] ?? [], (int) $v['size_id']),
                ];
            })
            ->values()
            ->all();

        return [
            'id' => (int) $row['id'],
            // أغلب المنتجات بدون اسم، والموقع يعرضها بالرقم التسلسلي
            'name' => $row['name_ar'] ?: null,
            'image' => $row['image_url'] ?? $row['image'] ?? null,
            'requires_text' => (bool) ($row['require_text_writing'] ?? false),
            'sizes' => $sizes,
            'flavors' => collect($row['flavors'] ?? [])->map(fn ($f) => [
                'id' => (int) $f['id'],
                'name' => $f['title_ar'] ?: $f['title_en'],
                'image' => $f['sliced_image_url'] ?? $f['element_image_url'] ?? null,
                'extra' => (float) ($f['price'] ?? 0),
            ])->values()->all(),
            'stuffings' => collect($row['stuffings'] ?? [])->map(fn ($s) => [
                'id' => (int) $s['id'],
                'name' => $s['title_ar'] ?: $s['title_en'],
            ])->values()->all(),
        ];
    }

    /**
     * الخصم الشغال الحين على هذا المقاس (نسبة أو مبلغ)، والخصومات المنتهية تنتجاهل
     */
    private function discount(array $discounts, int $sizeId): ?array
    {
        $now = now();

        foreach ($discounts as $d) {
            $sizes = collect($d['sizes'] ?? [])->pluck('id')->map(fn ($id) => (int) $id);
            $stores = collect($d['stores'] ?? [])->pluck('id')->map(fn ($id) => (int) $id);

            if (! ($d['is_active'] ?? false)
                || ($sizes->isNotEmpty() && ! $sizes->contains($sizeId))
                || ($stores->isNotEmpty() && ! $stores->contains(config('shop.store_id')))
                || (! empty($d['from_date']) && Carbon::parse($d['from_date'])->gt($now))
                || (! empty($d['to_date']) && Carbon::parse($d['to_date'])->lt($now))) {
                continue;
            }

            return ['type' => $d['type'] === 'percent' ? 'percent' : 'amount', 'amount' => (float) $d['amount']];
        }

        return null;
    }
}
