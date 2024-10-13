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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('item_instances', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->unsignedBigInteger('item_id');
            $table->json('data')->nullable();
            $table->timestamps();
            $table->foreign('item_id')->references('id')->on('items');
        });

        Schema::create('item_interactions', function (Blueprint $table) {
            $table->id();
            $table->string('class');
            $table->unsignedBigInteger('item_id');
            $table->json('data')->nullable();
            $table->timestamps();
            $table->foreign('item_id')->references('id')->on('items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_interactions');
        Schema::dropIfExists('item_instances');
        Schema::dropIfExists('items');
    }
};
