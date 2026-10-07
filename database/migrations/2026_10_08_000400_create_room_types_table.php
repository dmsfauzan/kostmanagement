<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedBigInteger('default_price')->default(0);
            $table->unsignedBigInteger('default_deposit')->default(0);
            $table->unsignedInteger('size_sqm')->nullable();
            $table->unsignedInteger('capacity')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
