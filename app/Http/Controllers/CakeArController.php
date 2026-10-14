<?php

namespace App\Http\Controllers;

use App\Models\Cake;
use App\Services\CakePricing;

class CakeArController extends Controller
{
    public function show(string $slug, CakePricing $pricing)
    {
        $cake = Cake::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $variants = $cake->variants()
            ->where('tier_count', 1)
            ->orderBy('sort_order')
            ->get();

        $defaultVariant = $variants->firstWhere('size_label', '4') ?? $variants->first();

        $prices = $pricing->startingPrices();
        $layerLimits = config('cake_ar.layers');

        return view('cake-ar-v3', compact('cake', 'variants', 'defaultVariant', 'prices', 'layerLimits'));
    }
}
