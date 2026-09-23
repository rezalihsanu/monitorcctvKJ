<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cameras', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->string('zona');
            $table->string('ip_address')->nullable();
            $table->string('stream_url')->nullable();
            $table->enum('status', ['online', 'offline', 'gangguan'])->default('online');
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cameras');
    }
};
