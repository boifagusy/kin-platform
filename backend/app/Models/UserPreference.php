<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'preference_key',
        'value_type',
        'value',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    public function getTypedValueAttribute()
    {
        return match ($this->value_type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'string' => (string) $this->value,
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
