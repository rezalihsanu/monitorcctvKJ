<?php

use App\Models\Camera;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Polling fallback - returns all camera statuses
Route::middleware('auth:sanctum')->get('/cameras/statuses', function () {
    return Camera::select('id', 'status', 'last_checked_at')->get();
});

// API untuk update IP dan Stream URL kamera (inline edit)
Route::middleware(['auth:sanctum', 'role:admin'])->patch('/cameras/{camera}/network', function (Request $request, Camera $camera) {
    $validated = $request->validate([
        'ip_address' => 'nullable|string|max:45',
        'stream_url' => 'nullable|string|max:255',
    ]);

    $camera->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'Data kamera berhasil diperbarui',
        'data' => $camera->only(['id', 'ip_address', 'stream_url'])
    ]);
});
