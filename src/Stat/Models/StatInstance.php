<?php

declare(strict_types=1);

namespace PbbgEngine\Stat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use PbbgEngine\Stat\AsValidatedCollection;
use PbbgEngine\Stat\StatService;
use PbbgEngine\Stat\Validators\Validator;

/**
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property Collection $data
 */
class StatInstance extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'data',
    ];

    protected $casts = [
        'data' => AsValidatedCollection::class,
    ];

    /**
     * Get the model that the stats belong to.
     *
     * @return MorphTo<Model, StatInstance>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function save(array $options = []): bool
    {
        $stats = app(StatService::class)->stats[$this->model_type] ?? [];
        foreach ($stats as $stat => $class) {
            if ($class) {
                /** @var Validator $validator */
                $validator = new $class;
                if (!isset($this->data[$stat])) {
                    $this->data[$stat] = $validator->default();
                } else {
                    $this->data[$stat] = $validator->validate($this->data[$stat]);
                }
            }
        }
        return parent::save($options);
    }
}
