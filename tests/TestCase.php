<?php

namespace Maize\Markable\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Maize\Markable\MarkableServiceProvider;
use Maize\Markable\Tests\Models\User;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Maize\\Markable\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            MarkableServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('markable.user_model', User::class);
        config()->set('markable.allowed_values.reaction', [
            'reaction_1',
            'reaction_2',
            'reaction_3',
            'reaction_4',
        ]);

        $migration = include __DIR__.'/../database/migrations/create_likes_table.php.stub';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/create_favorites_table.php.stub';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/create_bookmarks_table.php.stub';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/create_reactions_table.php.stub';
        $migration->up();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
}
