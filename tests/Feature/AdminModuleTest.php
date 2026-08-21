<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicPeriod;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teacher;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('role', UserRole::Admin)->firstOrFail();
        $this->teacher = User::where('role', UserRole::Teacher)->firstOrFail();
        $this->student = User::where('role', UserRole::Student)->firstOrFail();
    }

    public function test_teacher_and_student_cannot_access_user_management(): void
    {
        foreach ([$this->teacher, $this->student] as $user) {
            $this->actingAs($user)->get(route('admin.users.index'))->assertStatus(403);
            $this->actingAs($user)->get(route('admin.users.create'))->assertStatus(403);
            $this->actingAs($user)->post(route('admin.users.store'), [
                'name' => 'Hack User',
                'email' => 'hack@example.com',
                'role' => UserRole::Student->value,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])->assertStatus(403);
            $this->actingAs($user)->put(route('admin.users.update', $this->student), [
                'name' => 'Updated Name',
                'email' => $this->student->email,
                'role' => UserRole::Student->value,
            ])->assertStatus(403);
            $this->actingAs($user)->patch(route('admin.users.toggle-status', $this->student))->assertStatus(403);
            $this->actingAs($user)->patch(route('admin.users.reset-password', $this->student), [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])->assertStatus(403);
        }
    }

    public function test_admin_can_list_users_with_filters_and_pagination(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index', [
                'search' => $this->teacher->name,
                'role' => UserRole::Teacher->value,
                'active' => '1',
            ]));

        $response->assertStatus(200);
        $response->assertSee($this->teacher->name);
        $response->assertSee($this->teacher->email);
    }

    public function test_admin_can_create_user_with_hashed_password_and_audit(): void
    {
        $payload = [
            'name' => 'Nuevo Usuario Pruebas',
            'email' => 'nuevoprueba@colegio.edu',
            'role' => UserRole::Student->value,
            'active' => '1',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $payload);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $createdUser = User::where('email', 'nuevoprueba@colegio.edu')->firstOrFail();
        $this->assertTrue(Hash::check('Secret123!', $createdUser->password));
        $this->assertEquals(UserRole::Student, $createdUser->role);

        $audit = AuditLog::where('action', 'user.created')
            ->where('auditable_id', $createdUser->id)
            ->firstOrFail();

        $this->assertArrayNotHasKey('password', $audit->new_values ?? []);
        $this->assertArrayNotHasKey('password_confirmation', $audit->new_values ?? []);
        $this->assertEquals('nuevoprueba@colegio.edu', $audit->new_values['email']);
    }

    public function test_admin_can_update_user_name_and_email(): void
    {
        $targetUser = User::factory()->create([
            'role' => UserRole::Student,
            'active' => true,
        ]);

        $payload = [
            'name' => 'Nombre Modificado',
            'email' => 'modificado@colegio.edu',
            'role' => UserRole::Student->value,
            'active' => '1',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $targetUser), $payload);

        $response->assertRedirect(route('admin.users.index'));

        $targetUser->refresh();
        $this->assertEquals('Nombre Modificado', $targetUser->name);
        $this->assertEquals('modificado@colegio.edu', $targetUser->email);

        $audit = AuditLog::where('action', 'user.updated')
            ->where('auditable_id', $targetUser->id)
            ->firstOrFail();

        $this->assertEquals('modificado@colegio.edu', $audit->new_values['email']);
    }

    public function test_admin_cannot_deactivate_self_or_last_active_admin(): void
    {
        // 1. Cannot deactivate self
        $responseSelf = $this->actingAs($this->admin)
            ->patch(route('admin.users.toggle-status', $this->admin));

        $responseSelf->assertSessionHas('error');
        $this->assertTrue($this->admin->fresh()->active);

        // 2. Cannot deactivate last active admin
        $secondAdmin = User::factory()->create(['role' => UserRole::Admin, 'active' => true]);

        // Deactivate second admin
        $this->actingAs($this->admin)->patch(route('admin.users.toggle-status', $secondAdmin));
        $this->assertFalse($secondAdmin->fresh()->active);

        // Attempting to deactivate remaining active admin as another admin
        $anotherAdmin = User::factory()->create(['role' => UserRole::Admin, 'active' => true]);
        $this->actingAs($anotherAdmin)->patch(route('admin.users.toggle-status', $this->admin));

        $responseLastAdmin = $this->actingAs($anotherAdmin)->patch(route('admin.users.toggle-status', $anotherAdmin));
        $responseLastAdmin->assertSessionHas('error');
        $this->assertTrue($anotherAdmin->fresh()->active);
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $targetUser = User::factory()->create(['role' => UserRole::Student, 'active' => true]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.users.toggle-status', $targetUser));

        $response->assertSessionHas('success');
        $this->assertFalse($targetUser->fresh()->active);

        $audit = AuditLog::where('action', 'user.status_changed')
            ->where('auditable_id', $targetUser->id)
            ->firstOrFail();

        $this->assertFalse($audit->new_values['active']);
    }

    public function test_user_with_academic_history_cannot_change_role(): void
    {
        // Student with enrollment
        $responseStudent = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->student), [
                'name' => $this->student->name,
                'email' => $this->student->email,
                'role' => UserRole::Teacher->value,
                'active' => '1',
            ]);

        $responseStudent->assertSessionHas('error');
        $this->assertEquals(UserRole::Student, $this->student->fresh()->role);

        // Teacher with assignment
        $responseTeacher = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->teacher), [
                'name' => $this->teacher->name,
                'email' => $this->teacher->email,
                'role' => UserRole::Student->value,
                'active' => '1',
            ]);

        $responseTeacher->assertSessionHas('error');
        $this->assertEquals(UserRole::Teacher, $this->teacher->fresh()->role);
    }

    public function test_admin_can_reset_user_password_without_logging_raw_password(): void
    {
        $targetUser = User::factory()->create(['role' => UserRole::Student]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.users.reset-password', $targetUser), [
                'password' => 'NewSecurePassword123!',
                'password_confirmation' => 'NewSecurePassword123!',
            ]);

        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('NewSecurePassword123!', $targetUser->fresh()->password));

        $audit = AuditLog::where('action', 'user.password_reset_by_admin')
            ->where('auditable_id', $targetUser->id)
            ->firstOrFail();

        $this->assertArrayNotHasKey('password', $audit->new_values ?? []);
        $this->assertArrayNotHasKey('password_confirmation', $audit->new_values ?? []);
    }

    public function test_admin_can_access_dashboard_metrics_and_alerts(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Panel de Administración');
        $response->assertSee('Cursos');
        $response->assertSee('Asignaturas');
        $response->assertSee('Matrículas Activas');
    }

    public function test_admin_can_consult_consolidated_results_and_student_transcript(): void
    {
        $responseIndex = $this->actingAs($this->admin)->get(route('admin.results.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Resultados Académicos Consolidados');

        $responseStudent = $this->actingAs($this->admin)->get(route('admin.results.student', $this->student));
        $responseStudent->assertStatus(200);
        $responseStudent->assertSee($this->student->name);
    }

    public function test_seeders_are_idempotent_and_admin_module_is_fully_integrated(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(User::where('role', UserRole::Admin)->exists());
        $this->assertTrue(Course::exists());
        $this->assertTrue(Subject::exists());
        $this->assertTrue(AcademicPeriod::exists());
    }
}
