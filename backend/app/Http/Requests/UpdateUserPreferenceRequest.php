<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'category' => 'required|string|in:safety,notifications,privacy,appearance,account',
            'preferences' => 'required|array',
            'preferences.*' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'category.in' => 'Category must be one of: safety, notifications, privacy, appearance, account',
            'preferences.required' => 'Preferences array is required',
            'preferences.array' => 'Preferences must be an array',
        ];
    }
}
