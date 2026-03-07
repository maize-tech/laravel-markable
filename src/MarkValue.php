<?php

namespace Maize\Markable;

class MarkValue
{
    public static function resolve(null|string|\BackedEnum $value): ?string
    {
        return $value instanceof \BackedEnum ? (string) $value->value : $value;
    }

    public static function resolveAll(array $values): array
    {
        return array_map(fn ($v) => static::resolve($v), $values);
    }
}
