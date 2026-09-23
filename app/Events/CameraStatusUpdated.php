<?php

namespace App\Events;

use App\Models\Camera;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CameraStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Camera $camera;

    public function __construct(Camera $camera)
    {
        $this->camera = $camera;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('camera-status'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'camera.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id'     => $this->camera->id,
            'status' => $this->camera->status,
        ];
    }
}
