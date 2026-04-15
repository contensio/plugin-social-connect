<?php

/**
 * Social Connect — Contensio plugin.
 * Sign in with Google, GitHub, Facebook, or Microsoft.
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * LICENSE: AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 */

namespace Contensio\SocialConnect\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialLogin extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'linked_at'     => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
