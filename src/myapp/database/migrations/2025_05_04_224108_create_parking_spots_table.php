<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_spots', function (Blueprint $table) {
            $table->id();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->boolean('available')->default(true);
            $table->number('referenceId')->nullable();
            $table->date('parkedSince')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_spots');
    }
};
