<?php

namespace NUSWhispers\Tests\Http\Controllers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use NUSWhispers\Models\User;
use NUSWhispers\Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function testExistingAdminAuthenticationStillWorks(): void
    {
        $this->withoutMix();

        $user = User::factory()->admin()->create(['password' => Hash::make('existing-password')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'existing-password'])
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
        $this->get('/admin/confessions')->assertOk();
    }

    public function testPasswordResetsUseTheExistingDatabaseTable(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->postJson('/password/email', ['email' => $user->email])->assertOk();
        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->post('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'updated-password',
            'password_confirmation' => 'updated-password',
        ])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('updated-password', $user->fresh()->password));
    }
}
