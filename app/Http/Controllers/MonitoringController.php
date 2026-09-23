<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index()
    {
        $cameras = \App\Models\Camera::all();
        $zonas = \App\Models\Camera::select('zona')->distinct()->pluck('zona');

        return view('monitoring.index', compact('cameras', 'zonas'));
    }
}
