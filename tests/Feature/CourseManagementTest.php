<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_create_and_edit_courses(): void
    {
        $admin = User::factory()->admin()->create();

        // List
        $this->actingAs($admin)
            ->get('/admin/courses')
            ->assertOk()
            ->assertSee('Cursos Académicos');

        // Create
        $response = $this->actingAs($admin)->post('/admin/courses', [
            'name' => 'Décimo A',
            'code' => '10mo-a',
            'description' => 'Paralelo A',
            'active' => true,
        ]);

        $response->assertRedirect('/admin/courses');
        $this->assertDatabaseHas('courses', [
            'name' => 'Décimo A',
            'code' => '10MO-A',
            'active' => true,
        ]);

        // Edit
        $course = Course::where('code', '10MO-A')->firstOrFail();

        $updateResponse = $this->actingAs($admin)->put("/admin/courses/{$course->id}", [
            'name' => 'Décimo A Editado',
            'code' => '10MO-A',
            'description' => 'Nueva descripción',
            'active' => true,
        ]);

        $updateResponse->assertRedirect('/admin/courses');
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Décimo A Editado',
        ]);
    }

    public function test_teacher_and_student_cannot_access_course_management(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();

        $this->actingAs($teacher)->get('/admin/courses')->assertForbidden();
        $this->actingAs($teacher)->post('/admin/courses', ['name' => 'Test', 'code' => 'TST'])->assertForbidden();

        $this->actingAs($student)->get('/admin/courses')->assertForbidden();
        $this->actingAs($student)->post('/admin/courses', ['name' => 'Test', 'code' => 'TST'])->assertForbidden();
    }

    public function test_duplicate_course_code_is_rejected_case_insensitively(): void
    {
        $admin = User::factory()->admin()->create();
        Course::factory()->create(['code' => '8VO-A']);

        $response = $this->actingAs($admin)->post('/admin/courses', [
            'name' => 'Otro Octavo',
            'code' => '8vo-a',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_course_code_is_normalized_to_uppercase(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/courses', [
            'name' => 'Septimo B',
            'code' => '  7mo-b  ',
        ]);

        $this->assertDatabaseHas('courses', [
            'code' => '7MO-B',
        ]);
    }

    public function test_admin_can_toggle_course_status(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['active' => true]);

        $this->actingAs($admin)->patch("/admin/courses/{$course->id}/toggle-status")
            ->assertRedirect('/admin/courses');

        $this->assertFalse($course->fresh()->active);

        $this->actingAs($admin)->patch("/admin/courses/{$course->id}/toggle-status")
            ->assertRedirect('/admin/courses');

        $this->assertTrue($course->fresh()->active);
    }

    public function test_physical_deletion_route_does_not_exist_for_courses(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.courses.destroy'));

        $this->actingAs($admin)->delete("/admin/courses/{$course->id}")
            ->assertStatus(405);
    }
}
