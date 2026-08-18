<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeachingAssignmentRequest extends FormRequest
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
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
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
            'teacher_id.required' => 'Debe seleccionar un docente.',
            'teacher_id.exists' => 'El docente seleccionado no existe.',
            'course_id.required' => 'Debe seleccionar un curso.',
            'course_id.exists' => 'El curso seleccionado no existe.',
            'subject_id.required' => 'Debe seleccionar una asignatura.',
            'subject_id.exists' => 'La asignatura seleccionada no existe.',
            'academic_period_id.required' => 'Debe seleccionar un período académico.',
            'academic_period_id.exists' => 'El período académico seleccionado no existe.',
        ];
    }
}
