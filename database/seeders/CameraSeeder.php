<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CameraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zonas = ['Tribun Utara', 'Tribun Selatan', 'Tribun Timur', 'Tribun Barat', 'Lapangan', 'Gate Utama', 'Parkir'];
        
        // Kanjuruhan Stadium center (revised)
        $centerLat = -8.14960;
        $centerLng = 112.57390;
        $radius = 0.0009; // approx 90m - hanya di dalam area stadion

        // Hanya 30 kamera, tersebar di dalam stadion
        for ($i = 1; $i <= 30; $i++) {
            $zona = $zonas[array_rand($zonas)];
            
            // Random offset for dummy points (dalam radius kecil agar di dalam stadion)
            $lat = $centerLat + (rand(-100, 100) / 100) * $radius;
            $lng = $centerLng + (rand(-100, 100) / 100) * $radius;

            // Random status
            $rand = rand(1, 100);
            $status = 'online';
            if ($rand > 95) $status = 'offline';
            elseif ($rand > 85) $status = 'gangguan';

            \App\Models\Camera::create([
                'nama' => "CCTV - {$zona} - " . str_pad($i, 3, '0', STR_PAD_LEFT),
                'lat' => $lat,
                'lng' => $lng,
                'zona' => $zona,
                'ip_address' => "192.168.1." . rand(2, 200),
                'stream_url' => "http://test-server.local/live/{$i}.m3u8",
                'status' => $status,
                'last_checked_at' => now(),
            ]);
        }
    }
}
