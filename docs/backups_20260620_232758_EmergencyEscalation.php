<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmergencyEscalation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'escalation_type',
        'status',
        'priority',
        'location_lat',
        'location_lng',
        'assigned_admin_id',
        'resolved_by',
        'resolved_at',
        'notes',
    ];

    protected $casts = [
        'location_lat' => 'decimal:8',
        'location_lng' => 'decimal:8',
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(AdminUser::class, 'assigned_admin_id');
    }

    public function resolver()
    {
        return $this->belongsTo(AdminUser::class, 'resolved_by');
    }
}
