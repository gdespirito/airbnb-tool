<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_notes', function (Blueprint $table) {
            $table->dropColumn('response');
            $table->foreignId('parent_id')->nullable()->after('reservation_id')->constrained('reservation_notes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reservation_notes', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
            $table->text('response')->nullable()->after('content');
        });
    }
};
