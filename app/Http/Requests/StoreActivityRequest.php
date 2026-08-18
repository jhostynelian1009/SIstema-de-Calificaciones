<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is enforced via ActivityPolicy in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'due_date' => ['nullable', 'date'],
            'percentage' => ['required', 'numeric', 'gt:0', 'lte:100'],
        ];
    }

    /**
     * Custom validation messages in Spanish.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la actividad es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede superar los 150 caracteres.',
            'description.max' => 'La descripción no puede superar los 1000 caracteres.',
            'due_date.date' => 'La fecha de entrega debe ser una fecha válida.',
            'percentage.required' => 'El porcentaje es obligatorio.',
            'percentage.numeric' => 'El porcentaje debe ser un valor numérico.',
            'percentage.gt' => 'El porcentaje debe ser mayor que 0.00%.',
            'percentage.lte' => 'El porcentaje no puede ser mayor que 100.00%.',
        ];
    }
}
