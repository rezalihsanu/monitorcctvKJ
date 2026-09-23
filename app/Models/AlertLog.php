<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertLog extends Model
{
    protected $fillable = [
        'camera_id', 'status', 'timestamp', 'acknowledged_by', 'acknowledged_at'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function camera()
    {
        return $this->belongsTo(Camera::class);
    }

    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }
}
