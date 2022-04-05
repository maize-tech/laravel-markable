<?php

namespace Maize\Markable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Maize\Markable\Exceptions\InvalidMarkInstanceException;
use Maize\Markable\Scopes\MarkableScope;

trait Markable
{
    public static function bootMarkable(): void
    {
        static::registerMarks();
        static::addGlobalScope(new MarkableScope);
    }

    public static function marks(): array
    {
        return static::$marks ?? [];
    }

    public function scopeWhereHasMark(Builder $builder, Mark $mark, Model $user, ?string $value = null): Builder
    {
        return $builder->whereHas(
            $mark->markableRelationName(),
            fn (Builder $b) => $b->where([
                $mark->getQualifiedUserIdColumn() => $user->getKey(),
                'value' => $value,
            ])
        );
    }

    protected static function registerMarks(): void
    {
        foreach (static::marks() as $mark) {
            static::registerMark($mark);
        }
    }

    protected static function registerMark(string $mark)
    {
        $instance = new $mark;

        if (! $instance instanceof Mark) {
            throw InvalidMarkInstanceException::create();
        }

        static::addMarkableRelation($instance);
    }

    protected static function addMarkableRelation(Mark $mark)
    {
        static::resolveRelationUsing(
            $mark->markableRelationName(),
            fn ($markable) => $markable
                ->morphToMany(config('markable.user_model'), 'markable', $mark->getTable())
                ->using($mark->getMorphClass())
                ->withPivot('value')
                ->withTimestamps()
        );
    }
}
