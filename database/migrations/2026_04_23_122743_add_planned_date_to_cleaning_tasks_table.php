<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cleaning_tasks', function (Blueprint $table): void {
            $table->date('planned_date')->nullable()->after('scheduled_date');
        });
    }

    public function down(): void
    {
        Schema::table('cleaning_tasks', function (Blueprint $table): void {
            $table->dropColumn('planned_date');
        });
    }
};
