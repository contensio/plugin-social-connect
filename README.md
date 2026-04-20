# Social Connect - Contensio plugin

<p>
  <a href="https://packagist.org/packages/contensio/plugin-social-connect"><img src="https://img.shields.io/packagist/v/contensio/plugin-social-connect?include_prereleases&label=packagist" alt="Latest Version"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-AGPL--3.0--or--later-blue" alt="License"></a>
</p>

Sign in to Contensio with Google, GitHub, Facebook, or Microsoft accounts.

**Social Connect** is the first official Contensio plugin. It demonstrates how plugins extend the admin panel with their own UI (login buttons, profile sections, settings pages) without modifying core files.

## Install

### Option A - Composer (developers)

```bash
composer require contensio/plugin-social-connect
```

Then in the Contensio admin: **Plugins → Enable** next to "Social Connect". Migrations run automatically on enable.

### Option B - ZIP upload (non-technical users)

1. Download the latest release ZIP from [github.com/contensio/plugin-social-connect/releases](https://github.com/contensio/plugin-social-connect/releases)
2. In Contensio admin: **Plugins → Install Plugin** → upload the ZIP
3. Click **Enable**

Both paths produce the same result.

## Configure

After enabling the plugin:

1. Go to **Configuration → Social Connect**
2. For each provider you want to enable (Google / GitHub / Facebook / Microsoft):
   - Copy the **Redirect URL** from the settings page
   - Create an OAuth app at the provider's developer console, pasting in that Redirect URL
   - Copy back the **Client ID** and **Client Secret**
   - Toggle **Enabled** on, save
3. "Continue with Google" (etc.) buttons appear on the login screen

### Provider developer consoles

| Provider | Where to create an OAuth app |
|---|---|
| Google    | [console.cloud.google.com](https://console.cloud.google.com/apis/credentials) → OAuth 2.0 Client IDs |
| GitHub    | [github.com/settings/developers](https://github.com/settings/developers) → OAuth Apps |
| Facebook  | [developers.facebook.com/apps](https://developers.facebook.com/apps) |
| Microsoft | [portal.azure.com](https://portal.azure.com/) → Microsoft Entra ID → App registrations |

## How it works

- **Sign-in only for existing users.** Social Connect does not auto-register new Contensio users. If the provider's email doesn't match an existing Contensio account, sign-in is rejected with a friendly error. Administrators create users in **Users → Add User** first.
- **Email matching.** When a user signs in with Google for the first time, Contensio looks up their email in the `users` table. If it matches an existing active account, the social identity is linked automatically - subsequent sign-ins go straight through.
- **2FA still applies.** Users with 2FA enabled are still challenged for a code after social sign-in. Social Connect is not a 2FA bypass.
- **Disabled accounts (`is_active = false`) are rejected.**

## Linked accounts

From **Profile → Linked accounts**, users can:
- **Connect** an additional provider to their existing account
- **Unlink** a provider they no longer want to use

A provider can only be linked to one Contensio account at a time.

## Architecture notes

Social Connect ships with:
- A new table `social_logins` (created on plugin enable)
- Routes at `/login/{provider}` and `/login/{provider}/callback`
- An admin settings page at `/admin/settings/social-connect`
- A Configuration hub card
- Login-page provider buttons
- Profile-page "Linked accounts" card

All of the above is injected via Contensio's Hook system - zero modifications to core files.

## License

AGPL-3.0-or-later. Copyright © 2026 Iosif Gabriel Chimilevschi. Social Connect is operated by [Host Server SRL](https://hostserver.ro).
