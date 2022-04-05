<?php

namespace Maize\Markable\Tests;

use Maize\Markable\Exceptions\InvalidMarkableInstanceException;
use Maize\Markable\Models\Bookmark;
use Maize\Markable\Tests\Models\Article;
use Maize\Markable\Tests\Models\Post;
use Maize\Markable\Tests\Models\User;

class BookmarkTest extends TestCase
{
    /** @test */
    public function can_filter_models_with_bookmark()
    {
        $articles = Article::factory(5)->create();
        $posts = Post::factory(2)->create();
        $users = User::factory(2)->create();

        $this->assertCount(0, Article::whereHasBookmark($users[0])->get());
        $this->assertCount(0, Article::whereHasBookmark($users[1])->get());

        Bookmark::add($articles[0], $users[0]);
        $this->assertCount(1, Article::whereHasBookmark($users[0])->get());
        $this->assertCount(0, Article::whereHasBookmark($users[1])->get());

        Bookmark::add($articles[2], $users[0]);
        $this->assertCount(2, Article::whereHasBookmark($users[0])->get());
        $this->assertCount(0, Article::whereHasBookmark($users[1])->get());

        Bookmark::add($articles[2], $users[1]);
        $this->assertCount(2, Article::whereHasBookmark($users[0])->get());
        $this->assertCount(1, Article::whereHasBookmark($users[1])->get());

        $this->expectException(InvalidMarkableInstanceException::class);
        Bookmark::add($posts[0], $users[0]);
    }
}
