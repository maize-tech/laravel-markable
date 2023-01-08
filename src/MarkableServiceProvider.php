<?php

namespace Maize\Markable;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MarkableServiceProvider extends PackageServiceProvider
{
    const MIGRATIONS = [
        'create_bookmarks_table' => 'bookmark',
        'create_favorites_table' => 'favorite',
        'create_likes_table' => 'like',
        'create_reactions_table' => 'reaction',
    ];

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-markable-uuid')
            ->hasConfigFile();
    }

    public function bootingPackage()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $now = now();

        foreach (static::MIGRATIONS as $migrationFileName => $name) {
            $this->publishes([
                $this->package->basePath("/../database/migrations/{$migrationFileName}.php.stub") => $this->generateMigrationName(
                    $migrationFileName,
                    $now->addSecond()
                ), ], "{$this->package->shortName()}-migration-{$name}");
        }
    }
}
