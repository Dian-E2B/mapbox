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
        Schema::create('areas', function (Blueprint $table) {
        $table->id();
        $table->string('polygon_code')->unique();
        $table->json('coordinates');
        $table->double('area');
        $table->double('center_lng');
        $table->double('center_lat');
        $table->integer('isSprinkled')->nullable();
        $table->integer('isPlantable')->nullable();
        $table->integer('plantState')->nullable(); //1= small, 2 = growing, 3 = fully grown
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polygon');
    }
};
