<?php

use Illuminate\Support\Facades\Route;

// Test route
Route::get('/test-watchtower', function () {
    return ['status' => 'ok'];
});
