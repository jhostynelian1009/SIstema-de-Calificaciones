<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'score' => ['required', 'numeric', 'min:0', 'max:10'],
            'observation' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    /**
     * Custom spanish messages.
     */
    public function messages(): array
    {
        return [
            'score.required' => 'La calificación es obligatoria.',
            'score.numeric' => 'La calificación debe ser un valor numérico.',
            'score.min' => 'La calificación mínima permitida es 0.00.',
            'score.max' => 'La calificación máxima permitida es 10.00.',
            'observation.required' => 'La observación es obligatoria.',
            'observation.min' => 'La observación debe contener al menos 3 caracteres.',
            'observation.max' => 'La observación no puede exceder los 500 caracteres.',
        ];
    }
}
