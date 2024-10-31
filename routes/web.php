<?php

use App\Http\Controllers\PaintImageControlelr;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/paint-image', [PaintImageControlelr::class, "index"])->name('paint_image.home');
Route::post('/paint-image/store', [PaintImageControlelr::class, "store"])->name('paint_image.store');