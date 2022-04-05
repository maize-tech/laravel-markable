<?php

namespace Maize\Markable\Models;

use Maize\Markable\Mark;

class Reaction extends Mark
{
    public static function markableRelationName(): string
    {
        return 'reacters';
    }
}
