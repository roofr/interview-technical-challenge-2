<?php

use App\Enums\SpotType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_spots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_section_id')->constrained('parking_sections')->cascadeOnDelete();
            $table->unsignedSmallInteger('number'); // e.g. 15 — used for consecutiveness check
            $table->enum('type', array_column(SpotType::cases(), 'value'));
            $table->boolean('is_occupied')->default(false);
            $table->timestamps();

            $table->unique(['parking_section_id', 'number']);
            $table->index(['parking_section_id', 'type', 'is_occupied']); // availability queries
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_spots');
    }
};
