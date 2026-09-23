<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $camera->id ? 'Edit' : 'Tambah' }} Kamera — CCTV Kanjuruhan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0b0f1a; color: #e2e8f0; margin: 0; display: flex; align-items: flex-start; justify-content: center; min-height: 100vh; padding: 40px 24px; }
        .form-card { background: #111827; border: 1px solid rgba(255,255,255,.08); border-radius: 20px; padding: 36px 40px; width: 100%; max-width: 560px; }
        h2 { font-size: 20px; font-weight: 700; color: #f1f5f9; margin: 0 0 6px; }
        .subtitle { font-size: 13px; color: #64748b; margin: 0 0 32px; }
        .field { margin-bottom: 20px; }
        label { display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 7px; text-transform: uppercase; letter-spacing: .4px; }
        input, select { width: 100%; padding: 10px 14px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); border-radius: 10px; color: #e2e8f0; font-size: 14px; outline: none; transition: border-color .15s; font-family: inherit; }
        input:focus, select:focus { border-color: #3b82f6; background: rgba(59,130,246,.05); }
        select option { background: #1e293b; }
        .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .error { font-size: 11px; color: #f87171; margin-top: 5px; }
        .form-actions { display: flex; gap: 12px; margin-top: 28px; }
        .btn { display: inline-flex; align-items: center; padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 600; border: none; cursor: pointer; text-decoration: none; transition: all .15s; }
        .btn-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; }
        .btn-primary:hover { background: linear-gradient(135deg, #60a5fa, #3b82f6); transform: translateY(-1px); }
        .btn-cancel  { background: rgba(255,255,255,.05); color: #94a3b8; border: 1px solid rgba(255,255,255,.1); }
        .btn-cancel:hover { background: rgba(255,255,255,.08); }
        
        /* Map Picker */
        .map-picker-section { margin-bottom: 20px; }
        .map-picker-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .map-picker-header label { margin: 0; }
        .btn-toggle-map { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; background: rgba(59,130,246,.15); color: #60a5fa; border: 1px solid rgba(59,130,246,.3); cursor: pointer; transition: all .15s; }
        .btn-toggle-map:hover { background: rgba(59,130,246,.25); }
        #mapPicker { height: 400px; border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,.1); display: none; margin-top: 10px; }
        #mapPicker.active { display: block; }
        .map-hint { font-size: 11px; color: #64748b; margin-top: 6px; font-style: italic; }
        .leaflet-container { background: #0b0f1a; }
    </style>
</head>
<body>
<div class="form-card">
    <h2>{{ $camera->id ? 'Edit Kamera' : 'Tambah Kamera Baru' }}</h2>
    <p class="subtitle">{{ $camera->id ? 'Perbarui data kamera CCTV' : 'Tambahkan titik kamera CCTV baru ke sistem' }}</p>

    <form method="POST" action="{{ $camera->id ? route('admin.cameras.update', $camera) : route('admin.cameras.store') }}">
        @csrf
        @if($camera->id) @method('PUT') @endif

        <div class="field">
            <label>Nama Kamera</label>
            <input type="text" name="nama" value="{{ old('nama', $camera->nama) }}" placeholder="Contoh: CCTV Tribun Utara 01" required>
            @error('nama') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Zona</label>
            <select name="zona" required>
                @foreach(['Tribun Utara','Tribun Selatan','Tribun Timur','Tribun Barat','Lapangan','Gate Utama','Parkir'] as $z)
                <option value="{{ $z }}" {{ old('zona', $camera->zona) === $z ? 'selected' : '' }}>{{ $z }}</option>
                @endforeach
            </select>
            @error('zona') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field-row">
            <div class="field">
                <label>Latitude</label>
                <input type="number" name="lat" step="any" value="{{ old('lat', $camera->lat) }}" placeholder="-8.14960" required>
                @error('lat') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>Longitude</label>
                <input type="number" name="lng" step="any" value="{{ old('lng', $camera->lng) }}" placeholder="112.57390" required>
                @error('lng') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="field map-picker-section">
            <div class="map-picker-header">
                <label>📍 Pilih Titik di Peta</label>
                <button type="button" class="btn-toggle-map" id="toggleMapBtn">Buka Peta</button>
            </div>
            <div id="mapPicker"></div>
            <p class="map-hint">Klik di peta atau drag marker untuk menentukan koordinat CCTV</p>
            @error('lat') <div class="error" style="margin-top: 5px;">{{ $message }}</div> @enderror
            @error('lng') <div class="error" style="margin-top: 5px;">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>IP Address</label>
            <input type="text" name="ip_address" value="{{ old('ip_address', $camera->ip_address) }}" placeholder="192.168.1.10">
            @error('ip_address') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Stream URL (RTSP/HLS)</label>
            <input type="text" name="stream_url" value="{{ old('stream_url', $camera->stream_url) }}" placeholder="rtsp://192.168.1.10:554/stream">
            @error('stream_url') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Status</label>
            <select name="status" required>
                <option value="online"   {{ old('status', $camera->status) === 'online'   ? 'selected' : '' }}>Online</option>
                <option value="gangguan" {{ old('status', $camera->status) === 'gangguan' ? 'selected' : '' }}>Gangguan</option>
                <option value="offline"  {{ old('status', $camera->status) === 'offline'  ? 'selected' : '' }}>Offline</option>
            </select>
            @error('status') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ $camera->id ? 'Simpan Perubahan' : 'Tambah Kamera' }}</button>
            <a href="{{ route('admin.cameras.index') }}" class="btn btn-cancel">Batal</a>
        </div>
    </form>
</div>

<script>
(function () {
    const DEFAULT_LAT = -8.14960;
    const DEFAULT_LNG = 112.57390;
    const DEFAULT_ZOOM = 17;

    const latInput   = document.querySelector('input[name="lat"]');
    const lngInput   = document.querySelector('input[name="lng"]');
    const mapEl      = document.getElementById('mapPicker');
    const toggleBtn  = document.getElementById('toggleMapBtn');

    let map    = null;
    let marker = null;
    let initialized = false;

    function parseCoord(value, fallback) {
        const n = parseFloat(value);
        return isNaN(n) ? fallback : n;
    }

    function updateInputs(lat, lng) {
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
    }

    function initMap() {
        if (initialized) return;

        const lat = parseCoord(latInput.value, DEFAULT_LAT);
        const lng = parseCoord(lngInput.value, DEFAULT_LNG);

        map = L.map('mapPicker').setView([lat, lng], DEFAULT_ZOOM);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        marker = L.marker([lat, lng], { draggable: true }).addTo(map);

        marker.on('dragend', function (e) {
            const pos = e.target.getLatLng();
            updateInputs(pos.lat, pos.lng);
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            updateInputs(e.latlng.lat, e.latlng.lng);
        });

        initialized = true;

        // Fix rendering issue when map is shown after being hidden
        setTimeout(function () { map.invalidateSize(); }, 150);
    }

    // Sinkronisasi ketika user mengetik manual di input lat/lng
    function syncMarkerFromInputs() {
        if (!initialized) return;
        const lat = parseCoord(latInput.value, DEFAULT_LAT);
        const lng = parseCoord(lngInput.value, DEFAULT_LNG);
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng]);
    }

    latInput.addEventListener('change', syncMarkerFromInputs);
    lngInput.addEventListener('change', syncMarkerFromInputs);

    toggleBtn.addEventListener('click', function () {
        const isActive = mapEl.classList.toggle('active');
        toggleBtn.textContent = isActive ? 'Tutup Peta' : 'Buka Peta';

        if (isActive) {
            initMap();
        }
    });
})();
</script>
</body>
</html>
