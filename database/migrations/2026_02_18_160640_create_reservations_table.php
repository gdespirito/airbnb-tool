<?php

use App\Enums\ReservationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('airbnb_reservation_id')->nullable()->unique();
            $table->string('guest_name');
            $table->string('guest_phone')->nullable();
            $table->string('guest_email')->nullable();
            $table->unsignedSmallInteger('number_of_guests')->default(1);
            $table->date('check_in');
            $table->date('check_out');
            $table->string('status')->default(ReservationStatus::Confirmed->value);
            $table->text('notes')->nullable();
            $table->string('source')->default('manual');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
