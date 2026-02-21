<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['cleaning_contact_name', 'cleaning_contact_phone']);
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('cleaning_contact_name')->nullable()->after('checkout_time');
            $table->string('cleaning_contact_phone')->nullable()->after('cleaning_contact_name');
        });
    }
};
