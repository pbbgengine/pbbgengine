<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Concerns;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PbbgEngine\Quest\Exceptions\QuestsUnsupported;
use PbbgEngine\Quest\Models\QuestInstance;
use PbbgEngine\Quest\Models\QuestObjective;
use PbbgEngine\Quest\QuestProgressionService;

/**
 * @mixin Model
 */
trait HasQuests
{
    /**
     * Get related models that you also want to track quests for
     * when the model progresses a quest objective.
     *
     * @return array<int, self|null>
     */
    protected function getRelatedQuestModels(): array
    {
        return [];
    }

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
     * Sets the objective times performed to the given value.
     *
     * @throws Exception
     */
    public function progressTo(string $task, int $value): void
    {
        $this->progressQuests($task, $value, function(QuestInstance $instance, QuestObjective $objective, int $value): void
        {
            $progress = min($value, $objective->times_required);
            $instance->progress->put($objective->id, $progress);
            $instance->save();
        });
    }

    /**
     * Increments the objective value by the amount of times performed.
     *
     * @throws Exception
     */
    public function progress(string $task, int $times = 1): void
    {
        $this->progressQuests($task, $times, function(QuestInstance $instance, QuestObjective $objective, int $times): void
        {
            $progress = $instance->progress->get($objective->id, 0) + $times;
            $progress = min($progress, $objective->times_required);
            $instance->progress->put($objective->id, $progress);
            $instance->save();
        });
    }

    /**
     * Performs quest progression on uncompleted quest instances
     * that have the given task as an objective on the active stage.
     *
     * @throws Exception
     */
    private function progressQuests(string $task, int $times, callable $updateProgress): void
    {
        $models = array_merge([$this], $this->getRelatedQuestModels());
        $models = array_filter($models, fn ($model) => is_object($model));
        $service = new QuestProgressionService();
        foreach ($models as $model) {
            $traits = class_uses($model::class);
            if (!is_array($traits) || !in_array(HasQuests::class, $traits)) {
                throw new QuestsUnsupported($model);
            }
            if (!is_subclass_of($model, Model::class)) {
                throw new QuestsUnsupported($model);
            }
            $instances = $model->relationLoaded('quests')
                ? $model->quests->whereNull('completed_at')
                : $model->quests()->whereNull('completed_at')->get();

            foreach ($instances as $instance) {
                $service->progress($instance, $task, $times, $updateProgress);
            }
        }
    }
}
