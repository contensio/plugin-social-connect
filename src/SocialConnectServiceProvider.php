<?php

/**
 * Social Connect — Contensio plugin.
 * Sign in with Google, GitHub, Facebook, or Microsoft.
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * LICENSE: AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 */

namespace Contensio\SocialConnect;

use Contensio\Support\Hook;
use Contensio\SocialConnect\Support\Providers;
use Illuminate\Support\ServiceProvider;

/**
 * The entry point of the Social Connect plugin.
 *
 * When Contensio boots this provider (via PluginRegistry), it:
 *   1. Registers our views under the `social-connect::` namespace.
 *   2. Loads our routes + migrations so enable can auto-run them.
 *   3. Merges Socialite provider credentials into config('services.*')
 *      so Socialite's drivers resolve cleanly.
 *   4. Registers three Hook callbacks that inject UI into core views:
 *        - login.after_form     → "Continue with X" buttons on the login screen
 *        - profile.sections     → Linked accounts card on /admin/profile
 *        - settings.hub_cards   → Social Connect tile on /admin/settings
 *
 * Because this all runs from boot(), toggling the plugin off in admin
 * removes all of it on the next request — no lingering effects.
 */
class SocialConnectServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Views accessed as `social-connect::admin.settings`, `social-connect::partials.login-buttons`, etc.
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'social-connect');

        // Routes (public OAuth flow + authenticated unlink + admin settings)
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Migrations — run automatically on plugin enable (PluginController)
        // or manually via `php artisan migrate`
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Hook into Laravel's services.* config so Socialite drivers find
        // their credentials without users editing .env
        try {
            Providers::apply($this->app);
        } catch (\Throwable) {
            // Settings table may not exist during initial install
        }

        // ── Inject UI into core views via Contensio's Hook system ────────

        Hook::add('login.after_form', function () {
            // Only render if at least one provider is configured
            $providers = Providers::enabled();
            if (empty($providers)) {
                return '';
            }
            return view('social-connect::partials.login-buttons', [
                'providers' => $providers,
            ])->render();
        });

        Hook::add('profile.sections', function ($user) {
            if (! $user) {
                return '';
            }
            return view('social-connect::partials.profile-linked-accounts', [
                'user' => $user,
            ])->render();
        });

        Hook::add('settings.hub_cards', function () {
            return view('social-connect::partials.settings-hub-card')->render();
        });
    }
}
