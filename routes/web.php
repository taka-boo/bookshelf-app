<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReadingPlanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/* 公開ページ */
/* PG01: 書籍一覧 (トップ) */
Route::get('/books', [BookController::class, 'index'])->name('books.index');
/* PG11: ランキング */
Route::get('/ranking', [ReviewController::class, 'ranking'])->name('ranking.index');

/* 認証必須 */
Route::middleware('auth')->group(function () {
    /* PG03: 書籍登録 */
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');

    /* PG: ISBN検索 */
    Route::get('/books/isbn/{isbn}', [BookController::class, 'searchByIsbn'])->name('books.isbn');

    /* PG04: 書籍編集 */
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

    /* PG05: ジャンル一覧 */
    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');

    /* PG07: ジャンル登録 */
    Route::get('/genres/create', [GenreController::class, 'create'])->name('genres.create');
    Route::post('/genres', [GenreController::class, 'store'])->name('genres.store');

    /* PG06: ジャンル詳細 */
    Route::get('/genres/{genre}', [GenreController::class, 'show'])->name('genres.show');

    /* PG08: ジャンル編集 */
    Route::get('/genres/{genre}/edit', [GenreController::class, 'edit'])->name('genres.edit');
    Route::put('/genres/{genre}', [GenreController::class, 'update'])->name('genres.update');
    Route::delete('/genres/{genre}', [GenreController::class, 'destroy'])->name('genres.destroy');

    /* PG09': レビュー登録 */
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    /* PG09: レビュー編集 */
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    /* PG09": レビューいいね */
    Route::post('/reviews/{review}/like', [ReviewController::class, 'like'])->name('reviews.like');

    /* PG10: お気に入り一覧 */
    Route::get('/favorites', [ReviewController::class, 'favorites'])->name('favorites.index');
    Route::post('/books/{book}/favorite', [ReviewController::class, 'favoriteToggle'])->name('favorites.toggle');

    /* PG12: 読書計画一覧 */
    Route::get('/reading-plans', [ReadingPlanController::class, 'index'])->name('reading-plans.index');
    Route::get('/reading-plans/create', [ReadingPlanController::class, 'create'])->name('reading-plans.create');
    Route::post('/reading-plans', [ReadingPlanController::class, 'store'])->name('reading-plans.store');
    Route::get('/reading-plans/{readingPlan}/edit', [ReadingPlanController::class, 'edit'])->name('reading-plans.edit');
    Route::put('/reading-plans/{readingPlan}', [ReadingPlanController::class, 'update'])->name('reading-plans.update');
    Route::delete('/reading-plans/{readingPlan}', [ReadingPlanController::class, 'destroy'])->name('reading-plans.destroy');
    Route::post('/reading-plans/{readingPlan}/complete', [ReadingPlanController::class, 'complete'])->name('reading-plans.complete');
    Route::post('/reading-plans/{readingPlan}/reopen', [ReadingPlanController::class, 'reopen'])->name('reading-plans.reopen');

    /* PG13: マイ読書レポート */
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    /* PG14: 通知一覧 */
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

});

/* 公開ページ */
/* PG02: 書籍詳細 */
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
