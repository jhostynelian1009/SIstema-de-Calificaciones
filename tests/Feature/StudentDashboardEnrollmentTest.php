<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDashboardEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sees_only_own_enrollment_information(): void
    {
        $student1 = User::factory()->student()->create(['name' => 'Estudiante Uno']);
        $student2 = User::factory()->student()->create(['name' => 'Estudiante Dos']);

        $course1 = Course::factory()->create(['name' => 'Matemáticas Octavo A', 'code' => '8VO-M']);
        $course2 = Course::factory()->create(['name' => 'Física Noveno B', 'code' => '9NO-F']);
        $period = AcademicPeriod::factory()->create(['active' => true]);

        Enrollment::factory()->create([
            'student_id' => $student1->id,
            'course_id' => $course1->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        Enrollment::factory()->create([
            'student_id' => $student2->id,
            'course_id' => $course2->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $response = $this->actingAs($student1)->get('/student/dashboard');

        $response->assertOk();
        $response->assertSee('Matemáticas Octavo A');
        $response->assertDontSee('Física Noveno B');
    }

    public function test_student_without_enrollment_sees_safe_empty_state(): void
    {
        $student = User::factory()->student()->create();
        AcademicPeriod::factory()->create(['active' => true]);

        $response = $this->actingAs($student)->get('/student/dashboard');

        $response->assertOk();
        $response->assertSee('Aún no posee una matrícula registrada');
    }

    public function test_student_cannot_modify_own_or_others_enrollment(): void
    {
        $student = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['student_id' => $student->id, 'active' => true]);

        $this->actingAs($student)->put("/admin/enrollments/{$enrollment->id}", ['course_id' => $enrollment->course_id])->assertForbidden();
        $this->actingAs($student)->patch("/admin/enrollments/{$enrollment->id}/toggle-status")->assertForbidden();
    }
}
