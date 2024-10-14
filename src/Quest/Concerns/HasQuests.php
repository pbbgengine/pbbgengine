<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Concerns;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PbbgEngine\Quest\Models\QuestInstance;
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
     * Performs quest progression on uncompleted quest instances
     * that have the given task as an objective on the active stage.
     * Sets the objective times performed to the given value.
     *
     * @throws Exception
     */
    public function progressTo(string $task, int $value): void
    {
        $this->progress($task, $value, false);
    }

    /**
     * Performs quest progression on uncompleted quest instances
     * that have the given task as an objective on the active stage.
     * Increments the objective value by the amount of times performed.
     *
     * @throws Exception
     */
    public function progress(string $task, int $times = 1, bool $increment = true): void
    {
        $models = array_merge([$this], $this->getRelatedQuestModels());
        $models = array_filter($models, fn ($model) => is_object($model));
        foreach ($models as $model) {
            $traits = class_uses($model::class);
            if (!is_array($traits) || !in_array(HasQuests::class, $traits)) {
                throw new Exception($model::class . " does not support quests");
            }
            if (!is_subclass_of($model, Model::class)) {
                throw new Exception($model::class . " is not a model");
            }
            $instances = $model->relationLoaded('quests')
                ? $model->quests->whereNull('completed_at')
                : $model->quests()->whereNull('completed_at')->get();

            $service = new QuestProgressionService();

            foreach ($instances as $instance) {
                $service->progress($instance, $task, $times, $increment);
            }
        }
    }
}
