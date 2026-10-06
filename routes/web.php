<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
use App\Http\Controllers\CakeArController;
use App\Http\Controllers\CakeCustomizerController;

Route::get('/cake/{slug}/ar', [CakeArController::class, 'show'])
    ->name('cake.ar.show');

Route::get('/cake/{slug}/designs', [CakeCustomizerController::class, 'designs'])
    ->name('cake.designs');
Route::get('/cake/{slug}/customize', [CakeCustomizerController::class, 'show'])
    ->name('cake.customize.show');
Route::post('/cake/{slug}/customize/price', [CakeCustomizerController::class, 'price'])
    ->name('cake.customize.price');
Route::post('/cake/{slug}/customize/cart', [CakeCustomizerController::class, 'addToCart'])
    ->name('cake.customize.cart');
