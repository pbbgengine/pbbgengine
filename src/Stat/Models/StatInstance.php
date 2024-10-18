<?php

declare(strict_types=1);

namespace PbbgEngine\Stat\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
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
        'data' => AsCollection::class,
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
        $stats = Stat::where('model_type', $this->model_type)->get();
        foreach ($stats as $stat) {
            if ($stat->class) {
                /** @var Validator $validator */
                $validator = new $stat->class;
                if (!isset($this->data[$stat->name])) {
                    $this->data[$stat->name] = $validator->default();
                } else {
                    $this->data[$stat->name] = $validator->validate($this->data[$stat->name]);
                }
            }
        }
        return parent::save($options);
    }
}
