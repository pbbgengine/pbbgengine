<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute\Observers;

use PbbgEngine\Attribute\AttributeManager;
use PbbgEngine\Attribute\Exceptions\InvalidAttributeHandler;
use PbbgEngine\Attribute\Models\Attributes;
use PbbgEngine\Attribute\Validators\Validator;

class AttributeObserver
{
    public function saving(Attributes $model): void
    {
        $column = $model->name;
        $manager = app(AttributeManager::class);
        $service = app($manager->types[$column]);

        $stats = $service->handlers[$model->model_type] ?? [];
        foreach ($stats as $stat => $class) {
            if (!is_subclass_of($class, $service->handler)) {
                throw new InvalidAttributeHandler($class);
            }
            /** @var Validator $validator */
            $validator = new $class($model->model);
            if (!isset($model->attribute[$stat])) {
                $model->attribute[$stat] = $validator->default();
            } else {
                $model->attribute[$stat] = $validator->validate($model->attribute[$stat]);
            }
        }
    }
}
