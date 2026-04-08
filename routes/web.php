<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index']);
Route::get('/health', [DashboardController::class, 'health']);
Route::get('/status', [DashboardController::class, 'status']);

// Article CRUD
Route::post('/articles', [\App\Http\Controllers\ArticleController::class, 'store'])->name('articles.store');
Route::delete('/articles/{article}', [\App\Http\Controllers\ArticleController::class, 'destroy'])->name('articles.destroy');

// Cache demo
Route::post('/cache', [\App\Http\Controllers\CacheController::class, 'store'])->name('cache.store');
Route::post('/cache/flush', [\App\Http\Controllers\CacheController::class, 'flush'])->name('cache.flush');

// Storage demo
Route::post('/storage/upload', [\App\Http\Controllers\StorageController::class, 'upload'])->name('storage.upload');
Route::delete('/storage/{filename}', [\App\Http\Controllers\StorageController::class, 'destroy'])->name('storage.destroy');

// Search demo
Route::get('/search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');

// Queue demo
Route::post('/queue/dispatch', [\App\Http\Controllers\QueueController::class, 'dispatch'])->name('queue.dispatch');
