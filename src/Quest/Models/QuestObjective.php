<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $quest_stage_id
 * @property string $name
 * @property string $task
 * @property int $times_required
 */
class QuestObjective extends Model
{
    protected $fillable = ['name', 'quest_stage_id', 'task', 'times_required'];

    /**
     * Get the stage that the objective belongs to.
     *
     * @return BelongsTo<QuestStage, QuestObjective>
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(QuestStage::class);
    }

    /**
     * Get the transitions associated with the quest objective completion.
     *
     * @return HasMany<QuestTransition>
     */
    public function transitions(): HasMany
    {
        return $this->hasMany(QuestTransition::class, 'triggerable_id', $this->primaryKey)
            ->where('triggerable_type', self::class);
    }
}
