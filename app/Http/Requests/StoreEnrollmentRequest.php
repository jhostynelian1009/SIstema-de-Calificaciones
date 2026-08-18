<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'academic_period_id' => ['required', 'integer', 'exists:academic_periods,id'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Debe seleccionar un estudiante.',
            'student_id.exists' => 'El estudiante seleccionado no es válido.',
            'course_id.required' => 'Debe seleccionar un curso.',
            'course_id.exists' => 'El curso seleccionado no existe.',
            'academic_period_id.required' => 'Debe seleccionar un período académico.',
            'academic_period_id.exists' => 'El período académico seleccionado no existe.',
        ];
    }
}
