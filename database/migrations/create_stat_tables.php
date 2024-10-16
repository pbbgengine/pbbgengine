<?php

declare(strict_types=1);

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
        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('model_type');
            $table->string('class')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('stat_instances', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stat_instances');
        Schema::dropIfExists('stats');
    }
};
