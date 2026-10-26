<?php

use App\Http\Controllers\CakeArController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CakeArController::class, 'show'])->defaults('slug', 'signature-cake');

Route::get('/cake/{slug}/ar', [CakeArController::class, 'show'])
    ->name('cake.ar.show');

// الطلب الخاص بتصميم المدير (3D + AR + سعر + صورة بالذكاء الاصطناعي بنفس المواصفات).
// بدون ربط بنظام الطلبات: "إرسال للمراجعة" يعرض الملخص فقط
use App\Http\Controllers\SpecialOrderController;

Route::get('/special-order', [SpecialOrderController::class, 'show'])->name('special-order.show');
Route::post('/special-order/ai', [SpecialOrderController::class, 'generate'])
    ->middleware('throttle:5,1')
    ->name('special-order.ai');
Route::get('/special-order/ai/{job}', [SpecialOrderController::class, 'status'])
    ->middleware('throttle:60,1')
    ->name('special-order.ai.status');
Route::post('/special-order/review', [SpecialOrderController::class, 'review'])
    ->middleware('throttle:10,1')
    ->name('special-order.review');

// عينة طلب منتجات جوماكيك الجاهزة: صفحات مستقلة، "مراجعة الطلب" تعرض الملخص فقط
use App\Http\Controllers\ShopController;

Route::get('/products', [ShopController::class, 'index'])->name('shop.index');
Route::get('/products/{id}', [ShopController::class, 'show'])->whereNumber('id')->name('shop.show');
Route::post('/products/{id}/ai', [\App\Http\Controllers\ShopAiController::class, 'generate'])
    ->whereNumber('id')
    ->middleware('throttle:5,1')
    ->name('shop.ai');
Route::get('/products/ai/{job}', [\App\Http\Controllers\ShopAiController::class, 'status'])
    ->middleware('throttle:60,1')
    ->name('shop.ai.status');
