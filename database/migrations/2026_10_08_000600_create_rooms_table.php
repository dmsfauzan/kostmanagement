<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->foreignId('floor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->nullable()->nullOnDelete();
            $table->string('number');
            $table->string('slug');
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedBigInteger('deposit')->default(0);
            $table->unsignedInteger('size_sqm')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('available')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
