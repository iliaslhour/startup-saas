<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrganizationMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'L’email du membre est obligatoire.',
            'email.email' => 'L’email doit être valide.',
            'email.exists' => 'Aucun utilisateur n’existe avec cet email.',
            'role_id.required' => 'Le rôle est obligatoire.',
            'role_id.exists' => 'Le rôle sélectionné est invalide.',
        ];
    }
}