<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeachingAssignmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_reassign_and_toggle_teaching_assignments(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher1 = User::factory()->teacher()->create();
        $teacher2 = User::factory()->teacher()->create();
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);
        $period = AcademicPeriod::factory()->create(['active' => true]);

        // Create
        $response = $this->actingAs($admin)->post('/admin/teaching-assignments', [
            'teacher_id' => $teacher1->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $response->assertRedirect('/admin/teaching-assignments');
        $this->assertDatabaseHas('teaching_assignments', [
            'teacher_id' => $teacher1->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $assignment = TeachingAssignment::where('course_id', $course->id)
            ->where('subject_id', $subject->id)
            ->where('academic_period_id', $period->id)
            ->firstOrFail();

        // Reassign teacher
        $reassignResponse = $this->actingAs($admin)->put("/admin/teaching-assignments/{$assignment->id}", [
            'teacher_id' => $teacher2->id,
            'active' => true,
        ]);

        $reassignResponse->assertRedirect('/admin/teaching-assignments');
        $this->assertDatabaseHas('teaching_assignments', [
            'id' => $assignment->id,
            'teacher_id' => $teacher2->id,
        ]);

        // Toggle status
        $this->actingAs($admin)->patch("/admin/teaching-assignments/{$assignment->id}/toggle-status")
            ->assertRedirect('/admin/teaching-assignments');

        $this->assertFalse($assignment->fresh()->active);
    }

    public function test_teacher_and_student_cannot_access_assignment_management(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();

        $this->actingAs($teacher)->get('/admin/teaching-assignments')->assertForbidden();
        $this->actingAs($teacher)->post('/admin/teaching-assignments', [])->assertForbidden();

        $this->actingAs($student)->get('/admin/teaching-assignments')->assertForbidden();
        $this->actingAs($student)->post('/admin/teaching-assignments', [])->assertForbidden();
    }

    public function test_only_teacher_role_can_be_assigned(): void
    {
        $admin = User::factory()->admin()->create();
        $studentUser = User::factory()->student()->create();
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);
        $period = AcademicPeriod::factory()->create(['active' => true]);

        $response = $this->actingAs($admin)->post('/admin/teaching-assignments', [
            'teacher_id' => $studentUser->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
        ]);

        $response->assertSessionHasErrors('teacher_id');
    }

    public function test_inactive_teacher_or_academic_entity_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $inactiveTeacher = User::factory()->teacher()->create(['active' => false]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);
        $period = AcademicPeriod::factory()->create(['active' => true]);

        $response = $this->actingAs($admin)->post('/admin/teaching-assignments', [
            'teacher_id' => $inactiveTeacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
        ]);

        $response->assertSessionHasErrors('teacher_id');
    }

    public function test_duplicate_course_subject_period_assignment_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher1 = User::factory()->teacher()->create();
        $teacher2 = User::factory()->teacher()->create();
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);
        $period = AcademicPeriod::factory()->create(['active' => true]);

        TeachingAssignment::factory()->create([
            'teacher_id' => $teacher1->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
        ]);

        $response = $this->actingAs($admin)->post('/admin/teaching-assignments', [
            'teacher_id' => $teacher2->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
        ]);

        $response->assertSessionHasErrors('course_id');
    }

    public function test_teacher_can_have_multiple_different_assignments(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $course1 = Course::factory()->create(['active' => true]);
        $course2 = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);
        $period = AcademicPeriod::factory()->create(['active' => true]);

        TeachingAssignment::factory()->create([
            'teacher_id' => $teacher->id,
            'course_id' => $course1->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
        ]);

        $response = $this->actingAs($admin)->post('/admin/teaching-assignments', [
            'teacher_id' => $teacher->id,
            'course_id' => $course2->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
        ]);

        $response->assertRedirect('/admin/teaching-assignments');
        $this->assertEquals(2, TeachingAssignment::where('teacher_id', $teacher->id)->count());
    }
}
