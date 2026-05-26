<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_settings', function (Blueprint $table) {
            $table->id();
            $table->string('event_name')->default('Lucky Spin Exhibition');
            $table->date('start_date')->nullable()->comment('First day of the event');
            $table->unsignedSmallInteger('total_days')->default(1)->comment('Total number of event days');
            $table->timestamps();
        });

        // Seed a single default row so the app always has settings to read
        DB::table('event_settings')->insert([
            'event_name' => 'Lucky Spin Exhibition',
            'start_date' => now()->toDateString(),
            'total_days' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('event_settings');
    }
};
