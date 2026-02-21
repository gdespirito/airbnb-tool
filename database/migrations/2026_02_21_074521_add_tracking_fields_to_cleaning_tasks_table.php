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
        Schema::table('cleaning_tasks', function (Blueprint $table) {
            $table->string('estimated_arrival_time')->nullable()->after('notes');
            $table->datetime('started_at')->nullable()->after('estimated_arrival_time');
            $table->datetime('completed_at')->nullable()->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('cleaning_tasks', function (Blueprint $table) {
            $table->dropColumn(['estimated_arrival_time', 'started_at', 'completed_at']);
        });
    }
};
