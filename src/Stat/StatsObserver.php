<?php

declare(strict_types=1);

namespace PbbgEngine\Stat;

use Illuminate\Database\Eloquent\Model;

class StatsObserver
{
    public function saving(Model $model): void {
        if (method_exists($model, 'saveStats')) {
            $model->saveStats();
        }
    }
}
