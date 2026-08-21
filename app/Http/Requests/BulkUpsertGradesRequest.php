<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BulkUpsertGradesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'grades' => ['required', 'array'],
            'grades.*.student_id' => ['required', 'integer', 'exists:users,id'],
            'grades.*.score' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'grades.*.observation' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom validation logic for conditional inter-field rules.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $grades = $this->input('grades', []);
            if (! is_array($grades)) {
                return;
            }

            foreach ($grades as $index => $row) {
                $score = $row['score'] ?? null;
                $observation = $row['observation'] ?? null;

                $isScoreEmpty = $score === null || $score === '';
                $isObsEmpty = $observation === null || trim((string) $observation) === '';

                if ($isScoreEmpty && $isObsEmpty) {
                    continue;
                }

                if ($isScoreEmpty && ! $isObsEmpty) {
                    $validator->errors()->add(
                        "grades.{$index}.score",
                        'La calificación es obligatoria cuando se registra una observación.'
                    );
                }

                if (! $isScoreEmpty && $isObsEmpty) {
                    $validator->errors()->add(
                        "grades.{$index}.observation",
                        'La observación es obligatoria (mínimo 3 caracteres) cuando se registra una calificación.'
                    );
                } elseif (! $isObsEmpty && mb_strlen(trim((string) $observation)) < 3) {
                    $validator->errors()->add(
                        "grades.{$index}.observation",
                        'La observación debe contener al menos 3 caracteres.'
                    );
                }
            }
        });
    }

    /**
     * Custom spanish messages.
     */
    public function messages(): array
    {
        return [
            'grades.required' => 'La nómina de calificaciones es obligatoria.',
            'grades.array' => 'El formato de calificaciones no es válido.',
            'grades.*.student_id.required' => 'El identificador de estudiante es obligatorio.',
            'grades.*.student_id.exists' => 'Uno o más estudiantes especificados no existen.',
            'grades.*.score.numeric' => 'La calificación debe ser un valor numérico.',
            'grades.*.score.min' => 'La calificación mínima permitida es 0.00.',
            'grades.*.score.max' => 'La calificación máxima permitida es 10.00.',
            'grades.*.observation.max' => 'La observación no puede exceder los 500 caracteres.',
        ];
    }
}
