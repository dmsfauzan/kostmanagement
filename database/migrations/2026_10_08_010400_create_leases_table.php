<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedBigInteger('rent_amount')->default(0);
            $table->unsignedBigInteger('deposit_amount')->default(0);
            $table->string('billing_cycle')->default('monthly');
            $table->unsignedTinyInteger('due_day')->default(1);
            $table->string('late_fee_type')->default('none');
            $table->unsignedBigInteger('late_fee_value')->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('terminated_at')->nullable();
            $table->string('termination_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_id', 'status']);
            $table->index(['tenant_id', 'status']);
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leases');
    }
};
