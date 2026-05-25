<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_lot_id')->constrained()->cascadeOnDelete();
            $table->string('name', 10); // e.g. "A", "B", "C"
            $table->timestamps();

            $table->unique(['parking_lot_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_sections');
    }
};
