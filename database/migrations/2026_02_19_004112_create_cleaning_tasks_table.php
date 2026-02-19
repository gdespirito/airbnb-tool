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
        Schema::create('cleaning_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->string('cleaning_type')->default('checkout');
            $table->unsignedInteger('cleaning_fee')->nullable();
            $table->date('scheduled_date');
            $table->string('assigned_to')->nullable();
            $table->string('assigned_phone')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_tasks');
    }
};
