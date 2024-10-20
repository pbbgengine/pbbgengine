<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute\Observers;

use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Attribute\AttributeManager;

class AttributeProxyObserver
{
    /**
     * Saves the stats for a model.
     */
    public function saving(Model $model): void {
        if (method_exists($model, 'saveDynamicRelation')) {
            $manager = app(AttributeManager::class);
            foreach (array_keys($manager->types) as $type) {
                $model->saveDynamicRelation($type);
            }

        }
    }
}
