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
 * @property Collection $stats
 */
class Stats extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'stats',
    ];

    protected $casts = [
        'stats' => AsValidatedCollection::class,
    ];

    /**
     * Get the model that the stats belong to.
     *
     * @return MorphTo<Model, Stats>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function save(array $options = []): bool
    {
        $stats = app(StatService::class)->stats[$this->model_type] ?? [];
        foreach ($stats as $stat => $class) {
            if (is_subclass_of($class, Validator::class)) {
                $validator = new $class;
                if (!isset($this->stats[$stat])) {
                    $this->stats[$stat] = $validator->default();
                } else {
                    $this->stats[$stat] = $validator->validate($this->stats[$stat]);
                }
            }
        }
        return parent::save($options);
    }
}
