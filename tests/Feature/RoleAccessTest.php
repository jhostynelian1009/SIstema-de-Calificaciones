<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_dashboard_and_is_blocked_from_others(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Panel de Administración');

        $this->actingAs($admin)
            ->get('/teacher/dashboard')
            ->assertForbidden();

        $this->actingAs($admin)
            ->get('/student/dashboard')
            ->assertForbidden();
    }

    public function test_teacher_can_access_teacher_dashboard_and_is_blocked_from_others(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->get('/teacher/dashboard')
            ->assertOk()
            ->assertSee('Panel de Docente');

        $this->actingAs($teacher)
            ->get('/admin/dashboard')
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get('/student/dashboard')
            ->assertForbidden();
    }

    public function test_student_can_access_student_dashboard_and_is_blocked_from_others(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get('/student/dashboard')
            ->assertOk()
            ->assertSee('Mis Calificaciones');

        $this->actingAs($student)
            ->get('/admin/dashboard')
            ->assertForbidden();

        $this->actingAs($student)
            ->get('/teacher/dashboard')
            ->assertForbidden();
    }

    public function test_central_dashboard_redirects_to_role_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/dashboard')->assertRedirect('/admin/dashboard');

        $teacher = User::factory()->teacher()->create();
        $this->actingAs($teacher)->get('/dashboard')->assertRedirect('/teacher/dashboard');

        $student = User::factory()->student()->create();
        $this->actingAs($student)->get('/dashboard')->assertRedirect('/student/dashboard');
    }

    public function test_deactivated_user_loses_access_on_next_request(): void
    {
        $user = User::factory()->admin()->create(['active' => true]);

        $this->actingAs($user);
        $this->get('/admin/dashboard')->assertOk();

        // Deactivate user in database
        $user->update(['active' => false]);

        // Subsequent request should log out and redirect to login
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
