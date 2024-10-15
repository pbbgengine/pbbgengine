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
        Schema::create('quests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('initial_quest_stage_id')->nullable();
            $table->timestamps();
        });

        Schema::create('quest_instances', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->unsignedBigInteger('quest_id');
            $table->unsignedBigInteger('current_quest_stage_id');
            $table->json('progress')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->foreign('quest_id')->references('id')->on('quests');
        });

        Schema::create('quest_stages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quest_id');
            $table->string('name');
            $table->timestamps();
            $table->foreign('quest_id')->references('id')->on('quests');
        });

        Schema::create('quest_objectives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quest_stage_id');
            $table->string('name');
            $table->string('task');
            $table->unsignedInteger('times_required')->default(1);
            $table->timestamps();
            $table->foreign('quest_stage_id')->references('id')->on('quest_stages');
        });

        Schema::create('quest_transitions', function (Blueprint $table) {
            $table->id();
            $table->morphs('triggerable'); // Quest, QuestStage, QuestObjective
            $table->morphs('actionable'); // Quest, QuestStage
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quest_transitions');
        Schema::dropIfExists('quest_objectives');
        Schema::dropIfExists('quest_instances');
        Schema::dropIfExists('quests');
    }
};
