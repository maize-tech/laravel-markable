<?php

namespace Maize\Markable\Models;

use Maize\Markable\Mark;

class Favorite extends Mark
{
    public static function markableRelationName(): string
    {
        return 'favoriters';
    }
}
