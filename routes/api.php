<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SourceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Article routes
Route::apiResource('articles', ArticleController::class, ['only' => ['index', 'show']]);

// Source routes
Route::get('sources', [SourceController::class, 'index']);

// Author routes
Route::get('authors', [AuthorController::class, 'index']);

// Category routes
Route::get('categories', [CategoryController::class, 'index']);
