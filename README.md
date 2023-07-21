<p align="center">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="/art/socialcard-dark.png">
  <source media="(prefers-color-scheme: light)" srcset="/art/socialcard-light.png">
  <img src="/art/socialcard-light.png" alt="Social Card of Laravel Markable">
</picture>
</p>

# Laravel Markable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/maize-tech/laravel-markable.svg?style=flat-square)](https://packagist.org/packages/maize-tech/laravel-markable)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/maize-tech/laravel-markable/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/maize-tech/laravel-markable/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/maize-tech/laravel-markable/php-cs-fixer.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/maize-tech/laravel-markable/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/maize-tech/laravel-markable.svg?style=flat-square)](https://packagist.org/packages/maize-tech/laravel-markable)

This package allows you to easily add the markable feature to your application, as for example likes, bookmarks, favorites and so on.

## Installation

You can install the package via composer:

```bash
composer require maize-tech/laravel-markable
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="markable-migration-bookmark" # publishes bookmark migration
php artisan vendor:publish --tag="markable-migration-favorite" # publishes favorite migration
php artisan vendor:publish --tag="markable-migration-like"  # publishes like migration
php artisan vendor:publish --tag="markable-migration-reaction"  # publishes reaction migration

php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --tag="markable-config"
```

This is the content of the published config file:

```php
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
```

## Usage

### Basic

To use the package, add the `Maize\Markable\Markable` trait to the model where you want to have marks.

Once done, you can define the list of possible marks for the given model implementing the `$marks` array with the list of mark classes' namespace.

Here's an example model including the `Markable` trait and implementing the `Like` mark:

``` php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Maize\Markable\Markable;
use Maize\Markable\Models\Like;

class Course extends Model
{
    use Markable;

    protected $fillable = [
        'title',
        'description',
    ];

    protected static $marks = [
        Like::class,
    ];
}
```

You can now assign likes to the model:

``` php
use App\Models\Course;
use Maize\Markable\Models\Like;

$course = Course::firstOrFail();
$user = auth()->user();

Like::add($course, $user); // marks the course liked for the given user

Like::remove($course, $user); // unmarks the course liked for the given user

Like::toggle($course, $user); // toggles the course like for the given user

Like::has($course, $user); // returns whether the given user has marked as liked the course or not

Like::count($course); // returns the amount of like marks for the given course
```

### Custom mark model

The package allows you to define custom marks.

First thing you need to do is create a migration which defines the new mark model.
The package works with separate tables for each mark in order to increase the performances when executing related queries.

The migration table name should contain the prefix defined in `table_prefix` attribute under `config/markable.php`.
Default prefix is set to `markable_`.

Here's an example migration for bookmarks:

``` php
return new class extends Migration
{
    public function up()
    {
        Schema::create('markable_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->morphs('markable');
            $table->string('value')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }
}
```

Once done, you can create a new class which extends the abstract `Mark` class and implement the `markableRelationName` method, which is used to retrieve the users who marked a given model entity with the mark entity as pivot.

You can also override the `markRelationName` method, which is used to retrieve the list of marks of a given model entity.
By default, the relation name is the plural name of the mark class name.

Here's an example model for the bookmarks mark:

``` php
<?php

namespace App\Models;

use Maize\Markable\Mark;

class Bookmark extends Mark
{
    public static function markableRelationName(): string
    {
        return 'bookmarkers';
    }
    
    /**
     * The override is useless in this case, as I am returning the default
     * relation name which is the plural name of the mark class name (bookmarks, indeed)
     */
    public static function markRelationName(): string
    {
        return 'bookmarks';
    }
}
```

That's all!
You can now include the custom mark to all models you wish and use it as explained before.

### Working with mark values

You might need a custom mark with a subset of allowed values.

In this case, you can just define your custom mark as explained before and add the list of allowed values in `allowed_values` array under `config/markable.php`.

The array key name should match the mark's class name in lower case.

Here's an example when working with reactions:

``` php
'allowed_values' => [
    'reaction' => [
        'person_raising_hand',
        'heart',
        'kissing_heart',
    ],
],
```

You can then use the custom mark with values:

``` php
use App\Models\Post;
use Maize\Markable\Models\Reaction;

$post = Post::firstOrFail();
$user = auth()->user();

Reaction::add($post, $user, 'kissing_heart'); // adds the 'kissing_heart' reaction to the post for the given user

Reaction::remove($post, $user, 'kissing_heart'); // removes the 'kissing_heart' reaction to the post for the given user

Reaction::toggle($post, $user, 'heart'); // toggles the 'heart' reaction to the post for the given user

Reaction::has($post, $user, 'heart'); // returns whether the user has reacted with the 'heart' reaction to the given post or not

Reaction::count($post, 'person_raising_hand'); // returns the amount of 'person_raising_hand' reactions for the given post
```

### Retrieve the list of marks of an entity with eloquent

``` php
use App\Models\Course;
use App\Models\Post;

Course::firstOrFail()->likes; // returns the collection of like marks related to the course
Post::firstOrFail()->reactions; // returns the collection of reaction marks related to the post 
```

### Retrieve the list of users who marked an entity with eloquent

``` php
use App\Models\Course;
use App\Models\Post;

Course::firstOrFail()->likers; // returns the collection of users who liked the course along with the mark value as pivot
Post::firstOrFail()->reacters; // returns the collection of users who reacted to the post along with the mark value as pivot
```

### Filter marked models with eloquent

``` php
use App\Models\Course;
use App\Models\Post;

Course::whereHasLike(
    auth()->user()
)->get(); // returns all course models with a like from the given user

Post::whereHasReaction(
    auth()->user(),
    'heart'
)->get(); // returns all post models with a 'heart' reaction from the given user
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/maize-tech/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](https://github.com/maize-tech/.github/security/policy) on how to report security vulnerabilities.

## Credits

- [Enrico De Lazzari](https://github.com/enricodelazzari)
- [Riccardo Dalla Via](https://github.com/riccardodallavia)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
