<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAssignmentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_sees_only_own_active_assignments(): void
    {
        $teacher1 = User::factory()->teacher()->create();
        $teacher2 = User::factory()->teacher()->create();
        $course = Course::factory()->create(['active' => true]);
        $subject1 = Subject::factory()->create(['name' => 'Matemáticas Avanzadas', 'active' => true]);
        $subject2 = Subject::factory()->create(['name' => 'Física Cuántica', 'active' => true]);
        $subject3 = Subject::factory()->create(['name' => 'Química Orgánica', 'active' => true]);
        $period = AcademicPeriod::factory()->create(['active' => true]);

        // Active assignment for teacher1
        TeachingAssignment::factory()->create([
            'teacher_id' => $teacher1->id,
            'course_id' => $course->id,
            'subject_id' => $subject1->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        // Inactive assignment for teacher1
        TeachingAssignment::factory()->create([
            'teacher_id' => $teacher1->id,
            'course_id' => $course->id,
            'subject_id' => $subject2->id,
            'academic_period_id' => $period->id,
            'active' => false,
        ]);

        // Assignment for teacher2
        TeachingAssignment::factory()->create([
            'teacher_id' => $teacher2->id,
            'course_id' => $course->id,
            'subject_id' => $subject3->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $response = $this->actingAs($teacher1)->get('/teacher/assignments');

        $response->assertOk();
        $response->assertSee('Matemáticas Avanzadas');
        $response->assertDontSee('Física Cuántica');
        $response->assertDontSee('Química Orgánica');
    }

    public function test_teacher_visualizes_only_active_students_in_assigned_course_and_period(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);
        $period = AcademicPeriod::factory()->create(['active' => true]);

        $assignment = TeachingAssignment::factory()->create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $activeStudent = User::factory()->student()->create(['name' => 'Estudiante Activo Visitable', 'active' => true]);
        $inactiveStudent = User::factory()->student()->create(['name' => 'Estudiante Inactivo Oculto', 'active' => false]);

        // Active enrollment for active student
        Enrollment::factory()->create([
            'student_id' => $activeStudent->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        // Enrollment for inactive student
        Enrollment::factory()->create([
            'student_id' => $inactiveStudent->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $response = $this->actingAs($teacher)->get("/teacher/assignments/{$assignment->id}");

        $response->assertOk();
        $response->assertSee('Estudiante Activo Visitable');
        $response->assertDontSee('Estudiante Inactivo Oculto');
    }

    public function test_teacher_a_cannot_view_assignment_of_teacher_b(): void
    {
        $teacherA = User::factory()->teacher()->create();
        $teacherB = User::factory()->teacher()->create();

        $assignmentB = TeachingAssignment::factory()->create([
            'teacher_id' => $teacherB->id,
            'active' => true,
        ]);

        $response = $this->actingAs($teacherA)->get("/teacher/assignments/{$assignmentB->id}");

        $response->assertForbidden();
    }

    public function test_teacher_cannot_modify_assignments(): void
    {
        $teacher = User::factory()->teacher()->create();
        $assignment = TeachingAssignment::factory()->create(['teacher_id' => $teacher->id, 'active' => true]);

        $this->actingAs($teacher)->put("/admin/teaching-assignments/{$assignment->id}", ['teacher_id' => $teacher->id])->assertForbidden();
        $this->actingAs($teacher)->patch("/admin/teaching-assignments/{$assignment->id}/toggle-status")->assertForbidden();
    }
}
