<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertNote extends Model
{
    protected $fillable = [
        'alert_id',
        'admin_id',
        'note',
    ];

    public function alert(): BelongsTo
    {
        return $this->belongsTo(EmergencyEscalation::class, 'alert_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_id');
    }
}
