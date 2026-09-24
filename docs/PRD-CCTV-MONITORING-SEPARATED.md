# Product Requirements Document

## CCTV Monitoring Stadion Kanjuruhan

**Status:** Draft target architecture  
**Tanggal:** 24 September 2026  
**Target rilis:** MVP tahap pertama

## 1. Ringkasan Produk

Sistem CCTV Monitoring digunakan oleh operator dan administrator untuk memantau kondisi kamera CCTV pada area Stadion Kanjuruhan dan area perluasan monitoring di sekitar koordinat `-8.122813, 112.573182`.

Target pengembangan berikutnya adalah memisahkan aplikasi menjadi dua bagian:

1. **Backend:** Laravel API sebagai pusat autentikasi, data kamera, status kamera, alert, user, dan event real-time.
2. **Frontend:** aplikasi web terpisah untuk peta monitoring, live stream, alert, dan administrasi.

Backend menjadi satu-satunya sumber kebenaran data. Frontend tidak mengakses database secara langsung.

## 2. Tujuan Produk

- Menyediakan pemantauan kondisi CCTV secara terpusat.
- Menampilkan lokasi kamera pada peta dengan status yang mudah dibedakan.
- Memungkinkan operator membuka dan me-refresh live stream kamera.
- Mengirimkan perubahan status kamera secara real-time.
- Mencatat dan melacak alert kamera offline atau gangguan.
- Memberikan administrator kemampuan mengelola kamera dan user.
- Memisahkan siklus rilis frontend dan backend tanpa menduplikasi aturan bisnis.

## 3. Sasaran Pengguna

### Operator

- Melihat seluruh kamera yang berada dalam area monitoring.
- Mencari kamera berdasarkan nama, zona, atau IP address.
- Memfilter kamera berdasarkan zona.
- Melihat status kamera dan waktu pengecekan terakhir.
- Membuka live stream dan melakukan refresh kamera.
- Melihat, menerima, dan mengakui alert.

### Administrator

Memiliki seluruh kemampuan operator, ditambah:

- Menambah, mengubah, dan menghapus kamera.
- Mengubah nama, koordinat, zona, IP address, stream URL, dan status kamera.
- Menambah, mengubah, dan menghapus user.
- Mengatur role user sebagai `admin` atau `operator`.
- Mengelola konfigurasi koneksi kamera.

## 4. Ruang Lingkup MVP

### 4.1 Dashboard Monitoring

- Peta interaktif berbasis Leaflet.
- Marker kamera berbentuk icon kamera.
- Warna marker berdasarkan status:
  - Hijau: `online`
  - Kuning: `gangguan`
  - Merah: `offline`
- Marker clustering untuk kamera yang berdekatan.
- Counter total kamera berdasarkan status.
- Filter berdasarkan zona.
- Pencarian nama kamera, zona, atau IP address.
- Peta mencakup area Stadion Kanjuruhan dan area perluasan yang mencakup koordinat target.

### 4.2 Live Stream

- Membuka stream dari popup marker kamera.
- Dukungan utama untuk HLS `.m3u8`.
- Tombol refresh untuk menghentikan dan memuat ulang player kamera aktif.
- Tombol tutup stream dan pembersihan resource player.
- Status offline menonaktifkan akses stream.
- Error stream ditampilkan sebagai kondisi yang dapat dipahami operator.

> RTSP tidak diputar langsung oleh browser. Backend streaming atau gateway harus mengubah RTSP menjadi HLS atau WebRTC.

### 4.3 Status Kamera

- Backend menyimpan status kamera dan `last_checked_at`.
- Status diperiksa oleh worker atau scheduler.
- Perubahan menjadi `offline` atau `gangguan` menghasilkan alert.
- Frontend menerima update melalui WebSocket/Reverb.
- Polling fallback digunakan apabila koneksi real-time tidak tersedia.

### 4.4 Alert

- Membuat alert ketika status kamera berubah ke kondisi buruk.
- Menampilkan alert aktif pada dashboard.
- Menyimpan waktu kejadian, status, kamera, dan user yang menangani.
- Operator dapat melakukan ACK.
- Menyediakan halaman riwayat alert dengan pagination.

### 4.5 Autentikasi dan Otorisasi

- Login dan logout.
- Registrasi dan verifikasi email sesuai kebutuhan deployment.
- Reset password.
- Update profil dan password.
- Role-based access control.
- Endpoint administrasi hanya dapat diakses oleh role `admin`.
- API menggunakan token/session authentication yang dikontrol backend.

## 5. Target Arsitektur

```text
Frontend Web
  |-- REST API: kamera, user, alert, profile
  |-- WebSocket: perubahan status dan alert
  |-- HLS/WebRTC player: live stream
  |
Backend Laravel API
  |-- Authentication and authorization
  |-- Camera and alert domain logic
  |-- Scheduler/queue status checker
  |-- Broadcasting/Reverb
  |
Database
  |-- users
  |-- cameras
  |-- alert_logs
```

### 5.1 Target Backend

- Laravel API dalam repository/service backend.
- REST API berversi, misalnya `/api/v1`.
- Eloquent model dan migration untuk data kamera, user, dan alert.
- Form Request untuk validasi input.
- API Resource untuk format response yang konsisten.
- Policy atau middleware untuk pembatasan role.
- Laravel Reverb untuk event status kamera.
- Queue dan scheduler untuk pengecekan status kamera.
- Logging untuk kegagalan koneksi kamera dan kegagalan broadcast.
- Tidak mengirim password, secret, atau kredensial stream secara terbuka ke frontend.

### 5.2 Target Frontend

- Aplikasi web terpisah dari backend.
- Mengonsumsi REST API untuk seluruh data bisnis.
- Menggunakan WebSocket untuk update status tanpa reload halaman.
- Menyimpan token autentikasi secara aman sesuai strategi backend.
- Tidak memiliki aturan bisnis status kamera yang berbeda dari backend.
- Memiliki halaman monitoring, alert, administrasi kamera, administrasi user, login, dan profile.

## 6. Kontrak API Awal

Semua endpoint berikut berada di bawah prefix `/api/v1`.

| Method | Endpoint | Role | Tujuan |
|---|---|---|---|
| `POST` | `/auth/login` | Guest | Login user |
| `POST` | `/auth/logout` | Authenticated | Logout user |
| `GET` | `/me` | Authenticated | Data user aktif |
| `GET` | `/cameras` | Operator/Admin | Daftar kamera, search, filter zona/status |
| `GET` | `/cameras/{camera}` | Operator/Admin | Detail kamera |
| `GET` | `/cameras/statuses` | Operator/Admin | Status ringkas untuk polling fallback |
| `POST` | `/cameras` | Admin | Membuat kamera |
| `PATCH` | `/cameras/{camera}` | Admin | Mengubah data kamera |
| `DELETE` | `/cameras/{camera}` | Admin | Menghapus kamera |
| `GET` | `/alerts` | Operator/Admin | Riwayat dan filter alert |
| `POST` | `/alerts/{alert}/acknowledge` | Operator/Admin | Mengakui alert |
| `GET` | `/users` | Admin | Daftar user |
| `POST` | `/users` | Admin | Membuat user |
| `PATCH` | `/users/{user}` | Admin | Mengubah user |
| `DELETE` | `/users/{user}` | Admin | Menghapus user |

Contoh response kamera:

```json
{
  "id": 1,
  "name": "CCTV Tribun Utara 001",
  "zone": "Tribun Utara",
  "latitude": -8.1496,
  "longitude": 112.5739,
  "status": "online",
  "last_checked_at": "2026-09-24T10:15:00Z",
  "stream": {
    "type": "hls",
    "url": "https://stream.example.test/camera-1/index.m3u8"
  }
}
```

## 7. Event Real-Time

Backend menerbitkan event:

- `camera.status.updated`
- `alert.created`
- `alert.acknowledged`

Payload minimum `camera.status.updated`:

```json
{
  "camera_id": 1,
  "status": "offline",
  "last_checked_at": "2026-09-24T10:15:00Z"
}
```

Frontend harus memperbarui marker, counter, popup, dan panel alert berdasarkan event tersebut.

## 8. Aturan Bisnis Utama

- Status kamera hanya boleh bernilai `online`, `offline`, atau `gangguan`.
- Alert dibuat ketika terjadi perubahan menuju `offline` atau `gangguan`.
- ACK hanya berlaku pada alert aktif terbaru untuk kamera terkait.
- User tidak dapat menghapus akun sendiri.
- Kamera tanpa `stream_url` tetap dapat ditampilkan di peta, tetapi tidak dapat membuka stream.
- Kamera offline tidak dapat dibuka melalui tombol live stream.
- Koordinat harus berada pada rentang latitude `-90..90` dan longitude `-180..180`.
- Semua perubahan administrasi harus divalidasi di backend.

## 9. Kebutuhan Nonfungsional

- API menggunakan HTTPS pada environment staging dan production.
- Response API menggunakan format error yang konsisten.
- Endpoint list wajib mendukung pagination.
- Endpoint list kamera mendukung filter dan tidak mengembalikan data yang tidak diperlukan.
- Status kamera dapat diperbarui tanpa reload halaman.
- Frontend tetap menampilkan kondisi terakhir apabila WebSocket terputus.
- Aktivitas penting dicatat pada log backend.
- Kredensial database, token, dan secret stream disimpan melalui environment/secret manager.
- UI dapat digunakan pada desktop operator dan layar tablet.
- Backend harus dapat diuji tanpa menjalankan frontend.
- Frontend harus dapat diuji menggunakan mock API atau environment staging.

## 10. Di Luar Scope MVP

- Analitik video dan deteksi objek.
- Rekaman video dan playback histori.
- Transcoding RTSP internal.
- Mobile native Android/iOS.
- Multi-stadion dan multi-tenant.
- Notifikasi SMS, WhatsApp, atau email.
- Pengaturan polygon area melalui UI.
- Integrasi perangkat CCTV vendor tertentu.

## 11. Tahapan Implementasi

### Tahap 1: Kontrak dan fondasi backend

- Menetapkan format API dan event.
- Memisahkan controller web dari controller API.
- Menambahkan endpoint API kamera, alert, user, dan autentikasi.
- Menambahkan validasi, policy, resource, dan test endpoint.

### Tahap 2: Frontend terpisah

- Membuat shell aplikasi dan autentikasi.
- Memindahkan dashboard peta dari Blade ke frontend.
- Menghubungkan marker, search, filter, counter, dan popup ke API.
- Menghubungkan WebSocket dan polling fallback.

### Tahap 3: Stream dan alert

- Mengintegrasikan HLS/WebRTC player.
- Menambahkan refresh stream dan error state.
- Menampilkan alert real-time dan halaman riwayat.
- Menguji ACK dan konsistensi status.

### Tahap 4: Administrasi dan hardening

- Memindahkan CRUD kamera dan user ke frontend terpisah.
- Menambahkan audit log bila diperlukan.
- Menjalankan pengujian keamanan, beban, dan koneksi WebSocket.
- Menyiapkan deployment frontend dan backend secara independen.

## 12. Kriteria Penerimaan MVP

- Operator dapat login dan melihat peta kamera tanpa mengakses database secara langsung.
- Kamera yang berada di area Stadion Kanjuruhan dan area perluasan tampil pada peta.
- Marker menunjukkan status kamera yang benar.
- Operator dapat mencari kamera dan memfilter berdasarkan zona.
- Operator dapat membuka dan me-refresh stream HLS.
- Perubahan status backend terlihat di frontend tanpa reload halaman.
- Status offline atau gangguan menghasilkan alert yang dapat di-ACK.
- Admin dapat mengelola kamera dan user melalui frontend.
- Operator tidak dapat memanggil endpoint administrasi.
- Backend dan frontend dapat dijalankan, diuji, dan di-deploy secara terpisah.
- Kegagalan stream atau WebSocket tidak membuat seluruh halaman monitoring berhenti.

## 13. Indikator Keberhasilan

- Minimal 95% event perubahan status diterima frontend dalam waktu maksimal 5 detik pada environment normal.
- Peta monitoring dapat menampilkan seluruh kamera aktif tanpa reload manual.
- Tidak ada akses database langsung dari frontend.
- Seluruh endpoint administrasi memiliki pengujian otorisasi.
- Waktu pemuatan dashboard awal berada dalam target yang disepakati setelah jumlah kamera produksi diketahui.
