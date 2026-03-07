<?php

namespace Maize\Markable\Support;

class Config
{
    public static function getUserModel(): ?string
    {
        return config('markable.user_model');
    }

    public static function getTablePrefix(): string
    {
        return config('markable.table_prefix', 'markable_');
    }

    public static function getAllowedValues(string $className): array|string|null
    {
        return config("markable.allowed_values.{$className}");
    }
}
