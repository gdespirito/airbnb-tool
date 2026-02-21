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
        Schema::table('reservation_notes', function (Blueprint $table) {
            $table->string('from_agent')->nullable();
            $table->boolean('needs_response')->default(false);
            $table->timestamp('responded_at')->nullable();
            $table->boolean('response_notified')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('reservation_notes', function (Blueprint $table) {
            $table->dropColumn(['from_agent', 'needs_response', 'responded_at', 'response_notified']);
        });
    }
};
