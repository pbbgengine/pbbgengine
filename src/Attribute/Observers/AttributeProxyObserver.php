<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute\Observers;

use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Attribute\AttributeRegistry;

class AttributeProxyObserver
{
    /**
     * Saves the stats for a model.
     */
    public function saving(Model $model): void {
        if (method_exists($model, 'saveDynamicRelation')) {
            $registry = app(AttributeRegistry::class);
            foreach (array_keys($registry->handlers) as $type) {
                $model->saveDynamicRelation($type);
            }
        }
    }
}
