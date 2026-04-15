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

use App\Models\User;
use Contensio\SocialConnect\Models\SocialLogin;
use Contensio\SocialConnect\Support\Providers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

/**
 * OAuth redirect / callback / unlink handler.
 *
 * Flows supported:
 *   1. Anonymous sign-in — visitor hits /login/{provider}, OAuth flow runs,
 *      we match an existing user by email and log them in.
 *   2. Profile-link — already-logged-in user hits /login/{provider}?connect=1,
 *      comes back, we attach the social identity to their current account.
 *   3. Unlink — POST to /account/social/{provider}/unlink removes the link.
 *
 * Contensio is admin-managed: we do NOT auto-register users via social login.
 * Unknown emails are rejected with a clear error pointing to the admin.
 */
class ConnectController extends Controller
{
    public function redirect(Request $request, string $provider)
    {
        if (! $this->isAvailable($provider)) {
            return redirect()->route('cms.login')->withErrors([
                'social' => 'This sign-in provider is not available right now.',
            ]);
        }

        if ($request->boolean('connect') && Auth::check()) {
            $request->session()->put('socialconnect.connect_user_id', Auth::id());
        }

        $driver = Socialite::driver($provider);
        $scopes = Providers::all()[$provider]['scopes'] ?? [];
        if (! empty($scopes) && method_exists($driver, 'scopes')) {
            $driver->scopes($scopes);
        }

        return $driver->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        if (! $this->isAvailable($provider)) {
            return redirect()->route('cms.login')->withErrors([
                'social' => 'This sign-in provider is not available.',
            ]);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (InvalidStateException) {
            return redirect()->route('cms.login')->withErrors([
                'social' => 'Sign-in could not be completed. Please try again.',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('cms.login')->withErrors([
                'social' => 'Sign-in failed: ' . $e->getMessage(),
            ]);
        }

        $providerUserId = (string) $socialUser->getId();
        $email          = $socialUser->getEmail();
        $name           = $socialUser->getName() ?: $socialUser->getNickname();
        $avatar         = $socialUser->getAvatar();

        $link = SocialLogin::where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();

        $connectUserId = $request->session()->pull('socialconnect.connect_user_id');

        // --- Flow 1: "Connect from profile" ---
        if ($connectUserId) {
            $user = User::find($connectUserId);
            if (! $user) {
                return redirect()->route('cms.login')
                    ->withErrors(['social' => 'Session expired. Please try again.']);
            }

            if ($link && (int) $link->user_id !== (int) $user->id) {
                return redirect()->route('cms.admin.profile')->withErrors([
                    'social' => "This {$provider} account is already linked to a different user.",
                ]);
            }

            $this->upsertLink($user, $provider, $providerUserId, $email, $name, $avatar);

            return redirect()->route('cms.admin.profile')
                ->with('success', ucfirst($provider) . ' account linked.');
        }

        // --- Flow 2: known link — sign in ---
        if ($link) {
            $user = User::find($link->user_id);
            if (! $user) {
                $link->delete(); // orphaned link cleanup
                return $this->reject('That account no longer exists.');
            }
            return $this->signIn($user, $provider, $providerUserId, $email, $name, $avatar);
        }

        // --- Flow 3: no link yet — match by email ---
        if (! $email) {
            return $this->reject("{$provider} didn't share an email address with us. Ask your administrator to create your account manually.");
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            return $this->reject("No account matches {$email}. Ask your administrator to create one first.");
        }

        return $this->signIn($user, $provider, $providerUserId, $email, $name, $avatar);
    }

    public function unlink(Request $request, string $provider)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('cms.login');
        }

        SocialLogin::where('user_id', $user->id)
            ->where('provider', $provider)
            ->delete();

        return redirect()->route('cms.admin.profile')
            ->with('success', ucfirst($provider) . ' account unlinked.');
    }

    // ── Internal ────────────────────────────────────────────────────────────

    protected function isAvailable(string $provider): bool
    {
        return array_key_exists($provider, Providers::enabled());
    }

    protected function upsertLink(User $user, string $provider, string $providerUserId, ?string $email, ?string $name, ?string $avatar): void
    {
        SocialLogin::updateOrCreate(
            ['provider' => $provider, 'provider_user_id' => $providerUserId],
            [
                'user_id'       => $user->id,
                'email'         => $email,
                'name'          => $name,
                'avatar'        => $avatar,
                'linked_at'     => now(),
                'last_login_at' => now(),
            ]
        );
    }

    protected function signIn(User $user, string $provider, string $providerUserId, ?string $email, ?string $name, ?string $avatar)
    {
        if (! $user->is_active) {
            return $this->reject('Your account has been disabled.');
        }

        $this->upsertLink($user, $provider, $providerUserId, $email, $name, $avatar);

        // Respect 2FA — social auth shouldn't bypass the challenge.
        if (! is_null($user->two_factor_secret) && ! is_null($user->two_factor_confirmed_at)) {
            request()->session()->put([
                'login.id'       => $user->getAuthIdentifier(),
                'login.remember' => true,
            ]);
            return redirect()->route('two-factor.login');
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        if ($user->canAccessAdmin()) {
            return redirect()->intended(route('cms.admin.dashboard'));
        }
        return redirect()->intended(route('cms.home'));
    }

    protected function reject(string $message)
    {
        return redirect()->route('cms.login')->withErrors(['social' => $message]);
    }
}
