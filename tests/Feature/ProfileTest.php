<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_name_and_email(): void
    {
        $user = User::factory()->student()->create([
            'name' => 'Original Name',
            'email' => 'original@calificaciones.local',
        ]);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'New Name',
            'email' => 'updated@calificaciones.local',
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'updated@calificaciones.local',
        ]);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'existing@calificaciones.local']);
        $user = User::factory()->student()->create(['email' => 'user@calificaciones.local']);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Updated Name',
            'email' => 'existing@calificaciones.local',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_profile_form_cannot_escalate_role_or_active_status(): void
    {
        $user = User::factory()->student()->create([
            'role' => UserRole::Student,
            'active' => true,
        ]);

        $this->actingAs($user)->put('/profile', [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'admin',
            'active' => false,
        ]);

        $user->refresh();

        $this->assertEquals(UserRole::Student, $user->role);
        $this->assertTrue($user->active);
    }

    public function test_password_change_requires_valid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123!',
            'password_confirmation' => 'newpassword123!',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('oldpassword123', $user->fresh()->password));
    }

    public function test_password_is_updated_and_hashed(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123!',
            'password_confirmation' => 'newpassword123!',
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('newpassword123!', $user->fresh()->password));
    }
}
