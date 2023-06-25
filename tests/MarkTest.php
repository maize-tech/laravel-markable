<?php

namespace Maize\Markable\Tests;

use Maize\Markable\Exceptions\InvalidMarkableInstanceException;
use Maize\Markable\Models\Bookmark;
use Maize\Markable\Models\Favorite;
use Maize\Markable\Models\Like;
use Maize\Markable\Tests\Models\Article;
use Maize\Markable\Tests\Models\Post;
use Maize\Markable\Tests\Models\User;

class MarkTest extends TestCase
{
    /** @test */
    public function can_add_a_mark()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();

        Like::add($article, $user);

        $this->assertDatabaseHas((new Like)->getTable(), [
            'user_id' => $user->getKey(),
            'markable_id' => $article->getKey(),
            'markable_type' => $article->getMorphClass(),
            'value' => null,
        ]);
    }

    /** @test */
    public function can_add_a_mark_with_additional_data()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();

        Favorite::add($article, $user, null, [
            'metadata' => 'test data'
        ]);

        $this->assertDatabaseHas((new Favorite)->getTable(), [
            'user_id' => $user->getKey(),
            'markable_id' => $article->getKey(),
            'markable_type' => $article->getMorphClass(),
            'value' => null,
            'metadata' => 'test data',
        ]);
    }

    /** @test */
    public function cannot_remove_with_an_invalid_markable_type_fail()
    {
        $user = User::factory()->create();

        $this->expectException(InvalidMarkableInstanceException::class);
        Like::remove($user, $user);
    }

    /** @test */
    public function cannot_remove_an_unregistered_mark_fail()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->expectException(InvalidMarkableInstanceException::class);
        Bookmark::remove($post, $user);
    }

    /** @test */
    public function can_remove_a_mark()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();

        Like::add($article, $user);
        Like::remove($article, $user);

        $this->assertDatabaseMissing((new Like)->getTable(), [
            'user_id' => $user->getKey(),
            'markable_id' => $article->getKey(),
            'markable_type' => $article->getMorphClass(),
        ]);
    }

    /** @test */
    public function can_count_marks()
    {
        $articles = Article::factory(2)->create();
        $post = Post::factory()->create();
        $users = User::factory(2)->create();

        $this->assertEquals(0, Like::count($articles[0]));
        $this->assertEquals(0, Like::count($articles[1]));
        $this->assertEquals(0, Like::count($post));

        Like::add($articles[0], $users[0]);
        $this->assertEquals(1, Like::count($articles[0]));
        $this->assertEquals(0, Like::count($articles[1]));
        $this->assertEquals(0, Like::count($post));

        Like::add($articles[0], $users[0]);
        $this->assertEquals(1, Like::count($articles[0]));
        $this->assertEquals(0, Like::count($articles[1]));
        $this->assertEquals(0, Like::count($post));

        Like::add($articles[0], $users[1]);
        $this->assertEquals(2, Like::count($articles[0]));
        $this->assertEquals(0, Like::count($articles[1]));
        $this->assertEquals(0, Like::count($post));

        Like::add($articles[1], $users[0]);
        $this->assertEquals(2, Like::count($articles[0]));
        $this->assertEquals(1, Like::count($articles[1]));
        $this->assertEquals(0, Like::count($post));

        Like::add($post, $users[0]);
        $this->assertEquals(2, Like::count($articles[0]));
        $this->assertEquals(1, Like::count($articles[1]));
        $this->assertEquals(1, Like::count($post));
    }

    /** @test */
    public function can_check_if_user_has_mark()
    {
        $articles = Article::factory(2)->create();
        $post = Post::factory()->create();
        $users = User::factory(2)->create();

        $this->assertFalse(Like::has($articles[0], $users[0]));
        $this->assertFalse(Like::has($articles[0], $users[1]));
        $this->assertFalse(Like::has($articles[1], $users[0]));
        $this->assertFalse(Like::has($articles[1], $users[1]));

        Like::add($articles[0], $users[0]);
        $this->assertTrue(Like::has($articles[0], $users[0]));
        $this->assertFalse(Like::has($articles[0], $users[1]));
        $this->assertFalse(Like::has($articles[1], $users[0]));
        $this->assertFalse(Like::has($articles[1], $users[1]));
    }

    /** @test */
    public function can_toggle_a_mark()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();

        Like::toggle($article, $user);

        $this->assertDatabaseHas((new Like)->getTable(), [
            'user_id' => $user->getKey(),
            'markable_id' => $article->getKey(),
            'markable_type' => $article->getMorphClass(),
            'value' => null,
        ]);

        Like::toggle($article, $user);

        $this->assertDatabaseMissing((new Like)->getTable(), [
            'user_id' => $user->getKey(),
            'markable_id' => $article->getKey(),
            'markable_type' => $article->getMorphClass(),
        ]);
    }

    /** @test */
    public function can_retrieve_markable_morph()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();

        $like = Like::add($article, $user);

        $this->assertInstanceOf(Article::class, $like->markable);

        $this->assertEquals($article->getKey(), $like->markable->getKey());
    }

    /** @test */
    public function can_retrieve_user_relation()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();

        $like = Like::add($article, $user);

        $this->assertInstanceOf(User::class, $like->user);

        $this->assertEquals($user->getKey(), $like->user->getKey());
    }
}
