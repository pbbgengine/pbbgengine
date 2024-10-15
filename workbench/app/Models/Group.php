<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Quest\Concerns\HasQuests;
use Workbench\Database\Factories\GroupFactory;

class Group extends Model
{
    /** @use HasFactory<GroupFactory> */
    use HasFactory;

    use HasQuests;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name'];

    /**
     * Create a new factory instance for the model.
     *
     * @return GroupFactory<Group>
     */
    protected static function newFactory(): GroupFactory
    {
        return GroupFactory::new();
    }
}
