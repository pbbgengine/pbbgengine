<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PbbgEngine\Quest\Models\QuestInstance;

/**
 * @mixin Model
 */
trait HasQuests
{
    /**
     * Get item instances that belong to this model.
     *
     * @return HasMany<QuestInstance>
     */
    public function quests(): HasMany
    {
        return $this->hasMany(QuestInstance::class, 'model_id', $this->primaryKey)
            ->where('model_type', self::class)
            ->with('quest');
    }
}
