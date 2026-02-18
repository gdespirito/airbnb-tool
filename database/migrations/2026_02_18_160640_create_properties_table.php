<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('airbnb_url')->nullable();
            $table->string('airbnb_listing_id')->nullable();
            $table->string('ical_url')->nullable();
            $table->string('location');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('checkin_time', 5)->default('15:00');
            $table->string('checkout_time', 5)->default('12:00');
            $table->string('cleaning_contact_name')->nullable();
            $table->string('cleaning_contact_phone')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
