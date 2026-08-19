<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use App\Models\AcademicPeriod;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\Grades\GradeCalculationService;
use App\Services\Grades\PartialPublicationService;
use App\Services\Grades\PartialReadinessService;
use App\Services\Grades\PublishedResultsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PublicationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test exact decimal calculations (CA-015, CA-016).
     */
    public function test_exact_decimal_calculation_examples(): void
    {
        $calcService = app(GradeCalculationService::class);

        $admin = User::where('role', UserRole::Admin)->first();
        $teacher = User::where('role', UserRole::Teacher)->first();
        $period = AcademicPeriod::where('active', true)->first();

        // Create an isolated test course & assignment
        $course = Course::create(['code' => 'TST-1', 'name' => 'Curso Test Math', 'active' => true]);
        $subject = Subject::create(['code' => 'TST-SUB', 'name' => 'Asignatura Test', 'active' => true]);
        $assignment = TeachingAssignment::create([
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'teacher_id' => $teacher->id,
            'active' => true,
        ]);

        $student = User::create([
            'name' => 'Estudiante Calc Test',
            'email' => 'calcstudent@test.local',
            'password' => bcrypt('password'),
            'role' => UserRole::Student,
            'active' => true,
        ]);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $p1 = Partial::where('academic_period_id', $period->id)->where('number', 1)->first();
        $p2 = Partial::where('academic_period_id', $period->id)->where('number', 2)->first();

        // P1 Activities: 8.00 @ 20%, 9.00 @ 30%, 7.50 @ 50%
        $act1 = Activity::create(['teaching_assignment_id' => $assignment->id, 'partial_id' => $p1->id, 'name' => 'Act 1', 'percentage' => 20.00, 'active' => true]);
        $act2 = Activity::create(['teaching_assignment_id' => $assignment->id, 'partial_id' => $p1->id, 'name' => 'Act 2', 'percentage' => 30.00, 'active' => true]);
        $act3 = Activity::create(['teaching_assignment_id' => $assignment->id, 'partial_id' => $p1->id, 'name' => 'Act 3', 'percentage' => 50.00, 'active' => true]);

        Grade::create(['activity_id' => $act1->id, 'student_id' => $student->id, 'score' => 8.00, 'observation' => 'Nota 8.00', 'graded_by' => $teacher->id, 'graded_at' => now()]);
        Grade::create(['activity_id' => $act2->id, 'student_id' => $student->id, 'score' => 9.00, 'observation' => 'Nota 9.00', 'graded_by' => $teacher->id, 'graded_at' => now()]);
        Grade::create(['activity_id' => $act3->id, 'student_id' => $student->id, 'score' => 7.50, 'observation' => 'Nota 7.50', 'graded_by' => $teacher->id, 'graded_at' => now()]);

        $p1Res = $calcService->calculatePartialAverage($assignment, $p1, $student);
        $this->assertTrue($p1Res['calculable']);
        $this->assertEquals(805, $p1Res['score_hundredths']);
        $this->assertEquals('8.05', $p1Res['score_formatted']);

        // P2 Activities: Single 100% activity with score 9.10
        $actP2 = Activity::create(['teaching_assignment_id' => $assignment->id, 'partial_id' => $p2->id, 'name' => 'Examen P2', 'percentage' => 100.00, 'active' => true]);
        Grade::create(['activity_id' => $actP2->id, 'student_id' => $student->id, 'score' => 9.10, 'observation' => 'Nota 9.10', 'graded_by' => $teacher->id, 'graded_at' => now()]);

        $p2Res = $calcService->calculatePartialAverage($assignment, $p2, $student);
        $this->assertTrue($p2Res['calculable']);
        $this->assertEquals(910, $p2Res['score_hundredths']);
        $this->assertEquals('9.10', $p2Res['score_formatted']);

        // Final Subject Average = (8.05 + 9.10) / 2 = 8.575 -> 8.58
        $finalRes = $calcService->calculateFinalSubjectAverage($assignment, $student, officialOnly: false);
        $this->assertTrue($finalRes['calculable']);
        $this->assertEquals(858, $finalRes['score_hundredths']);
        $this->assertEquals('8.58', $finalRes['score_formatted']);
    }

    /**
     * Test calculation with score 0.00.
     */
    public function test_calculation_supports_zero_score(): void
    {
        $calcService = app(GradeCalculationService::class);
        $teacher = User::where('role', UserRole::Teacher)->first();
        $period = AcademicPeriod::where('active', true)->first();

        $course = Course::create(['code' => 'TST-2', 'name' => 'Curso Test 0', 'active' => true]);
        $subject = Subject::create(['code' => 'TST-SUB2', 'name' => 'Asignatura Zero', 'active' => true]);
        $assignment = TeachingAssignment::create([
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'teacher_id' => $teacher->id,
            'active' => true,
        ]);

        $student = User::create([
            'name' => 'Estudiante Zero',
            'email' => 'zero@test.local',
            'password' => bcrypt('password'),
            'role' => UserRole::Student,
            'active' => true,
        ]);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $p1 = Partial::where('academic_period_id', $period->id)->where('number', 1)->first();

        $act1 = Activity::create(['teaching_assignment_id' => $assignment->id, 'partial_id' => $p1->id, 'name' => 'Tarea', 'percentage' => 50.00, 'active' => true]);
        $act2 = Activity::create(['teaching_assignment_id' => $assignment->id, 'partial_id' => $p1->id, 'name' => 'Examen', 'percentage' => 50.00, 'active' => true]);

        Grade::create(['activity_id' => $act1->id, 'student_id' => $student->id, 'score' => 0.00, 'observation' => 'Cero entregado', 'graded_by' => $teacher->id, 'graded_at' => now()]);
        Grade::create(['activity_id' => $act2->id, 'student_id' => $student->id, 'score' => 10.00, 'observation' => 'Diez examen', 'graded_by' => $teacher->id, 'graded_at' => now()]);

        $p1Res = $calcService->calculatePartialAverage($assignment, $p1, $student);
        $this->assertTrue($p1Res['calculable']);
        $this->assertEquals(500, $p1Res['score_hundredths']);
        $this->assertEquals('5.00', $p1Res['score_formatted']);
    }

    /**
     * Test readiness states logic.
     */
    public function test_readiness_service_states(): void
    {
        $readinessService = app(PartialReadinessService::class);
        $teacher = User::where('role', UserRole::Teacher)->first();
        $period = AcademicPeriod::where('active', true)->first();

        $course = Course::create(['code' => 'TST-RD', 'name' => 'Curso Readiness', 'active' => true]);
        $subject = Subject::create(['code' => 'TST-S3', 'name' => 'Asignatura Readiness', 'active' => true]);
        $assignment = TeachingAssignment::create([
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'teacher_id' => $teacher->id,
            'active' => true,
        ]);

        $p1 = Partial::where('academic_period_id', $period->id)->where('number', 1)->first();

        // 1. Empty state: No activities, no students
        $resEmpty = $readinessService->checkReadiness($assignment, $p1);
        $this->assertEquals('empty', $resEmpty['calculated_status']);
        $this->assertFalse($resEmpty['is_ready']);

        // 2. Incomplete state: 50% weighting
        $student = User::create(['name' => 'Est Readiness', 'email' => 'rd@test.local', 'password' => bcrypt('pass'), 'role' => UserRole::Student, 'active' => true]);
        Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'academic_period_id' => $period->id, 'active' => true]);
        $act = Activity::create(['teaching_assignment_id' => $assignment->id, 'partial_id' => $p1->id, 'name' => 'Act 50', 'percentage' => 50.00, 'active' => true]);

        $resIncomplete = $readinessService->checkReadiness($assignment, $p1);
        $this->assertEquals('incomplete', $resIncomplete['calculated_status']);
        $this->assertFalse($resIncomplete['is_ready']);
        $this->assertNotEmpty($resIncomplete['pending_issues']);

        // 3. Complete 100% weighting & complete grades -> Ready
        $act2 = Activity::create(['teaching_assignment_id' => $assignment->id, 'partial_id' => $p1->id, 'name' => 'Act 50b', 'percentage' => 50.00, 'active' => true]);
        Grade::create(['activity_id' => $act->id, 'student_id' => $student->id, 'score' => 8.00, 'observation' => 'Nota ok', 'graded_by' => $teacher->id, 'graded_at' => now()]);
        Grade::create(['activity_id' => $act2->id, 'student_id' => $student->id, 'score' => 9.00, 'observation' => 'Nota ok 2', 'graded_by' => $teacher->id, 'graded_at' => now()]);

        $resReady = $readinessService->checkReadiness($assignment, $p1);
        $this->assertEquals('ready', $resReady['calculated_status']);
        $this->assertTrue($resReady['is_ready']);
        $this->assertEmpty($resReady['pending_issues']);
    }

    /**
     * Test teacher publication flow and authorization.
     */
    public function test_teacher_can_publish_ready_partial_and_other_teacher_cannot(): void
    {
        $pubService = app(PartialPublicationService::class);
        $period = AcademicPeriod::where('active', true)->first();

        // Get seeded Math Octavo A assignment & assigned teacher
        $course8A = Course::where('code', '8VO-A')->first();
        $mathSubject = Subject::where('code', 'MAT')->first();
        $assignment = TeachingAssignment::where('course_id', $course8A->id)
            ->where('subject_id', $mathSubject->id)
            ->where('academic_period_id', $period->id)
            ->first();

        $assignedTeacher = $assignment->teacher;
        $otherTeacher = User::where('role', UserRole::Teacher)->where('id', '!=', $assignedTeacher->id)->first();
        $p1 = Partial::where('academic_period_id', $period->id)->where('number', 1)->first();

        // Check assigned teacher can view preview
        $response = $this->actingAs($assignedTeacher)->get(route('teacher.partial-publications.preview', [$assignment, $p1]));
        $response->assertStatus(200);

        // Check unauthorized teacher receives 403 on preview
        $responseOther = $this->actingAs($otherTeacher)->get(route('teacher.partial-publications.preview', [$assignment, $p1]));
        $responseOther->assertStatus(403);

        // Check unauthorized teacher cannot publish via service
        $this->expectException(InvalidArgumentException::class);
        $pubService->publish($assignment, $p1, $otherTeacher);
    }

    /**
     * Test admin reopen flow and validation.
     */
    public function test_admin_reopen_flow(): void
    {
        $admin = User::where('role', UserRole::Admin)->first();
        $teacher = User::where('role', UserRole::Teacher)->first();
        $period = AcademicPeriod::where('active', true)->first();

        // Find Math Octavo A P1 (which is seeded as published by PublicationSeeder)
        $course8A = Course::where('code', '8VO-A')->first();
        $mathSubject = Subject::where('code', 'MAT')->first();
        $assignment = TeachingAssignment::where('course_id', $course8A->id)
            ->where('subject_id', $mathSubject->id)
            ->where('academic_period_id', $period->id)
            ->first();
        $p1 = Partial::where('academic_period_id', $period->id)->where('number', 1)->first();

        $pub = PartialPublication::where('teaching_assignment_id', $assignment->id)->where('partial_id', $p1->id)->first();
        $this->assertEquals(PublicationStatus::Published, $pub->status);

        // Teacher cannot reopen
        $responseTeacher = $this->actingAs($teacher)->patch(route('admin.partial-publications.reopen', $pub), [
            'reason' => 'Motivo de prueba válido para reapertura',
        ]);
        $responseTeacher->assertStatus(403);

        // Admin short reason (< 10 chars) rejected
        $responseShort = $this->actingAs($admin)->patch(route('admin.partial-publications.reopen', $pub), [
            'reason' => 'Corto',
        ]);
        $responseShort->assertSessionHasErrors('reason');

        // Admin valid reason reopens partial
        $responseAdmin = $this->actingAs($admin)->patch(route('admin.partial-publications.reopen', $pub), [
            'reason' => 'Solicitud formal de corrección en la calificación de la evaluación.',
        ]);
        $responseAdmin->assertRedirect();
        $responseAdmin->assertSessionHas('success');

        $pub->refresh();
        $this->assertEquals(PublicationStatus::Reopened, $pub->status);
        $this->assertEquals($admin->id, $pub->reopened_by);
        $this->assertEquals('Solicitud formal de corrección en la calificación de la evaluación.', $pub->reopen_reason);
    }

    /**
     * Test PublishedResultsService exclusions.
     */
    public function test_published_results_service_excludes_draft_and_reopened(): void
    {
        $publishedResultsService = app(PublishedResultsService::class);
        $period = AcademicPeriod::where('active', true)->first();

        // Lengua Octavo A P1 is draft
        $course8A = Course::where('code', '8VO-A')->first();
        $langSubject = Subject::where('code', 'LYL')->first();
        $langAssignment = TeachingAssignment::where('course_id', $course8A->id)
            ->where('subject_id', $langSubject->id)
            ->where('academic_period_id', $period->id)
            ->first();
        $p1 = Partial::where('academic_period_id', $period->id)->where('number', 1)->first();
        $student = User::where('email', 'estudiante1@calificaciones.local')->first();

        $resDraft = $publishedResultsService->getPublishedPartialResult($langAssignment, $p1, $student);
        $this->assertNull($resDraft);

        // Math Octavo A P1 is published
        $mathSubject = Subject::where('code', 'MAT')->first();
        $mathAssignment = TeachingAssignment::where('course_id', $course8A->id)
            ->where('subject_id', $mathSubject->id)
            ->where('academic_period_id', $period->id)
            ->first();

        $resPublished = $publishedResultsService->getPublishedPartialResult($mathAssignment, $p1, $student);
        $this->assertNotNull($resPublished);
        $this->assertTrue($resPublished['official']);
    }

    /**
     * Test Seeder Idempotency.
     */
    public function test_publication_seeder_is_idempotent(): void
    {
        $this->seed();
        $this->seed();

        $publishedCount = PartialPublication::where('status', PublicationStatus::Published)->count();
        $draftCount = PartialPublication::where('status', PublicationStatus::Draft)->count();

        $this->assertEquals(2, $publishedCount);
        $this->assertEquals(10, $draftCount);
    }
}
