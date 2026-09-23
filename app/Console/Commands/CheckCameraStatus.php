<?php

namespace App\Console\Commands;

use App\Events\CameraStatusUpdated;
use App\Models\AlertLog;
use App\Models\Camera;
use Illuminate\Console\Command;

class CheckCameraStatus extends Command
{
    protected $signature = 'cctv:check-status';

    protected $description = 'Simulate checking status of all CCTV cameras';

    public function handle(): void
    {
        $cameras = Camera::all();
        $this->info("Checking {$cameras->count()} cameras...");

        foreach ($cameras as $camera) {
            $previousStatus = $camera->status;

            // TODO: Replace with real ping/RTSP check in production
            // For now, we simulate a small chance of status change
            $rand = rand(1, 100);
            if ($rand <= 2) {
                $newStatus = 'offline';
            } elseif ($rand <= 6) {
                $newStatus = 'gangguan';
            } else {
                $newStatus = 'online';
            }

            $camera->update([
                'status'          => $newStatus,
                'last_checked_at' => now(),
            ]);

            // Log alert if status changed to bad state
            if ($previousStatus !== $newStatus && in_array($newStatus, ['offline', 'gangguan'])) {
                AlertLog::create([
                    'camera_id' => $camera->id,
                    'status'    => $newStatus,
                    'timestamp' => now(),
                ]);

                $this->warn("Alert: {$camera->nama} changed to {$newStatus}");
            }

            // Broadcast real-time update
            broadcast(new CameraStatusUpdated($camera));
        }

        $this->info('Done checking cameras.');
    }
}
