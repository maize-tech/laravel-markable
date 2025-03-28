<?php

namespace Maize\Markable\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Maize\Markable\Markable;
use Maize\Markable\Models\Like;

class Post extends Model
{
    use HasFactory;
    use Markable;

    protected static $marks = [
        Like::class,
    ];
}
