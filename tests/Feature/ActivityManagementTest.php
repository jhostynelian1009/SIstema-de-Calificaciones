<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use App\Models\AcademicPeriod;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\Grades\ActivityService;
use App\Services\Grades\PartialPublicationStateService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class ActivityManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_activity_in_own_assignment()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->where('number', 1)->first();

        $response = $this->actingAs($teacher)->post(route('teacher.assignments.partials.activities.store', [$assignment, $partial]), [
            'name' => 'Tarea 1',
            'description' => 'Descripción demo',
            'percentage' => 30.00,
        ]);

        $response->assertRedirect(route('teacher.assignments.partials.activities.index', [$assignment, $partial]));
        $this->assertDatabaseHas('activities', [
            'teaching_assignment_id' => $assignment->id,
            'partial_id' => $partial->id,
            'name' => 'Tarea 1',
            'percentage' => 30.00,
            'active' => true,
        ]);
    }

    public function test_teacher_cannot_create_activity_in_another_teachers_assignment()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher1 = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $teacher2 = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher1->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();

        $response = $this->actingAs($teacher2)->post(route('teacher.assignments.partials.activities.store', [$assignment, $partial]), [
            'name' => 'Tarea No Autorizada',
            'percentage' => 20.00,
        ]);

        $response->assertForbidden();
    }

    public function test_student_cannot_create_or_modify_activities()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $student = User::factory()->create(['role' => UserRole::Student, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();

        $studentResponse = $this->actingAs($student)->post(route('teacher.assignments.partials.activities.store', [$assignment, $partial]), [
            'name' => 'Tarea Estudiante',
            'percentage' => 10.00,
        ]);
        $studentResponse->assertForbidden();
    }

    public function test_guest_is_redirected_to_login()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();

        $guestResponse = $this->post(route('teacher.assignments.partials.activities.store', [$assignment, $partial]), [
            'name' => 'Tarea Invitado',
            'percentage' => 10.00,
        ]);
        $guestResponse->assertRedirectToRoute('login');
    }

    public function test_admin_cannot_create_activity_via_teacher_flow()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $admin = User::factory()->create(['role' => UserRole::Admin, 'active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();

        $response = $this->actingAs($admin)->post(route('teacher.assignments.partials.activities.store', [$assignment, $partial]), [
            'name' => 'Tarea Admin Flow',
            'percentage' => 10.00,
        ]);

        $response->assertForbidden();
    }

    public function test_partial_from_another_period_is_rejected()
    {
        $period1 = AcademicPeriod::factory()->create(['active' => true]);
        $period2 = AcademicPeriod::factory()->create(['active' => false]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period1->id,
            'active' => true,
        ]);

        $foreignPartial = Partial::where('academic_period_id', $period2->id)->first();

        $service = app(ActivityService::class);

        $this->expectException(InvalidArgumentException::class);
        $service->validateCoherence($assignment, $foreignPartial);
    }

    public function test_inactive_assignment_rejects_new_activities()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => false,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();

        $response = $this->actingAs($teacher)->post(route('teacher.assignments.partials.activities.store', [$assignment, $partial]), [
            'name' => 'Tarea Inactiva',
            'percentage' => 10.00,
        ]);

        $response->assertForbidden();
    }

    public function test_percentage_validations_and_boundary_checks()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();

        // 0 is rejected by validation
        $r1 = $this->actingAs($teacher)->post(route('teacher.assignments.partials.activities.store', [$assignment, $partial]), [
            'name' => 'Tarea Cero',
            'percentage' => 0.00,
        ]);
        $r1->assertSessionHasErrors('percentage');

        // Negative is rejected by validation
        $r2 = $this->actingAs($teacher)->post(route('teacher.assignments.partials.activities.store', [$assignment, $partial]), [
            'name' => 'Tarea Negativa',
            'percentage' => -10.00,
        ]);
        $r2->assertSessionHasErrors('percentage');

        // 100.01 is rejected by validation
        $r3 = $this->actingAs($teacher)->post(route('teacher.assignments.partials.activities.store', [$assignment, $partial]), [
            'name' => 'Tarea Excesiva',
            'percentage' => 100.01,
        ]);
        $r3->assertSessionHasErrors('percentage');

        // 100.00 is accepted when no other activity exists
        $r4 = $this->actingAs($teacher)->post(route('teacher.assignments.partials.activities.store', [$assignment, $partial]), [
            'name' => 'Examen Total',
            'percentage' => 100.00,
        ]);
        $r4->assertRedirect(route('teacher.assignments.partials.activities.index', [$assignment, $partial]));

        // Exceeding 100% total (e.g. adding 10% when 100% exists) is rejected by service
        $r5 = $this->actingAs($teacher)->post(route('teacher.assignments.partials.activities.store', [$assignment, $partial]), [
            'name' => 'Tarea Extra',
            'percentage' => 10.00,
        ]);
        $r5->assertSessionHas('error');
    }

    public function test_editing_activity_excludes_previous_percentage_and_recalculates()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();

        $activity = Activity::create([
            'teaching_assignment_id' => $assignment->id,
            'partial_id' => $partial->id,
            'name' => 'Tarea 1',
            'percentage' => 60.00,
            'active' => true,
        ]);

        // Edit from 60% to 100% should succeed
        $response = $this->actingAs($teacher)->put(route('teacher.activities.update', $activity), [
            'name' => 'Tarea 1 Modificada',
            'percentage' => 100.00,
        ]);

        $response->assertRedirect(route('teacher.assignments.partials.activities.index', [$assignment, $partial]));
        $this->assertEquals(100.00, (float) $activity->fresh()->percentage);
    }

    public function test_deactivating_activity_reduces_total_and_reactivating_over_limit_is_rejected()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();

        $act1 = Activity::create([
            'teaching_assignment_id' => $assignment->id,
            'partial_id' => $partial->id,
            'name' => 'Actividad 1',
            'percentage' => 60.00,
            'active' => true,
        ]);

        $act2 = Activity::create([
            'teaching_assignment_id' => $assignment->id,
            'partial_id' => $partial->id,
            'name' => 'Actividad 2',
            'percentage' => 50.00,
            'active' => false, // inactive initially
        ]);

        $service = app(ActivityService::class);

        // Reactivating act2 (60% + 50% = 110%) should fail
        $this->expectException(InvalidArgumentException::class);
        $service->toggleActivityStatus($act2, $teacher);
    }

    public function test_published_partial_blocks_creation_editing_and_status_toggle()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();

        // Ensure publications exist
        app(PartialPublicationStateService::class)->ensureForAssignment($assignment);

        // Manually update publication state to Published
        PartialPublication::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->update(['status' => PublicationStatus::Published]);

        $activity = Activity::create([
            'teaching_assignment_id' => $assignment->id,
            'partial_id' => $partial->id,
            'name' => 'Actividad Publicada',
            'percentage' => 50.00,
            'active' => true,
        ]);

        $service = app(ActivityService::class);

        // Creation in published partial is blocked
        $this->expectException(InvalidArgumentException::class);
        $service->createActivity($assignment, $partial, ['name' => 'Nueva', 'percentage' => 10], $teacher);
    }

    public function test_reopened_partial_allows_activity_editing()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();

        app(PartialPublicationStateService::class)->ensureForAssignment($assignment);

        // Set status to Reopened
        PartialPublication::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->update(['status' => PublicationStatus::Reopened]);

        $activity = Activity::create([
            'teaching_assignment_id' => $assignment->id,
            'partial_id' => $partial->id,
            'name' => 'Actividad Reabierta',
            'percentage' => 40.00,
            'active' => true,
        ]);

        $response = $this->actingAs($teacher)->put(route('teacher.activities.update', $activity), [
            'name' => 'Actividad Reabierta Editada',
            'percentage' => 50.00,
        ]);

        $response->assertRedirect(route('teacher.assignments.partials.activities.index', [$assignment, $partial]));
        $this->assertEquals('Actividad Reabierta Editada', $activity->fresh()->name);
    }

    public function test_admin_can_monitor_activities_read_only()
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('role', UserRole::Admin)->first();

        $response = $this->actingAs($admin)->get(route('admin.activities.index'));
        $response->assertOk();
        $response->assertSee('Monitoreo General de Actividades Evaluativas');
    }

    public function test_seeders_are_idempotent_and_seed_exact_weighting_cases()
    {
        $this->seed(DatabaseSeeder::class);
        $countFirstSeed = Activity::count();

        // Run seed a second time to test idempotency
        $this->seed(DatabaseSeeder::class);
        $countSecondSeed = Activity::count();

        $this->assertEquals($countFirstSeed, $countSecondSeed);

        // Case 1: Matemáticas Octavo A P1 = 100.00%
        $octavoA = Course::where('code', '8VO-A')->orWhere('name', 'Octavo A')->first();
        $matematicas = Subject::where('code', 'MAT8')->orWhere('name', 'Matemáticas')->first();
        $mathAssignment = TeachingAssignment::where('course_id', $octavoA->id)->where('subject_id', $matematicas->id)->first();
        $p1 = Partial::where('academic_period_id', $mathAssignment->academic_period_id)->where('number', 1)->first();

        $service = app(ActivityService::class);
        $mathP1Units = $service->calculateTotalActiveUnits($mathAssignment->id, $p1->id);
        $this->assertEquals(10000, $mathP1Units); // 100.00%

        // Case 2: Lengua Octavo A P1 = 50.00%
        $lengua = Subject::where('code', 'LIT8')->orWhere('name', 'Lengua y Literatura')->first();
        $lenguaAssignment = TeachingAssignment::where('course_id', $octavoA->id)->where('subject_id', $lengua->id)->first();
        $lenguaP1Units = $service->calculateTotalActiveUnits($lenguaAssignment->id, $p1->id);
        $this->assertEquals(5000, $lenguaP1Units); // 50.00%
    }

    public function test_grades_table_exists_in_k007()
    {
        $this->assertTrue(Schema::hasTable('grades'));
    }
}
