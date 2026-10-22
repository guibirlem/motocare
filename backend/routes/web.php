<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['application' => 'MotoCare', 'status' => 'ok']);
});

require __DIR__.'/history.php';
