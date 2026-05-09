<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AlertRequest extends FormRequest
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
            'severity' => ['required', 'in:critical,warning,info'],
            'source' => ['sometimes', 'in:zabbix,elastic,ia,manual'],
            'server_id' => ['nullable', 'exists:servers,id'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre de l\'alerte est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'severity.required' => 'La sévérité est obligatoire.',
            'severity.in' => 'La sévérité doit être: critical, warning ou info.',
        ];
    }
}