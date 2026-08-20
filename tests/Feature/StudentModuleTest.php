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
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StudentModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    #[Test]
    public function student_can_access_dashboard_and_grades()
    {
        $student = User::where('role', UserRole::Student)->firstOrFail();

        $response = $this->actingAs($student)->get(route('student.dashboard'));
        $response->assertStatus(200);
        $response->assertViewIs('student.dashboard');
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));

        $responseGrades = $this->actingAs($student)->get(route('student.grades.index'));
        $responseGrades->assertStatus(200);
        $responseGrades->assertViewIs('student.grades.index');
        $this->assertStringContainsString('no-store', (string) $responseGrades->headers->get('Cache-Control'));
    }

    #[Test]
    public function admin_and_teacher_cannot_access_student_dashboard_or_grades()
    {
        $admin = User::where('role', UserRole::Admin)->firstOrFail();
        $teacher = User::where('role', UserRole::Teacher)->firstOrFail();

        $this->actingAs($admin)->get(route('student.dashboard'))->assertStatus(403);
        $this->actingAs($admin)->get(route('student.grades.index'))->assertStatus(403);

        $this->actingAs($teacher)->get(route('student.dashboard'))->assertStatus(403);
        $this->actingAs($teacher)->get(route('student.grades.index'))->assertStatus(403);
    }

    #[Test]
    public function guest_is_redirected_to_login()
    {
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
        $this->get(route('student.grades.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function student_views_only_own_enrollment_and_results()
    {
        $studentA = User::factory()->student()->create(['name' => 'Estudiante A']);
        $studentB = User::factory()->student()->create(['name' => 'Estudiante B']);

        $courseA = Course::factory()->create(['name' => 'Curso Exclusivo A']);
        $courseB = Course::factory()->create(['name' => 'Curso Exclusivo B']);
        $period = AcademicPeriod::where('active', true)->firstOrFail();

        Enrollment::create([
            'student_id' => $studentA->id,
            'course_id' => $courseA->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        Enrollment::create([
            'student_id' => $studentB->id,
            'course_id' => $courseB->id,
            'academic_period_id' => $period->id,
            'active' => true,
        ]);

        // Student A index see course A, dont see course B
        $response = $this->actingAs($studentA)->get(route('student.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Curso Exclusivo A');
        $response->assertDontSee('Curso Exclusivo B');
    }

    #[Test]
    public function student_cannot_access_unauthorized_period_or_assignment_details()
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();
        $periodEnrolled = AcademicPeriod::firstOrFail();

        // Academic period student is NOT enrolled in
        $periodOther = AcademicPeriod::factory()->create([
            'name' => 'Período Sin Matrícula',
            'starts_at' => '2027-01-01',
            'ends_at' => '2027-06-30',
            'active' => false,
        ]);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'academic_period_id' => $periodEnrolled->id,
            'active' => true,
        ]);

        // Accessing period student is not enrolled in receives 403
        $this->actingAs($student)
            ->get(route('student.grades.period', $periodOther))
            ->assertStatus(403);

        // Accessing assignment of another course receives 403
        $otherCourse = Course::factory()->create();
        $otherSubject = Subject::factory()->create();
        $teacher = User::where('role', UserRole::Teacher)->firstOrFail();

        $assignmentOther = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'course_id' => $otherCourse->id,
            'subject_id' => $otherSubject->id,
            'academic_period_id' => $periodEnrolled->id,
            'active' => true,
        ]);

        $this->actingAs($student)
            ->get(route('student.grades.subject', [$periodEnrolled, $assignmentOther]))
            ->assertStatus(403);
    }

    #[Test]
    public function published_partial_shows_activities_and_grades_while_draft_shows_hidden_status()
    {
        $publishedPub = PartialPublication::where('status', PublicationStatus::Published)->firstOrFail();
        $publishedAssignment = $publishedPub->teachingAssignment;
        $period = $publishedAssignment->academicPeriod;

        $student = Enrollment::where('course_id', $publishedAssignment->course_id)
            ->where('academic_period_id', $period->id)
            ->firstOrFail()
            ->student;

        // View subject detail for published assignment
        $response = $this->actingAs($student)->get(route('student.grades.subject', [$period, $publishedAssignment]));
        $response->assertStatus(200);
        $response->assertSee('Resultado disponible');
        $response->assertSee('Promedio Oficial Parcial');

        // View subject detail for assignment with draft P1
        $draftPub = PartialPublication::where('status', PublicationStatus::Draft)->firstOrFail();
        $draftAssignment = $draftPub->teachingAssignment;

        // Ensure student is enrolled in draft assignment course/period
        Enrollment::firstOrCreate([
            'student_id' => $student->id,
            'course_id' => $draftAssignment->course_id,
            'academic_period_id' => $draftAssignment->academic_period_id,
        ], ['active' => true]);

        $responseDraft = $this->actingAs($student)->get(route('student.grades.subject', [$draftAssignment->academic_period_id, $draftAssignment]));
        $responseDraft->assertStatus(200);
        $responseDraft->assertSee('Resultados aún no publicados');
        $responseDraft->assertDontSee('Promedio Oficial Parcial');
    }

    #[Test]
    public function reopened_partial_shows_temporarily_unavailable_message_and_hides_grades()
    {
        $pub = PartialPublication::firstOrFail();
        $pub->update([
            'status' => PublicationStatus::Reopened,
            'reopen_reason' => 'Corrección requerida por docente',
        ]);

        $assignment = $pub->teachingAssignment;
        $period = $assignment->academicPeriod;

        $student = Enrollment::where('course_id', $assignment->course_id)
            ->where('academic_period_id', $period->id)
            ->firstOrFail()
            ->student;

        $response = $this->actingAs($student)->get(route('student.grades.subject', [$period, $assignment]));
        $response->assertStatus(200);
        $response->assertSee('Resultado temporalmente no disponible');
        // Must NOT show previous grade or internal reopen reason
        $response->assertDontSee('Corrección requerida por docente');
    }

    #[Test]
    public function score_zero_point_zero_is_formatted_and_displayed_correctly()
    {
        $pub = PartialPublication::where('status', PublicationStatus::Published)->firstOrFail();
        $assignment = $pub->teachingAssignment;
        $period = $assignment->academicPeriod;
        $partial = $pub->partial;

        $student = Enrollment::where('course_id', $assignment->course_id)
            ->where('academic_period_id', $period->id)
            ->firstOrFail()
            ->student;

        $activity = Activity::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->where('active', true)
            ->firstOrFail();

        Grade::updateOrCreate(
            ['activity_id' => $activity->id, 'student_id' => $student->id],
            ['score' => 0.00, 'observation' => 'Nota cero oficial']
        );

        $response = $this->actingAs($student)->get(route('student.grades.subject', [$period, $assignment]));
        $response->assertStatus(200);
        $response->assertSee('0.00');
        $response->assertSee('Nota cero oficial');
    }

    #[Test]
    public function historical_inactive_enrollment_allows_access_to_published_grades()
    {
        $student = User::factory()->student()->create();
        $course = Course::firstOrFail();
        $period = AcademicPeriod::firstOrFail();

        // Inactive enrollment
        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'active' => false,
        ]);

        $response = $this->actingAs($student)->get(route('student.grades.period', $period));
        $response->assertStatus(200);
        $response->assertSee('Matrícula Inactiva');
    }

    #[Test]
    public function student_without_enrollment_receives_clean_empty_state()
    {
        $studentWithoutEnrollment = User::factory()->student()->create();

        $responseDash = $this->actingAs($studentWithoutEnrollment)->get(route('student.dashboard'));
        $responseDash->assertStatus(200);
        $responseDash->assertSee('Sin Matrícula Registrada');

        $responseGrades = $this->actingAs($studentWithoutEnrollment)->get(route('student.grades.index'));
        $responseGrades->assertStatus(200);
        $responseGrades->assertSee('Sin Histórico de Períodos');
    }

    #[Test]
    public function printable_view_requires_student_owner_and_displays_official_results_only()
    {
        $pub = PartialPublication::where('status', PublicationStatus::Published)->firstOrFail();
        $assignment = $pub->teachingAssignment;
        $period = $assignment->academicPeriod;

        $student = Enrollment::where('course_id', $assignment->course_id)
            ->where('academic_period_id', $period->id)
            ->firstOrFail()
            ->student;

        // Guest redirected
        $this->get(route('student.grades.print', $period))->assertRedirect(route('login'));

        // Owner student can access printable page
        $response = $this->actingAs($student)->get(route('student.grades.print', $period));
        $response->assertStatus(200);
        $response->assertSee('Boletín Oficial de Calificaciones');
        $response->assertSee($student->name);
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }
}
