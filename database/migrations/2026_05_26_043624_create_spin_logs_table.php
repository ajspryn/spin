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
        Schema::create('spin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prize_id')->constrained()->cascadeOnDelete();
            $table->string('claim_code', 20)->unique();
            $table->boolean('is_claimed')->default(false);
            $table->timestamps();

            $table->index(['visitor_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spin_logs');
    }
};
