<?php

namespace Maize\Markable\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
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
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('markable.user_model', User::class);
        $app['config']->set('markable.allowed_values.reaction', [
            'reaction_1',
            'reaction_2',
            'reaction_3',
            'reaction_4',
        ]);

        include_once __DIR__.'/../database/migrations/create_users_table.php.stub';
        (new \CreateUsersTable())->up();

        include_once __DIR__.'/../database/migrations/create_articles_table.php.stub';
        (new \CreateArticlesTable())->up();

        include_once __DIR__.'/../database/migrations/create_posts_table.php.stub';
        (new \CreatePostsTable())->up();

        include_once __DIR__.'/../database/migrations/create_likes_table.php.stub';
        (new \CreateLikesTable())->up();

        include_once __DIR__.'/../database/migrations/create_favorites_table.php.stub';
        (new \CreateFavoritesTable())->up();

        include_once __DIR__.'/../database/migrations/create_bookmarks_table.php.stub';
        (new \CreateBookmarksTable())->up();

        include_once __DIR__.'/../database/migrations/create_reactions_table.php.stub';
        (new \CreateReactionsTable())->up();
    }
}
