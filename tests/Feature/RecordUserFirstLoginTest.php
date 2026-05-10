<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RecordUserFirstLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_login_records_timestamp_and_second_login_does_not_change_it(): void
    {
        $user = User::factory()->patient()->create([
            'first_login_at' => null,
        ]);

        Auth::login($user);

        $user->refresh();
        $this->assertNotNull($user->first_login_at);

        $first = $user->first_login_at->copy();

        Auth::logout();
        Auth::login(User::query()->findOrFail($user->getKey()));

        $user->refresh();
        $this->assertTrue($first->equalTo($user->first_login_at));
    }
}
