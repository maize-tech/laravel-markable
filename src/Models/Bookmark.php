<?php

namespace Maize\Markable\Models;

use Maize\Markable\Mark;

class Bookmark extends Mark
{
    public static function markableRelationName(): string
    {
        return 'bookmarkers';
    }
}
