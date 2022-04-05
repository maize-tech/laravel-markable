<?php

namespace Maize\Markable\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Maize\Markable\Markable;

class ModelWithInvalidMark extends Model
{
    use Markable;

    protected static $marks = [
        Article::class,
    ];
}
