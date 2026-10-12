<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * السعر المبدئي للكعكة الخاصة (نكهة وحشوة أساسية) من أسعار موقع جوماكيك:
 * سعر المقاس + زيادة عدد الطبقات. الفنتج بنفس سعر السادة.
 */
class CakePricing
{
    /**
     * أسعار المقاسات بدور واحد: ['4in' => [2 => 65, 3 => 75], ...] حسب الطبقات المسموحة في الصفحة.
     * الأدوار المتعددة ما لها سعر بعد في الموقع، فما ترجع هنا.
     */
    public function startingPrices(): array
    {
        $table = $this->table();
        $prices = [];

        foreach (config('cake_ar.layers') as $sizeKey => $allowed) {
            $inch = rtrim($sizeKey, 'in');
            if (! isset($table[$inch])) {
                continue;
            }

            foreach ($allowed as $layers) {
                if (isset($table[$inch]['layers'][$layers])) {
                    $prices[$sizeKey][$layers] = $table[$inch]['base'] + $table[$inch]['layers'][$layers];
                }
            }
        }

        return $prices;
    }

    /**
     * جدول الأسعار بنفس شكل الاحتياطي في config/cake_ar.php
     */
    public function table(): array
    {
        if ($cached = Cache::get('cake_ar.pricing')) {
            return $cached;
        }

        try {
            $table = $this->fetch();
            $minutes = config('cake_ar.pricing.cache_minutes');
        } catch (Throwable $e) {
            Log::warning('Jomacake pricing API unavailable, using fallback prices', ['error' => $e->getMessage()]);
            // نعيد المحاولة بعد 5 دقائق بدل ما ننتظر الموقع مع كل زائر
            $table = config('cake_ar.pricing.fallback');
            $minutes = 5;
        }

        Cache::put('cake_ar.pricing', $table, now()->addMinutes($minutes));

        return $table;
    }

    private function fetch(): array
    {
        $api = rtrim(config('cake_ar.pricing.api_url'), '/');
        $client = Http::acceptJson()->timeout(5);

        $sizes = $client->get("{$api}/special-request/sizes")->throw()->json('data');
        $rows = $client->get("{$api}/cakes-pricing")->throw()->json();
        $layerCounts = array_flip(config('cake_ar.pricing.layer_ids')); // layer_id => عدد الطبقات

        $table = [];
        foreach ($sizes as $size) {
            // "6 Inch" => "6"
            if (! preg_match('/^(\d+)/', $size['name'], $m)) {
                continue;
            }
            $layers = collect($rows)
                ->where('type', 'layer')
                ->where('size_id', (string) $size['id'])
                ->filter(fn ($row) => isset($layerCounts[(int) $row['layer_id']]))
                ->mapWithKeys(fn ($row) => [$layerCounts[(int) $row['layer_id']] => (float) $row['price']])
                ->sortKeys()
                ->all();

            $table[$m[1]] = ['base' => (float) $size['price'], 'layers' => $layers];
        }

        if (! $table) {
            throw new \RuntimeException('Empty sizes response');
        }

        return $table;
    }
}
