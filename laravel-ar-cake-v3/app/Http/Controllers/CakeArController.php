<?php

namespace App\Http\Controllers;

use App\Models\Cake;

class CakeArController extends Controller
{
    public function show(string $slug)
    {
        $cake = Cake::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $variants = $cake->variants()
            ->where('tier_count', 1)
            ->orderBy('sort_order')
            ->get();

        $defaultVariant = $variants->firstWhere('is_default', true) ?? $variants->first();

        return view('cake-ar-v3', compact('cake', 'variants', 'defaultVariant'));
    }
}
