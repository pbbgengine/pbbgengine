<?php

declare(strict_types=1);

namespace PbbgEngine\Tests\Quest;

use PbbgEngine\Quest\Models\Quest;
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
}
