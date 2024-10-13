<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int $id
 * @property string $name
 * @property int $initial_quest_stage_id
 */
class Quest extends Model
{
    protected $fillable = ['name', 'initial_quest_stage_id'];

    /**
     * Get the instances of the quest.
     *
     * @return HasMany<QuestInstance>
     */
    public function instances(): HasMany
    {
        return $this->hasMany(QuestInstance::class);
    }

    /**
     * Get the stages of the quest.
     *
     * @return HasMany<QuestStage>
     */
    public function stages(): HasMany
    {
        return $this->hasMany(QuestStage::class);
    }

    /**
     * Get the objectives of the quest.
     *
     * @return HasManyThrough<QuestObjective>
     */
    public function objectives(): HasManyThrough
    {
        return $this->hasManyThrough(QuestObjective::class, QuestStage::class);
    }
}
