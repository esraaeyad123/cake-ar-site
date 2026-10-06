<?php

namespace App\Http\Controllers;

use App\Models\Cake;
use App\Services\CakePriceCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CakeCustomizerController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $cake = $this->findCake($slug);
        $design = collect(config('cake_customizer.designs'))->firstWhere('key', $request->query('design'));

        return view('cake-customizer', [
            'cake' => $cake,
            'options' => config('cake_customizer'),
            'initial' => $design['selection'] ?? null,
        ]);
    }

    /**
     * الكولكشن: تصاميم جاهزة بسعرها المحسوب من السيرفر.
     */
    public function designs(string $slug, CakePriceCalculator $calculator)
    {
        $cake = $this->findCake($slug);

        $designs = collect(config('cake_customizer.designs'))->map(fn (array $design) => $design + [
            'price' => $calculator->quote($design['selection'])['total'],
        ]);

        return view('cake-designs', compact('cake', 'designs'));
    }

    /**
     * السعر المعتمد للاختيارات الحالية (الصفحة تستخدمه لتأكيد السعر المعروض).
     */
    public function price(Request $request, string $slug, CakePriceCalculator $calculator): JsonResponse
    {
        $this->findCake($slug);

        return response()->json($calculator->quote(
            $this->selection($request),
            $request->boolean('has_print_image')
        ));
    }

    /**
     * عينة: تحفظ الطلب في الجلسة بالسعر الذي حسبه السيرفر (أي سعر مرسل من المتصفح يُتجاهل).
     */
    public function addToCart(Request $request, string $slug, CakePriceCalculator $calculator): JsonResponse
    {
        $cake = $this->findCake($slug);
        $selection = $this->selection($request);

        $request->validate([
            'print_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:'.config('cake_customizer.writing.print_image_max_kb')],
        ]);

        $quote = $calculator->quote($selection, $request->hasFile('print_image'));

        // الصورة تُحفظ كما هي بدون أي تعديل (خاصة، غير متاحة للعامة)
        $printImage = $request->file('print_image')?->store('prints');

        $item = [
            'cake' => $cake->name,
            'selection' => $selection,
            'print_image' => $printImage,
            'items' => $quote['items'],
            'total' => $quote['total'],
        ];

        $request->session()->push('cart', $item);

        return response()->json([
            'item' => $item,
            'cart_count' => count($request->session()->get('cart', [])),
        ]);
    }

    private function findCake(string $slug): Cake
    {
        return Cake::where('slug', $slug)->where('is_active', true)->firstOrFail();
    }

    private function selection(Request $request): array
    {
        return $request->validate([
            'size' => ['required', 'string'],
            'flavor' => ['required', 'string'],
            'filling' => ['required', 'string'],
            'layers' => ['required', 'integer'],
            'color' => ['required', 'string'],
            'toppings' => ['nullable', 'array', 'max:10'],
            'toppings.*' => ['string'],
            'design' => ['nullable', 'string'],
            'text' => ['nullable', 'string', 'max:'.config('cake_customizer.writing.max_length')],
            'text_target' => ['nullable', 'string'],
        ]);
    }
}
