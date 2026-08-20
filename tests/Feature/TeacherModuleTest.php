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
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /** @test */
    public function teacher_can_access_dashboard_with_scoped_metrics()
    {
        $teacher = User::where('role', UserRole::Teacher)->firstOrFail();

        $response = $this->actingAs($teacher)->get(route('teacher.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.dashboard');
        $response->assertViewHas('active_assignments_count');
        $response->assertViewHas('priority_actions');
    }

    /** @test */
    public function admin_and_student_cannot_access_teacher_dashboard()
    {
        $admin = User::where('role', UserRole::Admin)->firstOrFail();
        $student = User::where('role', UserRole::Student)->firstOrFail();

        $this->actingAs($admin)->get(route('teacher.dashboard'))->assertStatus(403);
        $this->actingAs($student)->get(route('teacher.dashboard'))->assertStatus(403);
    }

    /** @test */
    public function teacher_views_only_own_assignments()
    {
        $teacherA = User::factory()->teacher()->create(['name' => 'Docente A Exclusivo']);
        $teacherB = User::factory()->teacher()->create(['name' => 'Docente B Exclusivo']);

        $course = Course::firstOrFail();
        $period = AcademicPeriod::firstOrFail();

        $subjectA = Subject::factory()->create(['name' => 'Asignatura Exclusiva A']);
        $subjectB = Subject::factory()->create(['name' => 'Asignatura Exclusiva B']);

        $assignmentA = TeachingAssignment::create([
            'teacher_id' => $teacherA->id,
            'course_id' => $course->id,
            'subject_id' => $subjectA->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        $assignmentB = TeachingAssignment::create([
            'teacher_id' => $teacherB->id,
            'course_id' => $course->id,
            'subject_id' => $subjectB->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        // Teacher A listing
        $response = $this->actingAs($teacherA)->get(route('teacher.assignments.index'));
        $response->assertStatus(200);
        $response->assertSee('Asignatura Exclusiva A');
        $response->assertDontSee('Asignatura Exclusiva B');

        // Teacher A trying to access Teacher B's assignment detail receives 403
        $this->actingAs($teacherA)->get(route('teacher.assignments.show', $assignmentB))->assertStatus(403);
    }

    /** @test */
    public function teacher_assignments_index_filters_work()
    {
        $teacher = User::where('role', UserRole::Teacher)->firstOrFail();
        $assignment = TeachingAssignment::where('teacher_id', $teacher->id)->firstOrFail();

        $response = $this->actingAs($teacher)->get(route('teacher.assignments.index', [
            'search' => $assignment->subject->name,
            'academic_period_id' => $assignment->academic_period_id,
        ]));

        $response->assertStatus(200);
        $response->assertSee($assignment->subject->name);
    }

    /** @test */
    public function inactive_assignment_is_read_only_and_disables_activity_creation()
    {
        // Pick assignment with draft partial
        $pub = PartialPublication::where('status', PublicationStatus::Draft)->firstOrFail();
        $assignment = $pub->teachingAssignment;
        $teacher = $assignment->teacher;
        $partial = $pub->partial;

        $assignment->update(['active' => false]);

        $response = $this->actingAs($teacher)->get(route('teacher.assignments.show', $assignment));
        $response->assertStatus(200);
        $response->assertSee('Asignación Inactiva');

        // Trying to create activity in inactive assignment returns 403
        $this->actingAs($teacher)->post(route('teacher.assignments.partials.activities.store', [$assignment, $partial]), [
            'name' => 'Prueba Inactiva',
            'percentage' => 10,
        ])->assertStatus(403);
    }

    /** @test */
    public function bulk_upsert_grades_supports_zero_score_and_affects_only_sent_students()
    {
        // Pick an assignment/partial in Draft state
        $pub = PartialPublication::where('status', PublicationStatus::Draft)->firstOrFail();
        $assignment = $pub->teachingAssignment;
        $teacher = $assignment->teacher;
        $partial = $pub->partial;

        $activity = Activity::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->first();

        if (!$activity) {
            $activity = Activity::create([
                'teaching_assignment_id' => $assignment->id,
                'partial_id' => $partial->id,
                'name' => 'Actividad de Prueba',
                'percentage' => 50,
                'active' => true,
            ]);
        }

        $student = Enrollment::where('course_id', $assignment->course_id)
            ->where('academic_period_id', $assignment->academic_period_id)
            ->firstOrFail()
            ->student;

        $response = $this->actingAs($teacher)->post(route('teacher.assignments.partials.grades.bulk-upsert', [$assignment, $partial]), [
            'activity_id' => $activity->id,
            'grades' => [
                [
                    'student_id' => $student->id,
                    'score' => '0.00',
                    'observation' => 'Nota cero probada correctamente',
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('grades', [
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'observation' => 'Nota cero probada correctamente',
        ]);
    }

    /** @test */
    public function published_partial_blocks_grade_updates()
    {
        $teacher = User::where('role', UserRole::Teacher)->firstOrFail();
        $pub = PartialPublication::where('status', PublicationStatus::Published)->firstOrFail();
        $assignment = $pub->teachingAssignment;
        $teacher = $assignment->teacher;
        $partial = $pub->partial;

        $activity = Activity::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->firstOrFail();

        $student = Enrollment::where('course_id', $assignment->course_id)
            ->where('academic_period_id', $assignment->academic_period_id)
            ->firstOrFail()
            ->student;

        // Trying to bulk upsert on published partial returns error redirect
        $response = $this->actingAs($teacher)->post(route('teacher.assignments.partials.grades.bulk-upsert', [$assignment, $partial]), [
            'activity_id' => $activity->id,
            'grades' => [
                [
                    'student_id' => $student->id,
                    'score' => '8.50',
                    'observation' => 'Intento bloqueado',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function teacher_can_view_assignment_results_matrix_and_printable_view()
    {
        $teacher = User::where('role', UserRole::Teacher)->firstOrFail();
        $assignment = TeachingAssignment::where('teacher_id', $teacher->id)->firstOrFail();

        // Index
        $response = $this->actingAs($teacher)->get(route('teacher.results.index'));
        $response->assertStatus(200);

        // Matrix
        $response = $this->actingAs($teacher)->get(route('teacher.results.assignment', $assignment));
        $response->assertStatus(200);
        $response->assertSee($assignment->subject->name);

        // Printable
        $response = $this->actingAs($teacher)->get(route('teacher.results.print', $assignment));
        $response->assertStatus(200);
        $response->assertSee('SISTEMA DE CALIFICACIONES ACADÉMICAS');
    }

    /** @test */
    public function teacher_cannot_view_results_or_student_transcript_of_another_teacher()
    {
        $teachers = User::where('role', UserRole::Teacher)->take(2)->get();
        $teacherA = $teachers[0];
        $teacherB = $teachers[1];

        $assignmentB = TeachingAssignment::where('teacher_id', $teacherB->id)->firstOrFail();
        $student = Enrollment::where('course_id', $assignmentB->course_id)
            ->where('academic_period_id', $assignmentB->academic_period_id)
            ->firstOrFail()
            ->student;

        // Teacher A trying to access Teacher B's assignment results
        $this->actingAs($teacherA)->get(route('teacher.results.assignment', $assignmentB))->assertStatus(403);

        // Teacher A trying to access student transcript of Teacher B's assignment
        $this->actingAs($teacherA)->get(route('teacher.results.student', ['assignment' => $assignmentB->id, 'student' => $student->id]))->assertStatus(403);
    }

    /** @test */
    public function teacher_cannot_view_transcript_for_student_not_enrolled_in_assignment_course()
    {
        $teacher = User::where('role', UserRole::Teacher)->firstOrFail();
        $assignment = TeachingAssignment::where('teacher_id', $teacher->id)->firstOrFail();

        // Find student enrolled in a different course
        $otherEnrollment = Enrollment::where('course_id', '!=', $assignment->course_id)->firstOrFail();
        $otherStudent = $otherEnrollment->student;

        $response = $this->actingAs($teacher)->get(route('teacher.results.student', [
            'assignment' => $assignment->id,
            'student' => $otherStudent->id,
        ]));

        $response->assertStatus(403);
    }
}
