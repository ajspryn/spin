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
        Schema::table('prizes', function (Blueprint $table) {
            $table->unsignedInteger('daily_limit')->nullable()->after('stock')->comment('Daily quota limit');
            $table->boolean('is_infinite')->default(false)->after('is_active')->comment('True if consolation prize with unlimited stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prizes', function (Blueprint $table) {
            $table->dropColumn(['daily_limit', 'is_infinite']);
        });
    }
};
