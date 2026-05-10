<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;

final class RecordUserFirstLogin
{
    public function handle(Login $event): void
    {
        $authenticatable = $event->user;

        if (! $authenticatable instanceof User) {
            return;
        }

        if ($authenticatable->first_login_at !== null) {
            return;
        }

        $authenticatable->forceFill([
            'first_login_at' => now(),
        ])->saveQuietly();
    }
}
