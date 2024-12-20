<?php

declare(strict_types=1);

namespace PbbgEngine\Quest;

use Exception;
use PbbgEngine\Quest\Events\TransitionEvent;
use PbbgEngine\Quest\Exceptions\InvalidTransition;
use PbbgEngine\Quest\Exceptions\QuestNotFound;
use PbbgEngine\Quest\Exceptions\StageNotFound;
use PbbgEngine\Quest\Exceptions\UnknownTransition;
use PbbgEngine\Quest\Models\Quest;
use PbbgEngine\Quest\Models\QuestInstance;
use PbbgEngine\Quest\Models\QuestObjective;
use Illuminate\Support\Collection;
use PbbgEngine\Quest\Models\QuestStage;
use PbbgEngine\Quest\Models\QuestTransition;
use PbbgEngine\Quest\Transitions\QuestStageCompleted;
use PbbgEngine\Quest\Transitions\QuestStartedOrCompleted;
use PbbgEngine\Quest\Transitions\Transition;

class QuestProgressionService
{
    /**
     * The actionable model types that can be handled by a quest transition.
     *
     * @var array<string, string>
     */
    public array $transitions = [
        Quest::class => QuestStartedOrCompleted::class,
        QuestStage::class => QuestStageCompleted::class,
    ];

    /**
     * Applies quest progression for the given objective.
     * Performs quest transitions that are applicable to the completed
     * objective, stage and quests.
     */
    public function progress(QuestInstance $instance, string $task, int $times, callable $updateProgress): void
    {
        $quest = $instance->quest;
        if (!$quest) {
            throw new QuestNotFound($instance->quest_id);
        }

        $stage = $quest->stages()->where('id', $instance->current_quest_stage_id)->first();
        if (!$stage) {
            throw new StageNotFound($instance->current_quest_stage_id);
        }

        $objective = $stage->objectives()->where('task', $task)->first();
        if (!$objective) {
            return;
        }

        $updateProgress($instance, $objective, $times);

        if ($this->isObjectiveComplete($instance, $objective)) {
            $this->handleTransitions($instance, $objective->transitions);
            if ($objective->stage) {
                $this->checkStageCompletion($instance, $objective->stage);
            }
        }
    }

    /**
     * Check if the given objective is complete.
     */
    private function isObjectiveComplete(QuestInstance $instance, QuestObjective $objective): bool
    {
        return $instance->progress->get($objective->id, 0) >= $objective->times_required;
    }

    /**
     * Handle quest transitions for the given collection of transitions.
     *
     * @param Collection<int, QuestTransition> $transitions
     * @throws Exception
     */
    private function handleTransitions(QuestInstance $instance, Collection $transitions): void
    {
        foreach ($transitions as $transition) {
            if (!isset($this->transitions[$transition->actionable_type])) {
                throw new UnknownTransition($transition->actionable_type);
            }
            if (!is_subclass_of($this->transitions[$transition->actionable_type], Transition::class)) {
                throw new InvalidTransition($transition->actionable_type);
            }
            $handler = new $this->transitions[$transition->actionable_type]();
            $handler->handle($instance, $transition);
            event(new TransitionEvent($instance, $transition));
        }
    }

    /**
     * Check if the given stage is complete and handle any applicable transitions.
     */
    private function checkStageCompletion(QuestInstance $instance, QuestStage $stage): void
    {
        if (!$this->areAllObjectivesComplete($instance, $stage->objectives)) {
            return;
        }

        $this->handleTransitions($instance, $stage->transitions);
        if ($stage->quest !== null) {
            $this->checkQuestCompletion($instance, $stage->quest);
        }
    }

    /**
     * Check if all objectives in the given collection are complete.
     *
     * @param Collection<int, QuestObjective> $objectives
     */
    private function areAllObjectivesComplete(QuestInstance $instance, Collection $objectives): bool
    {
        foreach ($objectives as $objective) {
            if ($instance->progress->get($objective->id, 0) < $objective->times_required) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if the given quest is complete and handle any applicable transitions.
     */
    private function checkQuestCompletion(QuestInstance $instance, Quest $quest): void
    {
        $this->handleTransitions($instance, $quest->transitions);
    }
}
