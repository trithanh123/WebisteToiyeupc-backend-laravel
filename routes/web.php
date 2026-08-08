<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/run-qdrant-index', function () {
    \Illuminate\Support\Facades\Artisan::call('qdrant:index');
    return "<pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";
});
