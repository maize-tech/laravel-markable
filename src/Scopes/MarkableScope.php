<?php

namespace Maize\Markable\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Maize\Markable\Mark;

class MarkableScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        //
    }

    public function extend(Builder $builder)
    {
        $marks = $builder->getModel()::marks();

        foreach ($marks as $mark) {
            $this->addWhereHasMark($builder, new $mark);
        }
    }

    protected function addWhereHasMark(Builder $builder, Mark $mark): void
    {
        $builder->macro(
            "whereHas{$mark::getMarkClassName()}",
            fn (Builder $b, $user, ?string $value = null) => $b->whereHasMark($mark, $user, $value)
        );
    }
}
