<?php

namespace Maize\Markable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maize\Markable\Exceptions\InvalidMarkableInstanceException;
use Maize\Markable\Exceptions\InvalidMarkValueException;
use Illuminate\Contracts\Auth\Authenticatable;

abstract class Mark extends MorphPivot
{
    public $incrementing = true;

    protected $casts = [
        'metadata' => 'array',
    ];

    abstract public static function markableRelationName(): string;

    public static function markRelationName(): string
    {
        return Str::of(
            class_basename(static::class)
        )->plural()->lower()->__toString();
    }

    public static function allowedValues(): array|string|null
    {
        $className = Str::lower(static::getMarkClassName());

        return config("markable.allowed_values.{$className}");
    }

    public static function getMarkClassName(): string
    {
        return Str::of(class_basename(static::class))
            ->__toString();
    }

    public static function add(Model $markable, Model | Authenticatable $user, string $value = null, array $metadata = []): self
    {
        static::validMarkable($markable);

        if (! static::hasAllowedValues($value)) {
            throw InvalidMarkValueException::create();
        }

        $attributes = [
            app(static::class)->getUserIdColumn() => $user->getKey(),
            'markable_id' => $markable->getKey(),
            'markable_type' => $markable->getMorphClass(),
            'value' => $value,
        ];

        $values = collect([
            'metadata' => $metadata,
        ])->when(
            value: static::forceSingleValuePerUser(),
            callback: fn (Collection $values) => $values
                ->add(Arr::pull($attributes, 'value'))
        )->toArray();

        return static::firstOrCreate($attributes, $values);
    }

    public static function remove(Model $markable, Model | Authenticatable $user, string $value = null)
    {
        static::validMarkable($markable);

        return static::where([
            app(static::class)->getUserIdColumn() => $user->getKey(),
            'markable_id' => $markable->getKey(),
            'markable_type' => $markable->getMorphClass(),
            'value' => $value,
        ])->get()->each->delete();
    }

    public static function count(Model $markable, string $value = null): int
    {
        static::validMarkable($markable);

        return static::where([
            'markable_id' => $markable->getKey(),
            'markable_type' => $markable->getMorphClass(),
            'value' => $value,
        ])->count();
    }

    public static function has(Model $markable, Model | Authenticatable $user, string $value = null): bool
    {
        return static::where([
            app(static::class)->getUserIdColumn() => $user->getKey(),
            'markable_id' => $markable->getKey(),
            'markable_type' => $markable->getMorphClass(),
            'value' => $value,
        ])->exists();
    }

    public static function toggle(Model $markable, Model | Authenticatable $user, string $value = null, array $metadata = [])
    {
        return static::has($markable, $user, $value)
            ? static::remove($markable, $user, $value)
            : static::add($markable, $user, $value, $metadata);
    }

    public static function hasAllowedValues(?string $value): bool
    {
        $allowedValues = static::allowedValues() ?? [null];

        if ($allowedValues === '*') {
            return true;
        }

        return in_array($value, $allowedValues);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('markable.user_model'));
    }

    public function markable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getUserIdColumn(): string
    {
        return defined('static::USER_ID') ? static::USER_ID : 'user_id';
    }

    public function getQualifiedUserIdColumn(): string
    {
        return $this->qualifyColumn($this->getUserIdColumn());
    }

    public function getTable(): string
    {
        if (is_null($this->table)) {
            $this->setTable(
                config('markable.table_prefix', 'markable_').
                Str::snake(Str::pluralStudly(class_basename($this)))
            );
        }

        return $this->table;
    }

    protected static function forceSingleValuePerUser(): bool
    {
        return false;
    }

    protected static function validMarkable(Model $markable): bool
    {
        if (! static::usesMarkableTrait($markable)) {
            throw InvalidMarkableInstanceException::create();
        }

        if (! in_array(static::class, $markable::marks())) {
            throw InvalidMarkableInstanceException::create();
        }

        return true;
    }

    protected static function usesMarkableTrait(Model $markable): bool
    {
        return in_array(
            Markable::class,
            trait_uses_recursive(get_class($markable))
        );
    }
}
