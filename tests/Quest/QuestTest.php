<?php

declare(strict_types=1);

namespace PbbgEngine\Tests\Quest;

use PbbgEngine\Quest\Models\Quest;
use PbbgEngine\Quest\Models\QuestObjective;
use PbbgEngine\Quest\Models\QuestStage;
use PbbgEngine\Tests\TestCase;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

class QuestTest extends TestCase
{
    private User $user;

    private function createQuest(): Quest
    {
        $quest = Quest::create(['name' => 'Test quest']);
        $stage = $quest->stages()->create(['name' => 'Stage 1']);
        $quest->initial_quest_stage_id = $stage->id;
        $quest->save();

        $stage->objectives()->create(['name' => 'Stage 1, Objective 1', 'task' => 'test:123', 'times_required' => 3]);
        $stage->objectives()->create(['name' => 'Stage 1, Objective 2', 'task' => 'hello']);

        $stage2 = $quest->stages()->create(['name' => 'Stage 2']);
        $stage2->objectives()->create(['name' => 'Stage 2, Objective 1', 'task' => 'hello', 'times_required' => 5]);

        $stage->transitions()->create([
            'triggerable_type' => QuestStage::class,
            'actionable_type' => QuestStage::class,
            'actionable_id' => $stage2->id,
        ]);

        $stage2->transitions()->create([
            'triggerable_type' => QuestStage::class,
            'actionable_type' => Quest::class,
            'actionable_id' => $quest->id,
        ]);

        return $quest;
    }

    public function setUp(): void
    {
        parent::setUp();

        $quest = $this->createQuest();

        $this->user = UserFactory::new()->createOne();
        $this->user->quests()->create([
            'model_type' => $this->user::class,
            'quest_id' => $quest->id,
            'current_quest_stage_id' => $quest->initial_quest_stage_id,
            'progress' => [],
        ]);
    }

    public function testModelHasQuestInstance(): void
    {
        $query = $this->user->quests();
        $this->assertTrue($query->exists());
        $instance = $query->first();
        $this->assertNotNull($instance);
    }

    public function testQuestExists(): void
    {
        $quest = Quest::find(1);
        $this->assertInstanceOf(Quest::class, $quest);
        $this->assertNotNull($quest->stages);
        $this->assertEquals('Test quest', $quest->name);
        $this->assertCount(2, $quest->stages);
        $stage = $quest->stages->shift();
        $this->assertNotNull($stage);
        $this->assertEquals('Stage 1', $stage->name);
        $this->assertCount(2, $stage->objectives);
        $this->assertNotNull($stage->transitions);
        $this->assertCount(1, $stage->transitions);
        $objective = $stage->objectives->shift();
        $this->assertNotNull($objective);
        $this->assertEquals('Stage 1, Objective 1', $objective->name);
        $objective = $stage->objectives->shift();
        $this->assertNotNull($objective);
        $this->assertEquals('Stage 1, Objective 2', $objective->name);
        $stage = $quest->stages->shift();
        $this->assertNotNull($stage);
        $this->assertEquals('Stage 2', $stage->name);
        $this->assertCount(1, $stage->objectives);
        $objective = $stage->objectives->shift();
        $this->assertNotNull($objective);
        $this->assertEquals('Stage 2, Objective 1', $objective->name);
    }

    public function testQuestProgression(): void
    {
        $instance = $this->user->quests->first();
        $this->assertNotNull($instance);
        $this->assertEquals([], $instance->progress->toArray());
        $this->user->progress('test:123');
        $this->assertEquals([1 => 1], $instance->progress->toArray());
        $this->user->progress('test:123', 100);
        $this->assertEquals([1 => 3], $instance->progress->toArray());
        $this->user->progress('hello');
        $this->assertEquals([1 => 3, 2 => 1], $instance->progress->toArray());
        // moved to the next stage by quest stage transition 1
        $this->assertEquals(2, $instance->current_quest_stage_id);
        $this->user->progress('hello', 4);
        $this->assertEquals([1 => 3, 2 => 1, 3 => 4], $instance->progress->toArray());
        $this->user->progress('hello');
        $this->assertEquals([1 => 3, 2 => 1, 3 => 5], $instance->progress->toArray());
        // completed by the quest stage transition 2
        $this->assertNotNull($instance->completed_at);
    }

    public function testQuestTransitions(): void
    {
        $quest = Quest::create(['name' => 'Test transitions']);
        $stage = $quest->stages()->create(['name' => 'Choose a side']);
        $quest->initial_quest_stage_id = $stage->id;
        $quest->save();

        $soloQuest = Quest::create(['name' => 'Solo quest']);
        $soloQuestStage = $soloQuest->stages()->create(['name' => 'Solo quest stage']);
        $soloQuest->initial_quest_stage_id = $soloQuestStage->id;
        $soloQuest->save();

        $redObjective = $stage->objectives()->create(['name' => 'Join the red team', 'task' => 'join_team:red']);
        $blueObjective = $stage->objectives()->create(['name' => 'Join the blue team', 'task' => 'join_team:blue']);
        $soloObjective = $stage->objectives()->create(['name' => 'Go solo', 'task' => 'go_solo']);

        $redStage = $quest->stages()->create(['name' => 'Red stage']);
        $defeatBlueObjective = $redStage->objectives()->create(['name' => 'Defeat the blue team', 'task' => 'defeat:blue', 'times_required' => 5]);

        $blueStage = $quest->stages()->create(['name' => 'Blue stage']);
        $defeatRedObjective = $blueStage->objectives()->create(['name' => 'Defeat the red team', 'task' => 'defeat:red', 'times_required' => 5]);

        $redObjective->transitions()->create([
            'triggerable_type' => QuestObjective::class,
            'actionable_type' => QuestStage::class,
            'actionable_id' => $redStage->id,
        ]);

        $blueObjective->transitions()->create([
            'triggerable_type' => QuestObjective::class,
            'actionable_type' => QuestStage::class,
            'actionable_id' => $blueStage->id,
        ]);

        $soloObjective->transitions()->create([
            'triggerable_type' => QuestObjective::class,
            'actionable_type' => Quest::class,
            'actionable_id' => $quest->id,
        ]);

        $soloObjective->transitions()->create([
            'triggerable_type' => QuestObjective::class,
            'actionable_type' => Quest::class,
            'actionable_id' => $soloQuest->id,
        ]);

        $redStage->transitions()->create([
            'triggerable_type' => QuestStage::class,
            'actionable_type' => Quest::class,
            'actionable_id' => $quest->id,
        ]);

        $blueStage->transitions()->create([
            'triggerable_type' => QuestStage::class,
            'actionable_type' => Quest::class,
            'actionable_id' => $quest->id,
        ]);

        $this->user->quests()->create([
            'model_type' => $this->user::class,
            'quest_id' => $quest->id,
            'current_quest_stage_id' => $quest->initial_quest_stage_id,
            'progress' => [],
        ]);

        $instance = $this->user->quests->where('id', $quest->id)->first();

        $this->user->progress('join_team:red');

        $this->assertEquals([$redObjective->id => 1], $instance->progress->toArray());
        $this->assertEquals($redStage->id, $instance->current_quest_stage_id);

        $this->user->progress('defeat:blue', 5);
        $this->assertEquals([$redObjective->id => 1, $defeatBlueObjective->id => 5], $instance->progress->toArray());
        $this->assertNotNull($instance->completed_at);

        $instance->completed_at = null;
        $instance->progress = [];
        $instance->current_quest_stage_id = $quest->initial_quest_stage_id;
        $instance->save();

        $this->user->progress('join_team:blue');

        $this->assertEquals([$blueObjective->id => 1], $instance->progress->toArray());
        $this->assertEquals($blueStage->id, $instance->current_quest_stage_id);

        $this->user->progress('defeat:red', 2);
        $this->assertEquals([$blueObjective->id => 1, $defeatRedObjective->id => 2], $instance->progress->toArray());
        $this->assertNull($instance->completed_at);

        $this->user->progress('defeat:red', 6);
        $this->assertEquals([$blueObjective->id => 1, $defeatRedObjective->id => 5], $instance->progress->toArray());
        $this->assertNotNull($instance->completed_at);

        $instance->completed_at = null;
        $instance->progress = [];
        $instance->current_quest_stage_id = $quest->initial_quest_stage_id;
        $instance->save();

        $this->user->progress('go_solo');
        $this->assertEquals([$soloObjective->id => 1], $instance->progress->toArray());
        $this->assertNotNull($instance->completed_at);
        $this->assertTrue($this->user->quests()->where('quest_id', $soloQuest->id)->exists());
    }
}
