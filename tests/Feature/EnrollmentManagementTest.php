<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

class EnrollmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_create_edit_and_toggle_enrollments(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        $course1 = Course::factory()->create(['active' => true]);
        $course2 = Course::factory()->create(['active' => true]);
        $period = AcademicPeriod::factory()->create(['active' => true]);

        // List
        $this->actingAs($admin)
            ->get('/admin/enrollments')
            ->assertOk()
            ->assertSee('Matrículas Académicas');

        // Create
        $response = $this->actingAs($admin)->post('/admin/enrollments', [
            'student_id' => $student->id,
            'course_id' => $course1->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $response->assertRedirect('/admin/enrollments');
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'course_id' => $course1->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $enrollment = Enrollment::where('student_id', $student->id)->firstOrFail();

        // Edit / Update course
        $updateResponse = $this->actingAs($admin)->put("/admin/enrollments/{$enrollment->id}", [
            'course_id' => $course2->id,
            'active' => true,
        ]);

        $updateResponse->assertRedirect('/admin/enrollments');
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'course_id' => $course2->id,
        ]);

        // Toggle status
        $this->actingAs($admin)->patch("/admin/enrollments/{$enrollment->id}/toggle-status")
            ->assertRedirect('/admin/enrollments');

        $this->assertFalse($enrollment->fresh()->active);
    }

    public function test_teacher_and_student_cannot_access_enrollment_management(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();

        $this->actingAs($teacher)->get('/admin/enrollments')->assertForbidden();
        $this->actingAs($teacher)->post('/admin/enrollments', [])->assertForbidden();

        $this->actingAs($student)->get('/admin/enrollments')->assertForbidden();
        $this->actingAs($student)->post('/admin/enrollments', [])->assertForbidden();
    }

    public function test_only_users_with_student_role_can_be_enrolled(): void
    {
        $admin = User::factory()->admin()->create();
        $teacherUser = User::factory()->teacher()->create();
        $course = Course::factory()->create(['active' => true]);
        $period = AcademicPeriod::factory()->create(['active' => true]);

        $response = $this->actingAs($admin)->post('/admin/enrollments', [
            'student_id' => $teacherUser->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
        ]);

        $response->assertSessionHasErrors('student_id');
    }

    public function test_inactive_student_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $inactiveStudent = User::factory()->student()->create(['active' => false]);
        $course = Course::factory()->create(['active' => true]);
        $period = AcademicPeriod::factory()->create(['active' => true]);

        $response = $this->actingAs($admin)->post('/admin/enrollments', [
            'student_id' => $inactiveStudent->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
        ]);

        $response->assertSessionHasErrors('student_id');
    }

    public function test_inactive_course_or_period_is_rejected_for_new_enrollment(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        $inactiveCourse = Course::factory()->create(['active' => false]);
        $inactivePeriod = AcademicPeriod::factory()->create(['active' => false]);

        $response1 = $this->actingAs($admin)->post('/admin/enrollments', [
            'student_id' => $student->id,
            'course_id' => $inactiveCourse->id,
            'academic_period_id' => AcademicPeriod::factory()->create(['active' => true])->id,
        ]);
        $response1->assertSessionHasErrors('course_id');

        $response2 = $this->actingAs($admin)->post('/admin/enrollments', [
            'student_id' => $student->id,
            'course_id' => Course::factory()->create(['active' => true])->id,
            'academic_period_id' => $inactivePeriod->id,
        ]);
        $response2->assertSessionHasErrors('academic_period_id');
    }

    public function test_student_cannot_have_two_enrollments_in_the_same_period(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();
        $course1 = Course::factory()->create(['active' => true]);
        $course2 = Course::factory()->create(['active' => true]);
        $period = AcademicPeriod::factory()->create(['active' => true]);

        Enrollment::factory()->create([
            'student_id' => $student->id,
            'course_id' => $course1->id,
            'academic_period_id' => $period->id,
        ]);

        $response = $this->actingAs($admin)->post('/admin/enrollments', [
            'student_id' => $student->id,
            'course_id' => $course2->id,
            'academic_period_id' => $period->id,
        ]);

        $response->assertSessionHasErrors('student_id');
    }

    public function test_deactivating_enrollment_does_not_physically_delete_it(): void
    {
        $admin = User::factory()->admin()->create();
        $enrollment = Enrollment::factory()->create(['active' => true]);

        $this->actingAs($admin)->patch("/admin/enrollments/{$enrollment->id}/toggle-status");

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'active' => false,
        ]);

        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.enrollments.destroy'));
    }
}
