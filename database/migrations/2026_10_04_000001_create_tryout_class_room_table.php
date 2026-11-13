<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tryout_class_room', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tryout_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_room_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['tryout_id', 'class_room_id'], 'tryout_class_room_tryout_class_unique');
            $table->index('class_room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tryout_class_room');
    }
};