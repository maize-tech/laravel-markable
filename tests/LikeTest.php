<?php

use Maize\Markable\Models\Like;
use Maize\Markable\Tests\Models\Article;
use Maize\Markable\Tests\Models\Post;
use Maize\Markable\Tests\Models\User;

it('can filter models with like', function () {
    $articles = Article::factory(5)->create();
    $posts = Post::factory(2)->create();
    $users = User::factory(2)->create();

    expect(Article::whereHasLike($users[0])->get())->toHaveCount(0);
    expect(Article::whereHasLike($users[1])->get())->toHaveCount(0);

    Like::add($articles[0], $users[0]);
    expect(Article::whereHasLike($users[0])->get())->toHaveCount(1);
    expect(Article::whereHasLike($users[1])->get())->toHaveCount(0);

    Like::add($articles[2], $users[0]);
    expect(Article::whereHasLike($users[0])->get())->toHaveCount(2);
    expect(Article::whereHasLike($users[1])->get())->toHaveCount(0);

    Like::add($articles[2], $users[1]);
    expect(Article::whereHasLike($users[0])->get())->toHaveCount(2);
    expect(Article::whereHasLike($users[1])->get())->toHaveCount(1);

    Like::add($posts[0], $users[0]);
    expect(Article::whereHasLike($users[0])->get())->toHaveCount(2);
    expect(Article::whereHasLike($users[1])->get())->toHaveCount(1);
});
