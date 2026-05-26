<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prizes', function (Blueprint $table) {
            $table->unsignedInteger('initial_stock')
                ->default(0)
                ->after('stock')
                ->comment('Original stock at event start — used for auto daily-limit calc');
        });

        // Back-fill: initial_stock = current stock for all existing prizes
        DB::statement('UPDATE prizes SET initial_stock = stock WHERE initial_stock = 0');
    }

    public function down(): void
    {
        Schema::table('prizes', function (Blueprint $table) {
            $table->dropColumn('initial_stock');
        });
    }
};
