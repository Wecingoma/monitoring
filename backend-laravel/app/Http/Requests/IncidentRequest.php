<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'severity' => ['required', 'in:critical,major,minor,low'],
            'status' => ['sometimes', 'in:open,investigating,identified,monitoring,resolved'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'started_at' => ['required', 'date'],
            'affected_servers' => ['nullable', 'array'],
            'root_cause' => ['nullable', 'string'],
            'resolution' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre de l\'incident est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'severity.required' => 'La sévérité est obligatoire.',
            'severity.in' => 'La sévérité doit être: critical, major, minor ou low.',
            'started_at.required' => 'La date de début est obligatoire.',
        ];
    }
}