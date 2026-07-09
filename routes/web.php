<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UrlController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\RedirectController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});

// Authentication
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Invitation Acceptance (Registration)
Route::get('/invite/{token}', [InvitationController::class, 'showRegistrationForm'])->name('invite');
Route::post('/invite/{token}', [InvitationController::class, 'register']);

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // URL Management
    Route::post('/urls', [UrlController::class, 'store'])->name('urls.store');
    
    // Invitations
    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
});

// Short URL Redirect Route
// Rate limited: 60 redirects per minute per IP address.
// Exceeding this returns HTTP 429 Too Many Requests with a Retry-After header.
Route::get('/{short_code}', [RedirectController::class, 'redirect'])
    ->name('redirect')
    ->middleware('throttle:60,1');

