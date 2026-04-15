<?php

/**
 * Social Connect — Contensio plugin.
 * Sign in with Google, GitHub, Facebook, or Microsoft.
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * LICENSE: AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 */

namespace Contensio\SocialConnect\Http\Controllers;

use Contensio\SocialConnect\Support\Providers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Admin settings page for Social Connect — /admin/settings/social-connect.
 */
class SettingsController extends Controller
{
    public function index()
    {
        $providers = [];
        foreach (Providers::all() as $key => $meta) {
            $providers[$key] = array_merge($meta, [
                'config'   => Providers::settingsFor($key),
                'redirect' => Providers::redirectUrlFor($key),
            ]);
        }

        return view('social-connect::admin.settings', compact('providers'));
    }

    public function save(Request $request)
    {
        foreach (array_keys(Providers::all()) as $provider) {
            Providers::saveSettings($provider, [
                'enabled'       => $request->boolean("providers.{$provider}.enabled"),
                'client_id'     => $request->input("providers.{$provider}.client_id"),
                'client_secret' => $request->input("providers.{$provider}.client_secret"),
            ]);
        }

        return redirect()
            ->route('socialconnect.settings')
            ->with('success', 'Social Connect settings saved.');
    }
}
