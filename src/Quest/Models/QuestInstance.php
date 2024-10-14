<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use PbbgEngine\Quest\QuestService;
use PbbgEngine\Quest\Transitions\Transition;

/**
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property int $quest_id
 * @property int $current_quest_stage_id
 * @property Collection $progress
 * @property ?Carbon $completed_at
 */
class QuestInstance extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'quest_id',
        'current_quest_stage_id',
        'progress',
        'completed_at',
    ];

    protected $casts = [
        'progress' => AsCollection::class,
        'completed_at' => 'datetime',
    ];

    /**
     * Get the underlying quest model.
     *
     * @return BelongsTo<Quest, QuestInstance>
     */
    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }

    /**
     * Get the model that owns the quest instance.
     *
     * @return MorphTo<Model, QuestInstance>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
