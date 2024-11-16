<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute\Observers;

use PbbgEngine\Attribute\AttributeRegistry;
use PbbgEngine\Attribute\Exceptions\InvalidAttributeValidator;
use PbbgEngine\Attribute\Models\Attributes;
use PbbgEngine\Attribute\Validators\Validator;

class AttributeObserver
{
    /**
     * @throws InvalidAttributeValidator
     */
    public function saving(Attributes $model): void
    {
        $column = $model->name;
        $registry = app(AttributeRegistry::class);
        $service = $registry->handlers[$column];

        $stats = $service->validators[$model->model_type] ?? [];
        foreach ($stats as $stat => $class) {
            if (!is_subclass_of($class, $service->validator)) {
                throw new InvalidAttributeValidator($class);
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
