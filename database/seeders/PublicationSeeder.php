<?php

namespace Database\Seeders;

use App\Enums\PublicationStatus;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Services\Grades\PartialPublicationService;
use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    /**
     * Run the database seeds for publication demonstration.
     */
    public function run(): void
    {
        $publicationService = app(PartialPublicationService::class);

        $activePeriod = AcademicPeriod::where('active', true)->first();
        if (! $activePeriod) {
            return;
        }

        $course8A = Course::where('code', '8VO-A')->first();
        $mathSubject = Subject::where('code', 'MAT')->first();

        if (! $course8A || ! $mathSubject) {
            return;
        }

        $assignment = TeachingAssignment::where('course_id', $course8A->id)
            ->where('subject_id', $mathSubject->id)
            ->where('academic_period_id', $activePeriod->id)
            ->where('active', true)
            ->first();

        if (! $assignment || ! $assignment->teacher) {
            return;
        }

        $p1 = Partial::where('academic_period_id', $activePeriod->id)->where('number', 1)->first();
        $p2 = Partial::where('academic_period_id', $activePeriod->id)->where('number', 2)->first();

        $teacher = $assignment->teacher;

        foreach ([$p1, $p2] as $partial) {
            if (! $partial) {
                continue;
            }

            $pub = PartialPublication::where('teaching_assignment_id', $assignment->id)
                ->where('partial_id', $partial->id)
                ->first();

            // Idempotency: skip if already published
            if ($pub && $pub->status === PublicationStatus::Published) {
                continue;
            }

            // Publish via service using real assigned teacher actor
            $publicationService->publish($assignment, $partial, $teacher);
        }
    }
}
