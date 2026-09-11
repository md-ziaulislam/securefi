<?php

use App\Http\Controllers\Api\ArticleApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Version 1
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::get('/articles', [ArticleApiController::class, 'index']);
    Route::get('/articles/{slug}', [ArticleApiController::class, 'show']);
    Route::get('/categories', [ArticleApiController::class, 'categories']);
    Route::get('/stats', [ArticleApiController::class, 'stats']);
});
