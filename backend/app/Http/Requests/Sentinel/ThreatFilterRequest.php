<?php

namespace App\Http\Requests\Sentinel;

use Illuminate\Foundation\Http\FormRequest;

class ThreatFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'severity' => 'sometimes|in:critical,high,medium,low,info',
            'event_type' => 'sometimes|string|max:50',
            'user_id' => 'sometimes|integer|exists:users,id',
            'source_ip' => 'sometimes|ip',
            'start_date' => 'sometimes|date|before_or_equal:end_date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'limit' => 'sometimes|integer|min:1|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'severity.in' => 'Severity must be one of: critical, high, medium, low, info',
            'source_ip.ip' => 'Invalid IP address format',
            'start_date.date' => 'Invalid start date format',
            'end_date.date' => 'Invalid end date format',
            'limit.max' => 'Limit cannot exceed 1000',
        ];
    }
}
