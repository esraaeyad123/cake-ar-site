<?php

namespace App\Http\Controllers;

use App\Services\ShopProducts;

class ShopController extends Controller
{
    public function index(ShopProducts $shop)
    {
        return view('shop.index', ['products' => $shop->all()]);
    }

    public function show(int $id, ShopProducts $shop)
    {
        $product = $shop->find($id);

        abort_unless($product, 404);

        return view('shop.show', compact('product'));
    }
}
