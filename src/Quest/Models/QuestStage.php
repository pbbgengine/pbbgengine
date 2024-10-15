<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property int $quest_id
 */
class QuestStage extends Model
{
    protected $fillable = ['name', 'quest_id'];

    /**
     * Get the quest that the stage belongs to.
     *
     * @return BelongsTo<Quest, QuestStage>
     */
    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }

    /**
     * Get the objectives of the stage.
     *
     * @return HasMany<QuestObjective>
     */
    public function objectives(): HasMany
    {
        return $this->hasMany(QuestObjective::class);
    }

    /**
     * Get the transitions associated with the quest stage completion.
     *
     * @return HasMany<QuestTransition>
     */
    public function transitions(): HasMany
    {
        return $this->hasMany(QuestTransition::class, 'triggerable_id', $this->primaryKey)
            ->where('triggerable_type', self::class);
    }
}
