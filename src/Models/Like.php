<?php

namespace Maize\Markable\Models;

use Maize\Markable\Mark;

class Like extends Mark
{
    public static function markableRelationName(): string
    {
        return 'likers';
    }
}
