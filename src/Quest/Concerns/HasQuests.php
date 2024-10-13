<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Concerns;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PbbgEngine\Quest\Models\QuestInstance;

/**
 * @mixin Model
 */
trait HasQuests
{
    /**
     * Get quest instances that belong to this model.
     *
     * @return HasMany<QuestInstance>
     */
    public function quests(): HasMany
    {
        return $this->hasMany(QuestInstance::class, 'model_id', $this->primaryKey)
            ->where('model_type', self::class)
            ->with('quest');
    }

    /**
     * Performs quest progression on uncompleted quest instances
     * that have the given task as an objective on the active stage.
     *
     * @param string $task
     * @param int $times
     * @throws Exception
     */
    public function progress(string $task, int $times = 1): void
    {
        if ($this->relationLoaded('quests')) {
            /** @var QuestInstance[] $instances */
            $instances = $this->quests->whereNull('completed_at');
        } else {
            /** @var QuestInstance[] $instances */
            $instances = $this->quests()->whereNull('completed_at')->get();
        }
        foreach ($instances as $instance) {
            $quest = $instance->quest;
            if (!$quest) {
                throw new Exception("quest not found");
            }
            $stage = $quest->stages()->where('id', $instance->current_quest_stage_id)->first();
            if (!$stage) {
                throw new Exception("stage not found");
            }
            $objective = $stage->objectives()->where('task', $task)->first();
            if (!$objective) {
                continue;
            }
            $instance->progress($objective, $times);
        }
    }
}
