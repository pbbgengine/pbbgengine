<?php

declare(strict_types=1);

namespace PbbgEngine\Stat\Observers;

use Illuminate\Database\Eloquent\Model;

class StatsProxyObserver
{
    /**
     * Saves the stats for a model.
     */
    public function saving(Model $model): void {
        if (method_exists($model, 'saveStats')) {
            $model->saveStats();
        }
    }
}
