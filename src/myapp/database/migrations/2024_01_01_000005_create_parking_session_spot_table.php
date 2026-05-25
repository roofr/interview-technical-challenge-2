<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_session_spot', function (Blueprint $table) {
            $table->foreignId('parking_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parking_spot_id')->constrained()->cascadeOnDelete();
            $table->primary(['parking_session_id', 'parking_spot_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_session_spot');
    }
};
