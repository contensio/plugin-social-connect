<?php

/**
 * Social Connect — Contensio plugin.
 * Routes for OAuth redirect / callback, unlink, and admin settings.
 */

use Contensio\SocialConnect\Http\Controllers\ConnectController;
use Contensio\SocialConnect\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// Public OAuth flow (anonymous users sign in via provider)
Route::middleware('web')->group(function () {
    Route::get('/login/{provider}',          [ConnectController::class, 'redirect'])
        ->name('socialconnect.redirect')
        ->where('provider', '[a-z_-]+');

    Route::get('/login/{provider}/callback', [ConnectController::class, 'callback'])
        ->name('socialconnect.callback')
        ->where('provider', '[a-z_-]+');
});

// Authenticated: unlink a provider from the current user's account
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/account/social/{provider}/unlink', [ConnectController::class, 'unlink'])
        ->name('socialconnect.unlink')
        ->where('provider', '[a-z_-]+');
});

// Admin settings page — lives under the configured admin prefix
Route::prefix(config('contensio.route_prefix', 'admin'))
    ->middleware(['web', 'contensio.auth', 'contensio.admin'])
    ->group(function () {
        Route::get('/settings/social-connect',  [SettingsController::class, 'index'])
            ->name('socialconnect.settings');
        Route::post('/settings/social-connect', [SettingsController::class, 'save'])
            ->name('socialconnect.settings.save');
    });
