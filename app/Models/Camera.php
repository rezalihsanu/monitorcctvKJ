<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Camera extends Model
{
    /** @use HasFactory<\Database\Factories\CameraFactory> */
    use HasFactory;

    protected $fillable = [
        'nama', 'lat', 'lng', 'zona', 'ip_address', 'stream_url', 'status', 'last_checked_at'
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
    ];
}
