<?php

namespace Maize\Markable\Tests;

use Maize\Markable\Exceptions\InvalidMarkInstanceException;
use Maize\Markable\Markable;
use Maize\Markable\Models\Like;
use Maize\Markable\Tests\Models\Article;
use Maize\Markable\Tests\Models\ModelWithInvalidMark;
use Maize\Markable\Tests\Models\Post;
use Maize\Markable\Tests\Models\User;

class MarkableTest extends TestCase
{
    /** @test */
    public function can_not_register_an_invalid_mark_in_markabale_model_fail()
    {
        $this->expectException(InvalidMarkInstanceException::class);

        $markable = new ModelWithInvalidMark;
    }

    /** @test */
    public function can_register_a_mark_in_markable_model()
    {
        $markable = new Article;

        $this->assertInstanceOf(Article::class, $markable);

        $this->assertContains(Markable::class, trait_uses_recursive($markable));
    }

    /** @test */
    public function can_filter_marked_models()
    {
        $articles = Article::factory(5)->create();
        $posts = Post::factory(2)->create();
        $users = User::factory(2)->create();

        $this->assertCount(0, Article::whereHasMark(app(Like::class), $users[0])->get());
        $this->assertCount(0, Article::whereHasMark(app(Like::class), $users[1])->get());

        Like::add($articles[0], $users[0]);
        $this->assertCount(1, Article::whereHasMark(app(Like::class), $users[0])->get());
        $this->assertCount(0, Article::whereHasMark(app(Like::class), $users[1])->get());

        Like::add($articles[2], $users[0]);
        $this->assertCount(2, Article::whereHasMark(app(Like::class), $users[0])->get());
        $this->assertCount(0, Article::whereHasMark(app(Like::class), $users[1])->get());

        Like::add($articles[2], $users[1]);
        $this->assertCount(2, Article::whereHasMark(app(Like::class), $users[0])->get());
        $this->assertCount(1, Article::whereHasMark(app(Like::class), $users[1])->get());

        Like::add($posts[0], $users[0]);
        $this->assertCount(2, Article::whereHasMark(app(Like::class), $users[0])->get());
        $this->assertCount(1, Article::whereHasMark(app(Like::class), $users[1])->get());
    }

    /** @test */
    public function it_should_delete_marks_related_to_deleted_markables()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();

        $mark = Like::add($article, $user);

        $this->assertDatabaseHas($mark->getTable(), [
            'user_id' => $user->getKey(),
            'markable_id' => $article->getKey(),
            'markable_type' => $article->getMorphClass(),
        ]);

        $article->delete();

        $this->assertDatabaseMissing($mark->getTable(), [
            'user_id' => $user->getKey(),
            'markable_id' => $article->getKey(),
            'markable_type' => $article->getMorphClass(),
        ]);
    }

    /** @test */
    public function it_should_retrieve_the_list_of_users_who_marked_an_entity()
    {
        $article = Article::factory()->create();
        $users = User::factory(2)->create();

        Like::add($article, $users[0]);
        Like::add($article, $users[1]);

        $this->assertEquals([
            $users[0]->getKey(),
            $users[1]->getKey(),
        ], $article->likers->modelKeys());
    }

    /** @test */
    public function it_should_retrieve_the_list_of_marks_of_an_entity()
    {
        $article = Article::factory()->create();
        $users = User::factory(2)->create();

        $like1 = Like::add($article, $users[0]);
        $like2 = Like::add($article, $users[1]);

        $this->assertEquals([
            $like1->getKey(),
            $like2->getKey(),
        ], $article->likes->modelKeys());
    }
}
