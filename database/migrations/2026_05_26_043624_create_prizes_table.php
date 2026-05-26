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
        Schema::create('prizes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('probability')->default(10)->comment('Weight 1-100 for weighted random');
            $table->unsignedInteger('stock')->default(0)->comment('Remaining prize stock');
            $table->string('bg_color', 7)->default('#6366F1')->comment('Hex color for wheel segment');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prizes');
    }
};
