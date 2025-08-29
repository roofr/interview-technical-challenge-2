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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_spot_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('vehicle_type', ['motorcycle', 'car', 'van']);
            $table->string('license_plate')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            $table->timestamp('parked_at')->nullable();
            $table->timestamp('unparked_at')->nullable();
            $table->json('ai_detection_data')->nullable(); // Store AI camera detection metadata
            $table->timestamps();

            $table->index(['vehicle_type', 'parked_at']);
            $table->index(['parking_spot_id', 'parked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
