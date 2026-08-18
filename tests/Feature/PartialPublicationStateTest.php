<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\Academic\TeachingAssignmentService;
use App\Services\Grades\PartialPublicationStateService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PartialPublicationStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_publication_status_enum_values_and_labels()
    {
        $this->assertEquals('draft', PublicationStatus::Draft->value);
        $this->assertEquals('published', PublicationStatus::Published->value);
        $this->assertEquals('reopened', PublicationStatus::Reopened->value);

        $this->assertEquals('Borrador', PublicationStatus::Draft->label());
        $this->assertEquals('Publicado', PublicationStatus::Published->label());
        $this->assertEquals('Reabierto', PublicationStatus::Reopened->label());
    }

    public function test_academic_period_has_exactly_two_partials_with_50_percent_weight()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $partials = Partial::where('academic_period_id', $period->id)->get();

        $this->assertCount(2, $partials);
        $this->assertEquals([1, 2], $partials->pluck('number')->toArray());
        $this->assertEquals(50.00, (float) $partials->first()->weight);
        $this->assertEquals(50.00, (float) $partials->last()->weight);
    }

    public function test_ensure_for_assignment_creates_exactly_two_draft_publication_rows()
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

        $service = app(PartialPublicationStateService::class);
        $publications = $service->ensureForAssignment($assignment);

        $this->assertCount(2, $publications);
        $this->assertEquals(PublicationStatus::Draft, $publications->first()->status);
        $this->assertEquals(PublicationStatus::Draft, $publications->last()->status);
        $this->assertDatabaseCount('partial_publications', 2);
    }

    public function test_ensure_for_assignment_is_idempotent()
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

        $service = app(PartialPublicationStateService::class);
        $service->ensureForAssignment($assignment);
        $service->ensureForAssignment($assignment);

        $this->assertDatabaseCount('partial_publications', 2);
    }

    public function test_cannot_associate_partial_from_another_period()
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

        $service = app(PartialPublicationStateService::class);

        $this->expectException(InvalidArgumentException::class);
        $service->validateCoherence($assignment, $foreignPartial);
    }

    public function test_reassigning_teacher_or_deactivating_assignment_preserves_publication_states()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher1 = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $teacher2 = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        $assignmentService = app(TeachingAssignmentService::class);
        $assignment = $assignmentService->createAssignment([
            'teacher_id' => $teacher1->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $this->assertDatabaseCount('partial_publications', 2);

        // Reassign teacher
        $assignmentService->reassignTeacher($assignment, $teacher2->id);
        $this->assertDatabaseCount('partial_publications', 2);

        // Deactivate assignment
        $assignmentService->toggleStatus($assignment);
        $this->assertDatabaseCount('partial_publications', 2);
    }

    public function test_seeder_creates_12_draft_publication_rows()
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('partial_publications', 12);
        $this->assertDatabaseCount('audit_logs', 0); // No state publication transitions logged yet

        // Ensure all are in draft
        $draftCount = PartialPublication::where('status', PublicationStatus::Draft)->count();
        $this->assertEquals(12, $draftCount);
    }

    public function test_admin_can_access_partial_publications_and_filter()
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('role', UserRole::Admin)->first();

        $response = $this->actingAs($admin)->get(route('admin.partial-publications.index'));
        $response->assertOk();
        $response->assertSee('Estados de Parciales por Asignación');
        $response->assertSee('Borrador');

        // Test filter by status
        $filterResponse = $this->actingAs($admin)->get(route('admin.partial-publications.index', ['status' => 'draft']));
        $filterResponse->assertOk();
    }

    public function test_teacher_can_view_own_assignments_with_draft_statuses_but_cannot_access_admin_partial_publications()
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::where('role', UserRole::Teacher)->first();

        // Access teacher assignments list
        $response = $this->actingAs($teacher)->get(route('teacher.assignments.index'));
        $response->assertOk();
        $response->assertSee('Borrador');

        // Access admin route should be forbidden
        $forbiddenResponse = $this->actingAs($teacher)->get(route('admin.partial-publications.index'));
        $forbiddenResponse->assertForbidden();
    }

    public function test_student_cannot_access_admin_partial_publications()
    {
        $this->seed(DatabaseSeeder::class);
        $student = User::where('role', UserRole::Student)->first();

        // Student gets 403
        $response = $this->actingAs($student)->get(route('admin.partial-publications.index'));
        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_when_accessing_admin_partial_publications()
    {
        $guestResponse = $this->get(route('admin.partial-publications.index'));
        $guestResponse->assertRedirectToRoute('login');
    }
}
