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
     * @throws Exception
     */
    public function progress(string $task, int $times = 1): void
    {
        $instances = $this->relationLoaded('quests')
            ? $this->quests->whereNull('completed_at')
            : $this->quests()->whereNull('completed_at')->get();

        $service = new QuestProgressionService();

        foreach ($instances as $instance) {
            $service->progress($instance, $task, $times);
        }
    }
}
