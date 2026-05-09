<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $serverId = $this->route('server')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'hostname' => ['required', 'string', 'max:255', $serverId ? 'unique:servers,hostname,'.$serverId : 'unique:servers,hostname'],
            'ip_address' => ['required', 'ip'],
            'os_type' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'in:online,offline,warning,unknown'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'zabbix_host_id' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du serveur est obligatoire.',
            'hostname.required' => 'Le hostname est obligatoire.',
            'hostname.unique' => 'Ce hostname est déjà utilisé.',
            'ip_address.required' => 'L\'adresse IP est obligatoire.',
            'ip_address.ip' => 'L\'adresse IP doit être valide.',
            'status.in' => 'Le statut doit être: online, offline, warning ou unknown.',
        ];
    }
}