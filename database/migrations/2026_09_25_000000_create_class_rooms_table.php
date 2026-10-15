<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150)->index();
            $table->string('education_level', 20)->index();
            $table->string('grade_level', 20);
            $table->unsignedInteger('capacity');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();

            $table->index(['education_level', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_rooms');
    }
};