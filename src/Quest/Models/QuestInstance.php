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

    /**
     * Applies quest progression for the given objective.
     * Performs quest transitions that are applicable to the completed
     * objective, stage and quests.
     */
    public function progress(QuestObjective $objective, int $times): void
    {
        $this->updateProgress($objective, $times);

        if ($this->isObjectiveComplete($objective)) {
            $this->handleTransitions($objective->transitions);
            if ($objective->stage) {
                $this->checkStageCompletion($objective->stage);
            }
        }
    }

    /**
     * Update the progress of the given objective.
     */
    private function updateProgress(QuestObjective $objective, int $times): void
    {
        $progress = $this->progress->get($objective->id, 0) + $times;
        $progress = min($progress, $objective->times_required);
        $this->progress->put($objective->id, $progress);
        $this->save();
    }

    /**
     * Check if the given objective is complete.
     */
    private function isObjectiveComplete(QuestObjective $objective): bool
    {
        return $this->progress->get($objective->id, 0) >= $objective->times_required;
    }

    /**
     * Handle quest transitions for the given collection of transitions.
     *
     * @param Collection<int, QuestTransition> $transitions
     * @throws Exception
     */
    private function handleTransitions(Collection $transitions): void
    {
        foreach ($transitions as $transition) {
            if (!isset(QuestService::$transitions[$transition->actionable_type])) {
                throw new Exception("invalid transition actionable type: $transition->actionable_type");
            }
            $handler = new QuestService::$transitions[$transition->actionable_type]();
            $handler->handle($this, $transition);
        }
    }

    /**
     * Check if the given stage is complete and handle any applicable transitions.
     */
    private function checkStageCompletion(QuestStage $stage): void
    {
        if (!$this->areAllObjectivesComplete($stage->objectives)) {
            return;
        }

        $this->handleTransitions($stage->transitions);
        if ($stage->quest !== null) {
            $this->checkQuestCompletion($stage->quest);
        }
    }

    /**
     * Check if all objectives in the given collection are complete.
     *
     * @param Collection<int, QuestObjective> $objectives
     */
    private function areAllObjectivesComplete(Collection $objectives): bool
    {
        foreach ($objectives as $objective) {
            if ($this->progress->get($objective->id, 0) < $objective->times_required) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if the given quest is complete and handle any applicable transitions.
     */
    private function checkQuestCompletion(Quest $quest): void
    {
        $this->handleTransitions($quest->transitions);
    }
}
