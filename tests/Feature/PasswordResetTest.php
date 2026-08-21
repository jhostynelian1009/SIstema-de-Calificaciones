<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_password_reset_link(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/password/email', [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status');
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->post('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secure-password!1',
            'password_confirmation' => 'new-secure-password!1',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertTrue(Hash::check('new-secure-password!1', $user->fresh()->password));
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/password/reset', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-secure-password!1',
            'password_confirmation' => 'new-secure-password!1',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
