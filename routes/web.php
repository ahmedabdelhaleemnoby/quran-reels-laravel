<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoGeneratorController;

Route::get('/', [VideoGeneratorController::class, 'index'])->name('generator.index');
Route::post('/generate', [VideoGeneratorController::class, 'generate'])->name('generator.generate');
Route::post('/preview', [VideoGeneratorController::class, 'preview'])->name('generator.preview');
Route::post('/generate-from-preview', [VideoGeneratorController::class, 'generateFromPreview'])->name('generator.generateFromPreview');
Route::get('/generator/progress', [VideoGeneratorController::class, 'progress'])->name('generator.progress');
Route::post('/cleanup', [VideoGeneratorController::class, 'cleanup'])->name('generator.cleanup');
