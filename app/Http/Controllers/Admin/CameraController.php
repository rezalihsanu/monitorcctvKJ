<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use Illuminate\Http\Request;

class CameraController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', ''));

        $cameras = Camera::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('zona', 'like', "%{$search}%")
                      ->orWhere('ip_address', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('zona')
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        return view('admin.cameras.index', compact('cameras', 'search', 'status'));
    }

    public function create()
    {
        return view('admin.cameras.form', ['camera' => new Camera()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'       => 'required|string|max:255',
            'lat'        => 'required|numeric|between:-90,90',
            'lng'        => 'required|numeric|between:-180,180',
            'zona'       => 'required|string|max:100',
            'ip_address' => 'nullable|string|max:45',
            'stream_url' => 'nullable|string|max:255',
            'status'     => 'required|in:online,offline,gangguan',
        ]);

        Camera::create($validated);

        return redirect()->route('admin.cameras.index')->with('success', 'Kamera berhasil ditambahkan.');
    }

    public function edit(Camera $camera)
    {
        return view('admin.cameras.form', compact('camera'));
    }

    public function update(Request $request, Camera $camera)
    {
        $validated = $request->validate([
            'nama'       => 'required|string|max:255',
            'lat'        => 'required|numeric|between:-90,90',
            'lng'        => 'required|numeric|between:-180,180',
            'zona'       => 'required|string|max:100',
            'ip_address' => 'nullable|string|max:45',
            'stream_url' => 'nullable|string|max:255',
            'status'     => 'required|in:online,offline,gangguan',
        ]);

        $camera->update($validated);

        return redirect()->route('admin.cameras.index')->with('success', 'Data kamera berhasil diperbarui.');
    }

    public function destroy(Camera $camera)
    {
        $camera->delete();
        return redirect()->route('admin.cameras.index')->with('success', 'Kamera berhasil dihapus.');
    }
}
