<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $triggerable_type
 * @property int $triggerable_id
 * @property string $actionable_type
 * @property int $actionable_id
 */
class QuestTransition extends Model
{
    protected $fillable = [
        'triggerable_type',
        'triggerable_id',
        'actionable_type',
        'actionable_id',
    ];

    /**
     * Get the model that triggers the quest transition.
     *
     * @return MorphTo<Model, QuestTransition>
     */
    public function triggerable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the model that the quest transition acts upon.
     *
     * @return MorphTo<Model, QuestTransition>
     */
    public function actionable(): MorphTo
    {
        return $this->morphTo();
    }
}
