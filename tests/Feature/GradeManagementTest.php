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
use App\Services\Grades\GradeCompletionService;
use App\Services\Grades\GradeService;
use App\Services\Grades\PartialPublicationStateService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class GradeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_grade_student_in_own_assignment()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $student = User::factory()->create(['role' => UserRole::Student, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->where('number', 1)->first();
        app(PartialPublicationStateService::class)->ensureForAssignment($assignment);

        $activity = Activity::create([
            'teaching_assignment_id' => $assignment->id,
            'partial_id' => $partial->id,
            'name' => 'Tarea 1',
            'percentage' => 30.00,
            'active' => true,
        ]);

        $response = $this->actingAs($teacher)->post(route('teacher.activities.grades.store', $activity), [
            'student_id' => $student->id,
            'score' => 9.50,
            'observation' => 'Buen desempeño y entrega a tiempo',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('grades', [
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'score' => 9.50,
            'observation' => 'Buen desempeño y entrega a tiempo',
            'graded_by' => $teacher->id,
        ]);
    }

    public function test_teacher_cannot_grade_another_teachers_assignment()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher1 = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $teacher2 = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $student = User::factory()->create(['role' => UserRole::Student, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher1->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();
        app(PartialPublicationStateService::class)->ensureForAssignment($assignment);

        $activity = Activity::create([
            'teaching_assignment_id' => $assignment->id,
            'partial_id' => $partial->id,
            'name' => 'Tarea 1',
            'percentage' => 30.00,
            'active' => true,
        ]);

        $response = $this->actingAs($teacher2)->post(route('teacher.activities.grades.store', $activity), [
            'student_id' => $student->id,
            'score' => 8.00,
            'observation' => 'Intento no autorizado',
        ]);

        $response->assertForbidden();
    }

    public function test_score_boundary_validations()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $student = User::factory()->create(['role' => UserRole::Student, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();
        app(PartialPublicationStateService::class)->ensureForAssignment($assignment);

        $activity = Activity::create([
            'teaching_assignment_id' => $assignment->id,
            'partial_id' => $partial->id,
            'name' => 'Tarea 1',
            'percentage' => 30.00,
            'active' => true,
        ]);

        // 0.00 is accepted
        $r1 = $this->actingAs($teacher)->post(route('teacher.activities.grades.store', $activity), [
            'student_id' => $student->id,
            'score' => 0.00,
            'observation' => 'Nota cero válida por falta de entrega',
        ]);
        $r1->assertRedirect();
        $this->assertEquals(0.00, (float) Grade::first()->score);

        // 10.00 is accepted
        $r2 = $this->actingAs($teacher)->post(route('teacher.activities.grades.store', $activity), [
            'student_id' => $student->id,
            'score' => 10.00,
            'observation' => 'Nota perfecta obtenida',
        ]);
        $r2->assertRedirect();
        $this->assertEquals(10.00, (float) Grade::first()->score);

        // -0.01 is rejected by request validation
        $r3 = $this->actingAs($teacher)->post(route('teacher.activities.grades.store', $activity), [
            'student_id' => $student->id,
            'score' => -0.01,
            'observation' => 'Nota negativa no permitida',
        ]);
        $r3->assertSessionHasErrors('score');

        // 10.01 is rejected by request validation
        $r4 = $this->actingAs($teacher)->post(route('teacher.activities.grades.store', $activity), [
            'student_id' => $student->id,
            'score' => 10.01,
            'observation' => 'Nota excedida no permitida',
        ]);
        $r4->assertSessionHasErrors('score');
    }

    public function test_observation_validations()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $student = User::factory()->create(['role' => UserRole::Student, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();
        app(PartialPublicationStateService::class)->ensureForAssignment($assignment);

        $activity = Activity::create([
            'teaching_assignment_id' => $assignment->id,
            'partial_id' => $partial->id,
            'name' => 'Tarea 1',
            'percentage' => 30.00,
            'active' => true,
        ]);

        // Empty observation rejected
        $r1 = $this->actingAs($teacher)->post(route('teacher.activities.grades.store', $activity), [
            'student_id' => $student->id,
            'score' => 8.00,
            'observation' => '',
        ]);
        $r1->assertSessionHasErrors('observation');

        // 2 chars observation rejected
        $r2 = $this->actingAs($teacher)->post(route('teacher.activities.grades.store', $activity), [
            'student_id' => $student->id,
            'score' => 8.00,
            'observation' => 'OK',
        ]);
        $r2->assertSessionHasErrors('observation');

        // 3 chars observation accepted
        $r3 = $this->actingAs($teacher)->post(route('teacher.activities.grades.store', $activity), [
            'student_id' => $student->id,
            'score' => 8.00,
            'observation' => 'Sol',
        ]);
        $r3->assertRedirect();

        // >500 chars observation rejected
        $longObs = str_repeat('a', 501);
        $r4 = $this->actingAs($teacher)->post(route('teacher.activities.grades.store', $activity), [
            'student_id' => $student->id,
            'score' => 8.00,
            'observation' => $longObs,
        ]);
        $r4->assertSessionHasErrors('observation');
    }

    public function test_bulk_upsert_saves_valid_rows_and_skips_empty_rows()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $s1 = User::factory()->create(['role' => UserRole::Student, 'active' => true]);
        $s2 = User::factory()->create(['role' => UserRole::Student, 'active' => true]);
        $s3 = User::factory()->create(['role' => UserRole::Student, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        foreach ([$s1, $s2, $s3] as $st) {
            Enrollment::create([
                'student_id' => $st->id,
                'course_id' => $course->id,
                'academic_period_id' => $period->id,
                'active' => true,
            ]);
        }

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();
        app(PartialPublicationStateService::class)->ensureForAssignment($assignment);

        $activity = Activity::create([
            'teaching_assignment_id' => $assignment->id,
            'partial_id' => $partial->id,
            'name' => 'Tarea Masiva',
            'percentage' => 40.00,
            'active' => true,
        ]);

        $response = $this->actingAs($teacher)->post(route('teacher.assignments.partials.grades.bulk-upsert', [$assignment, $partial]), [
            'activity_id' => $activity->id,
            'grades' => [
                ['student_id' => $s1->id, 'score' => 9.00, 'observation' => 'Excelente trabajo'],
                ['student_id' => $s2->id, 'score' => 7.50, 'observation' => 'Buen intento'],
                ['student_id' => $s3->id, 'score' => '', 'observation' => ''], // empty row skipped
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals(2, Grade::where('activity_id', $activity->id)->count());
        $this->assertDatabaseHas('grades', ['student_id' => $s1->id, 'score' => 9.00]);
        $this->assertDatabaseHas('grades', ['student_id' => $s2->id, 'score' => 7.50]);
        $this->assertDatabaseMissing('grades', ['student_id' => $s3->id]);
    }

    public function test_bulk_upsert_atomic_rollback_on_invalid_row()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $s1 = User::factory()->create(['role' => UserRole::Student, 'active' => true]);
        $s2 = User::factory()->create(['role' => UserRole::Student, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        foreach ([$s1, $s2] as $st) {
            Enrollment::create([
                'student_id' => $st->id,
                'course_id' => $course->id,
                'academic_period_id' => $period->id,
                'active' => true,
            ]);
        }

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();
        app(PartialPublicationStateService::class)->ensureForAssignment($assignment);

        $activity = Activity::create([
            'teaching_assignment_id' => $assignment->id,
            'partial_id' => $partial->id,
            'name' => 'Tarea Fallo',
            'percentage' => 50.00,
            'active' => true,
        ]);

        $response = $this->actingAs($teacher)->post(route('teacher.assignments.partials.grades.bulk-upsert', [$assignment, $partial]), [
            'activity_id' => $activity->id,
            'grades' => [
                ['student_id' => $s1->id, 'score' => 9.00, 'observation' => 'Correcto'],
                ['student_id' => $s2->id, 'score' => 15.00, 'observation' => 'Invalido'], // Invalid score > 10
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertEquals(0, Grade::where('activity_id', $activity->id)->count());
    }

    public function test_published_partial_blocks_grading()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $student = User::factory()->create(['role' => UserRole::Student, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();
        app(PartialPublicationStateService::class)->ensureForAssignment($assignment);

        // Update status to Published
        PartialPublication::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->update(['status' => PublicationStatus::Published]);

        $activity = Activity::create([
            'teaching_assignment_id' => $assignment->id,
            'partial_id' => $partial->id,
            'name' => 'Tarea Publicada',
            'percentage' => 30.00,
            'active' => true,
        ]);

        $service = app(GradeService::class);

        $this->expectException(InvalidArgumentException::class);
        $service->saveGrade($activity, $student->id, 8.00, 'Intento en parcial publicado', $teacher);
    }

    public function test_reopened_partial_records_audit_log()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $student = User::factory()->create(['role' => UserRole::Student, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $partial = Partial::where('academic_period_id', $period->id)->first();
        app(PartialPublicationStateService::class)->ensureForAssignment($assignment);

        // Update status to Reopened
        PartialPublication::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->update(['status' => PublicationStatus::Reopened]);

        $activity = Activity::create([
            'teaching_assignment_id' => $assignment->id,
            'partial_id' => $partial->id,
            'name' => 'Tarea Reabierta',
            'percentage' => 30.00,
            'active' => true,
        ]);

        $service = app(GradeService::class);
        $grade = $service->saveGrade($activity, $student->id, 7.50, 'Calificación tras reapertura', $teacher);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'grade.created_after_reopen',
            'auditable_type' => Grade::class,
            'auditable_id' => $grade->id,
        ]);
    }

    public function test_admin_can_view_grades_read_only()
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('role', UserRole::Admin)->first();

        $response = $this->actingAs($admin)->get(route('admin.grades.index'));
        $response->assertOk();
        $response->assertSee('Monitoreo General de Calificaciones');
    }

    public function test_student_and_guest_cannot_grade_or_view_grades()
    {
        $period = AcademicPeriod::factory()->create(['active' => true]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'active' => true]);
        $student = User::factory()->create(['role' => UserRole::Student, 'active' => true]);
        $course = Course::factory()->create(['active' => true]);
        $subject = Subject::factory()->create(['active' => true]);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

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
            'percentage' => 30.00,
            'active' => true,
        ]);

        // Student attempt to store grade
        $r1 = $this->actingAs($student)->post(route('teacher.activities.grades.store', $activity), [
            'student_id' => $student->id,
            'score' => 10.00,
            'observation' => 'Autocalificación',
        ]);
        $r1->assertForbidden();

        // Student attempt to access teacher grading index
        $r2 = $this->actingAs($student)->get(route('teacher.assignments.partials.grades.index', [$assignment, $partial]));
        $r2->assertForbidden();
    }

    public function test_seeders_are_idempotent_and_seed_21_demo_grades()
    {
        $this->seed(DatabaseSeeder::class);
        $countFirstSeed = Grade::count();

        // Run seed a second time to test idempotency
        $this->seed(DatabaseSeeder::class);
        $countSecondSeed = Grade::count();

        $this->assertEquals(21, $countFirstSeed);
        $this->assertEquals(21, $countSecondSeed);

        // Check Matemáticas Octavo A P1 & P2 = 18 grades
        $octavoA = Course::where('code', '8VO-A')->orWhere('name', 'Octavo A')->first();
        $matematicas = Subject::where('code', 'MAT8')->orWhere('name', 'Matemáticas')->first();
        $mathAssignment = TeachingAssignment::where('course_id', $octavoA->id)->where('subject_id', $matematicas->id)->first();
        $p1 = Partial::where('academic_period_id', $mathAssignment->academic_period_id)->where('number', 1)->first();
        $p2 = Partial::where('academic_period_id', $mathAssignment->academic_period_id)->where('number', 2)->first();

        $mathP1ActIds = Activity::where('teaching_assignment_id', $mathAssignment->id)->where('partial_id', $p1->id)->pluck('id');
        $mathP2ActIds = Activity::where('teaching_assignment_id', $mathAssignment->id)->where('partial_id', $p2->id)->pluck('id');

        $mathP1GradesCount = Grade::whereIn('activity_id', $mathP1ActIds)->count();
        $mathP2GradesCount = Grade::whereIn('activity_id', $mathP2ActIds)->count();

        $this->assertEquals(9, $mathP1GradesCount);
        $this->assertEquals(9, $mathP2GradesCount);

        // Check completion metrics
        $completionService = app(GradeCompletionService::class);

        $mathP1Metrics = $completionService->calculateForPartial($mathAssignment, $p1);
        $this->assertEquals('grading_complete', $mathP1Metrics['status']);

        $mathP2Metrics = $completionService->calculateForPartial($mathAssignment, $p2);
        $this->assertEquals('grading_complete', $mathP2Metrics['status']);

        // Check Lengua Octavo A P1 = 3 grades registered, 3 pending
        $lengua = Subject::where('code', 'LIT8')->orWhere('name', 'Lengua y Literatura')->first();
        $lenguaAssignment = TeachingAssignment::where('course_id', $octavoA->id)->where('subject_id', $lengua->id)->first();
        $lenguaP1Metrics = $completionService->calculateForPartial($lenguaAssignment, $p1);

        $this->assertEquals('grading_incomplete', $lenguaP1Metrics['status']);
        $this->assertEquals(3, $lenguaP1Metrics['completed_grades']);
        $this->assertEquals(3, $lenguaP1Metrics['pending_grades']);
    }

    public function test_averages_table_or_columns_do_not_exist_in_k007()
    {
        $this->assertTrue(Schema::hasTable('grades'));
        $this->assertFalse(Schema::hasColumn('grades', 'partial_average'));
        $this->assertFalse(Schema::hasColumn('grades', 'final_average'));
    }
}
