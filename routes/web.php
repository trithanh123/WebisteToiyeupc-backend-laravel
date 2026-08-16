<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/run-qdrant-index', function () {
    \Illuminate\Support\Facades\Artisan::call('qdrant:index');
    return "<pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";
});

// OAuth routes cần session để PKCE hoạt động
Route::get('/api/auth/{provider}/redirect', [AuthController::class, 'redirectToProvider']);
Route::get('/api/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback']);
