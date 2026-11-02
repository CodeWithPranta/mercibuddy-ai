<?php

use App\Http\Controllers\ArtisanController;
use App\Http\Controllers\ArtisanQueryController;
use App\Http\Controllers\AssistantStreamController;
use App\Http\Controllers\Auth\GoogleSocialiteController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DefaultPagesController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TechnicalSpecificationController;
use Illuminate\Http\Request;
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

Route::get('/', [LandingPageController::class, 'index']);
Route::get('/artisans/{country}/{category}', [ArtisanController::class, 'index'])->name('artisans.index');
Route::get('/filtered-artisans', [ArtisanController::class, 'legacyIndex'])->middleware('restrict.filtered.page');
Route::get('/artisan/{artisan}/{slug?}', [ArtisanController::class, 'detail'])
    ->middleware(['auth', 'verified'])
    ->name('artisan.show');
Route::post('/artisan/{artisan}/query', [ArtisanQueryController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('artisan.query.store');
Route::get('/notifications', [NotificationController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('notifications.index');
Route::get('/artisan-detail/{id}', [ArtisanController::class, 'legacyDetail'])->middleware(['auth', 'verified']);
Route::view('profile', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/page/{slug}', [DefaultPagesController::class, 'index']);

Route::get('/technical-specification.md', TechnicalSpecificationController::class)
    ->name('technical-specification.download');

// AI chat assistant (JS + Server-Sent Events, no Livewire roundtrip)
Route::post('/assistant/stream', [AssistantStreamController::class, 'send'])
    ->middleware('throttle:30,1')
    ->name('assistant.stream');
Route::post('/assistant/clear', [AssistantStreamController::class, 'clear'])->name('assistant.clear');

// Mail Contact Page
Route::get('/contact', [ContactController::class, 'contactForm'])->name('contact');
// Define a route for the contact form submission
Route::post('/contact', [ContactController::class, 'sendEmail'])->name('contact.send');

// Route::view('profile', 'profile')
//     ->middleware(['auth'])
//     ->name('profile');

/**
 * Google authentication route
 */
Route::get('auth/google', [GoogleSocialiteController::class, 'redirectToGoogle']);
Route::get('callback/google', [GoogleSocialiteController::class, 'handleCallback']);

Route::post('/user', function (Request $request) {
    return response()->json([
        'name' => $request->name,
    ], 201);
});

require __DIR__.'/auth.php';
