<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
use App\Http\Controllers\CakeArController;

Route::get('/cake/{slug}/ar', [CakeArController::class, 'show'])
    ->name('cake.ar.show');

// عينة تجريبية: تصميم الكيكة بالذكاء الاصطناعي ومعاينته 3D (تحتاج OPENAI_API_KEY في .env)
use App\Http\Controllers\AiCakeController;

Route::get('/cake/{slug}/ai', [AiCakeController::class, 'show'])->name('cake.ai.show');
Route::post('/cake/{slug}/ai', [AiCakeController::class, 'design'])
    ->middleware('throttle:5,1')
    ->name('cake.ai.design');

// عينة تجريبية: الطلب الخاص بتصميم المدير (3D + AR + سعر + صورة بالذكاء الاصطناعي بنفس المواصفات)
use App\Http\Controllers\SpecialOrderController;

Route::get('/special-order', [SpecialOrderController::class, 'show'])->name('special-order.show');
Route::post('/special-order/ai', [SpecialOrderController::class, 'generate'])
    ->middleware('throttle:5,1')
    ->name('special-order.ai');
