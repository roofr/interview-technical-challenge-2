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
        Schema::create('parking_spots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_lot_id')->constrained()->onDelete('cascade');
            $table->string('spot_number');
            $table->enum('spot_type', ['regular', 'motorcycle', 'van'])->default('regular');
            $table->enum('status', ['available', 'occupied', 'reserved', 'out_of_order'])->default('available');
            $table->integer('size_units')->default(1); // 1 for regular/motorcycle, 3 for van
            $table->string('floor_level')->nullable();
            $table->string('section')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['parking_lot_id', 'spot_number']);
            $table->index(['parking_lot_id', 'status']);
            $table->index(['spot_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_spots');
    }
};
