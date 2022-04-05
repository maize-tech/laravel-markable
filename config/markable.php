<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User model
    |--------------------------------------------------------------------------
    |
    | Here you may specify the fully qualified class name of the user model class.
    |
    */

    'user_model' => App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Table prefix
    |--------------------------------------------------------------------------
    |
    | Here you may specify the prefix for all mark tables.
    | If set, all migrations should be named with the given prefix and
    | the mark's class name.
    |
    */

    'table_prefix' => 'markable_',

    /*
    |--------------------------------------------------------------------------
    | Allowed values
    |--------------------------------------------------------------------------
    |
    | Here you may specify the list of allowed values for each mark type.
    | If a specific mark should not accept any values, you can avoid adding it
    | to the list.
    | The array key name should match the mark's class name in lower case.
    |
    */

    'allowed_values' => [
        'reaction' => [],
    ],
];
