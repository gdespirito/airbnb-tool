<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedSmallInteger('number_of_adults')->nullable()->after('number_of_guests');
            $table->unsignedSmallInteger('number_of_children')->nullable()->after('number_of_adults');
            $table->unsignedSmallInteger('number_of_infants')->nullable()->after('number_of_children');
            $table->unsignedSmallInteger('number_of_pets')->nullable()->after('number_of_infants');
            $table->string('channel_type')->nullable()->after('source');
            $table->datetime('booked_at')->nullable()->after('check_out');
            $table->datetime('cancelled_at')->nullable()->after('booked_at');
            $table->decimal('total_price', 10, 2)->nullable()->after('cancelled_at');
            $table->string('currency', 3)->nullable()->after('total_price');
            $table->string('check_in_time')->nullable()->after('currency');
            $table->string('check_out_time')->nullable()->after('check_in_time');
            $table->string('lock_code')->nullable()->after('check_out_time');
            $table->string('hostex_conversation_id')->nullable()->after('lock_code');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'number_of_adults',
                'number_of_children',
                'number_of_infants',
                'number_of_pets',
                'channel_type',
                'booked_at',
                'cancelled_at',
                'total_price',
                'currency',
                'check_in_time',
                'check_out_time',
                'lock_code',
                'hostex_conversation_id',
            ]);
        });
    }
};
