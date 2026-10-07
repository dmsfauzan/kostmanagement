<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('level')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['building_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floors');
    }
};
