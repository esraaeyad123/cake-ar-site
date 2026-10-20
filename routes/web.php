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
