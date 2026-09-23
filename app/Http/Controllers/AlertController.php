<?php

namespace App\Http\Controllers;

use App\Models\AlertLog;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index()
    {
        $alerts = AlertLog::with(['camera', 'acknowledgedBy'])
            ->orderByDesc('timestamp')
            ->paginate(50);

        return view('alerts.index', compact('alerts'));
    }

    public function acknowledge(int $cameraId)
    {
        $alert = AlertLog::where('camera_id', $cameraId)
            ->whereNull('acknowledged_at')
            ->latest('timestamp')
            ->first();

        if ($alert) {
            $alert->update([
                'acknowledged_by' => auth()->id(),
                'acknowledged_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
