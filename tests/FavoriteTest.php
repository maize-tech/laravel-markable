<?php

use Maize\Markable\Exceptions\InvalidMarkableInstanceException;
use Maize\Markable\Models\Favorite;
use Maize\Markable\Tests\Models\Article;
use Maize\Markable\Tests\Models\Post;
use Maize\Markable\Tests\Models\User;

it('can filter models with favorite', function () {
    $articles = Article::factory(5)->create();
    $posts = Post::factory(2)->create();
    $users = User::factory(2)->create();

    expect(Article::whereHasFavorite($users[0])->get())->toHaveCount(0);
    expect(Article::whereHasFavorite($users[1])->get())->toHaveCount(0);

    Favorite::add($articles[0], $users[0]);
    expect(Article::whereHasFavorite($users[0])->get())->toHaveCount(1);
    expect(Article::whereHasFavorite($users[1])->get())->toHaveCount(0);

    Favorite::add($articles[2], $users[0]);
    expect(Article::whereHasFavorite($users[0])->get())->toHaveCount(2);
    expect(Article::whereHasFavorite($users[1])->get())->toHaveCount(0);

    Favorite::add($articles[2], $users[1]);
    expect(Article::whereHasFavorite($users[0])->get())->toHaveCount(2);
    expect(Article::whereHasFavorite($users[1])->get())->toHaveCount(1);

    expect(fn () => Favorite::add($posts[0], $users[0]))->toThrow(InvalidMarkableInstanceException::class);
});
