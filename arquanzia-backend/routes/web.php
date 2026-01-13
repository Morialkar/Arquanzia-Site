<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\FeedPageController;
use App\Http\Controllers\ProxyController;
use App\Http\Controllers\BridgeController;
use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ModerationController;
use Illuminate\Support\Facades\Route;

Route::get('/healthz', HealthController::class);

Route::get('/media/{media}', [MediaController::class, 'show'])->name('media.show');

Route::get('/', [FeedPageController::class, 'index'])->name('feed');
Route::get('/post/{post}', [FeedPageController::class, 'show'])->name('post.show');

Route::get('/bibliotheque', [\App\Http\Controllers\LibraryController::class, 'index'])->name('library.index');
Route::get('/bibliotheque/{slug}', [\App\Http\Controllers\LibraryController::class, 'showBook'])->name('library.book');
Route::get('/bibliotheque/{book}/{chapter}', [\App\Http\Controllers\LibraryController::class, 'showChapter'])->name('library.chapter');
Route::post('/bibliotheque/{book}/{chapter}/complete', [\App\Http\Controllers\LibraryController::class, 'markChapterComplete'])->name('library.chapter.complete');

Route::get('/telechargement/livre/{slug}.{format}', [\App\Http\Controllers\DownloadController::class, 'downloadBook'])
    ->where('format', 'pdf|epub')
    ->name('download.book');
Route::get('/telechargement/livre/{book}/chapitre/{chapter}.{format}', [\App\Http\Controllers\DownloadController::class, 'downloadChapter'])
    ->where('format', 'pdf|epub')
    ->name('download.chapter');

Route::get('/encyclopedie', [\App\Http\Controllers\EncyclopediaController::class, 'index'])->name('encyclopedia.index');
Route::get('/encyclopedie/{path}', [\App\Http\Controllers\EncyclopediaController::class, 'show'])
    ->where('path', '.*')
    ->name('encyclopedia.show');

Route::get('/profil/{handle}', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
Route::get('/mon-compte', [\App\Http\Controllers\AccountController::class, 'index'])->name('account.index');
Route::put('/mon-compte', [\App\Http\Controllers\AccountController::class, 'update'])->name('account.update');
Route::post('/account/notifications', [\App\Http\Controllers\AccountController::class, 'updateNotifications'])->name('account.notifications');
Route::post('/account/theme', [\App\Http\Controllers\AccountController::class, 'updateTheme'])->name('account.theme');
Route::post('/mon-compte/token', [\App\Http\Controllers\AccountController::class, 'createToken'])->name('account.token.create');
Route::delete('/mon-compte/token/{token}', [\App\Http\Controllers\AccountController::class, 'revokeToken'])->name('account.token.revoke');
Route::get('/favoris', [\App\Http\Controllers\FavoriteController::class, 'index'])->name('favorites.index');
Route::post('/favoris', [\App\Http\Controllers\FavoriteController::class, 'toggle'])->name('favorites.toggle');
Route::get('/recherche', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');
Route::get('/api/recherche', [\App\Http\Controllers\SearchController::class, 'api'])->name('search.api');
Route::post('/api/theme', [\App\Http\Controllers\AccountController::class, 'updateTheme'])->name('api.theme');
Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.read');
Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
Route::get('/mon-acces', [\App\Http\Controllers\AccountController::class, 'access'])->name('account.access');
Route::post('/mon-acces/refresh', [\App\Http\Controllers\AccountController::class, 'refresh'])->name('account.refresh');

Route::post('/livraison/ajouter', [\App\Http\Controllers\DeliveryController::class, 'addEmail'])->name('delivery.add');
Route::delete('/livraison/{deliveryEmail}', [\App\Http\Controllers\DeliveryController::class, 'removeEmail'])->name('delivery.remove');
Route::post('/livraison/{deliveryEmail}/toggle', [\App\Http\Controllers\DeliveryController::class, 'toggleEmail'])->name('delivery.toggle');
Route::post('/livraison/{deliveryEmail}/test', [\App\Http\Controllers\DeliveryController::class, 'testSend'])->name('delivery.test');

Route::get('/connexion', [ClientAuthController::class, 'showLogin'])->name('login');
Route::post('/connexion', [ClientAuthController::class, 'sendMagicLink'])->name('login.send');
Route::get('/magic/{token}', [ClientAuthController::class, 'consumeMagicLink'])->name('magic.consume');
Route::post('/deconnexion', [ClientAuthController::class, 'logout'])->name('logout');

Route::get('/proxy/arquanzia', [ProxyController::class, 'arquanzia'])
    ->middleware('shopify.proxy')
    ->name('proxy.arquanzia');

Route::get('/bridge', [BridgeController::class, 'consume'])->name('bridge');

Route::post('/post/{post}/comments', [\App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
Route::post('/post/{post}/reactions', [\App\Http\Controllers\ReactionController::class, 'toggle'])->name('reactions.toggle');

Route::post('/webhooks/shopify/orders-paid', [WebhookController::class, 'ordersPaid'])
    ->middleware('shopify.webhook')
    ->name('webhook.orders-paid');

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

        Route::get('/moderation', [ModerationController::class, 'index'])->name('moderation.index');
        Route::delete('/comments/{comment}', [ModerationController::class, 'deleteComment'])->name('comments.destroy');
        Route::post('/users/{user}/readonly', [ModerationController::class, 'toggleReadonly'])->name('users.readonly');
        Route::post('/users/{user}/ban', [ModerationController::class, 'toggleBan'])->name('users.ban');

        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/entitlement', [\App\Http\Controllers\Admin\UserController::class, 'updateEntitlement'])->name('users.entitlement');
        Route::post('/users/{user}/sync-order', [\App\Http\Controllers\Admin\UserController::class, 'syncOrder'])->name('users.sync-order');
        Route::post('/users/{user}/ban-handle', [\App\Http\Controllers\Admin\UserController::class, 'banHandle'])->name('users.ban-handle');
        Route::post('/users/{user}/reset-handle-bans', [\App\Http\Controllers\Admin\UserController::class, 'resetHandleBans'])->name('users.reset-handle-bans');

        Route::resource('books', \App\Http\Controllers\Admin\BookController::class)->except(['show']);
        Route::post('/books/{book}/export', [\App\Http\Controllers\Admin\BookController::class, 'generateExport'])->name('books.export');
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

        Route::get('/delivery', [\App\Http\Controllers\Admin\DeliveryController::class, 'index'])->name('delivery.index');
        Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/audit', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit.index');

        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/logo', [\App\Http\Controllers\Admin\SettingsController::class, 'updateLogo'])->name('settings.logo');
        Route::delete('/settings/logo', [\App\Http\Controllers\Admin\SettingsController::class, 'deleteLogo'])->name('settings.logo.delete');
        Route::post('/settings/name', [\App\Http\Controllers\Admin\SettingsController::class, 'updateSiteName'])->name('settings.name');
        Route::post('/delivery/{deliveryJob}/retry', [\App\Http\Controllers\Admin\DeliveryController::class, 'retryJob'])->name('delivery.retry');
        Route::post('/delivery/email/{deliveryEmail}/disable', [\App\Http\Controllers\Admin\DeliveryController::class, 'disableEmail'])->name('delivery.disable');
        Route::post('/delivery/chapter/{chapter}/dispatch', [\App\Http\Controllers\Admin\DeliveryController::class, 'dispatchChapter'])->name('delivery.dispatch');
    });
});
