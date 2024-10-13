<?php

declare(strict_types=1);

namespace PbbgEngine\Item\Models;

use Exception;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use PbbgEngine\Item\Events\ItemInteractionEvent;
use PbbgEngine\Item\Interactions\Interaction;

/**
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property int $item_id
 * @property Collection $data
 * @property Item $item
 */
class ItemInstance extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'item_id',
        'data',
    ];

    protected $casts = [
        'data' => AsCollection::class,
    ];

    /**
     * Get the combined data from the item merged into the item instance.
     *
     * @return Collection<string, mixed>
     */
    public function getDataCombinedAttribute(): Collection
    {
        return $this->item->data->merge($this->data);
    }

    /**
     * Get the underlying item model.
     *
     * @return BelongsTo<Item, ItemInstance>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the model that owns the item instance.
     *
     * @return MorphTo<Model, ItemInstance>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Filters item instances that have their own data defined.
     *
     * @param Builder<ItemInstance> $query
     */
    public function scopeUnique(Builder $query): void
    {
        $query->whereNotNull('data');
    }

    /**
     * Performs a specific interaction on an item instance.
     * Returns a message bag containing the result of the interaction.
     * An exception may be thrown to handle misconfigurations.
     *
     * @param string $class
     * @return MessageBag
     * @throws Exception
     */
    public function interact(string $class): MessageBag
    {
        if (!class_exists($class)) {
            throw new Exception("interaction $class does not exist");
        }

        if (!is_subclass_of($class, Interaction::class)) {
            throw new Exception("class $class does not implement interaction");
        }

        if ($this->item->relationLoaded('interactions')) {
            $interaction = $this->item->interactions->where('class', $class)->first();
        } else {
            $interaction = $this->item->interactions()->where('class', $class)->first();
        }

        if (!$interaction) {
            throw new Exception("item $this->item_id does not have interaction: $class");
        }

        $handler = new $class;
        $messages = $handler->handle($this);

        event(new ItemInteractionEvent($this, $handler, $messages));

        return $messages;
    }
}
