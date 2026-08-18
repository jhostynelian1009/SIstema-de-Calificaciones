<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_create_and_edit_subjects(): void
    {
        $admin = User::factory()->admin()->create();

        // List
        $this->actingAs($admin)
            ->get('/admin/subjects')
            ->assertOk()
            ->assertSee('Asignaturas Académicas');

        // Create
        $response = $this->actingAs($admin)->post('/admin/subjects', [
            'name' => 'Física',
            'code' => 'fis',
            'description' => 'Materia de física',
            'active' => true,
        ]);

        $response->assertRedirect('/admin/subjects');
        $this->assertDatabaseHas('subjects', [
            'name' => 'Física',
            'code' => 'FIS',
            'active' => true,
        ]);

        // Edit
        $subject = Subject::where('code', 'FIS')->firstOrFail();

        $updateResponse = $this->actingAs($admin)->put("/admin/subjects/{$subject->id}", [
            'name' => 'Física Aplicada',
            'code' => 'FIS',
            'description' => 'Física aplicada',
            'active' => true,
        ]);

        $updateResponse->assertRedirect('/admin/subjects');
        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'name' => 'Física Aplicada',
        ]);
    }

    public function test_teacher_and_student_cannot_access_subject_management(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();

        $this->actingAs($teacher)->get('/admin/subjects')->assertForbidden();
        $this->actingAs($teacher)->post('/admin/subjects', ['name' => 'Test', 'code' => 'TST'])->assertForbidden();

        $this->actingAs($student)->get('/admin/subjects')->assertForbidden();
        $this->actingAs($student)->post('/admin/subjects', ['name' => 'Test', 'code' => 'TST'])->assertForbidden();
    }

    public function test_duplicate_subject_code_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        Subject::factory()->create(['code' => 'MAT']);

        $response = $this->actingAs($admin)->post('/admin/subjects', [
            'name' => 'Otra Matemática',
            'code' => 'mat',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_subject_code_is_normalized_to_uppercase(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/subjects', [
            'name' => 'Química',
            'code' => '  qui  ',
        ]);

        $this->assertDatabaseHas('subjects', [
            'code' => 'QUI',
        ]);
    }

    public function test_admin_can_toggle_subject_status(): void
    {
        $admin = User::factory()->admin()->create();
        $subject = Subject::factory()->create(['active' => true]);

        $this->actingAs($admin)->patch("/admin/subjects/{$subject->id}/toggle-status")
            ->assertRedirect('/admin/subjects');

        $this->assertFalse($subject->fresh()->active);
    }
}
