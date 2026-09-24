<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CCTV Monitoring — Stadion Kanjuruhan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css"/>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0b0f1a; color: #e2e8f0; margin: 0; }

        /* ─── Sidebar ─── */
        .sidebar {
            position: fixed; left: 0; top: 0; bottom: 0; width: 260px;
            background: linear-gradient(180deg, #111827 0%, #0f172a 100%);
            border-right: 1px solid rgba(255,255,255,.06);
            display: flex; flex-direction: column; z-index: 100;
        }
        .sidebar-brand {
            padding: 20px 24px 16px;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .sidebar-brand h1 { font-size: 14px; font-weight: 700; color: #60a5fa; margin: 0; letter-spacing: .5px; text-transform: uppercase; }
        .sidebar-brand p  { font-size: 11px; color: #64748b; margin: 4px 0 0; }
        .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
        .nav-section { font-size: 10px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 1px; padding: 12px 12px 6px; }
        .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: 8px; text-decoration: none;
            color: #94a3b8; font-size: 13px; font-weight: 500; margin-bottom: 2px;
            transition: all .15s;
        }
        .nav-link:hover, .nav-link.active { background: rgba(96,165,250,.12); color: #60a5fa; }
        .nav-link svg { width: 16px; height: 16px; flex-shrink: 0; }
        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255,255,255,.06);
            font-size: 12px; color: #64748b;
        }

        /* ─── Top Bar ─── */
        .topbar {
            position: fixed; left: 260px; right: 0; top: 0; height: 60px;
            background: rgba(11,15,26,.95); backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,.06);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px; z-index: 99;
        }
        .topbar-title { font-size: 15px; font-weight: 600; color: #f1f5f9; }
        .topbar-right { display: flex; align-items: center; gap: 16px; }
        .badge-count {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;
        }
        .badge-online  { background: rgba(34,197,94,.15); color: #4ade80; }
        .badge-gangguan{ background: rgba(234,179,8,.15);  color: #facc15; }
        .badge-offline { background: rgba(239,68,68,.15);  color: #f87171; }
        .btn-logout {
            padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 500;
            background: rgba(239,68,68,.12); color: #f87171; border: 1px solid rgba(239,68,68,.2);
            text-decoration: none; transition: all .15s; cursor: pointer;
        }
        .btn-logout:hover { background: rgba(239,68,68,.25); }

        /* ─── Main Content ─── */
        .main { margin-left: 260px; padding-top: 60px; min-height: 100vh; }
        .content { padding: 24px; }

        /* ─── Map Card ─── */
        .map-card {
            border-radius: 16px; overflow: hidden;
            border: 1px solid rgba(255,255,255,.06);
            box-shadow: 0 4px 32px rgba(0,0,0,.4);
        }
        .map-toolbar {
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
            padding: 14px 20px;
            background: rgba(17,24,39,.9);
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .map-toolbar label { font-size: 12px; color: #64748b; font-weight: 500; white-space: nowrap; }
        .select-zona {
            padding: 7px 12px; border-radius: 8px; font-size: 13px; font-weight: 500;
            background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
            color: #e2e8f0; outline: none; cursor: pointer; min-width: 180px;
        }
        .select-zona:focus { border-color: #3b82f6; }
        select option { background: #1e293b; }
        #map { height: calc(100vh - 60px - 24px - 24px - 56px); min-height: 500px; }

        /* ─── Leaflet Custom Markers ─── */
        .cctv-dot {
            width: 12px; height: 12px; border-radius: 50%;
            border: 2px solid rgba(255,255,255,.7);
            box-shadow: 0 0 8px currentColor;
        }
        .cctv-dot.online   { background: #22c55e; color: #22c55e; box-shadow: 0 0 10px #22c55e88; }
        .cctv-dot.gangguan { background: #eab308; color: #eab308; box-shadow: 0 0 10px #eab30888; }
        .cctv-dot.offline  { background: #ef4444; color: #ef4444; box-shadow: 0 0 10px #ef444488; }

        .cctv-marker {
            width: 30px; height: 30px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid rgba(255,255,255,.85);
            box-shadow: 0 0 12px currentColor;
        }
        .cctv-marker svg { width: 17px; height: 17px; stroke: #fff; }
        .cctv-marker.online   { background: #16a34a; color: #22c55e; }
        .cctv-marker.gangguan { background: #ca8a04; color: #eab308; }
        .cctv-marker.offline  { background: #dc2626; color: #ef4444; }

        .marker-cluster-online    { background: rgba(34,197,94,.25); }
        .marker-cluster-online div{ background: rgba(34,197,94,.85); }
        .marker-cluster-gangguan  { background: rgba(234,179,8,.25); }
        .marker-cluster-gangguan div{ background: rgba(234,179,8,.85); }
        .marker-cluster-offline   { background: rgba(239,68,68,.25); }
        .marker-cluster-offline div{ background: rgba(239,68,68,.85); }
        .marker-cluster div { color: #fff; font-weight: 700; font-size: 13px; }

        /* ─── Popup ─── */
        .leaflet-popup-content-wrapper {
            background: #1e293b !important;
            border: 1px solid rgba(255,255,255,.1) !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 32px rgba(0,0,0,.5) !important;
            color: #e2e8f0 !important;
            padding: 0 !important;
        }
        .leaflet-popup-content { margin: 0 !important; }
        .leaflet-popup-tip { background: #1e293b !important; }
        .popup-body { padding: 16px 18px; min-width: 220px; }
        .popup-title { font-size: 14px; font-weight: 700; color: #f1f5f9; margin-bottom: 4px; }
        .popup-zona  { font-size: 11px; color: #64748b; margin-bottom: 10px; }
        .popup-status {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; margin-bottom: 12px;
        }
        .popup-status.online   { background: rgba(34,197,94,.15); color: #4ade80; }
        .popup-status.gangguan { background: rgba(234,179,8,.15);  color: #facc15; }
        .popup-status.offline  { background: rgba(239,68,68,.15);  color: #f87171; }
        .popup-btn {
            display: block; width: 100%; padding: 8px; border-radius: 8px; text-align: center;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff; font-size: 12px; font-weight: 600; cursor: pointer; border: none;
            transition: all .15s;
        }
        .popup-btn:hover { background: linear-gradient(135deg, #60a5fa, #3b82f6); transform: translateY(-1px); }
        .popup-btn:disabled { background: #374151; color: #6b7280; cursor: not-allowed; transform: none; }

        /* ─── Stream Modal ─── */
        .stream-modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.85);
            display: none; align-items: center; justify-content: center;
            z-index: 1000; backdrop-filter: blur(4px);
        }
        .stream-modal-overlay.active { display: flex; }
        .stream-modal {
            background: #0f172a; border: 1px solid rgba(255,255,255,.1);
            border-radius: 16px; overflow: hidden; width: 90%; max-width: 860px;
            box-shadow: 0 24px 80px rgba(0,0,0,.8);
        }
        .stream-modal-header {
            padding: 16px 20px; background: rgba(17,24,39,.8);
            border-bottom: 1px solid rgba(255,255,255,.06);
            display: flex; align-items: center; justify-content: space-between;
        }
        .stream-modal-header h3 { font-size: 15px; font-weight: 600; margin: 0; color: #f1f5f9; }
        .stream-close-btn {
            width: 30px; height: 30px; border-radius: 8px; border: none; cursor: pointer;
            background: rgba(239,68,68,.15); color: #f87171; font-size: 18px; line-height: 1;
            display: flex; align-items: center; justify-content: center; transition: all .15s;
        }
        .stream-close-btn:hover { background: rgba(239,68,68,.3); }
        .stream-refresh-btn {
            width: 30px; height: 30px; border-radius: 8px; border: none; cursor: pointer;
            background: rgba(96,165,250,.15); color: #60a5fa; font-size: 18px; line-height: 1;
            display: flex; align-items: center; justify-content: center; transition: all .15s;
        }
        .stream-refresh-btn:hover { background: rgba(96,165,250,.3); }
        .stream-modal-actions { display: flex; align-items: center; gap: 8px; }
        .stream-body { padding: 0; aspect-ratio: 16/9; background: #000; display: flex; align-items: center; justify-content: center; position: relative; }
        .stream-body video, .stream-body iframe {
            width: 100%; height: 100%; border: none; display: block; background: #000;
        }
        .stream-placeholder {
            text-align: center; color: #64748b; padding: 20px;
        }
        .stream-placeholder svg { width: 64px; height: 64px; margin-bottom: 12px; opacity: .4; }
        .stream-placeholder h4 { font-size: 16px; color: #94a3b8; margin: 0 0 6px; }
        .stream-placeholder p  { font-size: 12px; margin: 0; }

        /* ─── Alert Panel ─── */
        .alert-panel {
            position: fixed; right: 24px; bottom: 24px; width: 340px;
            max-height: 400px; overflow-y: auto;
            z-index: 200;
            display: flex; flex-direction: column; gap: 8px;
        }
        .alert-item {
            background: #1e293b; border: 1px solid rgba(255,255,255,.08);
            border-radius: 12px; padding: 12px 14px;
            display: flex; align-items: flex-start; gap: 10px;
            animation: slideIn .3s ease;
        }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .alert-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
        .alert-dot.offline  { background: #ef4444; }
        .alert-dot.gangguan { background: #eab308; }
        .alert-text { flex: 1; }
        .alert-title { font-size: 12px; font-weight: 600; color: #f1f5f9; }
        .alert-sub   { font-size: 11px; color: #64748b; margin-top: 2px; }
        .alert-ack {
            padding: 3px 8px; font-size: 10px; font-weight: 600; border-radius: 6px;
            border: 1px solid rgba(96,165,250,.3); color: #60a5fa; background: rgba(96,165,250,.1);
            cursor: pointer; white-space: nowrap; transition: all .15s;
        }
        .alert-ack:hover { background: rgba(96,165,250,.2); }

        /* Legend */
        .map-legend {
            display: flex; gap: 16px; align-items: center; margin-left: auto;
        }
        .legend-item { display: flex; align-items: center; gap: 6px; font-size: 11px; color: #94a3b8; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; }

        /* Search Box */
        .search-container {
            display: flex; gap: 8px; align-items: center;
        }
        .search-input {
            padding: 8px 12px; border-radius: 8px; font-size: 13px;
            background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
            color: #e2e8f0; outline: none; min-width: 280px;
            transition: all .2s;
        }
        .search-input:focus {
            border-color: #3b82f6; background: rgba(59,130,246,.05);
        }
        .search-input::placeholder {
            color: #64748b;
        }
        .search-btn {
            padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
            background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff;
            border: none; cursor: pointer; transition: all .15s;
            display: flex; align-items: center; gap: 6px;
        }
        .search-btn:hover {
            background: linear-gradient(135deg, #60a5fa, #3b82f6); transform: translateY(-1px);
        }
        .search-btn:disabled {
            background: #374151; color: #6b7280; cursor: not-allowed; transform: none;
        }
        .clear-search {
            padding: 6px 10px; border-radius: 8px; font-size: 12px; font-weight: 500;
            background: rgba(255,255,255,.05); color: #94a3b8; border: 1px solid rgba(255,255,255,.1);
            cursor: pointer; text-decoration: none; white-space: nowrap;
            transition: all .15s;
        }
        .clear-search:hover {
            background: rgba(255,255,255,.1);
        }
        .search-results {
            position: absolute; top: 100%; left: 0; right: 0; margin-top: 4px;
            background: #1e293b; border: 1px solid rgba(255,255,255,.1);
            border-radius: 8px; overflow: hidden; max-height: 300px; overflow-y: auto;
            display: none; z-index: 500; box-shadow: 0 8px 32px rgba(0,0,0,.4);
        }
        .search-result-item {
            padding: 10px 14px; display: flex; align-items: center; gap: 10px;
            cursor: pointer; transition: background .15s; border-bottom: 1px solid rgba(255,255,255,.05);
        }
        .search-result-item:hover {
            background: rgba(96,165,250,.15);
        }
        .search-result-item:last-child {
            border-bottom: none;
        }
        .search-result-dot {
            width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
        }
        .search-result-dot.online { background: #22c55e; }
        .search-result-dot.gangguan { background: #eab308; }
        .search-result-dot.offline { background: #ef4444; }
        .search-result-text {
            flex: 1; font-size: 13px; color: #e2e8f0;
        }
        .search-result-zona {
            font-size: 11px; color: #64748b; font-weight: 500; white-space: nowrap;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <h1>CCTV Monitoring</h1>
        <p>Stadion Kanjuruhan</p>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Monitoring</div>
        <a href="{{ route('dashboard') }}" class="nav-link active">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
            Peta CCTV
        </a>
        <a href="{{ route('alerts.index') }}" class="nav-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            Log Alert
        </a>

        @if(auth()->user()->role === 'admin')
        <div class="nav-section" style="margin-top:8px;">Administrasi</div>
        <a href="{{ route('admin.cameras.index') }}" class="nav-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82V15.18a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            Manajemen Kamera
        </a>
        <a href="{{ route('admin.users.index') }}" class="nav-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Manajemen User
        </a>
        @endif
    </nav>
    <div class="sidebar-footer">
        <div style="font-weight:600; color:#94a3b8;">{{ auth()->user()->name }}</div>
        <div style="text-transform:capitalize; color:#60a5fa; font-size:11px;">{{ auth()->user()->role }}</div>
    </div>
</aside>

<!-- Top Bar -->
<header class="topbar">
    <div class="topbar-title">Peta Monitoring CCTV</div>
    <div class="topbar-right">
        <span class="badge-count badge-online" id="count-online">
            <span style="width:7px;height:7px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
            <span id="n-online">{{ $cameras->where('status','online')->count() }}</span> Online
        </span>
        <span class="badge-count badge-gangguan" id="count-gangguan">
            <span style="width:7px;height:7px;border-radius:50%;background:#eab308;display:inline-block;"></span>
            <span id="n-gangguan">{{ $cameras->where('status','gangguan')->count() }}</span> Gangguan
        </span>
        <span class="badge-count badge-offline" id="count-offline">
            <span style="width:7px;height:7px;border-radius:50%;background:#ef4444;display:inline-block;"></span>
            <span id="n-offline">{{ $cameras->where('status','offline')->count() }}</span> Offline
        </span>
        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn-logout">Keluar</button>
        </form>
    </div>
</header>

<!-- Main -->
<main class="main">
    <div class="content">
        <div class="map-card">
            <div class="map-toolbar">
                <div class="search-container" style="position:relative;">
                    <input type="text" class="search-input" id="searchInput" placeholder="Cari kamera (nama / zona)..." autocomplete="off">
                    <button class="search-btn" id="searchBtn">🔍</button>
                    <div class="search-results" id="searchResults"></div>
                </div>
                <label>Filter Zona:</label>
                <select class="select-zona" id="zonaFilter">
                    <option value="all">SEMUA ZONA</option>
                    @foreach($zonas as $zona)
                        <option value="{{ $zona }}">{{ $zona }}</option>
                    @endforeach
                </select>
                <a href="#" class="clear-search" id="clearSearch" style="display:none;">Reset</a>
                <div class="map-legend">
                    <div class="legend-item"><div class="legend-dot" style="background:#22c55e;"></div> Online</div>
                    <div class="legend-item"><div class="legend-dot" style="background:#eab308;"></div> Gangguan</div>
                    <div class="legend-item"><div class="legend-dot" style="background:#ef4444;"></div> Offline</div>
                </div>
            </div>
            <div id="map"></div>
        </div>
    </div>
</main>

<!-- Stream Modal -->
<div class="stream-modal-overlay" id="streamModal">
    <div class="stream-modal">
        <div class="stream-modal-header">
            <h3 id="streamTitle">Live Stream</h3>
            <div class="stream-modal-actions">
                <button class="stream-refresh-btn" onclick="refreshStream()" title="Refresh kamera" aria-label="Refresh kamera">↻</button>
                <button class="stream-close-btn" onclick="closeStream()" title="Tutup stream" aria-label="Tutup stream">✕</button>
            </div>
        </div>
        <div class="stream-body" id="streamBody">
            <div class="stream-placeholder">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.069A1 1 0 0121 8.82V15.18a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <h4>Live Stream On-Demand</h4>
                <p>Stream aktif saat kamera dibuka operator.<br>Backend streaming (HLS/WebRTC) perlu dikonfigurasi.</p>
            </div>
        </div>
    </div>
</div>

<!-- Alert Panel -->
<div class="alert-panel" id="alertPanel"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
    // Camera data from server
    var cameras = @json($cameras->values());
    var cameraMap = {};
    cameras.forEach(c => cameraMap[c.id] = c);

    // Stadion Kanjuruhan boundary polygon (area stadion)
    var stadiumBoundary = [
        [-8.12200, 112.57250], // Northwest corner of expanded monitoring area
        [-8.12200, 112.57530], // Northeast corner of expanded monitoring area
        [-8.15140, 112.57530],  // Southeast corner
        [-8.15140, 112.57250],  // Southwest corner
        [-8.12200, 112.57250]   // Close polygon
    ];

    // Function to check if a point is inside the stadium boundary
    function isInsideStadium(lat, lng) {
        var point = [lat, lng];
        var inside = false;
        
        for (var i = 0, j = stadiumBoundary.length - 1; i < stadiumBoundary.length; j = i++) {
            var xi = stadiumBoundary[i][0], yi = stadiumBoundary[i][1];
            var xj = stadiumBoundary[j][0], yj = stadiumBoundary[j][1];
            
            var intersect = ((yi > lng) !== (yj > lng)) && 
                           (lat < (xj - xi) * (lng - yi) / (yj - yi) + xi);
            if (intersect) inside = !inside;
        }
        
        return inside;
    }

    // Filter cameras: hanya tampilkan yang di dalam boundary stadion
    cameras = cameras.filter(function(cam) {
        var lat = parseFloat(cam.lat);
        var lng = parseFloat(cam.lng);
        return !isNaN(lat) && !isNaN(lng) && isInsideStadium(lat, lng);
    });
    
    // Rebuild camera map after filtering
    cameraMap = {};
    cameras.forEach(c => cameraMap[c.id] = c);

    // Initialize Leaflet Map — Stadion Kanjuruhan bounds (revised)
    var map = L.map('map', {
        center: [-8.14960, 112.57390],
        zoom: 17,
        minZoom: 15,
        maxZoom: 22,
        maxBounds: [[-8.1150, 112.5620], [-8.1610, 112.5860]],
        maxBoundsViscosity: 0.9,
        zoomControl: true
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxNativeZoom: 19,
        maxZoom: 22,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    // Add layer control
    L.control.layers(null, {}, { collapsed: true }).addTo(map);

    var markerGroup = L.markerClusterGroup({
        maxClusterRadius: 50,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        iconCreateFunction: function(cluster) {
            var children = cluster.getAllChildMarkers();
            var hasOffline = children.some(m => m.options.cameraStatus === 'offline');
            var hasGangguan = children.some(m => m.options.cameraStatus === 'gangguan');
            var cls = hasOffline ? 'offline' : hasGangguan ? 'gangguan' : 'online';
            return L.divIcon({
                html: '<div><span>' + cluster.getChildCount() + '</span></div>',
                className: 'marker-cluster marker-cluster-' + cls,
                iconSize: [40, 40]
            });
        }
    });

    var leafletMarkers = {}; // id -> L.Marker

    function makeIcon(status) {
        return L.divIcon({
            className: '',
            html: '<div class="cctv-marker ' + status + '"><svg fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15],
            popupAnchor: [0, -18]
        });
    }

    function makePopupHtml(cam) {
        var streamLabel = cam.status === 'offline' ? 'Kamera Offline' : 'Buka Live Stream';
        var streamDisabled = cam.status === 'offline' ? 'disabled' : '';
        return `<div class="popup-body">
            <div class="popup-title">${cam.nama}</div>
            <div class="popup-zona">Zona: ${cam.zona}</div>
            <div class="popup-status ${cam.status}">
                <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                ${cam.status.charAt(0).toUpperCase() + cam.status.slice(1)}
            </div>
            <button class="popup-btn" onclick="openStreamForCamera(${cam.id})" ${streamDisabled}>${streamLabel}</button>
        </div>`;
    }

    function renderMarkers(filterZona = 'all') {
        markerGroup.clearLayers();
        leafletMarkers = {};

        cameras.forEach(function(cam) {
            if (filterZona !== 'all' && cam.zona !== filterZona) return;

            var marker = L.marker([parseFloat(cam.lat), parseFloat(cam.lng)], {
                icon: makeIcon(cam.status),
                cameraStatus: cam.status,
                cameraId: cam.id
            });

            marker.bindPopup(makePopupHtml(cam), { maxWidth: 280 });
            leafletMarkers[cam.id] = marker;
            markerGroup.addLayer(marker);
        });

        map.addLayer(markerGroup);
    }

    renderMarkers();

    document.getElementById('zonaFilter').addEventListener('change', function() {
        renderMarkers(this.value);
        document.getElementById('searchInput').value = '';
        document.getElementById('searchResults').style.display = 'none';
        document.getElementById('clearSearch').style.display = 'none';
    });

    // ─── Search Functionality ───
    var searchInput = document.getElementById('searchInput');
    var searchResults = document.getElementById('searchResults');
    var searchBtn = document.getElementById('searchBtn');
    var clearSearch = document.getElementById('clearSearch');
    var selectedCameraId = null;

    // Live search on input
    searchInput.addEventListener('input', function() {
        var query = this.value.toLowerCase().trim();
        
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        var matches = cameras.filter(c => 
            c.nama.toLowerCase().includes(query) || 
            c.zona.toLowerCase().includes(query) ||
            (c.ip_address && c.ip_address.includes(query))
        ).slice(0, 10);

        if (matches.length === 0) {
            searchResults.innerHTML = '<div class="search-result-item" style="color:#64748b;cursor:default;">Tidak ditemukan kamera.</div>';
            searchResults.style.display = 'block';
            return;
        }

        var html = matches.map(c => `
            <div class="search-result-item" data-camera-id="${c.id}">
                <div class="search-result-dot ${c.status}"></div>
                <div class="search-result-text">${c.nama}</div>
                <div class="search-result-zona">${c.zona}</div>
            </div>
        `).join('');

        searchResults.innerHTML = html;
        searchResults.style.display = 'block';
    });

    // Click on search result
    searchResults.addEventListener('click', function(e) {
        var item = e.target.closest('.search-result-item');
        if (!item || item.style.cursor === 'default') return;

        var cameraId = parseInt(item.dataset.cameraId);
        var cam = cameraMap[cameraId];

        if (cam) {
            // Reset zona filter to 'all' to ensure marker is visible
            document.getElementById('zonaFilter').value = 'all';
            renderMarkers('all');
            
            var marker = leafletMarkers[cameraId];
            if (marker) {
                // zoomToShowLayer akan otomatis memecah cluster dan menampilkan marker
                markerGroup.zoomToShowLayer(marker, function() {
                    marker.openPopup();
                    marker.setZIndexOffset(1000); // Bring to front
                });
                
                // Fallback flyTo jika zoomToShowLayer tidak cukup
                map.flyTo([parseFloat(cam.lat), parseFloat(cam.lng)], Math.max(map.getZoom(), 18), {
                    duration: 1.0
                });
            }
            
            // Update UI
            searchInput.value = cam.nama;
            searchResults.style.display = 'none';
            clearSearch.style.display = 'inline-block';
            selectedCameraId = cameraId;
        }
    });
    searchBtn.addEventListener('click', function() {
        var query = searchInput.value.toLowerCase().trim();
        if (query.length < 1) {
            alert('Masukkan nama atau zona kamera untuk pencarian.');
            return;
        }

        // Find exact match or first partial match
        var match = cameras.find(c => 
            c.nama.toLowerCase() === query
        ) || cameras.find(c => 
            c.nama.toLowerCase().includes(query) || 
            c.zona.toLowerCase().includes(query)
        );

        if (match) {
            // Reset zona filter to show all markers
            document.getElementById('zonaFilter').value = 'all';
            renderMarkers('all');
            
            var marker = leafletMarkers[match.id];
            if (marker) {
                // zoomToShowLayer akan otomatis memecah cluster dan menampilkan marker
                markerGroup.zoomToShowLayer(marker, function() {
                    marker.openPopup();
                    marker.setZIndexOffset(1000);
                });
                
                // Fallback flyTo jika zoomToShowLayer tidak cukup
                map.flyTo([parseFloat(match.lat), parseFloat(match.lng)], Math.max(map.getZoom(), 18), {
                    duration: 1.0
                });
            }
            
            clearSearch.style.display = 'block';
            searchResults.style.display = 'none';
            selectedCameraId = match.id;
        } else {
            alert('Kamera tidak ditemukan.');
        }
    });

    // Clear search
    clearSearch.addEventListener('click', function(e) {
        e.preventDefault();
        searchInput.value = '';
        searchResults.style.display = 'none';
        clearSearch.style.display = 'none';
        selectedCameraId = null;
        map.setView([-8.14960, 112.57390], 17);
    });

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    // Enter key to search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchBtn.click();
        }
    });

    // ─── Stream Modal ───
    var currentStreamCameraId = null;

    function openStreamForCamera(cameraId) {
        var cam = cameraMap[cameraId];
        if (!cam) return;

        currentStreamCameraId = cameraId;
        openStream(cam.stream_url, cam.nama);
    }

    function openStream(url, nama) {
        if (!url || url === '') {
            alert('Stream URL tidak tersedia untuk kamera ini.');
            return;
        }

        document.getElementById('streamTitle').textContent = nama;
        var streamBody = document.getElementById('streamBody');
        streamBody.innerHTML = ''; // Clear previous content

        // Tambahkan protocol jika belum ada
        if (!url.startsWith('http://') && !url.startsWith('https://') && !url.startsWith('rtsp://')) {
            url = 'http://' + url;
        }

        // Cek apakah HLS (.m3u8)
        if (url.includes('.m3u8')) {
            // HLS Player menggunakan hls.js
            var video = document.createElement('video');
            video.controls = true;
            video.autoplay = true;
            video.muted = true;
            video.style.width = '100%';
            video.style.height = '100%';

            // Prioritaskan HLS.js jika tersedia
            if (typeof Hls !== 'undefined' && Hls.isSupported()) {
                var hls = new Hls({
                    debug: true, // Enable debug untuk melihat logs
                    enableWorker: true,
                    lowLatencyMode: true,
                    backBufferLength: 90
                });
                
                hls.loadSource(url);
                hls.attachMedia(video);
                
                hls.on(Hls.Events.MANIFEST_PARSED, function() {
                    console.log('HLS.js: Manifest parsed, starting playback');
                    video.play().catch(e => console.error('Playback error:', e));
                });
                
                hls.on(Hls.Events.ERROR, function(event, data) {
                    console.error('HLS.js Error:', data);
                    if (data.fatal) {
                        switch(data.type) {
                            case Hls.ErrorTypes.NETWORK_ERROR:
                                console.log('Network error, trying to recover...');
                                hls.startLoad();
                                break;
                            case Hls.ErrorTypes.MEDIA_ERROR:
                                console.log('Media error, trying to recover...');
                                hls.recoverMediaError();
                                break;
                            default:
                                console.log('Fatal error, destroying HLS instance');
                                hls.destroy();
                                break;
                        }
                    }
                });
                
                streamBody.appendChild(video);
                
                // Simpan instance HLS untuk cleanup nanti
                window.currentHls = hls;
            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                // Fallback: Native HLS support (Safari)
                console.log('Using native HLS support');
                video.src = url;
                streamBody.appendChild(video);
            } else {
                // Browser tidak support HLS
                streamBody.innerHTML = `
                    <div class="stream-placeholder">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:64px;height:64px;margin-bottom:12px;opacity:.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <h4>HLS.js Tidak Tersedia</h4>
                        <p>Browser Anda tidak mendukung HLS streaming.<br>Silakan gunakan browser modern atau install ekstensi HLS.</p>
                    </div>
                `;
            }
        } else {
            // Fallback: iframe untuk embed URL atau tampilkan placeholder
            streamBody.innerHTML = `
                <div class="stream-placeholder">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:64px;height:64px;margin-bottom:12px;opacity:.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.069A1 1 0 0121 8.82V15.18a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <h4>Stream RTSP/HTTP</h4>
                    <p style="font-family:monospace;font-size:11px;color:#60a5fa;margin:8px 0;">${url}</p>
                    <p>Format stream memerlukan player eksternal atau backend konversi ke HLS/WebRTC.<br>Untuk stream RTSP, gunakan software seperti VLC atau backend streaming server.</p>
                </div>
            `;
        }

        document.getElementById('streamModal').classList.add('active');
    }

    function refreshStream() {
        if (currentStreamCameraId === null) return;

        var cam = cameraMap[currentStreamCameraId];
        if (!cam || !cam.stream_url) {
            alert('Stream URL tidak tersedia untuk kamera ini.');
            return;
        }

        if (window.currentHls) {
            window.currentHls.destroy();
            window.currentHls = null;
        }

        openStream(cam.stream_url, cam.nama);
    }

    function closeStream() {
        var streamBody = document.getElementById('streamBody');
        streamBody.innerHTML = `
            <div class="stream-placeholder">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:64px;height:64px;margin-bottom:12px;opacity:.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.069A1 1 0 0121 8.82V15.18a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <h4>Live Stream On-Demand</h4>
                <p>Stream aktif saat kamera dibuka operator.<br>Backend streaming (HLS/WebRTC) perlu dikonfigurasi.</p>
            </div>
        `;
        
        // Clean up HLS instance jika ada
        if (window.currentHls) {
            try {
                window.currentHls.destroy();
                window.currentHls = null;
            } catch (e) {
                console.log('HLS cleanup:', e);
            }
        }

        currentStreamCameraId = null;
        
        document.getElementById('streamModal').classList.remove('active');
    }
    document.getElementById('streamModal').addEventListener('click', function(e) {
        if (e.target === this) closeStream();
    });

    // ─── Alert Panel ───
    var alertCount = {};
    function showAlert(cameraId, cameraName, status) {
        var panel = document.getElementById('alertPanel');
        var id = 'alert-' + cameraId;

        // Remove existing alert for same camera
        var existing = document.getElementById(id);
        if (existing) existing.remove();

        var item = document.createElement('div');
        item.className = 'alert-item';
        item.id = id;
        item.innerHTML = `
            <div class="alert-dot ${status}"></div>
            <div class="alert-text">
                <div class="alert-title">${cameraName}</div>
                <div class="alert-sub">Status berubah ke <strong>${status}</strong> — ${new Date().toLocaleTimeString('id-ID')}</div>
            </div>
            <button class="alert-ack" onclick="acknowledgeAlert(${cameraId}, this)">ACK</button>
        `;
        panel.appendChild(item);

        // Auto-remove after 30s
        setTimeout(() => { if (item.parentNode) item.remove(); }, 30000);
    }

    function acknowledgeAlert(cameraId, btn) {
        fetch(`/alerts/${cameraId}/acknowledge`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        }).then(() => {
            btn.closest('.alert-item').remove();
        });
    }

    // ─── Real-time Status Updates via Echo/Reverb ───
    function updateCameraStatus(id, newStatus) {
        var cam = cameraMap[id];
        if (!cam) return;

        var oldStatus = cam.status;
        cam.status = newStatus;

        // Update marker icon
        var marker = leafletMarkers[id];
        if (marker) {
            marker.setIcon(makeIcon(newStatus));
            marker.options.cameraStatus = newStatus;
            marker.setPopupContent(makePopupHtml(cam));
            // Refresh cluster
            markerGroup.refreshClusters(marker);
        }

        // Update status counters
        updateCounters();

        // Trigger alert if bad status
        if (oldStatus !== newStatus && (newStatus === 'offline' || newStatus === 'gangguan')) {
            showAlert(id, cam.nama, newStatus);
        }
    }

    function updateCounters() {
        var online = 0, gangguan = 0, offline = 0;
        Object.values(cameraMap).forEach(c => {
            if (c.status === 'online') online++;
            else if (c.status === 'gangguan') gangguan++;
            else offline++;
        });
        document.getElementById('n-online').textContent = online;
        document.getElementById('n-gangguan').textContent = gangguan;
        document.getElementById('n-offline').textContent = offline;
    }

    // Connect to Laravel Echo (Reverb)
    if (typeof window.Echo !== 'undefined') {
        window.Echo.channel('camera-status')
            .listen('.camera.status.updated', function(e) {
                updateCameraStatus(e.id, e.status);
            });
    } else {
        // Fallback polling every 30s if Echo/Reverb not available
        setInterval(function() {
            fetch('/api/cameras/statuses')
                .then(r => r.json())
                .then(data => {
                    data.forEach(c => updateCameraStatus(c.id, c.status));
                })
                .catch(() => {});
        }, 30000);
    }
</script>
@livewireScripts
</body>
</html>
