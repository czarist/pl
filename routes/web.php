<?php

use App\Http\Controllers\VideoGalleryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VideoGalleryController::class, 'index']);

Route::get('/fetch-eporner-videos', [VideoGalleryController::class, 'fetchEpornerVideos']);
Route::get('/fetch-redtube-videos', [VideoGalleryController::class, 'fetchRedtubeVideos']);
