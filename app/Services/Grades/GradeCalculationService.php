<?php

namespace App\Services\Grades;

use App\Enums\PublicationStatus;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Partial;
use App\Models\PartialPublication;
use App\Models\TeachingAssignment;
use App\Models\User;

class GradeCalculationService
{
    /**
     * Calculate partial average for a student in a teaching assignment and partial.
     *
     * @return array{
     *     calculable: bool,
     *     score_hundredths: int|null,
     *     score_formatted: string|null,
     *     components: array,
     *     error: string|null
     * }
     */
    public function calculatePartialAverage(
        TeachingAssignment $assignment,
        Partial $partial,
        User|int $student,
        bool $requirePublished = false
    ): array {
        $studentId = $student instanceof User ? $student->id : (int) $student;

        if ((int) $partial->academic_period_id !== (int) $assignment->academic_period_id) {
            return [
                'calculable' => false,
                'score_hundredths' => null,
                'score_formatted' => null,
                'components' => [],
                'error' => 'El parcial no pertenece al período académico de la asignación.',
            ];
        }

        if ($requirePublished) {
            $publication = PartialPublication::where('teaching_assignment_id', $assignment->id)
                ->where('partial_id', $partial->id)
                ->first();

            if (! $publication || $publication->status !== PublicationStatus::Published) {
                return [
                    'calculable' => false,
                    'score_hundredths' => null,
                    'score_formatted' => null,
                    'components' => [],
                    'error' => 'El parcial no se encuentra publicado de forma oficial.',
                ];
            }
        }

        $activeActivities = Activity::where('teaching_assignment_id', $assignment->id)
            ->where('partial_id', $partial->id)
            ->where('active', true)
            ->get();

        if ($activeActivities->isEmpty()) {
            return [
                'calculable' => false,
                'score_hundredths' => null,
                'score_formatted' => null,
                'components' => [],
                'error' => 'No existen actividades evaluativas activas en el parcial.',
            ];
        }

        // Sum weighting in hundredths (e.g. 100.00% = 10000)
        $totalWeightHundredths = 0;
        foreach ($activeActivities as $act) {
            $totalWeightHundredths += (int) round((float) $act->percentage * 100);
        }

        if ($totalWeightHundredths !== 10000) {
            return [
                'calculable' => false,
                'score_hundredths' => null,
                'score_formatted' => null,
                'components' => [],
                'error' => 'La ponderación total de las actividades no suma exactamente el 100.00%.',
            ];
        }

        $activityIds = $activeActivities->pluck('id')->toArray();
        $grades = Grade::whereIn('activity_id', $activityIds)
            ->where('student_id', $studentId)
            ->get()
            ->keyBy('activity_id');

        $numerator = 0;
        $components = [];

        foreach ($activeActivities as $act) {
            $grade = $grades->get($act->id);

            if (! $grade || $grade->score === null || mb_strlen(trim($grade->observation ?? '')) < 3) {
                return [
                    'calculable' => false,
                    'score_hundredths' => null,
                    'score_formatted' => null,
                    'components' => [],
                    'error' => "El estudiante no tiene una calificación u observación válida para la actividad '{$act->name}'.",
                ];
            }

            $scoreHundredths = (int) round((float) $grade->score * 100);
            $pctHundredths = (int) round((float) $act->percentage * 100);

            $product = $scoreHundredths * $pctHundredths;
            $numerator += $product;

            $components[] = [
                'activity_id' => $act->id,
                'activity_name' => $act->name,
                'percentage' => (float) $act->percentage,
                'percentage_hundredths' => $pctHundredths,
                'score' => (float) $grade->score,
                'score_hundredths' => $scoreHundredths,
                'observation' => $grade->observation,
                'product' => $product,
            ];
        }

        $partialAverageHundredths = (int) round($numerator / 10000, 0, PHP_ROUND_HALF_UP);
        $formatted = number_format($partialAverageHundredths / 100, 2, '.', '');

        return [
            'calculable' => true,
            'score_hundredths' => $partialAverageHundredths,
            'score_formatted' => $formatted,
            'components' => $components,
            'error' => null,
        ];
    }

    /**
     * Calculate final subject average for a student (P1 * 50% + P2 * 50%).
     *
     * @return array{
     *     calculable: bool,
     *     score_hundredths: int|null,
     *     score_formatted: string|null,
     *     p1: array,
     *     p2: array,
     *     error: string|null
     * }
     */
    public function calculateFinalSubjectAverage(
        TeachingAssignment $assignment,
        User|int $student,
        bool $officialOnly = true
    ): array {
        $partials = Partial::where('academic_period_id', $assignment->academic_period_id)
            ->orderBy('number', 'asc')
            ->get();

        $p1 = $partials->firstWhere('number', 1);
        $p2 = $partials->firstWhere('number', 2);

        if (! $p1 || ! $p2) {
            return [
                'calculable' => false,
                'score_hundredths' => null,
                'score_formatted' => null,
                'p1' => [],
                'p2' => [],
                'error' => 'No se encontraron los parciales P1 y P2 para el período académico.',
            ];
        }

        $p1Res = $this->calculatePartialAverage($assignment, $p1, $student, requirePublished: $officialOnly);
        $p2Res = $this->calculatePartialAverage($assignment, $p2, $student, requirePublished: $officialOnly);

        if (! $p1Res['calculable'] || ! $p2Res['calculable']) {
            return [
                'calculable' => false,
                'score_hundredths' => null,
                'score_formatted' => null,
                'p1' => $p1Res,
                'p2' => $p2Res,
                'error' => 'No es posible calcular la nota final porque uno o ambos parciales están incompletos o no publicados.',
            ];
        }

        $sumHundredths = $p1Res['score_hundredths'] + $p2Res['score_hundredths'];
        $finalHundredths = (int) round($sumHundredths / 2, 0, PHP_ROUND_HALF_UP);
        $formatted = number_format($finalHundredths / 100, 2, '.', '');

        return [
            'calculable' => true,
            'score_hundredths' => $finalHundredths,
            'score_formatted' => $formatted,
            'p1' => $p1Res,
            'p2' => $p2Res,
            'error' => null,
        ];
    }

    /**
     * Calculate general period average across all active applicable subjects for a course.
     *
     * @return array{
     *     calculable: bool,
     *     score_hundredths: int|null,
     *     score_formatted: string|null,
     *     subject_results: array,
     *     error: string|null
     * }
     */
    public function calculateGeneralAverage(
        Course $course,
        int $academicPeriodId,
        User|int $student,
        bool $officialOnly = true
    ): array {
        $assignments = TeachingAssignment::where('course_id', $course->id)
            ->where('academic_period_id', $academicPeriodId)
            ->where('active', true)
            ->get();

        if ($assignments->isEmpty()) {
            return [
                'calculable' => false,
                'score_hundredths' => null,
                'score_formatted' => null,
                'subject_results' => [],
                'error' => 'No existen asignaciones docentes activas en el curso y período.',
            ];
        }

        $sumFinalHundredths = 0;
        $subjectResults = [];

        foreach ($assignments as $assignment) {
            $subjectRes = $this->calculateFinalSubjectAverage($assignment, $student, officialOnly: $officialOnly);

            if (! $subjectRes['calculable']) {
                return [
                    'calculable' => false,
                    'score_hundredths' => null,
                    'score_formatted' => null,
                    'subject_results' => [],
                    'error' => "La asignatura '{$assignment->subject->name}' no tiene una nota final oficial calculable.",
                ];
            }

            $sumFinalHundredths += $subjectRes['score_hundredths'];
            $subjectResults[] = [
                'assignment_id' => $assignment->id,
                'subject_name' => $assignment->subject->name,
                'final_score_hundredths' => $subjectRes['score_hundredths'],
                'final_score_formatted' => $subjectRes['score_formatted'],
            ];
        }

        $count = count($assignments);
        $generalHundredths = (int) round($sumFinalHundredths / $count, 0, PHP_ROUND_HALF_UP);
        $formatted = number_format($generalHundredths / 100, 2, '.', '');

        return [
            'calculable' => true,
            'score_hundredths' => $generalHundredths,
            'score_formatted' => $formatted,
            'subject_results' => $subjectResults,
            'error' => null,
        ];
    }
}
