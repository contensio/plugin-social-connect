<?php

/**
 * Social Connect — Contensio plugin.
 * Sign in with Google, GitHub, Facebook, or Microsoft.
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * LICENSE: AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 */

namespace Contensio\SocialConnect\Support;

use Contensio\Models\Setting;

/**
 * Provider catalog + settings storage for Social Connect.
 *
 * Per-provider config is stored in the core settings table:
 *   module       = "social_connect"
 *   setting_key  = "google" (or "github" / "facebook" / "microsoft")
 *   value        = '{"enabled":true,"client_id":"...","client_secret":"..."}'
 *
 * At service-provider boot, `apply()` populates Laravel's `services.{provider}`
 * config so Socialite's drivers resolve without users editing .env files.
 * Values already set via .env take precedence (standard Laravel config merge).
 */
class Providers
{
    /** Supported providers + display metadata. */
    public static function all(): array
    {
        return [
            'google' => [
                'label'  => 'Google',
                'icon'   => 'bi-google',
                'color'  => '#ea4335',
                'scopes' => ['email', 'profile'],
                'docs'   => 'https://console.cloud.google.com/apis/credentials',
            ],
            'github' => [
                'label'  => 'GitHub',
                'icon'   => 'bi-github',
                'color'  => '#24292f',
                'scopes' => ['user:email'],
                'docs'   => 'https://github.com/settings/developers',
            ],
            'facebook' => [
                'label'  => 'Facebook',
                'icon'   => 'bi-facebook',
                'color'  => '#1877f2',
                'scopes' => ['email'],
                'docs'   => 'https://developers.facebook.com/apps',
            ],
            'microsoft' => [
                'label'  => 'Microsoft',
                'icon'   => 'bi-microsoft',
                'color'  => '#00a4ef',
                'scopes' => ['openid', 'profile', 'email', 'User.Read'],
                'docs'   => 'https://portal.azure.com/',
            ],
        ];
    }

    /** Saved settings for one provider, merged with defaults. */
    public static function settingsFor(string $provider): array
    {
        try {
            $raw = Setting::where('module', 'social_connect')
                ->where('setting_key', $provider)
                ->value('value');
            if ($raw) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    return array_merge(
                        ['enabled' => false, 'client_id' => '', 'client_secret' => ''],
                        $decoded
                    );
                }
            }
        } catch (\Throwable) {
            // Settings table not ready yet
        }

        return ['enabled' => false, 'client_id' => '', 'client_secret' => ''];
    }

    /** Persist a provider's settings. */
    public static function saveSettings(string $provider, array $values): void
    {
        $stored = [
            'enabled'       => (bool) ($values['enabled']       ?? false),
            'client_id'     => trim((string) ($values['client_id']     ?? '')),
            'client_secret' => trim((string) ($values['client_secret'] ?? '')),
        ];

        Setting::updateOrCreate(
            ['module' => 'social_connect', 'setting_key' => $provider],
            ['value' => json_encode($stored, JSON_UNESCAPED_SLASHES)]
        );
    }

    /** Providers that are supported, configured, and toggled on. */
    public static function enabled(): array
    {
        $out = [];
        foreach (static::all() as $key => $meta) {
            $cfg = static::settingsFor($key);
            if (! empty($cfg['enabled']) && ! empty($cfg['client_id']) && ! empty($cfg['client_secret'])) {
                $out[$key] = $meta;
            }
        }
        return $out;
    }

    /** OAuth redirect URL Contensio uses for a given provider. */
    public static function redirectUrlFor(string $provider): string
    {
        return url('/login/' . $provider . '/callback');
    }

    /**
     * Populate Laravel's `services.*` config from our saved settings so
     * Socialite's drivers find credentials at resolve-time.
     * Respects .env overrides (Laravel merge behavior).
     */
    public static function apply(\Illuminate\Contracts\Foundation\Application $app): void
    {
        foreach (array_keys(static::all()) as $provider) {
            $existing = $app['config']->get("services.{$provider}", []);

            if (empty($existing['client_id'])) {
                $cfg = static::settingsFor($provider);
                $app['config']->set("services.{$provider}", [
                    'client_id'     => $cfg['client_id']     ?: null,
                    'client_secret' => $cfg['client_secret'] ?: null,
                    'redirect'      => static::redirectUrlFor($provider),
                ]);
            } elseif (empty($existing['redirect'])) {
                $app['config']->set("services.{$provider}.redirect", static::redirectUrlFor($provider));
            }
        }
    }
}
