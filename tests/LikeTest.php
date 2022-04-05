<?php

namespace Maize\Markable\Tests;

use Maize\Markable\Models\Like;
use Maize\Markable\Tests\Models\Article;
use Maize\Markable\Tests\Models\Post;
use Maize\Markable\Tests\Models\User;

class LikeTest extends TestCase
{
    /** @test */
    public function can_filter_models_with_like()
    {
        $articles = Article::factory(5)->create();
        $posts = Post::factory(2)->create();
        $users = User::factory(2)->create();

        $this->assertCount(0, Article::whereHasLike($users[0])->get());
        $this->assertCount(0, Article::whereHasLike($users[1])->get());

        Like::add($articles[0], $users[0]);
        $this->assertCount(1, Article::whereHasLike($users[0])->get());
        $this->assertCount(0, Article::whereHasLike($users[1])->get());

        Like::add($articles[2], $users[0]);
        $this->assertCount(2, Article::whereHasLike($users[0])->get());
        $this->assertCount(0, Article::whereHasLike($users[1])->get());

        Like::add($articles[2], $users[1]);
        $this->assertCount(2, Article::whereHasLike($users[0])->get());
        $this->assertCount(1, Article::whereHasLike($users[1])->get());

        Like::add($posts[0], $users[0]);
        $this->assertCount(2, Article::whereHasLike($users[0])->get());
        $this->assertCount(1, Article::whereHasLike($users[1])->get());
    }
}
