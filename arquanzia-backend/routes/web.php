<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\FeedPageController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/healthz', HealthController::class);
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

Route::get('/media/{media}', [MediaController::class, 'show'])->name('media.show');

Route::get('/', [HomepageController::class, 'index'])->name('home');
Route::get('/fil', [FeedPageController::class, 'index'])->name('feed');
Route::get('/fil/{post}', [FeedPageController::class, 'show'])->name('post.show');

Route::get('/bibliotheque', [\App\Http\Controllers\LibraryController::class, 'index'])->name('library.index');
Route::get('/bibliotheque/{slug}', [\App\Http\Controllers\LibraryController::class, 'showBook'])->name('library.book');
Route::get('/bibliotheque/{book}/{chapter}', [\App\Http\Controllers\LibraryController::class, 'showChapter'])->name('library.chapter');

Route::get('/telechargement/livre/{slug}.{format}', [\App\Http\Controllers\DownloadController::class, 'downloadBook'])
    ->where('format', 'pdf|epub|edition')
    ->name('download.book');
Route::get('/telechargement/livre/{book}/chapitre/{chapter}.{format}', [\App\Http\Controllers\DownloadController::class, 'downloadChapter'])
    ->where('format', 'pdf|epub')
    ->name('download.chapter');
Route::get('/telechargement/image/{image}', [\App\Http\Controllers\DownloadController::class, 'downloadImage'])
    ->name('download.image');

Route::get('/encyclopedie', [\App\Http\Controllers\EncyclopediaController::class, 'index'])->name('encyclopedia.index');
Route::get('/encyclopedie/{path}', [\App\Http\Controllers\EncyclopediaController::class, 'show'])
    ->where('path', '.*')
    ->name('encyclopedia.show');

Route::get('/fragments', [\App\Http\Controllers\FragmentController::class, 'index'])->name('fragments.index');
Route::get('/fragments/{path}', [\App\Http\Controllers\FragmentController::class, 'show'])
    ->where('path', '.*')->name('fragments.show');

Route::get('/recherche', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');
Route::get('/api/recherche', [\App\Http\Controllers\SearchController::class, 'api'])->name('search.api');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'sendMagicLink'])->name('login.send');
    Route::get('/magic/{token}', [AdminAuthController::class, 'consumeMagicLink'])->name('magic');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('posts', AdminPostController::class)->except(['show']);

        Route::get('/admins', [AdminController::class, 'index'])->name('admins.index');
        Route::post('/admins', [AdminController::class, 'store'])->name('admins.store');
        Route::delete('/admins/{admin}', [AdminController::class, 'destroy'])->name('admins.destroy');

        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');

        Route::resource('books', \App\Http\Controllers\Admin\BookController::class)->except(['show']);
        Route::get('/books/{book}/chapters/create', [\App\Http\Controllers\Admin\ChapterController::class, 'create'])->name('chapters.create');
        Route::post('/books/{book}/chapters', [\App\Http\Controllers\Admin\ChapterController::class, 'store'])->name('chapters.store');
        Route::get('/books/{book}/chapters/{chapter}/edit', [\App\Http\Controllers\Admin\ChapterController::class, 'edit'])->name('chapters.edit');
        Route::put('/books/{book}/chapters/{chapter}', [\App\Http\Controllers\Admin\ChapterController::class, 'update'])->name('chapters.update');
        Route::delete('/books/{book}/chapters/{chapter}', [\App\Http\Controllers\Admin\ChapterController::class, 'destroy'])->name('chapters.destroy');

        Route::resource('encyclopedia', \App\Http\Controllers\Admin\EncyclopediaController::class)->except(['show']);
        Route::delete('/encyclopedia/{encyclopedium}/gallery/{image}', [\App\Http\Controllers\Admin\EncyclopediaController::class, 'deleteGalleryImage'])->name('encyclopedia.gallery.destroy');
        Route::get('/encyclopedia-import', [\App\Http\Controllers\Admin\EncyclopediaController::class, 'importForm'])->name('encyclopedia.import');
        Route::post('/encyclopedia-import/analyze', [\App\Http\Controllers\Admin\EncyclopediaController::class, 'importAnalyze'])->name('encyclopedia.import.analyze');
        Route::post('/encyclopedia-import/execute', [\App\Http\Controllers\Admin\EncyclopediaController::class, 'importExecute'])->name('encyclopedia.import.execute');

        Route::resource('fragments', \App\Http\Controllers\Admin\FragmentController::class)->except(['show']);

        Route::resource('wikilinks', \App\Http\Controllers\Admin\WikilinkController::class)->except(['show']);

        Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/audit', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit.index');

        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/logo', [\App\Http\Controllers\Admin\SettingsController::class, 'updateLogo'])->name('settings.logo');
        Route::delete('/settings/logo', [\App\Http\Controllers\Admin\SettingsController::class, 'deleteLogo'])->name('settings.logo.delete');
        Route::post('/settings/name', [\App\Http\Controllers\Admin\SettingsController::class, 'updateSiteName'])->name('settings.name');

    });
});
