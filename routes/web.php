<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoGeneratorController;

Route::get('/', [VideoGeneratorController::class, 'index'])->name('generator.index');
Route::post('/generate', [VideoGeneratorController::class, 'generate'])->name('generator.generate');
