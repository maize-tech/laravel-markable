<?php

namespace Maize\Markable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Maize\Markable\Exceptions\InvalidMarkInstanceException;
use Maize\Markable\Scopes\MarkableScope;
use Illuminate\Contracts\Auth\Authenticatable;

trait Markable
{
    public static function bootMarkable(): void
    {
        static::registerMarks();

        static::addGlobalScope(new MarkableScope);

        static::deleting(
            fn ($markable) => self::deleteMarks($markable)
        );
    }

    public static function marks(): array
    {
        return static::$marks ?? [];
    }

    public function scopeWhereHasMark(Builder $builder, Mark $mark, Model | Authenticatable $user, string $value = null): Builder
    {
        return $builder->whereHas(
            $mark->markableRelationName(),
            fn (Builder $b) => $b->where([
                $mark->getQualifiedUserIdColumn() => $user->getKey(),
                'value' => $value,
            ])
        );
    }

    protected static function deleteMarks(self $markable): void
    {
        foreach (static::marks() as $markClass) {
            $markModel = self::getMarkModelInstance($markClass);

            $markRelationName = $markModel->markRelationName();

            $markable->$markRelationName()->delete();
        }
    }

    protected static function registerMarks(): void
    {
        foreach (static::marks() as $markClass) {
            static::addMarkableRelation($markClass);
        }
    }

    protected static function addMarkableRelation(string $markClass): void
    {
        $markModel = self::getMarkModelInstance($markClass);

        static::resolveRelationUsing(
            $markModel->markableRelationName(),
            fn ($markable) => $markable
                ->morphToMany(config('markable.user_model'), 'markable', $markModel->getTable())
                ->using($markModel->getMorphClass())
                ->withPivot('value')
                ->withTimestamps()
        );

        static::resolveRelationUsing(
            $markModel->markRelationName(),
            fn ($markable) => $markable
                ->morphMany($markClass, 'markable')
        );
    }

    protected static function getMarkModelInstance(string $markClass): Mark
    {
        $instance = new $markClass;

        if (! $instance instanceof Mark) {
            throw InvalidMarkInstanceException::create();
        }

        return $instance;
    }
}
