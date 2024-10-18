<?php

declare(strict_types=1);

namespace PbbgEngine\Stat;

use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Stat\Validators\Validator;

class StatService
{
    /**
     * @var array<class-string<Model>, array<string, class-string<Validator>>>
     */
    public array $stats = [];
}
