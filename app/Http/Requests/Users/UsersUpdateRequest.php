<?php

namespace App\Http\Requests\Users;

use App\Enums\ContactType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsersUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit users');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:4', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['nullable', 'string', 'min:8'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
            'tags' => ['nullable', 'array'],
            'avatar' => ['nullable', 'image', 'max:2048'],

            'bio' => ['nullable', 'string', 'max:2000'],
            'contacts' => ['nullable', 'array'],
            'contacts.*.type' => ['required', Rule::enum(ContactType::class)],
            'contacts.*.value' => ['required', 'string', 'max:255'],
            'contacts.*.description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
