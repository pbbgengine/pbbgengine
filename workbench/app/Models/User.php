<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use PbbgEngine\Attribute\Concerns\HasDynamicAttributes;
use PbbgEngine\Attribute\Models\Attributes;
use PbbgEngine\Item\Concerns\HasItems;
use PbbgEngine\Quest\Concerns\HasQuests;
use Workbench\Database\Factories\UserFactory;

/**
 * @property Collection $stats
 * @property Collection $resources
 * @method HasOne<Attributes> stats()
 * @method HasOne<Attributes> resources()
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use HasItems, HasQuests, HasDynamicAttributes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'group_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return UserFactory<User>
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * @return BelongsTo<Group, User>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }


    /**
     * @return array<int, Group|null>
     */
    public function getRelatedQuestModels(): array
    {
        return [$this->group];
    }
}
