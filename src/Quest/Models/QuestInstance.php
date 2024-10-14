<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property int $quest_id
 * @property int $current_quest_stage_id
 * @property Collection $progress
 * @property Carbon $completed_at
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

    /**
     * Applies quest progression for the given objective.
     * Performs quest transitions that are applicable to the completed
     * objective, stage and quests.
     *
     * @param QuestObjective $objective
     * @param int $times
     */
    public function progress(QuestObjective $objective, int $times): void
    {
        $progress = $this->progress->get($objective->id, 0) + $times;
        $progress = $progress > $objective->times_required ? $objective->times_required : $progress;
        $this->progress->put($objective->id, $progress);
        $this->save();



        if ($progress < $objective->times_required) {
            return;
        }

        // objective complete
        if ($objective->transitions !== null) {
            foreach ($objective->transitions as $transition) {
                switch ($transition->actionable_type) {
                    case QuestStage::class:
                        $this->current_quest_stage_id = $transition->actionable_id;
                        $this->save();
                        break;
                    case Quest::class:
                        if ($this->quest_id === $transition->actionable_id) {
                            $this->completed_at = now();
                            $this->save();
                        } else {
                            $this->model->quests()->create([
                                'model_type' => $this->model::class,
                                'quest_id' => $transition->actionable_id,
                                'progress' => [],
                                'current_quest_stage_id' => $transition->actionable->initial_quest_stage_id,
                            ]);
                        }
                        break;
                }
            }
        }

        $stage = $objective->stage;
        if (!$stage) {
            return;
        }
        foreach ($stage->objectives as $objective) {
            $progress = $this->progress->get($objective->id, 0);
            if ($progress < $objective->times_required) {
                return;
            }
        }

        // stage complete

        if ($stage->transitions !== null) {
            foreach ($stage->transitions as $transition) {
                switch ($transition->actionable_type) {
                    case QuestStage::class:
                        $this->current_quest_stage_id = $transition->actionable_id;
                        $this->save();
                        break;
                    case Quest::class:
                        if ($this->quest_id === $transition->actionable_id) {
                            $this->completed_at = now();
                            $this->save();
                        } else {
                            $this->model->quests()->create([
                                'model_type' => $this->model::class,
                                'quest_id' => $transition->actionable_id,
                                'progress' => [],
                                'current_quest_stage_id' => $transition->actionable->initial_quest_stage_id,
                            ]);
                        }
                        break;
                }
            }
        }

        // quest complete

        $quest = $stage->quest;
        if (!$quest) {
            return;
        }

        if ($quest->transitions !== null) {
            foreach ($quest->transitions as $transition) {
                switch ($transition->actionable_type) {
                    case QuestStage::class:
                        $this->current_quest_stage_id = $transition->actionable_id;
                        $this->save();
                        break;
                    case Quest::class:
                        if ($this->quest_id === $transition->actionable_id) {
                            $this->completed_at = now();
                            $this->save();
                        } else {
                            $this->model->quests()->create([
                                'model_type' => $this->model::class,
                                'quest_id' => $transition->actionable_id,
                                'progress' => [],
                                'current_quest_stage_id' => $transition->actionable->initial_quest_stage_id,
                            ]);
                        }
                        break;
                }
            }
        }
    }
}
