<?php

use App\Http\Controllers\Admin\BettingTipController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\SubscriptionRequestController as AdminSubscriptionRequestController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeagueStatsController;
use App\Http\Controllers\PremiumController;
use App\Http\Controllers\ResultsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriptionRequestController;
use App\Http\Controllers\TipOfTheDayController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/premium', [PremiumController::class, 'index'])->name('premium');
Route::get('/tip-of-the-day', [TipOfTheDayController::class, 'index'])->name('tip-of-the-day');
Route::get('/results', [ResultsController::class, 'index'])->name('results');
Route::get('/league-stats', [LeagueStatsController::class, 'index'])->name('league-stats');
Route::get('/subscribe', [ContactController::class, 'index'])->name('subscribe');
Route::post('/subscribe', [ContactController::class, 'send'])->name('subscribe.send');
Route::get('/contact', [ContactController::class, 'showContact'])->name('contact');
Route::post('/contact', [ContactController::class, 'sendContact'])->name('contact.send');
Route::post('/premium/request', [SubscriptionRequestController::class, 'store'])->name('premium.request');
Route::post('/premium/access', [PremiumController::class, 'access'])->name('premium.access');
Route::post('/premium/revoke', [PremiumController::class, 'revoke'])->name('premium.revoke');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Admin — requires authenticated admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('betting-tips', BettingTipController::class);
    Route::patch('betting-tips/{bettingTip}/status', [BettingTipController::class, 'markStatus'])->name('betting-tips.mark-status');
    Route::resource('blogs', AdminBlogController::class);
    Route::get('subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
    Route::delete('subscribers/{email}', [SubscriberController::class, 'destroy'])->name('subscribers.destroy');
    Route::get('contacts', [ContactMessageController::class, 'index'])->name('contacts.index');
    Route::post('contacts/send-tips', [ContactMessageController::class, 'sendDailyTips'])->name('contacts.send-tips');
    Route::get('contacts/{contact}', [ContactMessageController::class, 'show'])->name('contacts.show');
    Route::delete('contacts/{contact}', [ContactMessageController::class, 'destroy'])->name('contacts.destroy');
    Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::put('settings/password', [AdminSettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('settings/managers', [AdminSettingsController::class, 'addManager'])->name('settings.managers.store');
    Route::delete('settings/managers/{user}', [AdminSettingsController::class, 'removeManager'])->name('settings.managers.destroy');
    Route::get('subscription-requests', [AdminSubscriptionRequestController::class, 'index'])->name('subscription-requests.index');
    Route::get('subscription-requests/{subscriptionRequest}', [AdminSubscriptionRequestController::class, 'show'])->name('subscription-requests.show');
    Route::patch('subscription-requests/{subscriptionRequest}/approve', [AdminSubscriptionRequestController::class, 'approve'])->name('subscription-requests.approve');
    Route::patch('subscription-requests/{subscriptionRequest}/reject', [AdminSubscriptionRequestController::class, 'reject'])->name('subscription-requests.reject');
    Route::delete('subscription-requests/{subscriptionRequest}', [AdminSubscriptionRequestController::class, 'destroy'])->name('subscription-requests.destroy');
});
