<?php

use App\Http\Controllers\VideoGalleryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VideoGalleryController::class, 'index']);
Route::get('/page/{page}', [VideoGalleryController::class, 'index'])->name('videos.page');
Route::get('/video/{video_id}/{title}', [VideoGalleryController::class, 'show'])->name('video.show');
Route::view('/404', 'errors.404')->name('error.404');
Route::get('/tags', [VideoGalleryController::class, 'tags'])->name('tags.index');
Route::get('/tag/{tag}', [VideoGalleryController::class, 'galleryByTag']);
Route::get('/search', [VideoGalleryController::class, 'searchVideos'])->name('videos.search');
